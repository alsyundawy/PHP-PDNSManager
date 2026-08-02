<?php
declare(strict_types=1);
namespace App\Services\PowerDNS;

interface PowerDNSClientInterface
{
    public function get(string $uri, array $query = []): array;
    public function post(string $uri, array $data = []): array;
    public function put(string $uri, array $data = []): array;
    public function patch(string $uri, array $data = []): array;
    public function delete(string $uri): void;
    public function getServerId(): string;
}
