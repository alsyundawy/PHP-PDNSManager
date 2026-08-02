<?php
declare(strict_types=1);
namespace App\Core\Exceptions;
use Exception;
class HttpException extends Exception
{
    protected int $statusCode;
    public function __construct(int $statusCode, string $message = '', ?Exception $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
    }
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
