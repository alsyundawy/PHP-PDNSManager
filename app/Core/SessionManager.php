<?php
declare(strict_types=1);
namespace App\Core;

class SessionManager
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }
    public function regenerate(): void
    {
        session_regenerate_id(true);
    }
    public function destroy(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
