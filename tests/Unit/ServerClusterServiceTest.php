<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Config;
use App\Core\Database;
use App\Core\Logger;
use App\Services\PowerDNS\ServerClusterService;
use PHPUnit\Framework\TestCase;

class ServerClusterServiceTest extends TestCase
{
    private Database $db;
    private ServerClusterService $service;

    protected function setUp(): void
    {
        $config = new Config(__DIR__ . '/../../config');
        $logger = new Logger($config);
        $this->db = new Database([
            'default' => 'sqlite',
            'connections' => [
                'sqlite' => [
                    'driver' => 'sqlite',
                    'database' => ':memory:',
                ],
            ],
        ]);

        $this->db->execute(
            'CREATE TABLE pdns_servers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                api_url TEXT NOT NULL,
                api_key TEXT NOT NULL,
                server_id TEXT NOT NULL DEFAULT "localhost",
                is_active INTEGER NOT NULL DEFAULT 1,
                is_default INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME,
                updated_at DATETIME
            )'
        );

        $this->service = new ServerClusterService($this->db, $config, $logger);
    }

    public function testCreateAndRetrieveServer(): void
    {
        $server = $this->service->createServer([
            'name' => 'Secondary-DNS-01',
            'api_url' => 'https://ns1.cluster.test:8081',
            'api_key' => 'secret-cluster-key',
            'server_id' => 'ns2',
            'is_active' => true,
            'is_default' => true,
        ]);

        $this->assertEquals('Secondary-DNS-01', $server->name);
        $this->assertEquals('https://ns1.cluster.test:8081', $server->apiUrl);
        $this->assertTrue($server->isDefault);

        $fetched = $this->service->getServerById($server->id);
        $this->assertNotNull($fetched);
        $this->assertEquals('Secondary-DNS-01', $fetched->name);

        $defaultServer = $this->service->getDefaultServer();
        $this->assertEquals('Secondary-DNS-01', $defaultServer->name);
    }

    public function testUpdateAndDeleteServer(): void
    {
        $server = $this->service->createServer([
            'name' => 'Cluster-Node-A',
            'api_url' => 'https://ns2.cluster.test:8081',
            'api_key' => 'test-key',
        ]);

        $this->service->updateServer($server->id, [
            'name' => 'Cluster-Node-Renamed',
        ]);

        $updated = $this->service->getServerById($server->id);
        $this->assertNotNull($updated);
        $this->assertEquals('Cluster-Node-Renamed', $updated->name);

        $this->service->deleteServer($server->id);
        $deleted = $this->service->getServerById($server->id);
        $this->assertNull($deleted);
    }
}
