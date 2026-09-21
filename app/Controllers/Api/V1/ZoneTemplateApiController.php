<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Core\Request;
use App\Core\Response;
use App\Services\DNS\RecordService;
use App\Services\DNS\ZoneTemplateService;

class ZoneTemplateApiController
{
    private ZoneTemplateService $templateService;
    private RecordService $recordService;

    public function __construct(ZoneTemplateService $templateService, RecordService $recordService)
    {
        $this->templateService = $templateService;
        $this->recordService = $recordService;
    }

    public function index(): Response
    {
        $templates = $this->templateService->getAllTemplates();
        return $this->jsonResponse(array_map(static fn ($t) => $t->toArray(), $templates));
    }

    public function store(Request $request): Response
    {
        $data = [
            'name' => (string) $request->input('name', ''),
            'description' => (string) $request->input('description', ''),
            'is_default' => (bool) $request->input('is_default', false),
        ];
        $records = (array) ($request->input('records') ?? []);
        $template = $this->templateService->createTemplate($data, $records);
        return $this->jsonResponse($template ? $template->toArray() : ['error' => 'Creation failed'], 201);
    }

    public function show(Request $request, string $id): Response
    {
        $template = $this->templateService->getTemplateById((int) $id);
        if ($template === null) {
            return $this->jsonResponse(['error' => 'Template not found'], 404);
        }
        return $this->jsonResponse($template->toArray());
    }

    public function apply(Request $request, string $id): Response
    {
        $templateId = (int) $id;
        $zoneName = (string) $request->input('zone_name', '');
        if ($zoneName === '') {
            return $this->jsonResponse(['error' => 'zone_name is required'], 422);
        }

        $results = $this->templateService->applyTemplateToZone($templateId, $zoneName, $this->recordService);
        return $this->jsonResponse(['results' => $results]);
    }

    public function destroy(Request $request, string $id): Response
    {
        $this->templateService->deleteTemplate((int) $id);
        return $this->jsonResponse(['success' => true]);
    }

    private function jsonResponse(array $data, int $status = 200): Response
    {
        $res = new Response($status, ['Content-Type' => 'application/json']);
        $res->getBody()->write((string) json_encode($data));
        return $res;
    }
}
