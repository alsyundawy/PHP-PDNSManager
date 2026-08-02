<?php
declare(strict_types=1);
namespace App\Core\Middleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Core\Response;
use App\Services\AuditLogService;

class AuditLogMiddleware implements MiddlewareInterface
{
    private AuditLogService $auditLog;
    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): Response
    {
        $response = $handler->handle($request);
        if ($request->getMethod() !== 'GET' && $response->getStatusCode() < 400) {
            $user = $request->getAttribute('user');
            $this->auditLog->log(
                $user ? $user->id : null,
                $request->getMethod() . ' ' . $request->getUri()->getPath(),
                $request->getParsedBody() ?? [],
                $response->getStatusCode()
            );
        }
        return $response;
    }
}
