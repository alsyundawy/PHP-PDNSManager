<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

class AuditLogNotFoundException extends HttpException
{
    public function __construct(string $message = 'Audit log entry not found', ?\Throwable $previous = null)
    {
        parent::__construct(404, $message, $previous);
    }
}
