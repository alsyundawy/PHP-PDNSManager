<?php
declare(strict_types=1);
namespace App\Services\PowerDNS\Resource;
use App\Services\PowerDNS\PowerDNSClientInterface;

class RecordResource
{
    private PowerDNSClientInterface $client;
    public function __construct(PowerDNSClientInterface $client)
    {
        $this->client = $client;
    }
    public function getForZone(string $zoneId): array
    {
        $zone = $this->client->get('zones/' . urlencode($zoneId));
        return $zone['records'] ?? [];
    }
    public function create(string $zoneId, array $record): array
    {
        $payload = [
            'rrsets' => [
                [
                    'name' => $record['name'],
                    'type' => $record['type'],
                    'ttl' => (int) ($record['ttl'] ?? 3600),
                    'changetype' => 'REPLACE',
                    'records' => [
                        ['content' => $record['content'], 'disabled' => false],
                    ],
                ],
            ],
        ];
        return $this->client->patch('zones/' . urlencode($zoneId), $payload);
    }
    public function update(string $zoneId, string $recordName, string $recordType, array $newRecord): array
    {
        $payload = [
            'rrsets' => [
                [
                    'name' => $recordName,
                    'type' => $recordType,
                    'ttl' => (int) ($newRecord['ttl'] ?? 3600),
                    'changetype' => 'REPLACE',
                    'records' => [
                        ['content' => $newRecord['content'], 'disabled' => false],
                    ],
                ],
            ],
        ];
        return $this->client->patch('zones/' . urlencode($zoneId), $payload);
    }
    public function delete(string $zoneId, string $recordName, string $recordType): void
    {
        $payload = [
            'rrsets' => [
                [
                    'name' => $recordName,
                    'type' => $recordType,
                    'changetype' => 'DELETE',
                ],
            ],
        ];
        $this->client->patch('zones/' . urlencode($zoneId), $payload);
    }
    public function bulkUpdate(string $zoneId, array $rrsets): array
    {
        $payload = ['rrsets' => $rrsets];
        return $this->client->patch('zones/' . urlencode($zoneId), $payload);
    }
}
