<?php
declare(strict_types=1);
namespace App\Models;

class ApiToken
{
    public int $id;
    public int $user_id;
    public string $token;
    public string $name;
    public ?\DateTimeImmutable $expires_at;
    public bool $is_revoked;
    public \DateTimeImmutable $created_at;
}
