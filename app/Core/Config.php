<?php
declare(strict_types=1);
namespace App\Core;

class Config
{
    private array $configs = [];
    public function __construct(string $configPath)
    {
        foreach (glob($configPath . '/*.php') as $file) {
            $key = basename($file, '.php');
            $this->configs[$key] = require $file;
        }
    }
    public function get(string $key, $default = null)
    {
        $parts = explode('.', $key);
        $data = $this->configs;
        foreach ($parts as $part) {
            if (!isset($data[$part])) {
                return $default;
            }
            $data = $data[$part];
        }
        return $data;
    }
    public function all(): array
    {
        return $this->configs;
    }
}
