<?php

declare(strict_types=1);

namespace App\Services\Backup;

use App\Services\DNS\ZoneService;
use App\Services\DNS\RecordService;
use App\Core\Logger;
use App\Core\Database;
use RuntimeException;
use InvalidArgumentException;

class BackupRestoreService
{
    private const CIPHER = 'aes-256-gcm';
    private const BACKUP_DIR = 'storage/backups';

    private ZoneService $zoneService;
    private RecordService $recordService;
    private Logger $logger;
    private ?Database $database;
    private string $storagePath;

    public function __construct(
        ZoneService $zoneService,
        RecordService $recordService,
        Logger $logger,
        ?Database $database = null,
        ?string $storagePath = null
    ) {
        $this->zoneService = $zoneService;
        $this->recordService = $recordService;
        $this->logger = $logger;
        $this->database = $database;
        $this->storagePath = rtrim($storagePath ?? dirname(__DIR__, 2) . '/' . self::BACKUP_DIR, '/');

        if (!is_dir($this->storagePath)) {
            @mkdir($this->storagePath, 0755, true);
        }
    }

    /**
     * Create a complete cluster zone backup snapshot (encrypted or plain JSON).
     */
    public function createFullBackup(?string $passphrase = null): array
    {
        $allZones = $this->zoneService->getAllZones();
        $snapshot = [
            'version' => '1.0.6',
            'created_at' => date('c'),
            'zone_count' => count($allZones),
            'zones' => [],
        ];

        foreach ($allZones as $zone) {
            $zoneId = $zone['id'] ?? $zone['name'];
            $records = $this->recordService->getRecordsForZone($zoneId);
            $snapshot['zones'][] = [
                'metadata' => $zone,
                'records' => $records,
            ];
        }

        $jsonPayload = json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($jsonPayload === false) {
            throw new RuntimeException('Failed to encode backup JSON payload.');
        }

        $timestamp = date('Ymd_His');
        $isEncrypted = $passphrase !== null && trim($passphrase) !== '';

        if ($isEncrypted) {
            $filename = "pdns_backup_{$timestamp}.enc";
            $fileData = $this->encryptPayload($jsonPayload, $passphrase);
        } else {
            $filename = "pdns_backup_{$timestamp}.json";
            $fileData = $jsonPayload;
        }

        $filepath = $this->storagePath . '/' . $filename;
        if (file_put_contents($filepath, $fileData) === false) {
            throw new RuntimeException("Failed to write backup to path: {$filepath}");
        }

        $sizeBytes = strlen($fileData);
        $checksum = hash('sha256', $fileData);
        file_put_contents($filepath . '.sha256', $checksum . '  ' . $filename);

        if ($this->database !== null) {
            try {
                $this->database->execute(
                    'INSERT INTO backups (filename, filepath, size_bytes, zone_count, checksum_sha256, is_encrypted, created_at) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)',
                    [$filename, $filepath, $sizeBytes, count($allZones), $checksum, $isEncrypted ? 1 : 0]
                );
            } catch (\Exception $e) {
                $this->logger->warning('Failed to persist backup record to DB: ' . $e->getMessage());
            }
        }

        $this->logger->info('Cluster backup created', [
            'filename' => $filename,
            'zones' => count($allZones),
            'encrypted' => $isEncrypted,
            'size' => $sizeBytes,
        ]);

        return [
            'filename' => $filename,
            'filepath' => $filepath,
            'size_bytes' => $sizeBytes,
            'checksum_sha256' => $checksum,
            'zone_count' => count($allZones),
            'is_encrypted' => $isEncrypted,
            'created_at' => $snapshot['created_at'],
        ];
    }

    /**
     * List all available backups from disk and database.
     */
    public function listBackups(): array
    {
        $files = glob($this->storagePath . '/pdns_backup_*.{json,enc}', GLOB_BRACE) ?: [];
        $backups = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $size = filesize($file) ?: 0;
            $checksumFile = $file . '.sha256';
            $checksum = file_exists($checksumFile) ? trim(explode(' ', file_get_contents($checksumFile) ?: '')[0]) : hash_file('sha256', $file);

            $backups[] = [
                'filename' => $filename,
                'filepath' => $file,
                'size_bytes' => $size,
                'checksum_sha256' => $checksum,
                'is_encrypted' => str_ends_with($filename, '.enc'),
                'created_at' => date('c', filemtime($file) ?: time()),
            ];
        }

