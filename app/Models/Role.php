<?php
declare(strict_types=1);
namespace App\Models;

class Role
{
    public int $id;
    public string $name;
    public string $description;
    public array $permissions = [];
}
