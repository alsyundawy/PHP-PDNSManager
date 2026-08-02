<?php
declare(strict_types=1);
namespace App\Models;

class AuditLog
{
    public int $id;
    public ?int $user_id;
    public string $action;
    public ?array $payload;
    public int $status_code;
    public string $ip_address;
    public \DateTimeImmutable $created_at;
}
