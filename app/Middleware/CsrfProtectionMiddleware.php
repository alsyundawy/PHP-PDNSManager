<?php
declare(strict_types=1);
namespace App\Core\Middleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Core\Response;
use App\Core\Exceptions\HttpException;
use App\Services\Auth\CsrfService;

class CsrfProtectionMiddleware implements MiddlewareInterface
{
    private CsrfService $csrf;
    public function __construct(CsrfService $csrf)
    {
        $this->csrf = $csrf;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): Response
    {
        if (in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $handler->handle($request);
        }
        if (str_starts_with($request->getUri()->getPath(), '/api')) {
            return $handler->handle($request);
        }
        $token = $request->getParsedBody()['_csrf'] ?? $request->getHeader('X-CSRF-TOKEN')[0] ?? null;
        if (!$token || !$this->csrf->validate($token)) {
            throw new HttpException(403, 'CSRF token invalid');
        }
        return $handler->handle($request);
    }
}
