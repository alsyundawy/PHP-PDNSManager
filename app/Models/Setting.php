<?php
declare(strict_types=1);
namespace App\Models;

class Setting
{
    public string $key;
    public ?string $value;
    public ?string $description;
}
