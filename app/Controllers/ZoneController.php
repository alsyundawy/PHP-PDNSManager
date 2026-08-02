<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Request;
use App\Core\Response;
use App\Core\Exceptions\ValidationException;
use App\Services\DNS\ZoneService;
use App\Services\DNS\RecordService;
use App\Services\DNS\DNSSECService;

class ZoneController
{
    private ZoneService $zoneService;
    private RecordService $recordService;
    private DNSSECService $dnssecService;
    public function __construct(
        ZoneService $zoneService,
        RecordService $recordService,
        DNSSECService $dnssecService
    ) {
        $this->zoneService = $zoneService;
        $this->recordService = $recordService;
        $this->dnssecService = $dnssecService;
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
                    'name' => $request->input('name'),
                    'kind' => $request->input('kind', 'Native'),
                    'masters' => array_filter(explode(',', $request->input('masters', ''))),
                    'nameservers' => array_filter(explode(',', $request->input('nameservers', ''))),
                    'dnssec' => (bool) $request->input('dnssec', false),
                ];
                $zone = $this->zoneService->createZone($data);
                return (new Response())->redirect('/zones/' . urlencode($zone['name']));
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
                    'kind' => $request->input('kind', $zone['kind']),
                    'masters' => array_filter(explode(',', $request->input('masters', ''))),
                    'nameservers' => array_filter(explode(',', $request->input('nameservers', ''))),
                ];
                $this->zoneService->updateZone($zoneId, $data);
                return (new Response())->redirect('/zones/' . urlencode($zoneId));
            } catch (ValidationException $e) {
                $error = $e->getErrors();
                $html = view('zone.edit', ['zone' => $zone, 'error' => $error, 'user' => $request->getAttribute('user'), 'csrfToken' => csrf_token()]);
                return (new Response())->html($html)->withStatus(422);
            }
        }
        $html = view('zone.edit', ['zone' => $zone, 'user' => $request->getAttribute('user'), 'csrfToken' => csrf_token()]);
        return (new Response())->html($html);
    }
    public function delete(Request $request, string $zoneId): Response
    {
        if ($request->getMethod() === 'POST') {
            $this->zoneService->deleteZone($zoneId);
            return (new Response())->redirect('/zones');
        }
        return (new Response())->redirect('/zones');
    }
    public function clone(Request $request, string $zoneId): Response
    {
        if ($request->getMethod() === 'POST') {
            $newName = $request->input('new_name');
            if (empty($newName)) {
                throw new ValidationException(['new_name' => 'New zone name is required']);
            }
            $this->zoneService->cloneZone($zoneId, $newName);
            return (new Response())->redirect('/zones/' . urlencode($newName));
        }
        $zone = $this->zoneService->getZone($zoneId);
        $html = view('zone.clone', ['zone' => $zone, 'user' => $request->getAttribute('user'), 'csrfToken' => csrf_token()]);
        return (new Response())->html($html);
    }
    public function check(Request $request, string $zoneId): Response
    {
        $result = $this->zoneService->checkZone($zoneId);
        return (new Response())->json($result);
    }
    public function export(Request $request, string $zoneId): Response
    {
        $export = $this->zoneService->exportZone($zoneId);
        return (new Response())->json($export);
    }
}
