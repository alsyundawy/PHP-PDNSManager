<?php

declare(strict_types=1);

namespace App\Models;

/**
 * @property bool $is_default
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class ZoneTemplate
{
    public int $id;
    public string $name;
    public ?string $description = null;
    public bool $isDefault = false;
    /** @var ZoneTemplateRecord[] */
    public array $records = [];
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    public function __get(string $name): mixed
    {
        return match ($name) {
            'is_default' => $this->isDefault,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            default => null,
        };
    }

    public function __set(string $name, mixed $value): void
    {
        match ($name) {
            'is_default' => $this->isDefault = (bool) $value,
            'created_at' => $this->createdAt = $value !== null ? (string) $value : null,
            'updated_at' => $this->updatedAt = $value !== null ? (string) $value : null,
            default => null,
        };
    }

    public function __isset(string $name): bool
    {
        return match ($name) {
            'is_default' => isset($this->isDefault),
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
            'description' => $this->description,
            'is_default' => $this->isDefault,
            'records' => array_map(static fn (ZoneTemplateRecord $r) => $r->toArray(), $this->records),
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
