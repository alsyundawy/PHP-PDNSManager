<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use App\Services\AuditLogService;

class AuditLogMiddleware implements MiddlewareInterface
{
    private AuditLogService $auditLog;
    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($request->getMethod() !== 'GET' && $response->getStatusCode() < 400) {
            $user = $request->getAttribute('user');
            $userId = null;
            if ($user instanceof \App\Models\User) {
                $userId = $user->id;
            } elseif (is_object($user) && isset($user->id) && is_numeric($user->id)) {
                $userId = (int) $user->id;
            } elseif (is_array($user) && isset($user['id']) && is_numeric($user['id'])) {
                $userId = (int) $user['id'];
            }

            $parsedBody = $request->getParsedBody();
            $payload = null;
            if (is_array($parsedBody)) {
                $payload = $parsedBody;
            } elseif (is_object($parsedBody)) {
                $payload = (array) $parsedBody;
            }

            $this->auditLog->log(
                $userId,
                $request->getMethod() . ' ' . $request->getUri()->getPath(),
                $payload,
                $response->getStatusCode()
            );
        }
        return $response;
    }
}
