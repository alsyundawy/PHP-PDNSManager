<?php
declare(strict_types=1);
namespace App\Controllers\Api\V1;
use App\Core\Request;
use App\Core\Response;
use App\Services\DNS\DNSSECService;
use App\Services\DNS\ZoneService;

class DNSSECApiController
{
    private DNSSECService $dnssecService;
    private ZoneService $zoneService;
    public function __construct(DNSSECService $dnssecService, ZoneService $zoneService)
    {
        $this->dnssecService = $dnssecService;
        $this->zoneService = $zoneService;
    }
    public function enable(Request $request, string $zoneId): Response
    {
        $this->zoneService->updateZone($zoneId, ['dnssec' => true]);
        return (new Response())->json(['message' => 'DNSSEC enabled']);
    }
    public function disable(Request $request, string $zoneId): Response
    {
        $this->zoneService->updateZone($zoneId, ['dnssec' => false]);
        return (new Response())->json(['message' => 'DNSSEC disabled']);
    }
    public function keys(Request $request, string $zoneId): Response
    {
        $keys = $this->dnssecService->getKeys($zoneId);
        return (new Response())->json(['data' => $keys]);
    }
    public function createKey(Request $request, string $zoneId): Response
    {
        $data = $request->getParsedBody();
        $key = $this->dnssecService->createKey($zoneId, $data);
        return (new Response())->json(['data' => $key], 201);
    }
    public function deleteKey(Request $request, string $zoneId, string $keyId): Response
    {
        $this->dnssecService->deleteKey($zoneId, $keyId);
        return (new Response())->json(['message' => 'Key deleted'], 204);
    }
}
