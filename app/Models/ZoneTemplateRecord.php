<?php

declare(strict_types=1);

namespace App\Models;

/**
 * @property int $template_id
 * @property string|null $created_at
 */
class ZoneTemplateRecord
{
    public int $id;
    public int $templateId;
    public string $name;
    public string $type;
    public string $content;
    public int $ttl = 3600;
    public ?int $priority = null;
    public ?string $createdAt = null;

    public function __get(string $name): mixed
    {
        return match ($name) {
            'template_id' => $this->templateId,
            'created_at' => $this->createdAt,
            default => null,
        };
    }

    public function __set(string $name, mixed $value): void
    {
        match ($name) {
            'template_id' => $this->templateId = (int) $value,
            'created_at' => $this->createdAt = $value !== null ? (string) $value : null,
            default => null,
        };
    }

    public function __isset(string $name): bool
    {
        return match ($name) {
            'template_id' => isset($this->templateId),
            'created_at' => isset($this->createdAt),
            default => false,
        };
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'template_id' => $this->templateId,
            'name' => $this->name,
            'type' => $this->type,
            'content' => $this->content,
            'ttl' => $this->ttl,
            'priority' => $this->priority,
            'created_at' => $this->createdAt,
        ];
    }
}
