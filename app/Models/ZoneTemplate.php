<?php

declare(strict_types=1);

namespace App\Models;

class ZoneTemplate
{
    public int $id;
    public string $name;
    public ?string $description = null;
    public bool $is_default = false;
    /** @var ZoneTemplateRecord[] */
    public array $records = [];
    public ?string $created_at = null;
    public ?string $updated_at = null;

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_default' => $this->is_default,
            'records' => array_map(static fn (ZoneTemplateRecord $r) => $r->toArray(), $this->records),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
