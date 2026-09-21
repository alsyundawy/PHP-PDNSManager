<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Exceptions\ValidationException;
use App\Services\DNS\ZoneService;
use App\Services\DNS\RecordService;
use App\Services\DNS\DNSSECService;
use App\Services\DNS\BindZoneService;
use App\Services\DNS\BulkRecordService;

class ZoneController
{
    private const ZONES_PATH = '/zones/';
    private const ZONES_URL = '/zones';
    private ZoneService $zoneService;
    private RecordService $recordService;
    private DNSSECService $dnssecService;
    private ?BindZoneService $bindService;
    private ?BulkRecordService $bulkService;

    public function __construct(
        ZoneService $zoneService,
        RecordService $recordService,
        DNSSECService $dnssecService,
        ?BindZoneService $bindService = null,
        ?BulkRecordService $bulkService = null
    ) {
        $this->zoneService = $zoneService;
        $this->recordService = $recordService;
        $this->dnssecService = $dnssecService;
        $this->bindService = $bindService;
        $this->bulkService = $bulkService;
    }
    public function index(Request $request): Response
    {
        $filters = [];
        if ($request->has('name')) {
            $filters['name'] = $request->input('name');
        }
        if ($request->has('type')) {
            $filters['type'] = $request->input('type');
        }
        $zones = $this->zoneService->getAllZones($filters);
        $html = view('zone.index', ['zones' => $zones, 'user' => $request->getAttribute('user'), 'csrfToken' => csrf_token()]);
        return (new Response())->html($html);
    }
    public function show(Request $request, string $zoneId): Response
    {
        $zone = $this->zoneService->getZone($zoneId);
        $records = $this->recordService->getRecordsForZone($zoneId);
        $keys = $this->dnssecService->getKeys($zoneId);
        $html = view('zone.show', ['zone' => $zone, 'records' => $records, 'keys' => $keys, 'user' => $request->getAttribute('user'), 'csrfToken' => csrf_token()]);
        return (new Response())->html($html);
    }
    public function create(Request $request): Response
    {
        if ($request->getMethod() === 'POST') {
            try {
                $data = [
                    'name' => (string) $request->input('name'),
                    'kind' => (string) $request->input('kind', 'Native'),
                    'masters' => array_filter(array_map('trim', explode(',', (string) $request->input('masters', '')))),
                    'nameservers' => array_filter(array_map('trim', explode(',', (string) $request->input('nameservers', '')))),
                    'dnssec' => (bool) $request->input('dnssec', false),
                ];
                $zone = $this->zoneService->createZone($data);
                return (new Response())->redirect(self::ZONES_PATH . urlencode($zone['name']));
            } catch (ValidationException $e) {
                $error = $e->getErrors();
                $html = view('zone.create', ['error' => $error, 'user' => $request->getAttribute('user'), 'csrfToken' => csrf_token()]);
                return (new Response())->html($html)->withStatus(422);
            }
        }
        $html = view('zone.create', ['user' => $request->getAttribute('user'), 'csrfToken' => csrf_token()]);
        return (new Response())->html($html);
    }
    public function edit(Request $request, string $zoneId): Response
    {
        $zone = $this->zoneService->getZone($zoneId);
        if ($request->getMethod() === 'POST') {
            try {
                $data = [
                    'kind' => (string) $request->input('kind', $zone['kind'] ?? 'Native'),
                    'masters' => array_filter(array_map('trim', explode(',', (string) $request->input('masters', '')))),
                    'nameservers' => array_filter(array_map('trim', explode(',', (string) $request->input('nameservers', '')))),
                ];
                $this->zoneService->updateZone($zoneId, $data);
                return (new Response())->redirect(self::ZONES_PATH . urlencode($zoneId));
            } catch (ValidationException $e) {
                $error = $e->getErrors();
                $html = view('zone.edit', ['zone' => $zone, 'error' => $error, 'user' => $request->getAttribute('user'), 'csrfToken' => csrf_token()]);
                return (new Response())->html($html)->withStatus(422);
            }
        }
        $html = view('zone.edit', ['zone' => $zone, 'user' => $request->getAttribute('user'), 'csrfToken' => csrf_token()]);
        return (new Response())->html($html);
    }
    public function delete(Request $request, string $zoneId): Response // NOSONAR
    {
        if ($request->getMethod() === 'POST') {
            $this->zoneService->deleteZone($zoneId);
        }
        return (new Response())->redirect(self::ZONES_URL);
    }
    public function clone(Request $request, string $zoneId): Response
    {
        if ($request->getMethod() === 'POST') {
            $newName = (string) $request->input('new_name');
            if (empty($newName)) {
                throw new ValidationException(['new_name' => 'New zone name is required']);
            }
            $this->zoneService->cloneZone($zoneId, $newName);
            return (new Response())->redirect(self::ZONES_PATH . urlencode($newName));
        }
        $zone = $this->zoneService->getZone($zoneId);
        $html = view('zone.clone', ['zone' => $zone, 'user' => $request->getAttribute('user'), 'csrfToken' => csrf_token()]);
        return (new Response())->html($html);
    }
    public function check(Request $request, string $zoneId): Response // NOSONAR
    {
        $result = $this->zoneService->checkZone($zoneId);
        return (new Response())->json($result);
    }
    public function export(Request $request, string $zoneId): Response // NOSONAR
    {
        $export = $this->zoneService->exportZone($zoneId);
        return (new Response())->json($export);
    }

