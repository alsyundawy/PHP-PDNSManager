<?php
declare(strict_types=1);
namespace App\Core;

use Nyholm\Psr7\Response as Psr7Response;

/**
 * BUGFIX: json()/html()/redirect() must return NEW instance (PSR-7 immutable).
 * Original code called $this->getBody()->write() and withStatus() on $this
 * instead of returning a new response — the stream write is fine, but
 * withHeader/withStatus must be chained on the returned clone.
 */
class Response extends Psr7Response
{
    public function json(array $data, int $status = 200): self
    {
        $response = new self($status, ['Content-Type' => 'application/json']);
        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));
        return $response;
    }

    public function html(string $html, int $status = 200): self
    {
        $response = new self($status, ['Content-Type' => 'text/html; charset=utf-8']);
        $response->getBody()->write($html);
        return $response;
    }

    public function redirect(string $url, int $status = 302): self
    {
        return new self($status, ['Location' => $url]);
    }
}
