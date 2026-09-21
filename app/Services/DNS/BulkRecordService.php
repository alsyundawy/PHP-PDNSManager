<?php

declare(strict_types=1);

namespace App\Services\DNS;

use App\Core\Logger;

class BulkRecordService
{
    private ZoneService $zoneService;
    private RecordService $recordService;
    private Logger $logger;

    public function __construct(ZoneService $zoneService, RecordService $recordService, Logger $logger)
    {
        $this->zoneService = $zoneService;
        $this->recordService = $recordService;
        $this->logger = $logger;
    }

    /**
     * Search records across all zones matching query name or content.
     */
    public function searchRecords(string $query, ?string $type = null): array
    {
        $zones = $this->zoneService->getAllZones();
        $results = [];
        $queryLower = strtolower(trim($query));
        $typeUpper = $type !== null ? strtoupper(trim($type)) : null;

        foreach ($zones as $zone) {
            $zoneId = $zone['id'] ?? '';
            try {
                $records = $this->recordService->getRecordsForZone($zoneId);
                foreach ($records as $record) {
                    $recType = strtoupper((string) ($record['type'] ?? ''));
                    if ($typeUpper !== null && $typeUpper !== '' && $recType !== $typeUpper) {
                        continue;
                    }

                    $recName = (string) ($record['name'] ?? '');
                    $recordsList = $record['records'] ?? [];

                    foreach ($recordsList as $contentItem) {
                        $content = (string) ($contentItem['content'] ?? '');
                        if (
                            $queryLower === '' ||
                            str_contains(strtolower($recName), $queryLower) ||
                            str_contains(strtolower($content), $queryLower)
                        ) {
                            $results[] = [
                                'zone_id' => $zoneId,
                                'zone_name' => $zone['name'] ?? $zoneId,
                                'name' => $recName,
                                'type' => $recType,
                                'ttl' => $record['ttl'] ?? 3600,
                                'content' => $content,
                                'disabled' => (bool) ($contentItem['disabled'] ?? false),
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                $this->logger->warning("Could not search records in zone {$zoneId}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Apply batch record updates across multiple zones.
     *
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array{zone_id: string, name: string, type: string, status: string, error?: string}>
     */
    public function batchUpdate(array $items): array
    {
        $results = [];
        foreach ($items as $item) {
            $zoneId = (string) ($item['zone_id'] ?? '');
            $name = (string) ($item['name'] ?? '');
            $type = strtoupper((string) ($item['type'] ?? 'A'));
            $newContent = (string) ($item['new_content'] ?? '');
            $ttl = (int) ($item['ttl'] ?? 3600);

            try {
                $this->recordService->updateRecord($zoneId, $name, $type, [
                    'ttl' => $ttl,
                    'changetype' => 'REPLACE',
                    'records' => [
                        [
                            'content' => $newContent,
                            'disabled' => false,
                        ],
                    ],
                ]);
                $results[] = [
                    'zone_id' => $zoneId,
                    'name' => $name,
                    'type' => $type,
                    'status' => 'success',
                ];
            } catch (\Throwable $e) {
                $this->logger->error("Batch update failed for {$name} in {$zoneId}: " . $e->getMessage());
                $results[] = [
                    'zone_id' => $zoneId,
                    'name' => $name,
                    'type' => $type,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Delete multiple records across multiple zones.
     *
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array{zone_id: string, name: string, type: string, status: string, error?: string}>
     */
    public function batchDelete(array $items): array
    {
        $results = [];
        foreach ($items as $item) {
            $zoneId = (string) ($item['zone_id'] ?? '');
            $name = (string) ($item['name'] ?? '');
            $type = strtoupper((string) ($item['type'] ?? ''));

            try {
                $this->recordService->deleteRecord($zoneId, $name, $type);
                $results[] = [
                    'zone_id' => $zoneId,
                    'name' => $name,
                    'type' => $type,
                    'status' => 'success',
                ];
            } catch (\Throwable $e) {
                $this->logger->error("Batch delete failed for {$name} in {$zoneId}: " . $e->getMessage());
                $results[] = [
                    'zone_id' => $zoneId,
                    'name' => $name,
                    'type' => $type,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
