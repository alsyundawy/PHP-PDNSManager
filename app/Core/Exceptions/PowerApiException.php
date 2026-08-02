<?php
declare(strict_types=1);
namespace App\Core\Exceptions;

class PowerApiException extends HttpException
{
    public function __construct(string $message = 'PowerDNS API error', int $statusCode = 502)
    {
        parent::__construct($statusCode, $message);
    }
}
