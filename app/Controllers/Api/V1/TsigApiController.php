<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Core\Request;
use App\Core\Response;
use App\Services\DNS\TsigService;
use InvalidArgumentException;

class TsigApiController
{
    private TsigService $tsigService;

    public function __construct(TsigService $tsigService)
    {
        $this->tsigService = $tsigService;
    }

    public function index(Request $request): Response
    {
        $keys = $this->tsigService->getAllKeys();
        return (new Response())->json($keys);
    }

    public function show(Request $request, string $id): Response
    {
        $key = $this->tsigService->getKey($id);
        return (new Response())->json($key);
    }

    public function store(Request $request): Response
    {
        $name = (string) $request->input('name', '');
        $algorithm = (string) $request->input('algorithm', 'hmac-sha256');
        $secret = $request->input('key') ?: $request->input('secret');

        try {
            $result = $this->tsigService->createKey($name, $algorithm, $secret !== null ? (string) $secret : null);
            return (new Response())->json($result, 201);
        } catch (InvalidArgumentException $e) {
            return (new Response())->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroy(Request $request, string $id): Response
    {
        $this->tsigService->deleteKey($id);
        return (new Response())->json(['message' => 'TSIG key deleted successfully']);
    }
}
