<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Config;
use App\Core\Database;
use App\Core\Logger;
use App\Services\DNS\ZoneTemplateService;
use PHPUnit\Framework\TestCase;

class ZoneTemplateServiceTest extends TestCase
{
    private Database $db;
    private ZoneTemplateService $service;

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
            'CREATE TABLE zone_templates (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                description TEXT,
                is_default INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME,
                updated_at DATETIME
            )'
        );

        $this->db->execute(
            'CREATE TABLE zone_template_records (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                template_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                type TEXT NOT NULL,
                content TEXT NOT NULL,
                ttl INTEGER NOT NULL DEFAULT 3600,
                priority INTEGER,
                created_at DATETIME
            )'
        );

        $this->service = new ZoneTemplateService($this->db, $logger);
    }

    public function testCreateTemplateWithRecords(): void
    {
        $template = $this->service->createTemplate([
            'name' => 'Web App Template',
            'description' => 'SPA with CDN and Mail',
            'is_default' => true,
        ], [
            ['name' => '@', 'type' => 'A', 'content' => '203.0.113.10', 'ttl' => 3600],
            ['name' => 'cdn', 'type' => 'CNAME', 'content' => 'fastly.net.', 'ttl' => 300],
            ['name' => '@', 'type' => 'MX', 'content' => 'mail.domain.com.', 'ttl' => 3600, 'priority' => 10],
        ]);

        $this->assertNotNull($template);
        $this->assertEquals('Web App Template', $template->name);
        $this->assertTrue($template->is_default);
        $this->assertCount(3, $template->records);

        $all = $this->service->getAllTemplates();
        $this->assertCount(1, $all);
    }

    public function testDeleteTemplate(): void
    {
        $template = $this->service->createTemplate([
            'name' => 'Temporary Template',
        ], [
            ['name' => '@', 'type' => 'A', 'content' => '1.2.3.4'],
        ]);

        $this->assertNotNull($template);
        $deleted = $this->service->deleteTemplate($template->id);
        $this->assertTrue($deleted);

        $this->assertNull($this->service->getTemplateById($template->id));
        $this->assertEmpty($this->service->getRecordsForTemplate($template->id));
    }
}
