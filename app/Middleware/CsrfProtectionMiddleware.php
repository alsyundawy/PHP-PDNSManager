<?php
declare(strict_types=1);
namespace App\Core\Middleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use App\Core\Exceptions\HttpException;
use App\Services\Auth\CsrfService;

class CsrfProtectionMiddleware implements MiddlewareInterface
{
    private CsrfService $csrf;
    public function __construct(CsrfService $csrf)
    {
        $this->csrf = $csrf;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $handler->handle($request);
        }
        if (str_starts_with($request->getUri()->getPath(), '/api')) {
            return $handler->handle($request);
        }
        $parsedBody = $request->getParsedBody();
        $token = null;
        if (is_array($parsedBody) && isset($parsedBody['_csrf']) && is_string($parsedBody['_csrf'])) {
            $token = $parsedBody['_csrf'];
        }
        if ($token === null) {
            $headerToken = $request->getHeaderLine('X-CSRF-TOKEN');
            if ($headerToken !== '') {
                $token = $headerToken;
            }
        }
        if (!$token || !$this->csrf->validate($token)) {
            throw new HttpException(403, 'CSRF token invalid');
        }
        return $handler->handle($request);
    }
}
