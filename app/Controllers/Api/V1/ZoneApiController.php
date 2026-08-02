<?php
declare(strict_types=1);
namespace App\Controllers\Api\V1;
use App\Core\Request;
use App\Core\Response;
use App\Services\DNS\ZoneService;
use App\Core\Exceptions\ValidationException;

class ZoneApiController
{
    private ZoneService $zoneService;
    public function __construct(ZoneService $zoneService)
    {
        $this->zoneService = $zoneService;
    }
    public function index(Request $request): Response
    {
        $zones = $this->zoneService->getAllZones($request->getQueryParams());
        return (new Response())->json(['data' => $zones]);
    }
    public function store(Request $request): Response
    {
        try {
            $data = $request->getParsedBody();
            $zone = $this->zoneService->createZone($data);
            return (new Response())->json(['data' => $zone], 201);
        } catch (ValidationException $e) {
            return (new Response())->json(['error' => $e->getErrors()], 422);
        }
    }
    public function show(Request $request, string $id): Response
    {
        $zone = $this->zoneService->getZone($id);
        return (new Response())->json(['data' => $zone]);
    }
    public function update(Request $request, string $id): Response
    {
        $data = $request->getParsedBody();
        $zone = $this->zoneService->updateZone($id, $data);
        return (new Response())->json(['data' => $zone]);
    }
    public function destroy(Request $request, string $id): Response
    {
        $this->zoneService->deleteZone($id);
        return (new Response())->json(['message' => 'Zone deleted'], 204);
    }
    public function clone(Request $request, string $id): Response
    {
        $data = $request->getParsedBody();
        if (empty($data['new_name'])) {
            return (new Response())->json(['error' => 'new_name required'], 422);
        }
        $zone = $this->zoneService->cloneZone($id, $data['new_name']);
        return (new Response())->json(['data' => $zone]);
    }
    public function check(Request $request, string $id): Response
    {
        $result = $this->zoneService->checkZone($id);
        return (new Response())->json(['data' => $result]);
    }
    public function export(Request $request, string $id): Response
    {
        $export = $this->zoneService->exportZone($id);
        return (new Response())->json(['data' => $export]);
    }
}
