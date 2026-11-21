<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Database;
use App\Services\Auth\AuthenticationService;
use PDO;

class InitialSeeder
{
    private Database $db;
    private AuthenticationService $auth;

    public function __construct(Database $db, AuthenticationService $auth)
    {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function run(): void
    {
        $roles = ['admin', 'operator', 'viewer'];
        foreach ($roles as $role) {
            $stmt = $this->db->execute('SELECT id FROM roles WHERE name = :name', ['name' => $role]);
            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->db->execute('INSERT INTO roles (name) VALUES (:name)', ['name' => $role]);
            }
        }

        $permissions = [
            'zone.view', 'zone.create', 'zone.edit', 'zone.delete',
            'record.view', 'record.create', 'record.edit', 'record.delete',
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'settings.view', 'settings.edit',
            'backup.view', 'backup.create', 'backup.restore'
        ];
        foreach ($permissions as $perm) {
            $stmt = $this->db->execute('SELECT id FROM permissions WHERE name = :name', ['name' => $perm]);
            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->db->execute('INSERT INTO permissions (name) VALUES (:name)', ['name' => $perm]);
            }
        }

        $adminRoleStmt = $this->db->execute('SELECT id FROM roles WHERE name = :name', ['name' => 'admin']);
        $adminRoleRow = $adminRoleStmt->fetch(PDO::FETCH_ASSOC);
        $adminRoleId = $adminRoleRow ? (int) $adminRoleRow['id'] : 1;

        $permsStmt = $this->db->execute('SELECT id FROM permissions');
        $perms = $permsStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($perms as $p) {
            $checkStmt = $this->db->execute(
                'SELECT permission_id FROM permission_role WHERE permission_id = :pid AND role_id = :rid',
                ['pid' => (int) $p['id'], 'rid' => $adminRoleId]
            );
            if (!$checkStmt->fetch(PDO::FETCH_ASSOC)) {
                $this->db->execute(
                    'INSERT INTO permission_role (permission_id, role_id) VALUES (:pid, :rid)',
                    ['pid' => (int) $p['id'], 'rid' => $adminRoleId]
                );
            }
        }

        $userStmt = $this->db->execute('SELECT id FROM users WHERE username = :username', ['username' => 'admin']);
        $existingAdmin = $userStmt->fetch(PDO::FETCH_ASSOC);
        if (!$existingAdmin) {
            $hash = $this->auth->hashPassword('ChangeMe@2026!');
            $this->db->execute(
                'INSERT INTO users (username, email, password_hash, is_active, created_at, updated_at)
                 VALUES (:username, :email, :hash, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)',
                [
                    'username' => 'admin',
                    'email' => 'admin@example.com',
                    'hash' => $hash,
                ]
            );
            $newAdminStmt = $this->db->execute('SELECT id FROM users WHERE username = :username', ['username' => 'admin']);
            $newAdminRow = $newAdminStmt->fetch(PDO::FETCH_ASSOC);
            $adminUserId = $newAdminRow ? (int) $newAdminRow['id'] : 1;

            $this->db->execute(
                'INSERT INTO user_role (user_id, role_id) VALUES (:uid, :rid)',
                ['uid' => $adminUserId, 'rid' => $adminRoleId]
            );
        }

        echo "Initial data seeded successfully.\n";
    }
}
