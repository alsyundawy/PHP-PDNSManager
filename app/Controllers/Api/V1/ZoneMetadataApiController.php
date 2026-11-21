<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Core\Request;
use App\Core\Response;
use App\Services\DNS\ZoneMetadataService;
use InvalidArgumentException;

class ZoneMetadataApiController
{
    private ZoneMetadataService $metadataService;

    public function __construct(ZoneMetadataService $metadataService)
    {
        $this->metadataService = $metadataService;
    }

    public function index(Request $request, string $zoneId): Response
    {
        $metadata = $this->metadataService->getAllMetadata($zoneId);
        return (new Response())->json($metadata);
    }

    public function show(Request $request, string $zoneId, string $kind): Response
    {
        $item = $this->metadataService->getMetadataKind($zoneId, $kind);
        return (new Response())->json($item);
    }

    public function update(Request $request, string $zoneId, string $kind): Response
    {
        $body = $request->getParsedBody();
        $metadata = (array) ($body['metadata'] ?? $request->input('metadata', []));

        try {
            $this->metadataService->setMetadataKind($zoneId, $kind, $metadata);
            return (new Response())->json(['message' => 'Metadata updated successfully', 'kind' => $kind, 'metadata' => $metadata]);
        } catch (InvalidArgumentException $e) {
            return (new Response())->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroy(Request $request, string $zoneId, string $kind): Response
    {
        $this->metadataService->deleteMetadataKind($zoneId, $kind);
        return (new Response())->json(['message' => 'Metadata deleted successfully']);
    }
}