    public function import(Request $request): Response
    {
        if ($request->getMethod() === 'POST') {
            return $this->processImport($request);
        }

        $html = view('zone.import', [
            'user' => $request->getAttribute('user'),
            'csrfToken' => csrf_token(),
        ]);
        return (new Response())->html($html);
    }

    private function processImport(Request $request): Response
    {
        $content = (string) $request->input('zone_content', '');
        $origin = (string) $request->input('origin', '');

        if ($this->bindService === null) {
            return (new Response())->redirect(self::ZONES_URL);
        }

        try {
            $parsed = $this->bindService->parse($content, $origin !== '' ? $origin : null);
            $zoneName = $parsed['origin'];

            // Create zone if not exists
            $this->zoneService->createZone([
                'name' => $zoneName,
                'kind' => 'Native',
            ]);

            // Create parsed records
            foreach ($parsed['records'] as $r) {
                if ($r['type'] === 'SOA') {
                    continue;
                }
                $this->recordService->createRecord($zoneName, [
                    'name' => $r['name'],
                    'type' => $r['type'],
                    'ttl' => $r['ttl'],
                    'changetype' => 'REPLACE',
                    'records' => [
                        [
                            'content' => $r['content'],
                            'disabled' => false,
                        ],
                    ],
                ]);
            }

            return (new Response())->redirect(self::ZONES_PATH . urlencode($zoneName));
        } catch (\Throwable $e) {
            $html = view('zone.import', [
                'error' => $e->getMessage(),
                'user' => $request->getAttribute('user'),
                'csrfToken' => csrf_token(),
            ]);
            return (new Response(400))->html($html);
        }
    }

    public function exportBind(Request $request, string $zoneId): Response // NOSONAR
    {
        if ($this->bindService === null) {
            return (new Response())->redirect(self::ZONES_PATH . urlencode($zoneId));
        }

        $records = $this->recordService->getRecordsForZone($zoneId);
        $flatRecords = [];
        foreach ($records as $rr) {
            foreach ($rr['records'] ?? [] as $r) {
                $flatRecords[] = [
                    'name' => $rr['name'],
                    'type' => $rr['type'],
                    'ttl' => $rr['ttl'] ?? 3600,
                    'content' => $r['content'] ?? '',
                ];
            }
        }

        $bindText = $this->bindService->serialize($zoneId, $flatRecords);
        $cleanName = rtrim($zoneId, '.');

        $response = new Response(200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $cleanName . '.zone"',
        ]);
        $response->getBody()->write($bindText);
        return $response;
    }

    public function bulk(Request $request): Response
    {
        if ($this->bulkService === null) {
            return (new Response())->redirect(self::ZONES_URL);
        }

        if ($request->getMethod() === 'POST') {
            $action = (string) $request->input('action', 'update');
            if ($action === 'delete') {
                $targets = (array) ($request->input('targets') ?? []);
                $results = $this->bulkService->batchDelete($targets);
            } else {
                $updates = (array) ($request->input('updates') ?? []);
                $results = $this->bulkService->batchUpdate($updates);
            }
            $html = view('record.bulk', [
                'results' => $results,
                'user' => $request->getAttribute('user'),
                'csrfToken' => csrf_token(),
            ]);
            return (new Response())->html($html);
        }

        $query = (string) $request->input('q', '');
        $type = (string) $request->input('type', '');
        $records = [];
        if ($query !== '' || $type !== '') {
            $records = $this->bulkService->searchRecords($query, $type !== '' ? $type : null);
        }

        $html = view('record.bulk', [
            'query' => $query,
            'type' => $type,
            'records' => $records,
            'user' => $request->getAttribute('user'),
            'csrfToken' => csrf_token(),
        ]);
        return (new Response())->html($html);
    }
}
