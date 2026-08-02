<?php
declare(strict_types=1);
namespace App\Core;
use Psr\Http\Message\ServerRequestInterface;

class Request
{
    private ServerRequestInterface $request;
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }
    public function getMethod(): string
    {
        return $this->request->getMethod();
    }
    public function getPath(): string
    {
        return $this->request->getUri()->getPath();
    }
    public function getQueryParams(): array
    {
        return $this->request->getQueryParams();
    }
    public function getParsedBody(): array
    {
        return $this->request->getParsedBody() ?? [];
    }
    public function getAttribute(string $name, $default = null)
    {
        return $this->request->getAttribute($name, $default);
    }
    public function withAttribute(string $name, $value): self
    {
        $new = clone $this;
        $new->request = $this->request->withAttribute($name, $value);
        return $new;
    }
    public function getServerRequest(): ServerRequestInterface
    {
        return $this->request;
    }
    public function input(string $key, $default = null)
    {
        $body = $this->getParsedBody();
        return $body[$key] ?? $this->getQueryParams()[$key] ?? $default;
    }
    public function has(string $key): bool
    {
        $body = $this->getParsedBody();
        return isset($body[$key]) || isset($this->getQueryParams()[$key]);
    }
}
