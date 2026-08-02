<?php
declare(strict_types=1);
namespace App\Services\DNS;
use App\Services\PowerDNS\Resource\ZoneResource;
use App\Services\PowerDNS\Resource\RecordResource;
use App\Services\PowerDNS\Resource\CryptokeyResource;
use App\Core\Logger;
use App\Core\Exceptions\PowerApiException;
use App\Core\Exceptions\ValidationException;

class ZoneService
{
    private ZoneResource $zoneResource;
    private RecordResource $recordResource;
    private CryptokeyResource $cryptokeyResource;
    private Logger $logger;
    public function __construct(
        ZoneResource $zoneResource,
        RecordResource $recordResource,
        CryptokeyResource $cryptokeyResource,
        Logger $logger
    ) {
        $this->zoneResource = $zoneResource;
        $this->recordResource = $recordResource;
        $this->cryptokeyResource = $cryptokeyResource;
        $this->logger = $logger;
    }
    public function getAllZones(array $filters = []): array
    {
        return $this->zoneResource->getAll($filters);
    }
    public function getZone(string $zoneId): array
    {
        return $this->zoneResource->get($zoneId);
    }
    public function createZone(array $data): array
    {
        $this->validateZoneData($data);
        $payload = [
            'name' => $data['name'],
            'kind' => $data['kind'] ?? 'Native',
            'masters' => $data['masters'] ?? [],
            'nameservers' => $data['nameservers'] ?? [],
            'dnssec' => (bool) ($data['dnssec'] ?? false),
        ];
        if (isset($data['catalog'])) {
            $payload['catalog'] = $data['catalog'];
        }
        if (isset($data['soa_edit_api'])) {
            $payload['soa_edit_api'] = $data['soa_edit_api'];
        }
        $zone = $this->zoneResource->create($payload);
        $this->logger->info('Zone created', ['zone' => $data['name']]);
        return $zone;
    }
    public function updateZone(string $zoneId, array $data): array
    {
        $allowed = ['kind', 'masters', 'nameservers', 'soa_edit_api', 'catalog'];
        $payload = array_intersect_key($data, array_flip($allowed));
        if (empty($payload)) {
            throw new ValidationException(['message' => 'No valid fields to update']);
        }
        $zone = $this->zoneResource->patch($zoneId, $payload);
        $this->logger->info('Zone updated', ['zone' => $zoneId]);
        return $zone;
    }
    public function deleteZone(string $zoneId): void
    {
        $this->zoneResource->delete($zoneId);
        $this->logger->info('Zone deleted', ['zone' => $zoneId]);
    }
    public function cloneZone(string $sourceZoneId, string $newName): array
    {
        $source = $this->getZone($sourceZoneId);
        $newZone = $this->createZone([
            'name' => $newName,
            'kind' => $source['kind'],
            'masters' => $source['masters'] ?? [],
            'nameservers' => $source['nameservers'] ?? [],
            'dnssec' => $source['dnssec'] ?? false,
        ]);
        $records = $this->recordResource->getForZone($sourceZoneId);
        $rrsets = [];
        foreach ($records as $record) {
            if ($record['type'] === 'SOA') {
                continue;
            }
            $rrsets[] = [
                'name' => str_replace($source['name'], $newName, $record['name']),
                'type' => $record['type'],
                'ttl' => $record['ttl'],
                'changetype' => 'REPLACE',
                'records' => $record['records'],
            ];
        }
        if (!empty($rrsets)) {
            $this->recordResource->bulkUpdate($newName, $rrsets);
        }
        $this->logger->info('Zone cloned', ['source' => $sourceZoneId, 'new' => $newName]);
        return $newZone;
    }
    public function checkZone(string $zoneId): array
    {
        return $this->zoneResource->check($zoneId);
    }
    public function diffZones(string $zoneId1, string $zoneId2): array
    {
        $zone1 = $this->getZone($zoneId1);
        $zone2 = $this->getZone($zoneId2);
        $records1 = $this->recordResource->getForZone($zoneId1);
        $records2 = $this->recordResource->getForZone($zoneId2);
        $map1 = [];
        foreach ($records1 as $r) {
            $key = $r['name'] . '|' . $r['type'];
            $map1[$key] = $r;
        }
        $map2 = [];
        foreach ($records2 as $r) {
            $key = $r['name'] . '|' . $r['type'];
            $map2[$key] = $r;
        }
        $diff = [
            'only_in_first' => [],
            'only_in_second' => [],
            'different' => [],
        ];
        foreach ($map1 as $key => $record) {
            if (!isset($map2[$key])) {
                $diff['only_in_first'][] = $record;
            } else {
                $record2 = $map2[$key];
                if ($record['ttl'] !== $record2['ttl'] || $record['records'] !== $record2['records']) {
                    $diff['different'][] = ['first' => $record, 'second' => $record2];
                }
            }
        }
        foreach ($map2 as $key => $record) {
            if (!isset($map1[$key])) {
                $diff['only_in_second'][] = $record;
            }
        }
        return $diff;
    }
    public function exportZone(string $zoneId): array
    {
        return $this->zoneResource->export($zoneId);
    }
    private function validateZoneData(array $data): void
    {
        $errors = [];
        if (empty($data['name'])) {
            $errors['name'] = 'Zone name is required';
        } elseif (!filter_var($data['name'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            $errors['name'] = 'Invalid zone name (must be a valid domain)';
        }
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
