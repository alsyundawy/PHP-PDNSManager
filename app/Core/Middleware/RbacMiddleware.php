<?php
declare(strict_types=1);
namespace App\Core\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use App\Core\Exceptions\AccessDeniedException;
use App\Services\Auth\RbacService;

/**
 * BUGFIX: Original file used namespace App\Core\Middleware but was placed
 * in app/Middleware/. File moved; namespace and use statements corrected.
 * Also: inferPermission() correctly handles PATCH method (mapped to 'edit').
 */
class RbacMiddleware implements MiddlewareInterface
{
    private RbacService $rbac;

    public function __construct(RbacService $rbac)
    {
        $this->rbac = $rbac;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute('user');
        if (!$user) {
            return $handler->handle($request);
        }

        $permission = $request->getAttribute('permission') ?: $this->inferPermission($request);

        if ($permission && !$this->rbac->hasPermission($user, $permission)) {
            throw new AccessDeniedException('Insufficient permissions');
        }

        return $handler->handle($request);
    }

    private function inferPermission(ServerRequestInterface $request): ?string
    {
        $path   = $request->getUri()->getPath();
        $method = $request->getMethod();

        // BUGFIX: Added PATCH → 'edit' mapping (was missing)
        $map = [
            'GET'    => 'view',
            'POST'   => 'create',
            'PUT'    => 'edit',
            'PATCH'  => 'edit',
            'DELETE' => 'delete',
        ];

        $action   = $map[$method] ?? 'view';
        $resource = explode('/', trim($path, '/'))[0] ?? 'dashboard';

        return $resource . '.' . $action;
    }
}
