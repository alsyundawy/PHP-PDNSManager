<?php

declare(strict_types=1);

namespace App\Models;

class ZoneTemplateRecord
{
    public int $id;
    public int $template_id;
    public string $name;
    public string $type;
    public string $content;
    public int $ttl = 3600;
    public ?int $priority = null;
    public ?string $created_at = null;

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'template_id' => $this->template_id,
            'name' => $this->name,
            'type' => $this->type,
            'content' => $this->content,
            'ttl' => $this->ttl,
            'priority' => $this->priority,
            'created_at' => $this->created_at,
        ];
    }
}
