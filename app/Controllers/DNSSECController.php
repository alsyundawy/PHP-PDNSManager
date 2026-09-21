<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\DNS\DNSSECService;
use App\Services\DNS\ZoneService;

class DNSSECController
{
    private const ZONE_PATH_PREFIX = '/zones/';

    private DNSSECService $dnssecService;
    private ZoneService $zoneService;

    public function __construct(DNSSECService $dnssecService, ZoneService $zoneService)
    {
        $this->dnssecService = $dnssecService;
        $this->zoneService = $zoneService;
    }

    public function enable(Request $request, string $zoneId): Response
    {
        if ($request->getMethod() === 'POST') {
            $this->zoneService->updateZone($zoneId, ['dnssec' => true]);
        }
        return $this->redirectToZone($zoneId);
    }

    public function disable(Request $request, string $zoneId): Response
    {
        if ($request->getMethod() === 'POST') {
            $this->zoneService->updateZone($zoneId, ['dnssec' => false]);
        }
        return $this->redirectToZone($zoneId);
    }

    public function createKey(Request $request, string $zoneId): Response
    {
        if ($request->getMethod() === 'POST') {
            $data = [
                'keytype' => $request->input('keytype', 'ksk'),
                'active' => (bool) $request->input('active', true),
                'bits' => (int) $request->input('bits', 2048),
                'algorithm' => $request->input('algorithm', 'rsasha256'),
            ];
            $this->dnssecService->createKey($zoneId, $data);
        }
        return $this->redirectToZone($zoneId);
    }

    public function deleteKey(Request $request, string $zoneId, string $keyId): Response
    {
        if ($request->getMethod() === 'POST') {
            $this->dnssecService->deleteKey($zoneId, $keyId);
        }
        return $this->redirectToZone($zoneId);
    }

    public function activateKey(Request $request, string $zoneId, string $keyId): Response
    {
        if ($request->getMethod() === 'POST') {
            $this->dnssecService->activateKey($zoneId, $keyId);
        }
        return $this->redirectToZone($zoneId);
    }

    public function deactivateKey(Request $request, string $zoneId, string $keyId): Response
    {
        if ($request->getMethod() === 'POST') {
            $this->dnssecService->deactivateKey($zoneId, $keyId);
        }
        return $this->redirectToZone($zoneId);
    }

    private function redirectToZone(string $zoneId): Response
    {
        return (new Response())->redirect(self::ZONE_PATH_PREFIX . urlencode($zoneId));
    }
}
