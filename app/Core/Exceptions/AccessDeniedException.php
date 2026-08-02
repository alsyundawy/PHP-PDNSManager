<?php
declare(strict_types=1);
namespace App\Core\Exceptions;

/**
 * BUGFIX: Was in app/Exceptions/ with namespace App\Core\Exceptions
 * but not under app/Core/. Moved to app/Core/Exceptions/ to match autoload.
 */
class AccessDeniedException extends HttpException
{
    public function __construct(string $message = 'Access denied', int $statusCode = 403)
    {
        parent::__construct($statusCode, $message);
    }
}
