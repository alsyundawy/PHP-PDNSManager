<?php

declare(strict_types=1);

use App\Core\View;

if (!function_exists('view')) {
    function view(string $view, array $data = []): string
    {
        static $viewRenderer = null;
        if ($viewRenderer === null) {
            $viewRenderer = new View(__DIR__ . '/../../');
        }
        return $viewRenderer->render($view, $data);
    }
}
if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return View::asset($path);
    }
}
if (!function_exists('csrf_token')) {
    function csrf_token(): string // NOSONAR
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
if (!function_exists('request')) {
    function request(): \App\Core\Request
    {
        static $request = null;
        if ($request === null) {
            $request = new \App\Core\Request(
                (new \Nyholm\Psr7Server\ServerRequestCreator(
                    new \Nyholm\Psr7\Factory\Psr17Factory(),
                    new \Nyholm\Psr7\Factory\Psr17Factory(),
                    new \Nyholm\Psr7\Factory\Psr17Factory(),
                    new \Nyholm\Psr7\Factory\Psr17Factory()
                ))->fromGlobals()
            );
        }
        return $request;
    }
}
if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, mixed $default = null): mixed // NOSONAR
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? false;
        if ($value === false) {
            $value = getenv($key);
        }
        if ($value === false) {
            return $default;
        }
        return match (strtolower((string) $value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => $value,
        };
    }
}
