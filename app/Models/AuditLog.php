<?php

declare(strict_types=1);

namespace App\Models;

class AuditLog
{
    public int $id;
    public ?int $userId = null;
    public string $action;
    public ?array $payload = null;
    public int $statusCode;
    public string $ipAddress;
    public \DateTimeImmutable $createdAt;

    public function __get(string $name): mixed
    {
        return match ($name) {
            'user_id' => $this->userId,
            'status_code' => $this->statusCode,
            'ip_address' => $this->ipAddress,
            'created_at' => $this->createdAt,
            default => null,
        };
    }

    public function __set(string $name, mixed $value): void
    {
        match ($name) {
            'user_id' => $this->userId = $value !== null ? (int) $value : null,
            'status_code' => $this->statusCode = (int) $value,
            'ip_address' => $this->ipAddress = (string) $value,
            'created_at' => $this->createdAt = $value instanceof \DateTimeImmutable ? $value : new \DateTimeImmutable((string) $value),
            default => null,
        };
    }

    public function __isset(string $name): bool
    {
        return match ($name) {
            'user_id' => isset($this->userId),
            'status_code' => isset($this->statusCode),
            'ip_address' => isset($this->ipAddress),
            'created_at' => isset($this->createdAt),
            default => false,
        };
    }
}
