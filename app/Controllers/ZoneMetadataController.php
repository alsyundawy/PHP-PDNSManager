<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\DNS\ZoneMetadataService;
use App\Services\DNS\ZoneService;
use InvalidArgumentException;

class ZoneMetadataController
{
    private const ZONES_PATH = '/zones/';
    private ZoneMetadataService $metadataService;
    private ZoneService $zoneService;

    public function __construct(ZoneMetadataService $metadataService, ZoneService $zoneService)
    {
        $this->metadataService = $metadataService;
        $this->zoneService = $zoneService;
    }

    public function index(Request $request, string $zoneId): Response
    {
        $zone = $this->zoneService->getZone($zoneId);
        $metadata = $this->metadataService->getAllMetadata($zoneId);
        $supportedKinds = $this->metadataService->getSupportedKinds();

        $html = view('metadata.index', [
            'zone' => $zone,
            'metadata' => $metadata,
            'supportedKinds' => $supportedKinds,
            'user' => $request->getAttribute('user'),
            'csrfToken' => csrf_token(),
        ]);

        return (new Response())->html($html);
    }

    public function update(Request $request, string $zoneId): Response
    {
        $kind = (string) $request->input('kind', '');
        $valuesRaw = (string) $request->input('values', '');
        $values = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $valuesRaw))));

        try {
            $this->metadataService->setMetadataKind($zoneId, $kind, $values);
        } catch (InvalidArgumentException $e) {
            // Flash error or handle gracefully
        }

        return (new Response())->redirect(self::ZONES_PATH . urlencode($zoneId) . '#metadata');
    }

    public function delete(Request $request, string $zoneId, string $kind): Response
    {
        try {
            $this->metadataService->deleteMetadataKind($zoneId, $kind);
        } catch (InvalidArgumentException $e) {
            // Flash error or handle gracefully
        }

        return (new Response())->redirect(self::ZONES_PATH . urlencode($zoneId) . '#metadata');
    }
}
