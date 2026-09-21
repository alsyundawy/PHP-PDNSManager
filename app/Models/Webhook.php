<?php

declare(strict_types=1);

namespace App\Models;

class Webhook
{
    public int $id;
    public string $name;
    public string $url;
    public ?string $secret = null;
    /** @var string[] */
    public array $events = [];
    public bool $is_active = true;
    public ?string $last_triggered_at = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'secret' => $this->secret,
            'events' => $this->events,
            'is_active' => $this->is_active,
            'last_triggered_at' => $this->last_triggered_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
