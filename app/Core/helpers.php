<?php
if (!function_exists('view')) {
    function view(string $view, array $data = []): string
    {
        static $viewRenderer = null;
        if ($viewRenderer === null) {
            $viewRenderer = new \App\Core\View(__DIR__ . '/../../');
        }
        return $viewRenderer->render($view, $data);
    }
}
if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return \App\Core\View::asset($path);
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
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($value === false || $value === null) {
            return $default;
        }
        switch (strtolower((string) $value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
            default:
                break;
        }
        return $value;
    }
}
