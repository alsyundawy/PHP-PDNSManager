<?php
namespace Database\Seeders;
use App\Core\Database;
use App\Services\Auth\AuthenticationService;

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
            $this->db->execute('INSERT IGNORE INTO roles (name) VALUES (:name)', ['name' => $role]);
        }
        $permissions = ['zone.view','zone.create','zone.edit','zone.delete','record.view','record.create','record.edit','record.delete','user.view','user.create','user.edit','user.delete','settings.view','settings.edit','backup.view','backup.create','backup.restore'];
        foreach ($permissions as $perm) {
            $this->db->execute('INSERT IGNORE INTO permissions (name) VALUES (:name)', ['name' => $perm]);
        }
        $adminRole = $this->db->execute('SELECT id FROM roles WHERE name = "admin"')->fetch()['id'];
        $perms = $this->db->execute('SELECT id FROM permissions')->fetchAll();
        foreach ($perms as $p) {
            $this->db->execute('INSERT IGNORE INTO permission_role (permission_id, role_id) VALUES (:pid, :rid)', ['pid' => $p['id'], 'rid' => $adminRole]);
        }
        $hash = $this->auth->hashPassword('admin123');
        $this->db->execute('INSERT IGNORE INTO users (username, email, password_hash, is_active) VALUES (:username, :email, :hash, 1)',
            ['username' => 'admin', 'email' => 'admin@localhost', 'hash' => $hash]);
        $adminUser = $this->db->execute('SELECT id FROM users WHERE username = "admin"')->fetch()['id'];
        $this->db->execute('INSERT IGNORE INTO user_role (user_id, role_id) VALUES (:uid, :rid)', ['uid' => $adminUser, 'rid' => $adminRole]);
        echo "Initial data seeded successfully.\n";
    }
}
