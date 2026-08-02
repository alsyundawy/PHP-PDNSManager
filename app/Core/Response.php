<?php
declare(strict_types=1);
namespace App\Core;
use Nyholm\Psr7\Response as Psr7Response;

class Response extends Psr7Response
{
    public function json(array $data, int $status = 200): self
    {
        $this->getBody()->write(json_encode($data));
        return $this->withStatus($status)
                    ->withHeader('Content-Type', 'application/json');
    }
    public function html(string $html, int $status = 200): self
    {
        $this->getBody()->write($html);
        return $this->withStatus($status)
                    ->withHeader('Content-Type', 'text/html');
    }
    public function redirect(string $url, int $status = 302): self
    {
        return $this->withStatus($status)
                    ->withHeader('Location', $url);
    }
}
