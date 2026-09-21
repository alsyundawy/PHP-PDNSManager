<?php

declare(strict_types=1);

namespace App\Services\Health;

use App\Core\Config;
use App\Core\Database;
use App\Core\Logger;
use App\Services\PowerDNS\PowerDNSClientInterface;

class HealthCheckService
{
    private Database $db;
    private Config $config;
    private PowerDNSClientInterface $pdns;
    private Logger $logger;

    public function __construct(Database $db, Config $config, PowerDNSClientInterface $pdns, Logger $logger)
    {
        $this->db = $db;
        $this->config = $config;
        $this->pdns = $pdns;
        $this->logger = $logger;
    }

    public function checkOverall(): array
    {
        $dbCheck = $this->checkDatabase();
        $pdnsCheck = $this->checkPowerDNS();
        $storageCheck = $this->checkStorage();
        $systemCheck = $this->checkSystem();

        $isHealthy = $dbCheck['status'] === 'healthy' && $pdnsCheck['status'] === 'healthy';
        $isDegraded = !$isHealthy && ($dbCheck['status'] === 'healthy' || $pdnsCheck['status'] === 'healthy');

        $overallStatus = $isHealthy ? 'healthy' : ($isDegraded ? 'degraded' : 'unhealthy');

        return [
            'status' => $overallStatus,
            'timestamp' => date('c'),
            'app' => [
                'name' => $this->config->get('app.name', 'PHP-PDNSManager'),
                'env' => $this->config->get('app.env', 'production'),
                'php_version' => PHP_VERSION,
            ],
            'checks' => [
                'database' => $dbCheck,
                'powerdns' => $pdnsCheck,
                'storage' => $storageCheck,
                'system' => $systemCheck,
            ],
        ];
    }

    public function checkDatabase(): array
    {
        $start = microtime(true);
        try {
            $stmt = $this->db->execute('SELECT 1');
            $stmt->fetch();
            $latency = round((microtime(true) - $start) * 1000, 2);
            return [
                'status' => 'healthy',
                'latency_ms' => $latency,
                'message' => 'Database connection successful',
            ];
        } catch (\Throwable $e) {
            $this->logger->error('Database health check failed: ' . $e->getMessage());
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function checkPowerDNS(): array
    {
        $start = microtime(true);
        try {
            $res = $this->pdns->get('');
            $latency = round((microtime(true) - $start) * 1000, 2);
            return [
                'status' => 'healthy',
                'latency_ms' => $latency,
                'version' => $res['version'] ?? 'unknown',
                'daemon_type' => $res['daemon_type'] ?? 'authoritative',
            ];
        } catch (\Throwable $e) {
            $this->logger->error('PowerDNS health check failed: ' . $e->getMessage());
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function checkStorage(): array
    {
        $storagePath = dirname(__DIR__, 2) . '/storage';
        $logsPath = $storagePath . '/logs';
        $writable = is_dir($storagePath) && is_writable($storagePath);
        $logsWritable = is_dir($logsPath) ? is_writable($logsPath) : @mkdir($logsPath, 0775, true);

        return [
            'status' => ($writable || $logsWritable) ? 'healthy' : 'degraded',
            'storage_writable' => $writable,
            'logs_writable' => $logsWritable,
        ];
    }

    public function checkSystem(): array
    {
        $requiredExtensions = ['pdo', 'curl', 'openssl', 'json', 'mbstring', 'sodium'];
        $missing = [];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }

        return [
            'status' => empty($missing) ? 'healthy' : 'unhealthy',
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'missing_extensions' => $missing,
        ];
    }
}
