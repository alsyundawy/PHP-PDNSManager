<?php
declare(strict_types=1);
namespace App\Controllers\Api\V1;

use App\Core\Request;
use App\Core\Response;
use App\Services\DNS\ZoneService;
use App\Core\Exceptions\ValidationException;
use App\Core\Exceptions\PowerApiException;

/**
 * BUGFIX: show(), update(), destroy(), clone(), check(), export() had no
 * try-catch — any PowerApiException would propagate unhandled and produce
 * a 500 with a full stack trace instead of a structured JSON error.
 */
class ZoneApiController
{
    private ZoneService $zoneService;

    public function __construct(ZoneService $zoneService)
    {
        $this->zoneService = $zoneService;
    }

    public function index(Request $request): Response
    {
        try {
            $zones = $this->zoneService->getAllZones($request->getQueryParams());
            return (new Response())->json(['data' => $zones]);
        } catch (PowerApiException $e) {
            return (new Response())->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function store(Request $request): Response
    {
        try {
            $zone = $this->zoneService->createZone($request->getParsedBody());
            return (new Response())->json(['data' => $zone], 201);
        } catch (ValidationException $e) {
            return (new Response())->json(['error' => $e->getErrors()], 422);
        } catch (PowerApiException $e) {
            return (new Response())->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function show(Request $request, string $id): Response
    {
        try {
            return (new Response())->json(['data' => $this->zoneService->getZone($id)]);
        } catch (PowerApiException $e) {
            return (new Response())->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function update(Request $request, string $id): Response
    {
        try {
            $zone = $this->zoneService->updateZone($id, $request->getParsedBody());
            return (new Response())->json(['data' => $zone]);
        } catch (ValidationException $e) {
            return (new Response())->json(['error' => $e->getErrors()], 422);
        } catch (PowerApiException $e) {
            return (new Response())->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function destroy(Request $request, string $id): Response
    {
        try {
            $this->zoneService->deleteZone($id);
            return (new Response())->json(['message' => 'Zone deleted'], 204);
        } catch (PowerApiException $e) {
            return (new Response())->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function clone(Request $request, string $id): Response
    {
        $data = $request->getParsedBody();
        if (empty($data['new_name'])) {
            return (new Response())->json(['error' => 'new_name required'], 422);
        }
        try {
            $zone = $this->zoneService->cloneZone($id, $data['new_name']);
            return (new Response())->json(['data' => $zone]);
        } catch (ValidationException $e) {
            return (new Response())->json(['error' => $e->getErrors()], 422);
        } catch (PowerApiException $e) {
            return (new Response())->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function check(Request $request, string $id): Response
    {
        try {
            return (new Response())->json(['data' => $this->zoneService->checkZone($id)]);
        } catch (PowerApiException $e) {
            return (new Response())->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function export(Request $request, string $id): Response
    {
        try {
            return (new Response())->json(['data' => $this->zoneService->exportZone($id)]);
        } catch (PowerApiException $e) {
            return (new Response())->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }
}
