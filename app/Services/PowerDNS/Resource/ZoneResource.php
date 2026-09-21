<?php

declare(strict_types=1);

namespace App\Services\PowerDNS\Resource;

use App\Services\PowerDNS\PowerDNSClientInterface;

class ZoneResource
{
    private const ZONES_PREFIX = 'zones/';
    private const ZONES_ENDPOINT = 'zones';

    private PowerDNSClientInterface $client;

    public function __construct(PowerDNSClientInterface $client)
    {
        $this->client = $client;
    }

    public function getAll(array $filters = []): array
    {
        $query = [];
        if (isset($filters['name'])) {
            $query['name'] = $filters['name'];
        }
        if (isset($filters['type'])) {
            $query['type'] = $filters['type'];
        }
        return $this->client->get(self::ZONES_ENDPOINT, $query);
    }

    public function get(string $zoneId): array
    {
        return $this->client->get(self::ZONES_PREFIX . urlencode($zoneId));
    }

    public function create(array $data): array
    {
        return $this->client->post(self::ZONES_ENDPOINT, $data);
    }

    public function update(string $zoneId, array $data): array
    {
        return $this->client->put(self::ZONES_PREFIX . urlencode($zoneId), $data);
    }

    public function patch(string $zoneId, array $data): array
    {
        return $this->client->patch(self::ZONES_PREFIX . urlencode($zoneId), $data);
    }

    public function delete(string $zoneId): void
    {
        $this->client->delete(self::ZONES_PREFIX . urlencode($zoneId));
    }

    public function export(string $zoneId): array
    {
        $raw = $this->client->getRaw(self::ZONES_PREFIX . urlencode($zoneId) . '/export');
        return ['zone' => $zoneId, 'raw' => $raw];
    }

    public function check(string $zoneId): array
    {
        return $this->client->get(self::ZONES_PREFIX . urlencode($zoneId) . '/check');
    }

    public function notify(string $zoneId): void
    {
        $this->client->put(self::ZONES_PREFIX . urlencode($zoneId) . '/notify');
    }

    public function getCryptokeys(string $zoneId): array
    {
        return $this->client->get(self::ZONES_PREFIX . urlencode($zoneId) . '/cryptokeys');
    }
}
