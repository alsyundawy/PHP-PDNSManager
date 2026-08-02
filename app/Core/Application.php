<?php
declare(strict_types=1);
namespace App\Core;
use App\Core\Exceptions\HttpException;
use App\Core\Middleware\MiddlewarePipeline;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

class Application implements RequestHandlerInterface
{
    private Container $container;
    private Router $router;
    private Config $config;
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->container = new Container();
        $this->config = new Config($basePath . '/config');
        $this->container->singleton(Container::class, $this->container);
        $this->container->singleton(Application::class, $this);
        $this->container->singleton(RequestHandlerInterface::class, $this);
        $this->container->singleton(Config::class, $this->config);
        $this->container->singleton(Logger::class, function () {
            return new Logger($this->config);
        });
        $this->container->singleton(Router::class, function () {
            return new Router($this->container);
        });
        $this->container->singleton(EventDispatcher::class, function () {
            return new EventDispatcher();
        });
        $this->container->singleton(Database::class, function () {
            return new Database($this->config->get('database'));
        });
        $this->container->singleton(Psr17Factory::class, new Psr17Factory());
        $this->container->singleton(ServerRequestCreator::class, function (Container $c) {
            return new ServerRequestCreator(
                $c->get(Psr17Factory::class),
                $c->get(Psr17Factory::class),
                $c->get(Psr17Factory::class),
                $c->get(Psr17Factory::class)
            );
        });
        $this->container->bind(\App\Repositories\Contracts\UserRepositoryInterface::class, \App\Repositories\UserRepository::class);
        $this->container->bind(\App\Repositories\Contracts\RoleRepositoryInterface::class, \App\Repositories\RoleRepository::class);
        $this->container->bind(\App\Repositories\Contracts\AuditLogRepositoryInterface::class, \App\Repositories\AuditLogRepository::class);
        $this->container->bind(\App\Services\PowerDNS\PowerDNSClientInterface::class, \App\Services\PowerDNS\PowerDNSClient::class);
        $this->loadRoutes();
    }

    public function run(): void
    {
        $request = $this->container->get(ServerRequestCreator::class)->fromGlobals();
        $pipeline = new MiddlewarePipeline($this->container);
        $pipeline->pipe(Middleware\ContentSecurityPolicyMiddleware::class);
        $pipeline->pipe(Middleware\CsrfProtectionMiddleware::class);
        $pipeline->pipe(Middleware\RateLimitMiddleware::class);
        $pipeline->pipe(Middleware\AuthenticationMiddleware::class);
        $pipeline->pipe(Middleware\RbacMiddleware::class);
        $pipeline->pipe(Middleware\AuditLogMiddleware::class);
        $response = $pipeline->process($request, $this);
        $this->emit($response);
    }

    public function handle(ServerRequestInterface $request): Response // NOSONAR
    {
        try {
            if (!$request->getAttribute('middleware_processed')) {
                $request = $request->withAttribute('middleware_processed', true);
                $pipeline = new MiddlewarePipeline($this->container);
                $pipeline->pipe(Middleware\ContentSecurityPolicyMiddleware::class);
                $pipeline->pipe(Middleware\CsrfProtectionMiddleware::class);
                $pipeline->pipe(Middleware\RateLimitMiddleware::class);
                $pipeline->pipe(Middleware\AuthenticationMiddleware::class);
                $pipeline->pipe(Middleware\RbacMiddleware::class);
                $pipeline->pipe(Middleware\AuditLogMiddleware::class);
                $res = $pipeline->process($request, $this);
                return $res instanceof Response ? $res : new Response($res->getStatusCode(), $res->getHeaders(), $res->getBody());
            }
            return $this->router->dispatch($request);
        } catch (HttpException $e) {
            return $this->errorResponse($e->getStatusCode(), $e->getMessage());
        } catch (\Throwable $e) {
            $logger = $this->container->get(Logger::class);
            $logger->error($e->getMessage(), ['exception' => $e]);
            $status = 500;
            $message = $this->config->get('app.debug') ? $e->getMessage() : 'Internal Server Error';
            return $this->errorResponse($status, $message);
        }
    }

    private function errorResponse(int $status, string $message): Response
    {
        $response = new Response($status, ['Content-Type' => 'application/json']);
        $response->getBody()->write((string) json_encode(['error' => ['code' => $status, 'message' => $message]]));
        return $response;
    }

    private function loadRoutes(): void
    {
        $router = $this->container->get(Router::class); // NOSONAR - used in required route files
        require_once $this->basePath . '/routes/web.php';
        require_once $this->basePath . '/routes/api.php';
    }

    private function emit(Response $response): void
    {
        http_response_code($response->getStatusCode());
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("{$name}: {$value}", false);
            }
        }
        echo (string) $response->getBody();
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
    public function getBasePath(): string
    {
        return $this->basePath;
    }
}
