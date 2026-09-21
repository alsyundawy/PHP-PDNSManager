<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;
use App\Core\Middleware\AuditLogMiddleware;
use App\Core\Middleware\AuthenticationMiddleware;
use App\Core\Middleware\ContentSecurityPolicyMiddleware;
use App\Core\Middleware\CsrfProtectionMiddleware;
use App\Core\Middleware\MiddlewarePipeline;
use App\Core\Middleware\RateLimitMiddleware;
use App\Core\Middleware\RbacMiddleware;
use App\Repositories\AuditLogRepository;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Services\PowerDNS\PowerDNSClient;
use App\Services\PowerDNS\PowerDNSClientInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
        $this->router = new Router($this->container);
        $this->container->singleton(Router::class, $this->router);
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
        $this->container->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->container->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->container->bind(AuditLogRepositoryInterface::class, AuditLogRepository::class);
        $this->container->singleton(PowerDNSClient::class, function (Container $c) {
            return new PowerDNSClient($c->get(Config::class), $c->get(Logger::class));
        });
        $this->container->bind(PowerDNSClientInterface::class, function (Container $c) {
            return $c->get(PowerDNSClient::class);
        });
        $this->loadRoutes();
    }

    public function run(): void
    {
        $request = $this->container->get(ServerRequestCreator::class)->fromGlobals();
        $pipeline = new MiddlewarePipeline($this->container);
        $pipeline->pipe(ContentSecurityPolicyMiddleware::class);
        $pipeline->pipe(CsrfProtectionMiddleware::class);
        $pipeline->pipe(RateLimitMiddleware::class);
        $pipeline->pipe(AuthenticationMiddleware::class);
        $pipeline->pipe(RbacMiddleware::class);
        $pipeline->pipe(AuditLogMiddleware::class);
        $response = $pipeline->process($request, $this);
        $this->emit($response);
    }

    public function handle(ServerRequestInterface $request): Response // NOSONAR
    {
        try {
            if (!$request->getAttribute('middleware_processed')) {
                $request = $request->withAttribute('middleware_processed', true);
                $pipeline = new MiddlewarePipeline($this->container);
                $pipeline->pipe(ContentSecurityPolicyMiddleware::class);
                $pipeline->pipe(CsrfProtectionMiddleware::class);
                $pipeline->pipe(RateLimitMiddleware::class);
                $pipeline->pipe(AuthenticationMiddleware::class);
                $pipeline->pipe(RbacMiddleware::class);
                $pipeline->pipe(AuditLogMiddleware::class);
                return $pipeline->process($request, $this);
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
