<?php

declare(strict_types=1);

namespace App\Console;

use App\Core\Application;
use App\Core\Database;
use App\Services\Backup\BackupRestoreService;
use App\Services\Health\HealthCheckService;
use App\Services\PowerDNS\Resource\ZoneResource;
use PDOException;
use Throwable;

class Kernel
{
    private Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle CLI command execution.
     *
     * @param array<int, string> $argv
     * @return int Exit code (0 for success, non-zero for error)
     */
    public function handle(array $argv): int
    {
        $args = array_slice($argv, 1);
        $command = $args[0] ?? 'help';

        if (in_array($command, ['-h', '--help', 'help'], true)) {
            $this->printHelp();
            return 0;
        }

        try {
            return match ($command) {
                'backup:run' => $this->runBackup($args),
                'cluster:probe' => $this->runClusterProbe(),
                'zone:notify' => $this->runZoneNotify($args[1] ?? null),
                'zone:rectify' => $this->runZoneRectify($args[1] ?? null),
                'migrate' => $this->runMigrate(),
                default => $this->commandNotFound($command),
            };
        } catch (Throwable $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Execute backup:run [--encrypt] [--passphrase=...]
     *
     * @param array<int, string> $args
     */
    private function runBackup(array $args): int
    {
        $this->info("Initiating full cluster zone backup snapshot...");

        $encrypt = false;
        $passphrase = null;

        foreach ($args as $arg) {
            if ($arg === '--encrypt') {
                $encrypt = true;
            } elseif (str_starts_with($arg, '--passphrase=')) {
                $passphrase = substr($arg, 13);
                $encrypt = true;
            }
        }

        if ($encrypt && ($passphrase === null || $passphrase === '')) {
            $passphrase = bin2hex(random_bytes(16));
            $this->warn("No passphrase supplied. Generated secure random passphrase: {$passphrase}");
        }

        /** @var BackupRestoreService $backupService */
        $backupService = $this->app->getContainer()->get(BackupRestoreService::class);
        $result = $backupService->createFullBackup($passphrase);

        $this->success("Backup completed successfully!");
        echo "--------------------------------------------------------\n";
        echo " Backup ID   : " . $result['id'] . "\n";
        echo " File Name   : " . $result['filename'] . "\n";
        echo " File Path   : " . $result['filepath'] . "\n";
        echo " Zones Count : " . $result['zone_count'] . "\n";
        echo " Size        : " . number_format($result['size']) . " bytes\n";
        echo " Encrypted   : " . ($result['is_encrypted'] ? "YES (AES-256-GCM)" : "NO") . "\n";
        echo " SHA256 Sum  : " . $result['checksum'] . "\n";
        echo "--------------------------------------------------------\n";

        return 0;
    }

    /**
     * Execute cluster:probe
     */
    private function runClusterProbe(): int
    {
        $this->info("Probing PowerDNS Authoritative Server and Cluster Nodes...");

        /** @var HealthCheckService $healthService */
        $healthService = $this->app->getContainer()->get(HealthCheckService::class);
        $health = $healthService->checkOverall();

        $status = $health['status'];
        $statusColored = match ($status) {
            'healthy' => "\033[32m[HEALTHY]\033[0m",
            'degraded' => "\033[33m[DEGRADED]\033[0m",
            default => "\033[31m[UNHEALTHY]\033[0m",
        };

        echo "Cluster Overall Status: {$statusColored}\n";
        echo "Timestamp: " . ($health['timestamp'] ?? date('c')) . "\n\n";

        printf("%-20s %-12s %-12s %-30s\n", "COMPONENT", "STATUS", "LATENCY", "DETAILS");
        echo str_repeat('-', 78) . "\n";

        foreach ($health['checks'] as $component => $data) {
            $compStatus = $data['status'] ?? 'unknown';
            $latency = isset($data['latency_ms']) ? $data['latency_ms'] . ' ms' : 'N/A';
            $details = $data['message'] ?? ($data['version'] ?? ($data['driver'] ?? 'OK'));

            $badge = $compStatus === 'healthy' ? "\033[32mOK\033[0m" : "\033[31mFAIL\033[0m";
            printf("%-20s %-20s %-12s %-30s\n", ucfirst((string) $component), $badge, $latency, substr((string) $details, 0, 30));
        }

        return $status === 'unhealthy' ? 1 : 0;
    }

    /**
     * Execute zone:notify <zone_id>
     */
    private function runZoneNotify(?string $zoneId): int
    {
        if ($zoneId === null || trim($zoneId) === '') {
            $this->error("Usage: php bin/pdns zone:notify <zone_id>");
            return 1;
        }

        $this->info("Sending DNS NOTIFY packets for zone: {$zoneId}...");

        /** @var ZoneResource $zoneResource */
        $zoneResource = $this->app->getContainer()->get(ZoneResource::class);
        $zoneResource->notify($zoneId);

        $this->success("DNS NOTIFY packet broadcast sent successfully for zone '{$zoneId}'.");
        return 0;
    }

    /**
     * Execute zone:rectify <zone_id>
     */
    private function runZoneRectify(?string $zoneId): int
    {
        if ($zoneId === null || trim($zoneId) === '') {
            $this->error("Usage: php bin/pdns zone:rectify <zone_id>");
            return 1;
        }

        $this->info("Rectifying zone DNSSEC metadata and order keys: {$zoneId}...");

        /** @var ZoneResource $zoneResource */
        $zoneResource = $this->app->getContainer()->get(ZoneResource::class);
        $zoneResource->rectify($zoneId);

        $this->success("Zone '{$zoneId}' rectified successfully.");
        return 0;
    }

    /**
     * Execute migrate
     */
    private function runMigrate(): int
    {
        $this->info("Checking database migrations in database/migrations/...");

        /** @var Database $db */
        $db = $this->app->getContainer()->get(Database::class);
        $pdo = $db->getPdo();

        $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        // Ensure migrations table exists
        if ($driver === 'sqlite') {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS migrations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    migration VARCHAR(255) NOT NULL UNIQUE,
                    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )"
            );
        } else {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255) NOT NULL UNIQUE,
                    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        }

