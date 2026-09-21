<?php

declare(strict_types=1);

namespace App\Services\PowerDNS\Resource;

use App\Services\PowerDNS\PowerDNSClientInterface;

class CryptokeyResource
{
    private const ZONES_PREFIX = 'zones/';
    private const CRYPTOKEYS_SEGMENT = '/cryptokeys';
    private const CRYPTOKEYS_PATH = '/cryptokeys/';

    private PowerDNSClientInterface $client;

    public function __construct(PowerDNSClientInterface $client)
    {
        $this->client = $client;
    }

    public function getAll(string $zoneId): array
    {
        return $this->client->get(self::ZONES_PREFIX . urlencode($zoneId) . self::CRYPTOKEYS_SEGMENT);
    }

    public function get(string $zoneId, string $keyId): array
    {
        return $this->client->get(self::ZONES_PREFIX . urlencode($zoneId) . self::CRYPTOKEYS_PATH . urlencode($keyId));
    }

    public function create(string $zoneId, array $data): array
    {
        return $this->client->post(self::ZONES_PREFIX . urlencode($zoneId) . self::CRYPTOKEYS_SEGMENT, $data);
    }

    public function delete(string $zoneId, string $keyId): void
    {
        $this->client->delete(self::ZONES_PREFIX . urlencode($zoneId) . self::CRYPTOKEYS_PATH . urlencode($keyId));
    }

    public function update(string $zoneId, string $keyId, array $data): array
    {
        return $this->client->put(self::ZONES_PREFIX . urlencode($zoneId) . self::CRYPTOKEYS_PATH . urlencode($keyId), $data);
    }
}
