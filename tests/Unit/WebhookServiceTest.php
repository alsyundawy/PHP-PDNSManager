<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Config;
use App\Core\Database;
use App\Core\Logger;
use App\Services\Webhook\WebhookService;
use PHPUnit\Framework\TestCase;

class WebhookServiceTest extends TestCase
{
    private Database $db;
    private WebhookService $service;

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
            'CREATE TABLE webhooks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                url TEXT NOT NULL,
                secret TEXT,
                events TEXT NOT NULL,
                is_active INTEGER NOT NULL DEFAULT 1,
                last_triggered_at DATETIME,
                created_at DATETIME,
                updated_at DATETIME
            )'
        );

        $this->service = new WebhookService($this->db, $logger);
    }

    public function testCreateAndRetrieveWebhook(): void
    {
        $hook = $this->service->createWebhook([
            'name' => 'Slack DNS Alerts',
            'url' => 'https://hooks.slack.com/services/test',
            'secret' => 'webhook-secret-key',
            'events' => ['zone.created', 'record.deleted'],
            'is_active' => true,
        ]);

        $this->assertNotNull($hook);
        $this->assertEquals('Slack DNS Alerts', $hook->name);
        $this->assertEquals('https://hooks.slack.com/services/test', $hook->url);
        $this->assertEquals('webhook-secret-key', $hook->secret);
        $this->assertContains('zone.created', $hook->events);

        $all = $this->service->getAllWebhooks();
        $this->assertCount(1, $all);

        $updated = $this->service->updateWebhook($hook->id, [
            'name' => 'Slack DNS Alerts Production',
        ]);
        $this->assertTrue($updated);

        $this->service->deleteWebhook($hook->id);
        $this->assertEmpty($this->service->getAllWebhooks());
    }
}