        usort($backups, fn ($a, $b) => strcmp($b['filename'], $a['filename']));
        return $backups;
    }

    /**
     * Inspect a backup package and preview contents without modifying the live database.
     */
    public function inspectBackup(string $filepath, ?string $passphrase = null): array
    {
        if (!file_exists($filepath)) {
            throw new InvalidArgumentException("Backup file not found at: {$filepath}");
        }

        $raw = file_get_contents($filepath);
        if ($raw === false) {
            throw new RuntimeException("Could not read backup file at {$filepath}");
        }

        if (str_ends_with($filepath, '.enc')) {
            if ($passphrase === null || $passphrase === '') {
                throw new InvalidArgumentException('Passphrase required to decrypt this backup file.');
            }
            $jsonStr = $this->decryptPayload($raw, $passphrase);
        } else {
            $jsonStr = $raw;
        }

        $data = json_decode($jsonStr, true);
        if (!is_array($data) || !isset($data['zones'])) {
            throw new RuntimeException('Corrupted or invalid backup file structure.');
        }

        return $data;
    }

    /**
     * Restore a backup package. If $dryRun is true, returns a diff without making live changes.
     */
    public function restoreBackup(string $filepath, ?string $passphrase = null, bool $dryRun = true): array
    {
        $data = $this->inspectBackup($filepath, $passphrase);
        $zonesToRestore = $data['zones'] ?? [];

        $plan = [
            'dry_run' => $dryRun,
            'total_zones' => count($zonesToRestore),
            'created' => [],
            'updated' => [],
            'errors' => [],
        ];

        foreach ($zonesToRestore as $item) {
            $meta = $item['metadata'] ?? [];
            $records = $item['records'] ?? [];
            $zoneName = $meta['name'] ?? null;

            if ($zoneName === null) {
                continue;
            }

            try {
                $exists = false;
                try {
                    $this->zoneService->getZone($zoneName);
                    $exists = true;
                } catch (\Exception) {
                    $exists = false;
                }

                if (!$dryRun) {
                    if (!$exists) {
                        $this->zoneService->createZone($meta);
                    }
                    if (!empty($records)) {
                        $this->recordService->bulkUpdate($zoneName, $records);
                    }
                }

                if ($exists) {
                    $plan['updated'][] = $zoneName;
                } else {
                    $plan['created'][] = $zoneName;
                }
            } catch (\Exception $e) {
                $plan['errors'][] = "Zone {$zoneName}: " . $e->getMessage();
            }
        }

        if (!$dryRun) {
            $this->logger->info('Cluster backup restored', [
                'filepath' => $filepath,
                'created' => count($plan['created']),
                'updated' => count($plan['updated']),
            ]);
        }

        return $plan;
    }

    /**
     * Delete a backup file and its checksum.
     */
    public function deleteBackup(string $filename): void
    {
        $cleanName = basename($filename);
        $target = $this->storagePath . '/' . $cleanName;

        if (file_exists($target)) {
            @unlink($target);
            @unlink($target . '.sha256');
        }

        if ($this->database !== null) {
            try {
                $this->database->execute('DELETE FROM backups WHERE filename = ?', [$cleanName]);
            } catch (\Exception) {
                // Ignore DB deletion error if table not active
            }
        }

        $this->logger->info('Backup deleted', ['filename' => $cleanName]);
    }

    /**
     * Encrypt JSON payload using AES-256-GCM.
     */
    private function encryptPayload(string $plaintext, string $passphrase): string
    {
        $salt = random_bytes(16);
        $key = hash_pbkdf2('sha256', $passphrase, $salt, 10000, 32, true);
        $iv = random_bytes(12); // Standard 96-bit IV for GCM
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            16
        );

        if ($ciphertext === false) {
            throw new RuntimeException('OpenSSL AES-256-GCM encryption failed.');
        }

        // Output format: salt(16) + iv(12) + tag(16) + ciphertext
        return $salt . $iv . $tag . $ciphertext;
    }

    /**
     * Decrypt AES-256-GCM encrypted payload.
     */
    private function decryptPayload(string $binaryData, string $passphrase): string
    {
        if (strlen($binaryData) < 44) {
            throw new InvalidArgumentException('Encrypted backup data is truncated or corrupt.');
        }

        $salt = substr($binaryData, 0, 16);
        $iv = substr($binaryData, 16, 12);
        $tag = substr($binaryData, 28, 16);
        $ciphertext = substr($binaryData, 44);

        $key = hash_pbkdf2('sha256', $passphrase, $salt, 10000, 32, true);

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plaintext === false) {
            throw new InvalidArgumentException('Failed to decrypt backup: incorrect passphrase or corrupted data.');
        }

        return $plaintext;
    }
}
