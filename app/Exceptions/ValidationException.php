<?php
declare(strict_types=1);
namespace App\Core\Exceptions;
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
