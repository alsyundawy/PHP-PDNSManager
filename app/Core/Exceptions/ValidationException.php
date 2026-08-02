<?php
declare(strict_types=1);
namespace App\Core\Exceptions;

/**
 * BUGFIX: Original extends HttpException without use statement — works only
 * if both are in same namespace. Added explicit extends with correct namespace.
 */
class ValidationException extends HttpException
{
    private array $errors;

    public function __construct(array $errors, string $message = 'Validation failed', int $statusCode = 422)
    {
        parent::__construct($statusCode, $message);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
