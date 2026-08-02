<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Request;
use App\Core\Response;
use App\Core\Exceptions\ValidationException;
use App\Services\DNS\RecordService;
use App\Services\DNS\ZoneService;

class RecordController
{
    private const ZONES_PATH = '/zones/';
    private RecordService $recordService;
    private ZoneService $zoneService;
    public function __construct(RecordService $recordService, ZoneService $zoneService)
    {
        $this->recordService = $recordService;
        $this->zoneService = $zoneService;
    }
    public function store(Request $request, string $zoneId): Response
    {
        try {
            $data = [
                'name' => $request->input('name'),
                'type' => $request->input('type'),
                'content' => $request->input('content'),
                'ttl' => (int) $request->input('ttl', 3600),
            ];
            $this->recordService->createRecord($zoneId, $data);
            return (new Response())->redirect(self::ZONES_PATH . urlencode($zoneId));
        } catch (ValidationException $e) {
            $error = $e->getErrors();
            $zone = $this->zoneService->getZone($zoneId);
            $records = $this->recordService->getRecordsForZone($zoneId);
            $keys = [];
            $html = view('zone.show', ['zone' => $zone, 'records' => $records, 'keys' => $keys, 'error' => $error, 'user' => $request->getAttribute('user'), 'csrfToken' => csrf_token()]);
            return (new Response())->html($html)->withStatus(422);
        }
    }
    public function update(Request $request, string $zoneId, string $recordName, string $recordType): Response
    {
        try {
            $data = [
                'name' => $recordName,
                'type' => $recordType,
                'content' => $request->input('content'),
                'ttl' => (int) $request->input('ttl', 3600),
            ];
            $this->recordService->updateRecord($zoneId, $recordName, $recordType, $data);
            return (new Response())->redirect(self::ZONES_PATH . urlencode($zoneId));
        } catch (ValidationException $e) {
            return (new Response())->redirect(self::ZONES_PATH . urlencode($zoneId) . '?error=' . urlencode($e->getMessage()));
        }
    }
    public function delete(Request $request, string $zoneId, string $recordName, string $recordType): Response // NOSONAR
    {
        $this->recordService->deleteRecord($zoneId, $recordName, $recordType);
        return (new Response())->redirect(self::ZONES_PATH . urlencode($zoneId));
    }
    public function bulk(Request $request, string $zoneId): Response
    {
        if ($request->getMethod() !== 'POST') {
            return (new Response())->redirect(self::ZONES_PATH . urlencode($zoneId));
        }
        $selected = (array) $request->input('selected', []);
        if (empty($selected)) {
            return (new Response())->redirect(self::ZONES_PATH . urlencode($zoneId) . '?error=No records selected');
        }
        $action = (string) $request->input('action');
        if ($action === 'delete') {
            $this->handleBulkDelete($zoneId, $selected);
        } elseif ($action === 'update_ttl') {
            $newTtl = (int) $request->input('ttl', 3600);
            $this->handleBulkUpdateTtl($zoneId, $selected, $newTtl);
        }
        return (new Response())->redirect(self::ZONES_PATH . urlencode($zoneId) . '?success=1');
    }
    private function handleBulkDelete(string $zoneId, array $selected): void
    {
        foreach ($selected as $recordKey) {
            $parts = explode('|', (string) $recordKey, 2);
            if (count($parts) === 2) {
                $this->recordService->deleteRecord($zoneId, $parts[0], $parts[1]);
            }
        }
    }
    private function handleBulkUpdateTtl(string $zoneId, array $selected, int $newTtl): void
    {
        foreach ($selected as $recordKey) {
            $parts = explode('|', (string) $recordKey, 2);
            if (count($parts) === 2) {
                $this->recordService->updateRecord($zoneId, $parts[0], $parts[1], ['ttl' => $newTtl]);
            }
        }
    }
}
