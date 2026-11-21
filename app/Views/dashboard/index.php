<?php
declare(strict_types=1);

/**
 * PHP-PDNSManager Enterprise Edition — Modernized Dashboard View
 *
 * @var int|null $totalZones
 * @var int|null $activeServers
 * @var string|null $serverHealth
 * @var string|null $serverStatusClass
 */
$title = __('nav_dashboard');
$totalZones = (int) ($totalZones ?? 0);
$activeServers = (int) ($activeServers ?? 1);
$serverHealth = $serverHealth ?? 'OK';
$serverStatusClass = $serverStatusClass ?? 'text-success';
?>
<!-- KPI Metric Cards Grid -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-glass shadow-sm border-0 h-100 p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase"><?= htmlspecialchars(__('total_zones'), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-circle p-2">
                    <i class="fas fa-layer-group fa-lg" aria-hidden="true"></i>
                </span>
            </div>
            <h2 class="fw-bold mb-1"><?= number_format($totalZones) ?></h2>
            <div class="small text-success d-flex align-items-center">
                <i class="fas fa-arrow-up me-1" aria-hidden="true"></i>
                <span><?= htmlspecialchars(__('authoritative_dns'), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-glass shadow-sm border-0 h-100 p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase"><?= htmlspecialchars(__('active_servers'), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-circle p-2">
                    <i class="fas fa-server fa-lg" aria-hidden="true"></i>
                </span>
            </div>
            <h2 class="fw-bold mb-1"><?= number_format($activeServers) ?></h2>
            <div class="small text-primary d-flex align-items-center">
                <i class="fas fa-network-wired me-1" aria-hidden="true"></i>
                <span><?= htmlspecialchars(__('cluster_health'), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-glass shadow-sm border-0 h-100 p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase"><?= htmlspecialchars(__('cluster_health'), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-circle p-2">
                    <i class="fas fa-heartbeat fa-lg" aria-hidden="true"></i>
                </span>
            </div>
            <h2 class="fw-bold mb-1 <?= htmlspecialchars($serverStatusClass, ENT_QUOTES, 'UTF-8') ?>">
                <i class="fas fa-check-circle me-1" aria-hidden="true"></i> <?= htmlspecialchars($serverHealth, ENT_QUOTES, 'UTF-8') ?>
            </h2>
            <div class="small text-muted d-flex align-items-center">
                <i class="fas fa-wave-square me-1" aria-hidden="true"></i>
                <span><?= htmlspecialchars(__('pdns_api_latency'), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-glass shadow-sm border-0 h-100 p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Security Posture</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-circle p-2">
                    <i class="fas fa-shield-alt fa-lg" aria-hidden="true"></i>
                </span>
            </div>
            <h2 class="fw-bold mb-1 text-success">
                <i class="fas fa-lock me-1" aria-hidden="true"></i> Hardened
            </h2>
            <div class="small text-muted d-flex align-items-center">
                <i class="fas fa-user-shield me-1" aria-hidden="true"></i>
                <span>RBAC, CSRF & Nonce CSP</span>
            </div>
        </div>
    </div>
</div>

<!-- Charts & Telemetry Grid -->
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-7">
        <div class="card card-glass shadow-sm border-0 h-100 p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">
                    <i class="fas fa-chart-pie text-primary me-2" aria-hidden="true"></i> Zone Distribution
                </h5>
                <span class="badge bg-secondary-subtle text-secondary">PowerDNS Live</span>
            </div>
            <div style="position: relative; height: 260px; min-height: 240px;">
                <canvas id="zoneChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card card-glass shadow-sm border-0 h-100 p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">
                    <i class="fas fa-bolt text-warning me-2" aria-hidden="true"></i> <?= htmlspecialchars(__('recent_audit_events'), ENT_QUOTES, 'UTF-8') ?>
                </h5>
                <a href="/audit-logs" class="small text-decoration-none"><?= htmlspecialchars(__('view'), ENT_QUOTES, 'UTF-8') ?> &rarr;</a>
            </div>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-2 px-0 border-secondary border-opacity-10">
                    <div>
                        <i class="fas fa-shield-halved text-success me-2" aria-hidden="true"></i>
                        <span>Zero CDN Offline Engine</span>
                    </div>
                    <span class="badge bg-success-subtle text-success">100% Local</span>
                </li>
                <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-2 px-0 border-secondary border-opacity-10">
                    <div>
                        <i class="fas fa-certificate text-info me-2" aria-hidden="true"></i>
                        <span>RFC 9460 HTTPS/SVCB</span>
                    </div>
                    <span class="badge bg-info-subtle text-info">RFC 9460</span>
                </li>
                <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-2 px-0 border-secondary border-opacity-10">
                    <div>
                        <i class="fas fa-book-atlas text-primary me-2" aria-hidden="true"></i>
                        <span>RFC 9432 Catalog Zones</span>
                    </div>
                    <span class="badge bg-primary-subtle text-primary">Active</span>
                </li>
                <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-2 px-0 border-secondary border-opacity-10">
                    <div>
                        <i class="fas fa-mobile-screen text-warning me-2" aria-hidden="true"></i>
                        <span>Mobile Anti-Clipping Viewport</span>
                    </div>
                    <span class="badge bg-warning-subtle text-warning">HyperOS Ready</span>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Quick Actions Row -->
<div class="row g-3">
    <div class="col-12">
        <div class="card card-glass shadow-sm border-0 p-3 p-md-4">
            <h5 class="fw-bold mb-3">
                <i class="fas fa-rocket text-primary me-2" aria-hidden="true"></i> <?= htmlspecialchars(__('quick_actions'), ENT_QUOTES, 'UTF-8') ?>
            </h5>
            <div class="d-flex flex-wrap gap-2">
                <a href="/zones/create" class="btn btn-primary shadow-sm">
                    <i class="fas fa-plus me-1" aria-hidden="true"></i> <?= htmlspecialchars(__('create_new_zone'), ENT_QUOTES, 'UTF-8') ?>
                </a>
                <a href="/zones/import" class="btn btn-outline-secondary">
                    <i class="fas fa-file-import me-1" aria-hidden="true"></i> <?= htmlspecialchars(__('import_bind_zone'), ENT_QUOTES, 'UTF-8') ?>
                </a>
                <a href="/zones/bulk-records" class="btn btn-outline-secondary">
                    <i class="fas fa-layer-group me-1" aria-hidden="true"></i> <?= htmlspecialchars(__('nav_bulk_records'), ENT_QUOTES, 'UTF-8') ?>
                </a>
                <a href="/zones/reverse-wizard" class="btn btn-outline-info">
                    <i class="fas fa-undo-alt me-1" aria-hidden="true"></i> <?= htmlspecialchars(__('nav_reverse_wizard'), ENT_QUOTES, 'UTF-8') ?>
                </a>
                <a href="/backups" class="btn btn-outline-secondary">
                    <i class="fas fa-shield-alt me-1" aria-hidden="true"></i> <?= htmlspecialchars(__('nav_cluster_backups'), ENT_QUOTES, 'UTF-8') ?>
                </a>
                <a href="/audit-logs/export?format=csv" class="btn btn-outline-success ms-auto">
                    <i class="fas fa-download me-1" aria-hidden="true"></i> <?= htmlspecialchars(__('export_csv'), ENT_QUOTES, 'UTF-8') ?>
                </a>
            </div>
        </div>
    </div>
</div>
