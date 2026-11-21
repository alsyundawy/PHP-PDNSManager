<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\PowerDNS\Resource\SearchResource;
use App\Services\DNS\ZoneService;

class SearchController
{
    private SearchResource $searchResource;
    private ZoneService $zoneService;

    public function __construct(SearchResource $searchResource, ZoneService $zoneService)
    {
        $this->searchResource = $searchResource;
        $this->zoneService = $zoneService;
    }

    /**
     * Async JSON search for Ctrl+K Command Palette.
     */
    public function search(Request $request): Response
    {
        $query = trim((string) $request->input('q', ''));
        if (strlen($query) < 2) {
            return (new Response())->json(['results' => []]);
        }

        $results = [];

        // 1. Search PowerDNS live data (zones, records, comments)
        try {
            $pdnsResults = $this->searchResource->search($query, 15);
            foreach ($pdnsResults as $item) {
                $objectType = $item['object_type'] ?? 'record';
                $name = $item['name'] ?? '';
                $zoneId = $item['zone_id'] ?? ($item['zone'] ?? $name);

                $results[] = [
                    'title' => $name,
                    'type' => strtoupper($objectType),
                    'subtitle' => ($item['content'] ?? '') ?: ($item['description'] ?? "Zone {$zoneId}"),
                    'url' => '/zones/' . urlencode($zoneId),
                ];
            }
        } catch (\Exception) {
            // Fallback to local zone matching
            $zones = $this->zoneService->getAllZones(['name' => "*{$query}*"]);
            foreach (array_slice($zones, 0, 10) as $zone) {
                $results[] = [
                    'title' => $zone['name'],
                    'type' => 'ZONE',
                    'subtitle' => "Kind: {$zone['kind']} | Serial: " . ($zone['serial'] ?? 'N/A'),
                    'url' => '/zones/' . urlencode($zone['name']),
                ];
            }
        }

        // 2. Navigation quick jumps
        $navJumps = [
            ['title' => 'Create New Zone', 'keywords' => ['create', 'new', 'zone', 'add'], 'url' => '/zones/create', 'type' => 'ACTION'],
            ['title' => 'Bulk Records Manager', 'keywords' => ['bulk', 'batch', 'records', 'mass'], 'url' => '/zones/bulk-records', 'type' => 'ACTION'],
            ['title' => 'DNS Security Toolbox (SPF/DKIM/DMARC)', 'keywords' => ['toolbox', 'spf', 'dkim', 'dmarc', 'rbl'], 'url' => '/toolbox', 'type' => 'ACTION'],
            ['title' => 'TSIG Keys Management', 'keywords' => ['tsig', 'keys', 'axfr', 'transfer'], 'url' => '/tsig-keys', 'type' => 'ACTION'],
            ['title' => 'Cluster Backups & Restore', 'keywords' => ['backup', 'restore', 'export', 'snapshot'], 'url' => '/backups', 'type' => 'ACTION'],
            ['title' => 'Server Clusters', 'keywords' => ['server', 'cluster', 'node', 'powerdns'], 'url' => '/servers', 'type' => 'ACTION'],
            ['title' => 'System Health Check', 'keywords' => ['health', 'status', 'ping', 'latency'], 'url' => '/health', 'type' => 'ACTION'],
        ];

        $lowerQuery = strtolower($query);
        foreach ($navJumps as $jump) {
            foreach ($jump['keywords'] as $kw) {
                if (str_contains($kw, $lowerQuery) || str_contains(strtolower($jump['title']), $lowerQuery)) {
                    $results[] = [
                        'title' => $jump['title'],
                        'type' => $jump['type'],
                        'subtitle' => 'Application Navigation',
                        'url' => $jump['url'],
                    ];
                    break;
                }
            }
        }

        return (new Response())->json(['results' => $results]);
    }
}
