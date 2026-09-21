<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\PowerDNS\ServerClusterService;

class ServerController
{
    private ServerClusterService $clusterService;

    public function __construct(ServerClusterService $clusterService)
    {
        $this->clusterService = $clusterService;
    }

    public function index(Request $request): Response
    {
        $servers = $this->clusterService->getAllServers();
        $html = view('server.index', [
            'servers' => $servers,
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
                'api_url' => (string) $request->input('api_url', ''),
                'api_key' => (string) $request->input('api_key', ''),
                'server_id' => (string) $request->input('server_id', 'localhost'),
                'is_active' => (bool) $request->input('is_active', true),
                'is_default' => (bool) $request->input('is_default', false),
            ];
            $this->clusterService->createServer($data);
            return (new Response())->redirect('/servers');
        }

        $html = view('server.create', [
            'user' => $request->getAttribute('user'),
            'csrfToken' => csrf_token(),
        ]);
        return (new Response())->html($html);
    }

    public function edit(Request $request, string $id): Response
    {
        $serverId = (int) $id;
        $server = $this->clusterService->getServerById($serverId);
        if ($server === null) {
            return (new Response())->redirect('/servers');
        }

        if ($request->getMethod() === 'POST') {
            $data = [
                'name' => (string) $request->input('name', ''),
                'api_url' => (string) $request->input('api_url', ''),
                'api_key' => (string) $request->input('api_key', ''),
                'server_id' => (string) $request->input('server_id', 'localhost'),
                'is_active' => (bool) $request->input('is_active', true),
                'is_default' => (bool) $request->input('is_default', false),
            ];
            $this->clusterService->updateServer($serverId, $data);
            return (new Response())->redirect('/servers');
        }

        $html = view('server.edit', [
            'server' => $server,
            'user' => $request->getAttribute('user'),
            'csrfToken' => csrf_token(),
        ]);
        return (new Response())->html($html);
    }

    public function delete(Request $request, string $id): Response
    {
        $serverId = (int) $id;
        $this->clusterService->deleteServer($serverId);
        return (new Response())->redirect('/servers');
    }

    public function test(Request $request): Response
    {
        $apiUrl = (string) $request->input('api_url', '');
        $apiKey = (string) $request->input('api_key', '');
        $serverId = (string) $request->input('server_id', 'localhost');

        $result = $this->clusterService->testConnection($apiUrl, $apiKey, $serverId);
        $res = new Response(200, ['Content-Type' => 'application/json']);
        $res->getBody()->write((string) json_encode($result));
        return $res;
    }
}
