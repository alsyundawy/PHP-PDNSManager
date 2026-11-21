<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\Database;
use App\Core\Logger;
use App\Core\SessionManager;
use App\Models\AuditLog;
use App\Repositories\AuditLogRepository;
use App\Repositories\UserRepository;
use App\Services\Auth\AuthenticationService;
use Database\Seeders\InitialSeeder;
use PHPUnit\Framework\TestCase;

class SqlitePortabilityTest extends TestCase
{
    private Database $db;

    protected function setUp(): void
    {
        $config = [
            'default' => 'sqlite',
            'connections' => [
                'sqlite' => [
                    'driver' => 'sqlite',
                    'database' => ':memory:',
                ],
            ],
        ];

        $this->db = new Database($config);
        $pdo = $this->db->getPdo();

        // Create core tables in memory with SQLite syntax
        $pdo->exec(
            'CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(64) NOT NULL UNIQUE,
                email VARCHAR(255) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                totp_secret VARCHAR(255) NULL,
                is_active INTEGER DEFAULT 1,
                last_login DATETIME NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            )'
        );

        $pdo->exec(
            'CREATE TABLE roles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(64) NOT NULL UNIQUE,
                description VARCHAR(255) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            )'
        );

        $pdo->exec(
            'CREATE TABLE permissions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(64) NOT NULL UNIQUE,
                description VARCHAR(255) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            )'
        );

        $pdo->exec(
            'CREATE TABLE user_role (
                user_id INTEGER NOT NULL,
                role_id INTEGER NOT NULL,
                PRIMARY KEY (user_id, role_id)
            )'
        );

        $pdo->exec(
            'CREATE TABLE permission_role (
                permission_id INTEGER NOT NULL,
                role_id INTEGER NOT NULL,
                PRIMARY KEY (permission_id, role_id)
            )'
        );

        $pdo->exec(
            'CREATE TABLE audit_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NULL,
                action VARCHAR(255) NOT NULL,
                payload TEXT NULL,
                status_code INTEGER NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            )'
        );
    }

    public function testAuditLogCreateWithCurrentTimestampAndArrayPayload(): void
    {
        $repo = new AuditLogRepository($this->db);
        $log = $repo->create([
            'user_id' => 1,
            'action' => 'test.action',
            'payload' => ['foo' => 'bar', 'count' => 42],
            'status_code' => 200,
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertInstanceOf(AuditLog::class, $log);
        $this->assertSame(1, $log->id);
        $this->assertSame('test.action', $log->action);
        $this->assertIsArray($log->payload);
        $this->assertSame('bar', $log->payload['foo']);
    }

    public function testUserRepositoryUpdateWithCurrentTimestamp(): void
    {
        $repo = new UserRepository($this->db);
        $user = $repo->create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password_hash' => 'hash',
            'is_active' => 1,
        ]);

        $updated = $repo->update($user->id, ['email' => 'updated@example.com']);
        $this->assertTrue($updated);

        $reloaded = $repo->find($user->id);
        $this->assertNotNull($reloaded);
        $this->assertSame('updated@example.com', $reloaded->email);
    }

    public function testInitialSeederRunsOnSqliteAndSetsDocumentedPassword(): void
    {
        $userRepo = new UserRepository($this->db);
        $logger = new Logger(sys_get_temp_dir());
        $session = new SessionManager();
        $auth = new AuthenticationService($userRepo, $logger, $session);

        $seeder = new InitialSeeder($this->db, $auth);
        $seeder->run();

        // Check admin user seeded
        $admin = $userRepo->findByUsername('admin');
        $this->assertNotNull($admin);

        // Verify authentication with documented password ChangeMe@2026!
        $authenticated = $auth->login('admin', 'ChangeMe@2026!', '127.0.0.1');
        $this->assertNotNull($authenticated);
        $this->assertSame('admin', $authenticated->username);
    }

    public function testHashPasswordFallback(): void
    {
        $userRepo = new UserRepository($this->db);
        $logger = new Logger(sys_get_temp_dir());
        $session = new SessionManager();
        $auth = new AuthenticationService($userRepo, $logger, $session);

        $hash = $auth->hashPassword('secret_test_password');
        $this->assertNotEmpty($hash);
        $this->assertTrue(password_verify('secret_test_password', $hash));
    }
}
