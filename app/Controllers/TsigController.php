<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\DNS\TsigService;
use InvalidArgumentException;

class TsigController
{
    private const TSIG_URI = '/tsig-keys';
    private TsigService $tsigService;

    public function __construct(TsigService $tsigService)
    {
        $this->tsigService = $tsigService;
    }

    public function index(Request $request): Response
    {
        $keys = [];
        try {
            $keys = $this->tsigService->getAllKeys();
        } catch (\Throwable) {
            $keys = [];
        }
        $algorithms = $this->tsigService->getSupportedAlgorithms();

        $html = view('tsig.index', [
            'keys' => $keys,
            'algorithms' => $algorithms,
            'user' => $request->getAttribute('user'),
            'csrfToken' => csrf_token(),
        ]);

        return (new Response())->html($html);
    }

    public function create(Request $request): Response
    {
        $name = (string) $request->input('name', '');
        $algorithm = (string) $request->input('algorithm', 'hmac-sha256');
        $secret = $request->input('secret');

        try {
            $this->tsigService->createKey($name, $algorithm, $secret !== null ? (string) $secret : null);
        } catch (InvalidArgumentException $e) {
            // Redirect with error
        }

        return (new Response())->redirect(self::TSIG_URI);
    }

    public function delete(Request $request, string $id): Response
    {
        try {
            $this->tsigService->deleteKey($id);
        } catch (\Exception) {
            // Redirect with error
        }

        return (new Response())->redirect(self::TSIG_URI);
    }
}
