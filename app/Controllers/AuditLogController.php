<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\Contracts\AuditLogRepositoryInterface;

class AuditLogController
{
    private AuditLogRepositoryInterface $auditRepo;

    public function __construct(AuditLogRepositoryInterface $auditRepo)
    {
        $this->auditRepo = $auditRepo;
    }

    public function index(Request $request): Response
    {
        $filters = $this->extractFilters($request);
        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(10, (int) $request->input('per_page', 25)));
        $offset = ($page - 1) * $perPage;

        $logs = $this->auditRepo->search($filters, $perPage, $offset);
        $total = $this->auditRepo->count($filters);
        $totalPages = (int) ceil($total / $perPage);

        $html = view('audit.index', [
            'logs' => $logs,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'filters' => $filters,
            'user' => $request->getAttribute('user'),
            'csrfToken' => csrf_token(),
        ]);
        return (new Response())->html($html);
    }

    public function export(Request $request): Response
    {
        $filters = $this->extractFilters($request);
        $format = strtolower((string) $request->input('format', 'csv'));
        $logs = $this->auditRepo->search($filters, 10000, 0);

        if ($format === 'json') {
            $filename = 'audit_logs_' . date('Ymd_His') . '.json';
            $response = new Response(200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
            $response->getBody()->write((string) json_encode($logs, JSON_PRETTY_PRINT));
            return $response;
        }

        // CSV export
        $filename = 'audit_logs_' . date('Ymd_His') . '.csv';
        $out = fopen('php://temp', 'w+b');
        if ($out !== false) {
            fputcsv($out, ['ID', 'User ID', 'Action', 'Status Code', 'IP Address', 'Created At', 'Payload']);
            foreach ($logs as $log) {
                fputcsv($out, [
                    $log['id'] ?? '',
                    $log['user_id'] ?? '',
                    $log['action'] ?? '',
                    $log['status_code'] ?? '',
                    $log['ip_address'] ?? '',
                    $log['created_at'] ?? '',
                    is_array($log['payload'] ?? null) ? json_encode($log['payload']) : (string) ($log['payload'] ?? ''),
                ]);
            }
            rewind($out);
            $csvData = (string) stream_get_contents($out);
            fclose($out);
        } else {
            $csvData = '';
        }

        $response = new Response(200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
        $response->getBody()->write($csvData);
        return $response;
    }

    private function extractFilters(Request $request): array
    {
        $filters = [];
        if ($request->has('action') && trim((string) $request->input('action')) !== '') {
            $filters['action'] = trim((string) $request->input('action'));
        }
        if ($request->has('user_id') && trim((string) $request->input('user_id')) !== '') {
            $filters['user_id'] = (int) $request->input('user_id');
        }
        if ($request->has('date_from') && trim((string) $request->input('date_from')) !== '') {
            $filters['date_from'] = trim((string) $request->input('date_from'));
        }
        if ($request->has('date_to') && trim((string) $request->input('date_to')) !== '') {
            $filters['date_to'] = trim((string) $request->input('date_to'));
        }
        return $filters;
    }
}
