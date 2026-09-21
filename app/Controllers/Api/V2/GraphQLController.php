<?php

declare(strict_types=1);

namespace App\Controllers\Api\V2;

use App\Core\Response;
use App\Services\DNS\RecordService;
use App\Services\DNS\ZoneService;
use App\Services\DNS\ZoneTemplateService;
use App\Services\Health\HealthCheckService;
use App\Services\PowerDNS\ServerClusterService;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class GraphQLController
{
    private ZoneService $zoneService;
    private RecordService $recordService;
    private ServerClusterService $serverService;
    private HealthCheckService $healthService;
    private ZoneTemplateService $templateService;
    private AuditLogRepositoryInterface $auditRepo;

    public function __construct(
        ZoneService $zoneService,
        RecordService $recordService,
        ServerClusterService $serverService,
        HealthCheckService $healthService,
        ZoneTemplateService $templateService,
        AuditLogRepositoryInterface $auditRepo
    ) {
        $this->zoneService = $zoneService;
        $this->recordService = $recordService;
        $this->serverService = $serverService;
        $this->healthService = $healthService;
        $this->templateService = $templateService;
        $this->auditRepo = $auditRepo;
    }

    public function handle(ServerRequestInterface $request): Response
    {
        $method = $request->getMethod();
        $query = '';
        $variables = [];

        if ($method === 'POST') {
            $parsed = $request->getParsedBody();
            if (empty($parsed)) {
                $raw = (string) $request->getBody();
                $parsed = json_decode($raw, true);
            }
            if (is_array($parsed)) {
                $query = (string) ($parsed['query'] ?? '');
                $variables = is_array($parsed['variables'] ?? null) ? $parsed['variables'] : [];
            }
        } else {
            $params = $request->getQueryParams();
            $query = (string) ($params['query'] ?? '');
            if (isset($params['variables']) && is_string($params['variables'])) {
                $decoded = json_decode($params['variables'], true);
                $variables = is_array($decoded) ? $decoded : [];
            }
        }

        if (trim($query) === '') {
            return $this->jsonResponse(['errors' => [['message' => 'GraphQL query must not be empty']]]);
        }

        try {
            $data = $this->executeGraphQL($query, $variables);
            return $this->jsonResponse(['data' => $data]);
        } catch (\Throwable $e) {
            return $this->jsonResponse([
                'errors' => [
                    ['message' => $e->getMessage()]
                ]
            ]);
        }
    }

    private function executeGraphQL(string $query, array $variables): array
    {
        $query = trim($query);
        $data = [];

        // Check for schema introspection query (__schema or __type)
        if (str_contains($query, '__schema') || str_contains($query, '__type')) {
            return [
                '__schema' => [
                    'queryType' => ['name' => 'Query'],
                    'mutationType' => ['name' => 'Mutation'],
                    'types' => [
                        ['name' => 'Zone', 'kind' => 'OBJECT'],
                        ['name' => 'Record', 'kind' => 'OBJECT'],
                        ['name' => 'Server', 'kind' => 'OBJECT'],
                        ['name' => 'Health', 'kind' => 'OBJECT'],
                    ],
                ],
            ];
        }

        // Query: zones
        if (preg_match('/\bzones\s*(\([^\)]*\))?\s*\{/i', $query)) {
            $zones = $this->zoneService->getAllZones();
            $data['zones'] = array_map(static function ($z) {
                return [
                    'id' => $z['id'] ?? '',
                    'name' => $z['name'] ?? '',
                    'kind' => $z['kind'] ?? 'Native',
                    'serial' => $z['serial'] ?? 0,
                    'account' => $z['account'] ?? '',
                    'dnssec' => (bool) ($z['dnssec'] ?? false),
                ];
            }, $zones);
        }

        // Query: zone(id: "...")
        if (preg_match('/\bzone\s*\(\s*id\s*:\s*["\']([^"\']+)["\']\s*\)\s*\{/i', $query, $matches)) {
            $zoneId = $matches[1];
            $zone = $this->zoneService->getZone($zoneId);
            $records = [];
            try {
                $rawRecords = $this->recordService->getRecordsForZone($zoneId);
                foreach ($rawRecords as $rr) {
                    $recList = $rr['records'] ?? [];
                    foreach ($recList as $c) {
                        $records[] = [
                            'name' => $rr['name'] ?? '',
                            'type' => $rr['type'] ?? '',
                            'ttl' => $rr['ttl'] ?? 3600,
                            'content' => $c['content'] ?? '',
                        ];
                    }
                }
            } catch (\Throwable) {
                // Keep records empty on error
            }

            $data['zone'] = [
                'id' => $zone['id'] ?? $zoneId,
                'name' => $zone['name'] ?? $zoneId,
                'kind' => $zone['kind'] ?? 'Native',
                'serial' => $zone['serial'] ?? 0,
                'dnssec' => (bool) ($zone['dnssec'] ?? false),
                'records' => $records,
            ];
        }

        // Query: servers
        if (preg_match('/\bservers\s*\{/i', $query)) {
            $servers = $this->serverService->getAllServers();
            $data['servers'] = array_map(static fn ($s) => $s->toArray(), $servers);
        }

        // Query: health
        if (preg_match('/\bhealth\s*\{/i', $query)) {
            $data['health'] = $this->healthService->checkOverall();
        }

        // Query: templates
        if (preg_match('/\btemplates\s*\{/i', $query)) {
            $templates = $this->templateService->getAllTemplates();
            $data['templates'] = array_map(static fn ($t) => $t->toArray(), $templates);
        }

        // Query: auditLogs
        if (preg_match('/\bauditLogs\s*(\(\s*limit\s*:\s*(\d+)\s*\))?\s*\{/i', $query, $matches)) {
            $limit = isset($matches[2]) ? (int) $matches[2] : 10;
            $data['auditLogs'] = $this->auditRepo->getRecent($limit);
        }

        // Mutation: createZone
        if (preg_match('/\bcreateZone\s*\(\s*name\s*:\s*["\']([^"\']+)["\'](?:\s*,\s*kind\s*:\s*["\']([^"\']+)["\'])?\s*\)\s*\{/i', $query, $matches)) {
            $name = $matches[1];
            $kind = $matches[2] ?? 'Native';
            $zone = $this->zoneService->createZone(['name' => $name, 'kind' => $kind]);
            $data['createZone'] = [
                'id' => $zone['id'] ?? $name,
                'name' => $zone['name'] ?? $name,
                'kind' => $zone['kind'] ?? $kind,
            ];
        }

        // Mutation: deleteZone
        if (preg_match('/\bdeleteZone\s*\(\s*id\s*:\s*["\']([^"\']+)["\']\s*\)/i', $query, $matches)) {
            $id = $matches[1];
            $this->zoneService->deleteZone($id);
            $data['deleteZone'] = true;
        }

        return $data;
    }

    private function jsonResponse(array $payload, int $status = 200): Response
    {
        $response = new Response($status, [
            'Content-Type' => 'application/json',
            'X-GraphQL-Engine' => 'PHP-PDNSManager/2.0',
        ]);
        $response->getBody()->write((string) json_encode($payload));
        return $response;
    }
}
