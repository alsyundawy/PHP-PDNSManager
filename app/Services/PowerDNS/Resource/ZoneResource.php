<?php
declare(strict_types=1);
namespace App\Services\PowerDNS\Resource;
use App\Services\PowerDNS\PowerDNSClientInterface;

class ZoneResource
{
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
        return $this->client->get('zones', $query);
    }
    public function get(string $zoneId): array
    {
        return $this->client->get('zones/' . urlencode($zoneId));
    }
    public function create(array $data): array
    {
        return $this->client->post('zones', $data);
    }
    public function update(string $zoneId, array $data): array
    {
        return $this->client->put('zones/' . urlencode($zoneId), $data);
    }
    public function patch(string $zoneId, array $data): array
    {
        return $this->client->patch('zones/' . urlencode($zoneId), $data);
    }
    public function delete(string $zoneId): void
    {
        $this->client->delete('zones/' . urlencode($zoneId));
    }
    public function export(string $zoneId): array
    {
        return $this->client->get('zones/' . urlencode($zoneId) . '/export');
    }
    public function check(string $zoneId): array
    {
        return $this->client->get('zones/' . urlencode($zoneId) . '/check');
    }
    public function notify(string $zoneId): void
    {
        $this->client->put('zones/' . urlencode($zoneId) . '/notify');
    }
    public function getCryptokeys(string $zoneId): array
    {
        return $this->client->get('zones/' . urlencode($zoneId) . '/cryptokeys');
    }
}
