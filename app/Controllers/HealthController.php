<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\Health\HealthCheckService;

class HealthController
{
    private HealthCheckService $healthService;

    public function __construct(HealthCheckService $healthService)
    {
        $this->healthService = $healthService;
    }

    public function index(Request $request): Response
    {
        $health = $this->healthService->checkOverall();
        $status = $health['status'] === 'unhealthy' ? 503 : 200;

        $accept = $request->getServerRequest()->getHeaderLine('Accept');
        if (str_contains($accept, 'text/html') && !str_contains($accept, 'application/json')) {
            $html = view('health.index', [
                'health' => $health,
                'user' => $request->getAttribute('user'),
                'csrfToken' => csrf_token(),
            ]);
            return (new Response($status))->html($html);
        }

        $res = new Response($status, ['Content-Type' => 'application/json']);
        $res->getBody()->write((string) json_encode($health, JSON_PRETTY_PRINT));
        return $res;
    }
}
