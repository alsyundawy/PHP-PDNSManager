<?php
declare(strict_types=1);
namespace App\Services\PowerDNS\Resource;
use App\Services\PowerDNS\PowerDNSClientInterface;

class ServerResource
{
    private PowerDNSClientInterface $client;
    public function __construct(PowerDNSClientInterface $client)
    {
        $this->client = $client;
    }
    public function getStats(): array
    {
        return $this->client->get('statistics');
    }
    public function getConfig(): array
    {
        return $this->client->get('config');
    }
    public function getInfo(): array
    {
        return $this->client->get('');
    }
    public function health(): bool
    {
        try {
            $this->client->get('');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
