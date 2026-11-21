<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\DNS\DnsToolboxService;
use App\Services\DNS\TsigService;
use App\Services\DNS\ZoneMetadataService;
use App\Services\Backup\BackupRestoreService;
use App\Services\PowerDNS\Resource\TsigResource;
use App\Services\PowerDNS\Resource\ZoneResource;
use App\Services\PowerDNS\PowerDNSClientInterface;
use App\Services\DNS\ZoneService;
use App\Services\DNS\RecordService;
use App\Core\Logger;
use InvalidArgumentException;

class V106ServicesTest extends TestCase
{
    private Logger $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(Logger::class);
    }

    public function testSpfValidationDetectsLookupExceededAndPermissiveAll(): void
    {
        $toolbox = new DnsToolboxService($this->logger);

        // Record with 11 lookups (exceeds RFC 7208 limit of 10)
        $excessiveSpf = 'v=spf1 a mx include:spf1.net include:spf2.net include:spf3.net include:spf4.net include:spf5.net include:spf6.net include:spf7.net include:spf8.net include:spf9.net ~all';
        $res = $toolbox->validateSpf('example.com', $excessiveSpf);

        $this->assertFalse($res['valid']);
        $this->assertGreaterThan(10, $res['lookup_count']);
        $this->assertNotEmpty($res['errors']);

        // Permissive +all
        $permissiveSpf = 'v=spf1 a +all';
        $resPerm = $toolbox->validateSpf('example.com', $permissiveSpf);
        $this->assertTrue($resPerm['valid']);
        $this->assertNotEmpty($resPerm['warnings']);
        $this->assertStringContainsString('+all', $resPerm['warnings'][0]);
    }

    public function testSpfAndDmarcBuilder(): void
    {
        $toolbox = new DnsToolboxService($this->logger);

        $spf = $toolbox->buildSpf([
            'a' => true,
            'mx' => true,
            'ip4' => ['192.0.2.1', '198.51.100.1'],
            'include' => ['_spf.google.com'],
            'qualifier' => '~all',
        ]);
        $this->assertSame('v=spf1 a mx ip4:192.0.2.1 ip4:198.51.100.1 include:_spf.google.com ~all', $spf);

        $dmarc = $toolbox->buildDmarc([
            'p' => 'reject',
            'sp' => 'quarantine',
            'rua' => 'dmarc@example.com',
            'pct' => 100,
        ]);
        $this->assertSame('v=DMARC1; p=reject; sp=quarantine; rua=mailto:dmarc@example.com; aspf=r; adkim=r;', $dmarc);
    }

    public function testTsigServiceGeneratesSecureRandomSecret(): void
    {
        $client = $this->createMock(PowerDNSClientInterface::class);
        $client->expects($this->once())
            ->method('post')
            ->with('tsigkeys', $this->callback(function ($payload) {
                return $payload['name'] === 'axfr-key'
                    && $payload['algorithm'] === 'hmac-sha256'
                    && strlen(base64_decode($payload['key'], true)) === 32;
            }))
            ->willReturn(['id' => 'axfr-key']);

        $tsigResource = new TsigResource($client);
        $tsigService = new TsigService($tsigResource, $this->logger);

        $result = $tsigService->createKey('axfr-key', 'hmac-sha256');
        $this->assertSame('axfr-key', $result['id']);
    }

    public function testTsigServiceThrowsOnInvalidAlgorithm(): void
    {
        $client = $this->createMock(PowerDNSClientInterface::class);
        $tsigResource = new TsigResource($client);
        $tsigService = new TsigService($tsigResource, $this->logger);

        $this->expectException(InvalidArgumentException::class);
        $tsigService->createKey('invalid-key', 'unsupported-algorithm');
    }

    public function testZoneMetadataServiceValidation(): void
    {
        $client = $this->createMock(PowerDNSClientInterface::class);
        $zoneResource = new ZoneResource($client);
        $metaService = new ZoneMetadataService($zoneResource, $this->logger);

        $kinds = $metaService->getSupportedKinds();
        $this->assertArrayHasKey('SOA-EDIT', $kinds);
        $this->assertArrayHasKey('ALLOW-AXFR-FROM', $kinds);

        $this->expectException(InvalidArgumentException::class);
        $metaService->setMetadataKind('example.com', 'INVALID_KIND', ['val']);
    }

    public function testBackupRestoreEncryptedRoundtrip(): void
    {
        $zoneService = $this->createMock(ZoneService::class);
        $zoneService->method('getAllZones')->willReturn([
            ['id' => 'example.com.', 'name' => 'example.com.', 'kind' => 'Native'],
        ]);

        $recordService = $this->createMock(RecordService::class);
        $recordService->method('getRecordsForZone')->willReturn([
            ['name' => 'example.com.', 'type' => 'SOA', 'ttl' => 3600, 'records' => [['content' => 'ns1.example.com. hostmaster.example.com. 1 10800 3600 604800 3600']]],
            ['name' => 'www.example.com.', 'type' => 'A', 'ttl' => 3600, 'records' => [['content' => '192.0.2.1', 'disabled' => false]]],
        ]);

        $tmpDir = sys_get_temp_dir() . '/pdns_backup_test_' . uniqid();
        mkdir($tmpDir, 0755, true);

        try {
            $backupService = new BackupRestoreService(
                $zoneService,
                $recordService,
                $this->logger,
                null,
                $tmpDir
            );

            $passphrase = 'SecretEnterprisePass2026!';
            $meta = $backupService->createFullBackup($passphrase);

            $this->assertTrue($meta['is_encrypted']);
            $this->assertFileExists($meta['filepath']);
            $this->assertFileExists($meta['filepath'] . '.sha256');

            // Decrypt and inspect
            $inspected = $backupService->inspectBackup($meta['filepath'], $passphrase);
            $this->assertSame(1, $inspected['zone_count']);
            $this->assertSame('example.com.', $inspected['zones'][0]['metadata']['name']);

            // Failed decryption on wrong passphrase
            $this->expectException(InvalidArgumentException::class);
            $backupService->inspectBackup($meta['filepath'], 'WrongPassword');
        } finally {
            // Cleanup
            array_map('unlink', glob($tmpDir . '/*') ?: []);
            @rmdir($tmpDir);
        }
    }
}
