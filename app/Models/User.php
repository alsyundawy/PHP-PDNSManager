<?php

declare(strict_types=1);

namespace App\Models;

class User
{
    public int $id;
    public string $username;
    public string $email;
    public string $passwordHash;
    public bool $isActive;
    public array $roles = [];
    public array $permissions = [];
    public ?string $totpSecret = null;
    public ?\DateTimeImmutable $lastLogin = null;
    public \DateTimeImmutable $createdAt;
    public \DateTimeImmutable $updatedAt;

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function __get(string $name): mixed
    {
        $camel = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
        if (property_exists($this, $camel)) {
            return $this->$camel;
        }
        return null;
    }

    public function __set(string $name, mixed $value): void
    {
        $camel = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
        if (property_exists($this, $camel)) {
            $this->$camel = $value;
        }
    }

    public function __isset(string $name): bool
    {
        $camel = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
        return property_exists($this, $camel) && isset($this->$camel);
    }
}
