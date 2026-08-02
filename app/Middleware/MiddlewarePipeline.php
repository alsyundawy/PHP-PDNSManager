<?php
declare(strict_types=1);
namespace App\Core\Middleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Core\Container;
use App\Core\Response;

class MiddlewarePipeline implements RequestHandlerInterface
{
    private Container $container;
    private array $middlewares = [];
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    public function pipe(string $middlewareClass): void
    {
        $this->middlewares[] = $middlewareClass;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): Response
    {
        if (empty($this->middlewares)) {
            return $handler->handle($request);
        }
        $middlewareClass = array_shift($this->middlewares);
        $middleware = $this->container->get($middlewareClass);
        return $middleware->process($request, $this);
    }
    public function handle(ServerRequestInterface $request): Response
    {
        return $this->process($request, $this->container->get(\App\Core\Application::class));
    }
}
