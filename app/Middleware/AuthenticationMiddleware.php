<?php
declare(strict_types=1);
namespace App\Core\Middleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Core\Response;
use App\Core\Exceptions\HttpException;
use App\Services\Auth\AuthenticationService;

class AuthenticationMiddleware implements MiddlewareInterface
{
    private AuthenticationService $auth;
    public function __construct(AuthenticationService $auth)
    {
        $this->auth = $auth;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): Response
    {
        $route = $request->getAttribute('route');
        if ($route && in_array($route, ['login', 'logout'])) {
            return $handler->handle($request);
        }
        $user = $this->auth->check();
        if (!$user) {
            if (str_starts_with($request->getUri()->getPath(), '/api')) {
                throw new HttpException(401, 'Unauthenticated');
            }
            return new Response(302, ['Location' => '/login']);
        }
        $request = $request->withAttribute('user', $user);
        return $handler->handle($request);
    }
}
