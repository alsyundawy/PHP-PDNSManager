<?php

declare(strict_types=1);

namespace App\Models;

class Organization
{
    public int $id;
    public string $name;
    public string $slug;
    public ?string $description = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
