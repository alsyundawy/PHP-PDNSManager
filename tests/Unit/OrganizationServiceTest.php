<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Config;
use App\Core\Database;
use App\Core\Logger;
use App\Services\Auth\OrganizationService;
use PHPUnit\Framework\TestCase;

class OrganizationServiceTest extends TestCase
{
    private Database $db;
    private OrganizationService $service;

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
            'CREATE TABLE organizations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                slug TEXT NOT NULL UNIQUE,
                description TEXT,
                created_at DATETIME,
                updated_at DATETIME
            )'
        );

        $this->db->execute(
            'CREATE TABLE organization_user (
                organization_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                role TEXT NOT NULL DEFAULT "member",
                created_at DATETIME,
                PRIMARY KEY (organization_id, user_id)
            )'
        );

        $this->db->execute(
            'CREATE TABLE zone_organizations (
                zone_id TEXT NOT NULL,
                organization_id INTEGER NOT NULL,
                created_at DATETIME,
                PRIMARY KEY (zone_id, organization_id)
            )'
        );

        $this->service = new OrganizationService($this->db, $logger);
    }

    public function testOrganizationLifecycleAndPermissions(): void
    {
        $org = $this->service->createOrganization('Acme Corp', 'acme-corp', 'Enterprise client org');
        $this->assertNotNull($org);
        $this->assertEquals('Acme Corp', $org->name);
        $this->assertEquals('acme-corp', $org->slug);

        $userId = 42;
        $this->service->addUserToOrganization($org->id, $userId, 'admin');

        $userOrgs = $this->service->getUserOrganizations($userId);
        $this->assertCount(1, $userOrgs);
        $this->assertEquals('Acme Corp', $userOrgs[0]->name);

        // Assign zone to organization
        $zoneId = 'acme.org.';
        $this->service->assignZoneToOrganization($zoneId, $org->id);

        // Test zone access
        $hasAccess = $this->service->canUserAccessZone($userId, $zoneId);
        $this->assertTrue($hasAccess);

        // Another user without org membership should not have access
        $otherUserId = 99;
        $otherHasAccess = $this->service->canUserAccessZone($otherUserId, $zoneId);
        $this->assertFalse($otherHasAccess);

        // Superadmin bypasses org restriction
        $adminHasAccess = $this->service->canUserAccessZone($otherUserId, $zoneId, true);
        $this->assertTrue($adminHasAccess);
    }
}
