<?php

declare(strict_types=1);

namespace App\Models;

/**
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Organization
{
    public int $id;
    public string $name;
    public string $slug;
    public ?string $description = null;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    public function __get(string $name): mixed
    {
        return match ($name) {
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            default => null,
        };
    }

    public function __set(string $name, mixed $value): void
    {
        match ($name) {
            'created_at' => $this->createdAt = $value !== null ? (string) $value : null,
            'updated_at' => $this->updatedAt = $value !== null ? (string) $value : null,
            default => null,
        };
    }

    public function __isset(string $name): bool
    {
        return match ($name) {
            'created_at' => isset($this->createdAt),
            'updated_at' => isset($this->updatedAt),
            default => false,
        };
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
