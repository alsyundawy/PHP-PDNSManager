<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    private string $basePath;
    private ?string $layout = 'admin';
    private array $sharedData = [];
    private static array $globalShared = [];

    public static function setGlobal(string $key, mixed $value): void
    {
        self::$globalShared[$key] = $value;
    }

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function share(string $key, mixed $value): void
    {
        $this->sharedData[$key] = $value;
    }

    public function render(string $view, array $data = []): string
    {
        $data = array_merge(self::$globalShared, $this->sharedData, $data);
        $content = $this->renderView($view, $data);
        if ($this->layout !== null && $this->layout !== '') {
            $layoutData = array_merge($data, ['content' => $content]);
            return $this->renderView('layouts/' . $this->layout, $layoutData);
        }
        return $content;
    }
    private function renderView(string $view, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        include_once $this->basePath . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        $output = ob_get_clean();
        return is_string($output) ? $output : '';
    }
    public function setLayout(?string $layout): void
    {
        $this->layout = $layout;
    }
    public function disableLayout(): void
    {
        $this->layout = null;
    }
    public static function asset(string $path): string
    {
        $publicPath = __DIR__ . '/../../public/' . ltrim($path, '/');
        if (file_exists($publicPath)) {
            $version = filemtime($publicPath);
            return '/' . ltrim($path, '/') . '?v=' . $version;
        }
        return '/' . ltrim($path, '/');
    }
}
