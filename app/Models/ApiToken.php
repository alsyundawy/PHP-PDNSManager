<?php

declare(strict_types=1);

namespace App\Models;

/**
 * @property int $user_id
 * @property \DateTimeImmutable|null $expires_at
 * @property bool $is_revoked
 * @property \DateTimeImmutable $created_at
 */
class ApiToken
{
    public int $id;
    public int $userId;
    public string $token;
    public string $name;
    public ?\DateTimeImmutable $expiresAt = null;
    public bool $isRevoked = false;
    public \DateTimeImmutable $createdAt;

    public function __get(string $name): mixed
    {
        return match ($name) {
            'user_id' => $this->userId,
            'expires_at' => $this->expiresAt,
            'is_revoked' => $this->isRevoked,
            'created_at' => $this->createdAt,
            default => null,
        };
    }

    public function __set(string $name, mixed $value): void
    {
        if ($name === 'user_id') {
            $this->userId = (int) $value;
        } elseif ($name === 'expires_at') {
            $this->expiresAt = $this->parseDateTime($value);
        } elseif ($name === 'is_revoked') {
            $this->isRevoked = (bool) $value;
        } elseif ($name === 'created_at') {
            $this->createdAt = $value instanceof \DateTimeImmutable ? $value : new \DateTimeImmutable((string) $value);
        }
    }

    private function parseDateTime(mixed $value): ?\DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }
        return new \DateTimeImmutable((string) $value);
    }

    public function __isset(string $name): bool
    {
        return match ($name) {
            'user_id' => isset($this->userId),
            'expires_at' => isset($this->expiresAt),
            'is_revoked' => true,
            'created_at' => isset($this->createdAt),
            default => false,
        };
    }
}
