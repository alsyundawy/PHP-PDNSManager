<?php
declare(strict_types=1);
namespace App\Models;

class User
{
    public int $id;
    public string $username;
    public string $email;
    public string $password_hash;
    public bool $is_active;
    public array $roles = [];
    public array $permissions = [];
    public ?string $totp_secret = null;
    public ?\DateTimeImmutable $last_login = null;
    public \DateTimeImmutable $created_at;
    public \DateTimeImmutable $updated_at;
}
