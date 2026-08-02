<?php
declare(strict_types=1);
namespace App\Core\Middleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Core\Response;
use App\Core\Config;

class ContentSecurityPolicyMiddleware implements MiddlewareInterface
{
    private Config $config;
    public function __construct(Config $config)
    {
        $this->config = $config;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): Response
    {
        $response = $handler->handle($request);
        if ($this->config->get('security.csp_enabled', true)) {
            $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' data: https:; connect-src 'self' https:;";
            $response = $response->withHeader('Content-Security-Policy', $csp);
            $response = $response->withHeader('X-Content-Type-Options', 'nosniff');
            $response = $response->withHeader('X-Frame-Options', 'DENY');
            $response = $response->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response = $response->withHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
            $response = $response->withHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
        return $response;
    }
}
