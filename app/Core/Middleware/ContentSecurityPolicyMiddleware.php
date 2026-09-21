<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use App\Core\Config;
use App\Core\View;

/**
 * SECURITY FIX: Removed 'unsafe-inline' from script-src.
 * Added nonce-based CSP support. The nonce is generated per-request and
 * passed as a request attribute so views can reference it.
 * Also added X-Permitted-Cross-Domain-Policies header.
 */
class ContentSecurityPolicyMiddleware implements MiddlewareInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $nonce = base64_encode(random_bytes(16));
        View::setGlobal('cspNonce', $nonce);
        $request  = $request->withAttribute('csp_nonce', $nonce);
        $response = $handler->handle($request);

        if ($this->config->get('security.csp_enabled', true)) {
            // SECURITY HARDENING: Strict Zero CDN Content Security Policy with Nonce
            $csp = "default-src 'self'; "
                 . "script-src 'self' 'nonce-{$nonce}'; "
                 . "style-src 'self' 'unsafe-inline'; "
                 . "img-src 'self' data:; "
                 . "font-src 'self' data:; "
                 . "connect-src 'self'; "
                 . "frame-ancestors 'none'; "
                 . "base-uri 'self'; "
                 . "form-action 'self';";

            $response = $response
                ->withHeader('Content-Security-Policy', $csp)
                ->withHeader('X-Content-Type-Options', 'nosniff')
                ->withHeader('X-Frame-Options', 'DENY')
                ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
                ->withHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()')
                ->withHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload')
                ->withHeader('X-Permitted-Cross-Domain-Policies', 'none');
        }
        return $response;
    }
}
