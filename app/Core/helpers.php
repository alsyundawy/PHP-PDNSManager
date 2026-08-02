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
    function csrf_token(): string
    {
        static $token = null;
        if ($token === null) {
            $token = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $token;
        }
        return $token;
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
