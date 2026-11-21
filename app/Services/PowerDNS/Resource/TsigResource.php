<?php

declare(strict_types=1);

namespace App\Services\PowerDNS\Resource;

use App\Services\PowerDNS\PowerDNSClientInterface;

class TsigResource
{
    private const TSIG_ENDPOINT = 'tsigkeys';

    private PowerDNSClientInterface $client;

    public function __construct(PowerDNSClientInterface $client)
    {
        $this->client = $client;
    }

    public function getAll(): array
    {
        return $this->client->get(self::TSIG_ENDPOINT);
    }

    public function get(string $id): array
    {
        return $this->client->get(self::TSIG_ENDPOINT . '/' . urlencode($id));
    }

    public function create(array $data): array
    {
        return $this->client->post(self::TSIG_ENDPOINT, $data);
    }

    public function update(string $id, array $data): array
    {
        return $this->client->put(self::TSIG_ENDPOINT . '/' . urlencode($id), $data);
    }

    public function delete(string $id): void
    {
        $this->client->delete(self::TSIG_ENDPOINT . '/' . urlencode($id));
    }
}
