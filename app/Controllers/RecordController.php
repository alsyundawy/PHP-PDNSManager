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
            return (new Response())->redirect('/zones/' . urlencode($zoneId));
        } catch (ValidationException $e) {
            $error = $e->getErrors();
            $zone = $this->zoneService->getZone($zoneId);
            $records = $this->recordService->getRecordsForZone($zoneId);
            $keys = []; // not needed for error
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
            return (new Response())->redirect('/zones/' . urlencode($zoneId));
        } catch (ValidationException $e) {
            return (new Response())->redirect('/zones/' . urlencode($zoneId) . '?error=' . urlencode($e->getMessage()));
        }
    }
    public function delete(Request $request, string $zoneId, string $recordName, string $recordType): Response
    {
        $this->recordService->deleteRecord($zoneId, $recordName, $recordType);
        return (new Response())->redirect('/zones/' . urlencode($zoneId));
    }
    public function bulk(Request $request, string $zoneId): Response
    {
        if ($request->getMethod() === 'POST') {
            $action = $request->input('action');
            $selected = $request->input('selected', []);
            if (empty($selected)) {
                return (new Response())->redirect('/zones/' . urlencode($zoneId) . '?error=No records selected');
            }
            if ($action === 'delete') {
                foreach ($selected as $recordKey) {
                    [$name, $type] = explode('|', $recordKey, 2);
                    $this->recordService->deleteRecord($zoneId, $name, $type);
                }
            } elseif ($action === 'update_ttl') {
                $newTtl = (int) $request->input('ttl', 3600);
                foreach ($selected as $recordKey) {
                    [$name, $type] = explode('|', $recordKey, 2);
                    $this->recordService->updateRecord($zoneId, $name, $type, ['ttl' => $newTtl]);
                }
            }
            return (new Response())->redirect('/zones/' . urlencode($zoneId) . '?success=1');
        }
        return (new Response())->redirect('/zones/' . urlencode($zoneId));
    }
}
