<?php
declare(strict_types=1);
namespace App\Core\Exceptions;
class AccessDeniedException extends HttpException
{
    public function __construct(string $message = 'Access denied', int $statusCode = 403)
    {
        parent::__construct($statusCode, $message);
    }
}
