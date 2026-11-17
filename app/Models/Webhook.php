<?php

declare(strict_types=1);

namespace App\Models;

/**
 * @property bool $is_active
 * @property string|null $last_triggered_at
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Webhook
{
    public int $id;
    public string $name;
    public string $url;
    public ?string $secret = null;
    /** @var string[] */
    public array $events = [];
    public bool $isActive = true;
    public ?string $lastTriggeredAt = null;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    public function __get(string $name): mixed
    {
        return match ($name) {
            'is_active' => $this->isActive,
            'last_triggered_at' => $this->lastTriggeredAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            default => null,
        };
    }

    public function __set(string $name, mixed $value): void
    {
        match ($name) {
            'is_active' => $this->isActive = (bool) $value,
            'last_triggered_at' => $this->lastTriggeredAt = $value !== null ? (string) $value : null,
            'created_at' => $this->createdAt = $value !== null ? (string) $value : null,
            'updated_at' => $this->updatedAt = $value !== null ? (string) $value : null,
            default => null,
        };
    }

    public function __isset(string $name): bool
    {
        return match ($name) {
            'is_active' => isset($this->isActive),
            'last_triggered_at' => isset($this->lastTriggeredAt),
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
            'url' => $this->url,
            'secret' => $this->secret,
            'events' => $this->events,
            'is_active' => $this->isActive,
            'last_triggered_at' => $this->lastTriggeredAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
