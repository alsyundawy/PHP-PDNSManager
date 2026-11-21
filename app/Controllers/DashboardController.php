<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\DNS\ZoneService;
use App\Services\PowerDNS\ServerClusterService;

class DashboardController
{
    private ?ZoneService $zoneService;
    private ?ServerClusterService $clusterService;

    public function __construct(?ZoneService $zoneService = null, ?ServerClusterService $clusterService = null)
    {
        $this->zoneService = $zoneService;
        $this->clusterService = $clusterService;
    }

    public function index(Request $request): Response
    {
        $user = $request->getAttribute('user');

        $totalZones = 0;
        $activeServers = 1;
        $serverHealth = 'OK';
        $serverStatusClass = 'text-success';

        if ($this->zoneService !== null) {
            try {
                $zones = $this->zoneService->getAllZones();
                $totalZones = count($zones);
            } catch (\Throwable) {
                $totalZones = 0;
            }
        }

        if ($this->clusterService !== null) {
            try {
                $servers = $this->clusterService->getAllServers();
                $activeServers = count(array_filter($servers, fn ($s) => $s->isActive));
                $defaultServer = $this->clusterService->getDefaultServer();
                $conn = $this->clusterService->testConnection(
                    $defaultServer->apiUrl,
                    $defaultServer->apiKey,
                    $defaultServer->serverId
                );
                if (!($conn['success'] ?? false)) {
                    $serverHealth = 'Degraded';
                    $serverStatusClass = 'text-warning';
                }
            } catch (\Throwable) {
                $serverHealth = 'Offline';
                $serverStatusClass = 'text-danger';
            }
        }

        $html = view('dashboard.index', [
            'user' => $user,
            'csrfToken' => csrf_token(),
            'totalZones' => $totalZones,
            'activeServers' => $activeServers,
            'serverHealth' => $serverHealth,
            'serverStatusClass' => $serverStatusClass,
        ]);
        return (new Response())->html($html);
    }
}
