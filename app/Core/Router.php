<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;

class Router
{
    private array $routes = [];
    private array $groupStack = [];
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function addRoute(string $method, string $path, mixed $handler, array $middlewares = []): void
    {
        $path = $this->prefixPath($path);
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function get(string $path, mixed $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }
    public function post(string $path, mixed $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }
    public function put(string $path, mixed $handler, array $middlewares = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }
    public function delete(string $path, mixed $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    private function prefixPath(string $path): string
    {
        $prefix = '';
        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
        }
        $path = '/' . trim($path, '/');
        return $prefix . $path;
    }

    public function dispatch(ServerRequestInterface $request): Response
    {
        $method = $request->getMethod();
        $uri = $request->getUri()->getPath();
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            $pattern = $this->convertToRegex($route['path']);
            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $handler = $this->resolveHandler($route['handler']);
                $response = $handler($request, ...$params);
                if ($response instanceof Response) {
                    return $response;
                }
                return $this->createResponse($response);
            }
        }
        throw new HttpException(404, 'Route not found');
    }

    private function resolveHandler(mixed $handler): callable
    {
        if (is_array($handler) && count($handler) === 2) {
            $controller = $this->container->get($handler[0]);
            $handler = [$controller, $handler[1]];
        }
        if (is_callable($handler)) {
            return $handler;
        }
        throw new HttpException(500, 'Invalid route handler');
    }

    private function convertToRegex(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]\w*)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function createResponse(mixed $data): Response
    {
        $factory = $this->container->get(Psr17Factory::class);
        $response = $factory->createResponse(200);
        if (is_array($data) || is_object($data)) {
            $response->getBody()->write((string) json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
        }
        $response->getBody()->write((string) $data);
        return $response;
    }
}
