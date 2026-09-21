<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Core\Database;
use App\Core\Logger;
use App\Models\Organization;
use PDO;

class OrganizationService
{
    private Database $db;
    private Logger $logger;

    public function __construct(Database $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * @return Organization[]
     */
    public function getAllOrganizations(): array
    {
        try {
            $stmt = $this->db->execute('SELECT * FROM organizations ORDER BY name ASC');
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map([$this, 'hydrateOrganization'], $rows);
        } catch (\Throwable $e) {
            $this->logger->error('Error getting organizations: ' . $e->getMessage());
            return [];
        }
    }

    public function getOrganizationById(int $id): ?Organization
    {
        try {
            $stmt = $this->db->execute('SELECT * FROM organizations WHERE id = ? LIMIT 1', [$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                return null;
            }
            return $this->hydrateOrganization($row);
        } catch (\Throwable $e) {
            $this->logger->error('Error getting organization by id: ' . $e->getMessage());
            return null;
        }
    }

    public function createOrganization(string $name, string $slug, ?string $description = null): ?Organization
    {
        $this->db->execute(
            'INSERT INTO organizations (name, slug, description, created_at, updated_at) VALUES (?, ?, ?, datetime("now"), datetime("now"))',
            [trim($name), trim($slug), $description]
        );

        $id = (int) $this->db->lastInsertId();
        return $this->getOrganizationById($id);
    }

    public function addUserToOrganization(int $organizationId, int $userId, string $role = 'member'): void
    {
        $this->db->execute(
            'INSERT OR REPLACE INTO organization_user (organization_id, user_id, role, created_at) VALUES (?, ?, ?, datetime("now"))',
            [$organizationId, $userId, $role]
        );
    }

    public function removeUserFromOrganization(int $organizationId, int $userId): void
    {
        $this->db->execute(
            'DELETE FROM organization_user WHERE organization_id = ? AND user_id = ?',
            [$organizationId, $userId]
        );
    }

    public function assignZoneToOrganization(string $zoneId, int $organizationId): void
    {
        $this->db->execute(
            'INSERT OR IGNORE INTO zone_organizations (zone_id, organization_id, created_at) VALUES (?, ?, datetime("now"))',
            [$zoneId, $organizationId]
        );
    }

    public function removeZoneFromOrganization(string $zoneId, int $organizationId): void
    {
        $this->db->execute(
            'DELETE FROM zone_organizations WHERE zone_id = ? AND organization_id = ?',
            [$zoneId, $organizationId]
        );
    }

    /**
     * @return Organization[]
     */
    public function getUserOrganizations(int $userId): array
    {
        try {
            $stmt = $this->db->execute(
                'SELECT o.* FROM organizations o JOIN organization_user ou ON o.id = ou.organization_id WHERE ou.user_id = ?',
                [$userId]
            );
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map([$this, 'hydrateOrganization'], $rows);
        } catch (\Throwable $e) {
            $this->logger->error('Error getting user organizations: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if a user has access to a given zone.
     * If the user is a super admin, they have global access.
     * If the zone is not mapped to any organization, it's considered unmanaged/public within the cluster.
     * If the zone is mapped to organizations, user must belong to at least one of those organizations.
     */
    public function canUserAccessZone(int $userId, string $zoneId, bool $isSuperAdmin = false): bool
    {
        if ($isSuperAdmin) {
            return true;
        }

        try {
            // Check if zone is assigned to any org
            $stmt = $this->db->execute('SELECT COUNT(*) as cnt FROM zone_organizations WHERE zone_id = ?', [$zoneId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalAssigned = (int) ($row['cnt'] ?? 0);

            if ($totalAssigned === 0) {
                // Zone is globally accessible
                return true;
            }

            // Check if user belongs to an org assigned to this zone
            $stmt = $this->db->execute(
                'SELECT COUNT(*) as cnt FROM zone_organizations zo
                 JOIN organization_user ou ON zo.organization_id = ou.organization_id
                 WHERE zo.zone_id = ? AND ou.user_id = ?',
                [$zoneId, $userId]
            );
            $userAccess = $stmt->fetch(PDO::FETCH_ASSOC);
            return ((int) ($userAccess['cnt'] ?? 0)) > 0;
        } catch (\Throwable $e) {
            $this->logger->error('Error checking zone access: ' . $e->getMessage());
            return true;
        }
    }

    private function hydrateOrganization(array $row): Organization
    {
        $org = new Organization();
        $org->id = (int) ($row['id'] ?? 0);
        $org->name = (string) ($row['name'] ?? '');
        $org->slug = (string) ($row['slug'] ?? '');
        $org->description = isset($row['description']) ? (string) $row['description'] : null;
        $org->created_at = isset($row['created_at']) ? (string) $row['created_at'] : null;
        $org->updated_at = isset($row['updated_at']) ? (string) $row['updated_at'] : null;
        return $org;
    }
}
