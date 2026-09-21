<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Core\Request;
use App\Core\Response;
use App\Services\PowerDNS\ServerClusterService;

class ServerApiController
{
    private ServerClusterService $clusterService;

    public function __construct(ServerClusterService $clusterService)
    {
        $this->clusterService = $clusterService;
    }

    public function index(): Response
    {
        $servers = $this->clusterService->getAllServers();
        return $this->jsonResponse(array_map(static fn ($s) => $s->toArray(), $servers));
    }

    public function store(Request $request): Response
    {
        $data = [
            'name' => (string) $request->input('name', ''),
            'api_url' => (string) $request->input('api_url', ''),
            'api_key' => (string) $request->input('api_key', ''),
            'server_id' => (string) $request->input('server_id', 'localhost'),
            'is_active' => (bool) $request->input('is_active', true),
            'is_default' => (bool) $request->input('is_default', false),
        ];
        $server = $this->clusterService->createServer($data);
        return $this->jsonResponse($server->toArray(), 201);
    }

    public function show(Request $request, string $id): Response
    {
        $server = $this->clusterService->getServerById((int) $id);
        if ($server === null) {
            return $this->jsonResponse(['error' => 'Server not found'], 404);
        }
        return $this->jsonResponse($server->toArray());
    }

    public function update(Request $request, string $id): Response
    {
        $serverId = (int) $id;
        $data = [
            'name' => $request->input('name'),
            'api_url' => $request->input('api_url'),
            'api_key' => $request->input('api_key'),
            'server_id' => $request->input('server_id'),
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : null,
            'is_default' => $request->has('is_default') ? (bool) $request->input('is_default') : null,
        ];
        $updated = $this->clusterService->updateServer($serverId, array_filter($data, static fn ($v) => $v !== null));
        if (!$updated) {
            return $this->jsonResponse(['error' => 'Server not found or update failed'], 400);
        }
        return $this->jsonResponse(['success' => true]);
    }

    public function destroy(Request $request, string $id): Response
    {
        $this->clusterService->deleteServer((int) $id);
        return $this->jsonResponse(['success' => true]);
    }

    public function test(Request $request): Response
    {
        $apiUrl = (string) $request->input('api_url', '');
        $apiKey = (string) $request->input('api_key', '');
        $serverId = (string) $request->input('server_id', 'localhost');

        $result = $this->clusterService->testConnection($apiUrl, $apiKey, $serverId);
        return $this->jsonResponse($result);
    }

    private function jsonResponse(array $data, int $status = 200): Response
    {
        $res = new Response($status, ['Content-Type' => 'application/json']);
        $res->getBody()->write((string) json_encode($data));
        return $res;
    }
}
