<?php
declare(strict_types=1);
namespace App\Services\DNS;

use App\Services\PowerDNS\Resource\RecordResource;
use App\Core\Logger;
use App\Core\Exceptions\ValidationException;

class RecordService
{
    private RecordResource $recordResource;
    private Logger $logger;

    public function __construct(RecordResource $recordResource, Logger $logger)
    {
        $this->recordResource = $recordResource;
        $this->logger         = $logger;
    }

    public function getRecordsForZone(string $zoneId): array
    {
        return $this->recordResource->getForZone($zoneId);
    }

    public function createRecord(string $zoneId, array $data): array
    {
        $this->validateRecordData($data);
        $result = $this->recordResource->create($zoneId, $data);
        $this->logger->info('Record created', [
            'zone' => $zoneId,
            'name' => $data['name'],
            'type' => $data['type'],
        ]);
        return $result;
    }

    public function updateRecord(string $zoneId, string $recordName, string $recordType, array $data): array
    {
        $this->validateRecordData($data);
        $result = $this->recordResource->update($zoneId, $recordName, $recordType, $data);
        $this->logger->info('Record updated', [
            'zone' => $zoneId,
            'name' => $recordName,
            'type' => $recordType,
        ]);
        return $result;
    }

    public function deleteRecord(string $zoneId, string $recordName, string $recordType): void
    {
        $this->recordResource->delete($zoneId, $recordName, $recordType);
        $this->logger->info('Record deleted', [
            'zone' => $zoneId,
            'name' => $recordName,
            'type' => $recordType,
        ]);
    }

    public function bulkUpdate(string $zoneId, array $rrsets): array
    {
        $result = $this->recordResource->bulkUpdate($zoneId, $rrsets);
        $this->logger->info('Bulk records updated', ['zone' => $zoneId, 'count' => count($rrsets)]);
        return $result;
    }

    public function importRecords(string $zoneId, array $records): array
    {
        $rrsets = [];
        foreach ($records as $record) {
            $this->validateRecordData($record);
            $rrsets[] = [
                'name'       => $record['name'],
                'type'       => $record['type'],
                'ttl'        => (int) ($record['ttl'] ?? 3600),
                'changetype' => 'REPLACE',
                'records'    => [['content' => $record['content'], 'disabled' => false]],
            ];
        }
        return $this->bulkUpdate($zoneId, $rrsets);
    }

    public function exportRecords(string $zoneId): array
    {
        return $this->getRecordsForZone($zoneId);
    }

    private function validateRecordData(array $data): void
    {
        $errors = [];
        if (empty($data['name'])) {
            $errors['name'] = 'Record name is required';
        }
        if (empty($data['type'])) {
            $errors['type'] = 'Record type is required';
        }
        if (empty($data['content'])) {
            $errors['content'] = 'Record content is required';
        }
        // BUGFIX: Original check '!isset($data[\'ttl\']) || (int) $data[\'ttl\'] < 0'
        // allows TTL = 0 which is valid in DNS. Corrected to allow >= 0.
        if (isset($data['ttl']) && (int) $data['ttl'] < 0) {
            $errors['ttl'] = 'TTL must be a non-negative integer';
        }
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
