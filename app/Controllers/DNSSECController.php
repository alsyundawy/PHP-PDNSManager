<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Request;
use App\Core\Response;
use App\Services\DNS\DNSSECService;
use App\Services\DNS\ZoneService;

class DNSSECController
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
        if ($request->getMethod() === 'POST') {
            $this->zoneService->updateZone($zoneId, ['dnssec' => true]);
            return (new Response())->redirect('/zones/' . urlencode($zoneId));
        }
        return (new Response())->redirect('/zones/' . urlencode($zoneId));
    }
    public function disable(Request $request, string $zoneId): Response
    {
        if ($request->getMethod() === 'POST') {
            $this->zoneService->updateZone($zoneId, ['dnssec' => false]);
            return (new Response())->redirect('/zones/' . urlencode($zoneId));
        }
        return (new Response())->redirect('/zones/' . urlencode($zoneId));
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
            return (new Response())->redirect('/zones/' . urlencode($zoneId));
        }
        return (new Response())->redirect('/zones/' . urlencode($zoneId));
    }
    public function deleteKey(Request $request, string $zoneId, string $keyId): Response
    {
        if ($request->getMethod() === 'POST') {
            $this->dnssecService->deleteKey($zoneId, $keyId);
        }
        return (new Response())->redirect('/zones/' . urlencode($zoneId));
    }
    public function activateKey(Request $request, string $zoneId, string $keyId): Response
    {
        if ($request->getMethod() === 'POST') {
            $this->dnssecService->activateKey($zoneId, $keyId);
        }
        return (new Response())->redirect('/zones/' . urlencode($zoneId));
    }
    public function deactivateKey(Request $request, string $zoneId, string $keyId): Response
    {
        if ($request->getMethod() === 'POST') {
            $this->dnssecService->deactivateKey($zoneId, $keyId);
        }
        return (new Response())->redirect('/zones/' . urlencode($zoneId));
    }
}
