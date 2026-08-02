<?php
declare(strict_types=1);
namespace App\Core;

class View
{
    private string $basePath;
    private string $layout = 'admin';
    private array $sharedData = [];
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }
    public function share(string $key, $value): void
    {
        $this->sharedData[$key] = $value;
    }
    public function render(string $view, array $data = []): string
    {
        $data = array_merge($this->sharedData, $data);
        $content = $this->renderView($view, $data);
        if ($this->layout) {
            $layoutData = array_merge($data, ['content' => $content]);
            return $this->renderView('layouts/' . $this->layout, $layoutData);
        }
        return $content;
    }
    private function renderView(string $view, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        include $this->basePath . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        return ob_get_clean();
    }
    public function setLayout(string $layout): void
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
