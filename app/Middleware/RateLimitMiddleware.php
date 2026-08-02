<?php
declare(strict_types=1);
namespace App\Core\Middleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Core\Response;
use App\Core\Exceptions\HttpException;
use App\Core\Config;

class RateLimitMiddleware implements MiddlewareInterface
{
    private Config $config;
    private array $storage = [];
    public function __construct(Config $config)
    {
        $this->config = $config;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): Response
    {
        if (!$this->config->get('security.rate_limit.enabled', true)) {
            return $handler->handle($request);
        }
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = 'rate_limit:' . $ip;
        $limit = $this->config->get('security.rate_limit.requests', 100);
        $window = $this->config->get('security.rate_limit.window', 60);
        $current = $this->storage[$key] ?? ['count' => 0, 'reset' => time() + $window];
        if (time() > $current['reset']) {
            $current = ['count' => 0, 'reset' => time() + $window];
        }
        if ($current['count'] >= $limit) {
            throw new HttpException(429, 'Too many requests. Please try again later.');
        }
        $current['count']++;
        $this->storage[$key] = $current;
        return $handler->handle($request);
    }
}
