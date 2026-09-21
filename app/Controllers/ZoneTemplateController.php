<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\DNS\RecordService;
use App\Services\DNS\ZoneService;
use App\Services\DNS\ZoneTemplateService;

class ZoneTemplateController
{
    private ZoneTemplateService $templateService;
    private ZoneService $zoneService;
    private RecordService $recordService;

    public function __construct(
        ZoneTemplateService $templateService,
        ZoneService $zoneService,
        RecordService $recordService
    ) {
        $this->templateService = $templateService;
        $this->zoneService = $zoneService;
        $this->recordService = $recordService;
    }

    public function index(Request $request): Response
    {
        $templates = $this->templateService->getAllTemplates();
        $zones = $this->zoneService->getAllZones();
        $html = view('template.index', [
            'templates' => $templates,
            'zones' => $zones,
            'user' => $request->getAttribute('user'),
            'csrfToken' => csrf_token(),
        ]);
        return (new Response())->html($html);
    }

    public function create(Request $request): Response
    {
        if ($request->getMethod() === 'POST') {
            $data = [
                'name' => (string) $request->input('name', ''),
                'description' => (string) $request->input('description', ''),
                'is_default' => (bool) $request->input('is_default', false),
            ];

            $names = (array) ($request->input('rec_name') ?? []);
            $types = (array) ($request->input('rec_type') ?? []);
            $contents = (array) ($request->input('rec_content') ?? []);
            $ttls = (array) ($request->input('rec_ttl') ?? []);
            $priorities = (array) ($request->input('rec_priority') ?? []);

            $records = [];
            foreach ($names as $idx => $n) {
                if (trim((string) $n) !== '' && isset($types[$idx], $contents[$idx])) {
                    $records[] = [
                        'name' => (string) $n,
                        'type' => (string) $types[$idx],
                        'content' => (string) $contents[$idx],
                        'ttl' => (int) ($ttls[$idx] ?? 3600),
                        'priority' => isset($priorities[$idx]) && $priorities[$idx] !== '' ? (int) $priorities[$idx] : null,
                    ];
                }
            }

            $this->templateService->createTemplate($data, $records);
            return (new Response())->redirect('/templates');
        }

        $html = view('template.create', [
            'user' => $request->getAttribute('user'),
            'csrfToken' => csrf_token(),
        ]);
        return (new Response())->html($html);
    }

    public function apply(Request $request): Response
    {
        $templateId = (int) $request->input('template_id', 0);
        $zoneName = (string) $request->input('zone_name', '');

        if ($templateId > 0 && $zoneName !== '') {
            $this->templateService->applyTemplateToZone($templateId, $zoneName, $this->recordService);
        }

        return (new Response())->redirect('/zones/' . urlencode($zoneName));
    }

    public function delete(Request $request, string $id): Response
    {
        $this->templateService->deleteTemplate((int) $id);
        return (new Response())->redirect('/templates');
    }
}