        // Fetch already applied migrations
        $stmt = $pdo->query("SELECT migration FROM migrations");
        $applied = $stmt !== false ? $stmt->fetchAll(\PDO::FETCH_COLUMN) : [];
        $appliedSet = array_flip($applied);

        $migrationsDir = $this->app->getBasePath() . '/database/migrations';
        $files = glob($migrationsDir . '/*.sql');
        if ($files === false || empty($files)) {
            $this->warn("No migration files found in {$migrationsDir}");
            return 0;
        }

        sort($files);
        $count = 0;

        foreach ($files as $filePath) {
            $filename = basename($filePath);
            if (isset($appliedSet[$filename])) {
                continue;
            }

            $this->info("Migrating: {$filename}...");
            $sql = file_get_contents($filePath);
            if ($sql === false || trim($sql) === '') {
                continue;
            }

            // Split into separate queries if needed
            $queries = array_filter(
                array_map('trim', explode(';', $sql)),
                fn ($q) => $q !== ''
            );

            try {
                $db->beginTransaction();
                foreach ($queries as $query) {
                    $pdo->exec($query);
                }
                $recordStmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
                $recordStmt->execute([$filename]);
                $db->commit();

                $this->success("Migrated:  {$filename}");
                $count++;
            } catch (PDOException $e) {
                if ($db->getPdo()->inTransaction()) {
                    $db->rollBack();
                }
                $this->error("Failed migrating {$filename}: " . $e->getMessage());
                return 1;
            }
        }

        if ($count === 0) {
            $this->success("Nothing to migrate. All database migrations are up to date.");
        } else {
            $this->success("Successfully applied {$count} migration(s).");
        }

        return 0;
    }

    private function commandNotFound(string $command): int
    {
        $this->error("Unknown command: '{$command}'");
        $this->printHelp();
        return 1;
    }

    private function printHelp(): void
    {
        echo "\033[1;34m╔═══════════════════════════════════════════════════════════════╗\033[0m\n";
        echo "\033[1;34m║  PHP-PDNSManager Enterprise Edition v1.0.6 — CLI Console      ║\033[0m\n";
        echo "\033[1;34m╚═══════════════════════════════════════════════════════════════╝\033[0m\n\n";

        echo "\033[1mUsage:\033[0m\n";
        echo "  php bin/pdns <command> [arguments] [options]\n\n";

        echo "\033[1mAvailable Commands:\033[0m\n";
        printf("  \033[32m%-22s\033[0m %s\n", "backup:run", "Create a full cluster zone snapshot (supports --encrypt, --passphrase=...)");
        printf("  \033[32m%-22s\033[0m %s\n", "cluster:probe", "Probe live PowerDNS nodes, database, and system health status");
        printf("  \033[32m%-22s\033[0m %s\n", "zone:notify <zone>", "Broadcast RFC 1996 DNS NOTIFY packets to slave nameservers");
        printf("  \033[32m%-22s\033[0m %s\n", "zone:rectify <zone>", "Rectify DNSSEC order keys and metadata for the specified zone");
        printf("  \033[32m%-22s\033[0m %s\n", "migrate", "Run pending database schema migrations from database/migrations/");
        printf("  \033[32m%-22s\033[0m %s\n\n", "help", "Display this help message");

        echo "\033[1mOptions for backup:run:\033[0m\n";
        echo "  --encrypt               Encrypt backup archive with AES-256-GCM\n";
        echo "  --passphrase=<secret>   Specify encryption passphrase (auto-generated if omitted)\n\n";
    }

    private function info(string $msg): void
    {
        echo "\033[36m[INFO]\033[0m {$msg}\n";
    }

    private function success(string $msg): void
    {
        echo "\033[32m[SUCCESS]\033[0m {$msg}\n";
    }

    private function warn(string $msg): void
    {
        echo "\033[33m[WARNING]\033[0m {$msg}\n";
    }

    private function error(string $msg): void
    {
        fwrite(STDERR, "\033[31m[ERROR]\033[0m {$msg}\n");
    }
}
