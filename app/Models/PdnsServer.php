<?php

declare(strict_types=1);

namespace App\Models;

class PdnsServer
{
    public int $id;
    public string $name;
    public string $apiUrl;
    public string $apiKey;
    public string $serverId = 'localhost';
    public bool $isActive = true;
    public bool $isDefault = false;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'api_url' => $this->apiUrl,
            'api_key' => $this->apiKey,
            'server_id' => $this->serverId,
            'is_active' => $this->isActive,
            'is_default' => $this->isDefault,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
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
