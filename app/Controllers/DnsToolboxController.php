<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\DNS\DnsToolboxService;

class DnsToolboxController
{
    private DnsToolboxService $toolboxService;

    public function __construct(DnsToolboxService $toolboxService)
    {
        $this->toolboxService = $toolboxService;
    }

    public function index(Request $request): Response
    {
        $action = (string) $request->input('action', 'spf');
        $result = null;

        if ($request->getMethod() === 'POST') {
            if ($action === 'spf_validate') {
                $domain = (string) $request->input('domain', '');
                $record = $request->input('record') ? (string) $request->input('record') : null;
                $result = $this->toolboxService->validateSpf($domain, $record);
            } elseif ($action === 'spf_build') {
                $params = [
                    'a' => (bool) $request->input('a'),
                    'mx' => (bool) $request->input('mx'),
                    'ip4' => array_filter(array_map('trim', explode(',', (string) $request->input('ip4', '')))),
                    'ip6' => array_filter(array_map('trim', explode(',', (string) $request->input('ip6', '')))),
                    'include' => array_filter(array_map('trim', explode(',', (string) $request->input('include', '')))),
                    'qualifier' => (string) $request->input('qualifier', '~all'),
                ];
                $result = ['built_spf' => $this->toolboxService->buildSpf($params)];
            } elseif ($action === 'dkim_inspect') {
                $domain = (string) $request->input('domain', '');
                $selector = (string) $request->input('selector', 'default');
                $result = $this->toolboxService->inspectDkim($domain, $selector);
            } elseif ($action === 'dmarc_validate') {
                $domain = (string) $request->input('domain', '');
                $record = $request->input('record') ? (string) $request->input('record') : null;
                $result = $this->toolboxService->validateDmarc($domain, $record);
            } elseif ($action === 'dmarc_build') {
                $params = [
                    'p' => (string) $request->input('p', 'quarantine'),
                    'sp' => (string) $request->input('sp', ''),
                    'rua' => (string) $request->input('rua', ''),
                    'ruf' => (string) $request->input('ruf', ''),
                    'pct' => (int) ($request->input('pct') ?: 100),
                ];
                $result = ['built_dmarc' => $this->toolboxService->buildDmarc($params)];
            } elseif ($action === 'rbl_check') {
                $ip = (string) $request->input('ip', '');
                $result = $this->toolboxService->checkRbl($ip);
            }
        }

        $html = view('toolbox.index', [
            'action' => $action,
            'result' => $result,
            'user' => $request->getAttribute('user'),
            'csrfToken' => csrf_token(),
        ]);

        return (new Response())->html($html);
    }
}
