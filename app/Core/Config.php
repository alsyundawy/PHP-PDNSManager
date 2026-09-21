<?php

declare(strict_types=1);

namespace App\Core;

class Config
{
    private static array $cachedConfigs = [];
    private array $configs = [];

    public function __construct(string $configPath)
    {
        $canonicalPath = realpath($configPath) ?: $configPath;
        if (isset(self::$cachedConfigs[$canonicalPath])) {
            $this->configs = self::$cachedConfigs[$canonicalPath];
            return;
        }

        $files = glob($canonicalPath . '/*.php');
        if ($files !== false) {
            foreach ($files as $file) {
                $key = basename($file, '.php');
                $canonicalFile = realpath($file) ?: $file;
                if (!isset(self::$cachedConfigs['__files'][$canonicalFile])) {
                    $loaded = require_once $file;
                    self::$cachedConfigs['__files'][$canonicalFile] = is_array($loaded) ? $loaded : [];
                }
                $this->configs[$key] = self::$cachedConfigs['__files'][$canonicalFile];
            }
        }
        self::$cachedConfigs[$canonicalPath] = $this->configs;
    }
    public function get(string $key, mixed $default = null): mixed
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
