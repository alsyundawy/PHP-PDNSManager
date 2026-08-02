<?php
declare(strict_types=1);
namespace App\Services\PowerDNS\Resource;
use App\Services\PowerDNS\PowerDNSClientInterface;

class CryptokeyResource
{
    private PowerDNSClientInterface $client;
    public function __construct(PowerDNSClientInterface $client)
    {
        $this->client = $client;
    }
    public function getAll(string $zoneId): array
    {
        return $this->client->get('zones/' . urlencode($zoneId) . '/cryptokeys');
    }
    public function get(string $zoneId, string $keyId): array
    {
        return $this->client->get('zones/' . urlencode($zoneId) . '/cryptokeys/' . urlencode($keyId));
    }
    public function create(string $zoneId, array $data): array
    {
        return $this->client->post('zones/' . urlencode($zoneId) . '/cryptokeys', $data);
    }
    public function delete(string $zoneId, string $keyId): void
    {
        $this->client->delete('zones/' . urlencode($zoneId) . '/cryptokeys/' . urlencode($keyId));
    }
    public function update(string $zoneId, string $keyId, array $data): array
    {
        return $this->client->put('zones/' . urlencode($zoneId) . '/cryptokeys/' . urlencode($keyId), $data);
    }
}
