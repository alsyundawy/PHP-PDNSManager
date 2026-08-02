<?php
declare(strict_types=1);
namespace Tests\Feature;
use PHPUnit\Framework\TestCase;
use App\Core\Application;
use App\Core\Config;
use App\Core\Database;
use App\Services\Auth\AuthenticationService;

class AuthTest extends TestCase
{
    private AuthenticationService $auth;
    protected function setUp(): void {
        $app = new Application(__DIR__ . '/../../');
        $container = $app->getContainer();
        $db = $container->get(Database::class);
        $this->setupDatabase($db);
        $this->auth = $container->get(AuthenticationService::class);
    }
    private function setupDatabase(Database $db): void {
        $db->execute('CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE, email TEXT UNIQUE, password_hash TEXT, is_active INTEGER DEFAULT 1, last_login DATETIME, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP)');
        $hash = password_hash('password123', PASSWORD_ARGON2ID);
        $db->execute('INSERT OR IGNORE INTO users (username, email, password_hash) VALUES (?, ?, ?)', ['testuser', 'test@example.com', $hash]);
    }
    public function testLoginSuccess(): void {
        $user = $this->auth->login('testuser', 'password123', '127.0.0.1');
        $this->assertNotNull($user);
        $this->assertEquals('testuser', $user->username);
    }
    public function testLoginFailure(): void {
        $user = $this->auth->login('testuser', 'wrong', '127.0.0.1');
        $this->assertNull($user);
    }
}
