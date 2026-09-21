<?php /** @var array $health */ ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0 text-gray-800">System & Cluster Health</h1>
        <p class="text-muted small mb-0">Real-time health monitoring of PowerDNS Authoritative and core services</p>
    </div>
    <div>
        <a href="/health" class="btn btn-outline-primary" target="_blank">
            <i class="fas fa-code me-1" aria-hidden="true"></i> View JSON API
        </a>
    </div>
</div>

<!-- Overall Status Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center">
            <?php if (($health['status'] ?? '') === 'healthy'): ?>
                <div class="rounded-circle bg-success text-white p-3 me-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fas fa-check fa-2x" aria-hidden="true"></i>
                </div>
                <div>
                    <h4 class="mb-1 text-success fw-bold">All Systems Operational</h4>
                    <span class="text-muted small">Checked at <?= htmlspecialchars($health['timestamp'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php elseif (($health['status'] ?? '') === 'degraded'): ?>
                <div class="rounded-circle bg-warning text-dark p-3 me-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fas fa-exclamation-triangle fa-2x" aria-hidden="true"></i>
                </div>
                <div>
                    <h4 class="mb-1 text-warning fw-bold">Degraded Performance</h4>
                    <span class="text-muted small">Some services are experiencing issues</span>
                </div>
            <?php else: ?>
                <div class="rounded-circle bg-danger text-white p-3 me-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fas fa-times fa-2x" aria-hidden="true"></i>
                </div>
                <div>
                    <h4 class="mb-1 text-danger fw-bold">System Critical Outage</h4>
                    <span class="text-muted small">One or more core components are unavailable</span>
                </div>
            <?php endif; ?>
        </div>
        <div>
            <span class="badge bg-secondary-subtle text-secondary fs-6 px-3 py-2">
                PHP <?= htmlspecialchars($health['app']['php_version'] ?? PHP_VERSION, ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>
    </div>
</div>

<!-- Component Checks Grid -->
<div class="row g-4">
    <!-- PowerDNS Check -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-server me-2" aria-hidden="true"></i> PowerDNS Authoritative Service
                </h6>
                <?php $pdnsStatus = $health['checks']['powerdns']['status'] ?? 'unknown'; ?>
                <span class="badge bg-<?= $pdnsStatus === 'healthy' ? 'success' : 'danger' ?>">
                    <?= ucfirst($pdnsStatus) ?>
                </span>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Latency:</strong> <?= htmlspecialchars((string) ($health['checks']['powerdns']['latency_ms'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?> ms</p>
                <p class="mb-2"><strong>Version:</strong> <?= htmlspecialchars((string) ($health['checks']['powerdns']['version'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></p>
                <p class="mb-0"><strong>Daemon Type:</strong> <?= htmlspecialchars((string) ($health['checks']['powerdns']['daemon_type'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></p>
                <?php if (!empty($health['checks']['powerdns']['error'])): ?>
                    <div class="alert alert-danger mt-3 mb-0 small">
                        <?= htmlspecialchars($health['checks']['powerdns']['error'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Database Check -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-database me-2" aria-hidden="true"></i> Relational Database
                </h6>
                <?php $dbStatus = $health['checks']['database']['status'] ?? 'unknown'; ?>
                <span class="badge bg-<?= $dbStatus === 'healthy' ? 'success' : 'danger' ?>">
                    <?= ucfirst($dbStatus) ?>
                </span>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Latency:</strong> <?= htmlspecialchars((string) ($health['checks']['database']['latency_ms'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?> ms</p>
                <p class="mb-0"><strong>Message:</strong> <?= htmlspecialchars((string) ($health['checks']['database']['message'] ?? 'Connected'), ENT_QUOTES, 'UTF-8') ?></p>
                <?php if (!empty($health['checks']['database']['error'])): ?>
                    <div class="alert alert-danger mt-3 mb-0 small">
                        <?= htmlspecialchars($health['checks']['database']['error'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Storage & Cache -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-folder me-2" aria-hidden="true"></i> Storage & Permissions
                </h6>
                <?php $storageStatus = $health['checks']['storage']['status'] ?? 'unknown'; ?>
                <span class="badge bg-<?= $storageStatus === 'healthy' ? 'success' : 'warning' ?>">
                    <?= ucfirst($storageStatus) ?>
                </span>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Storage Writable:</strong> <?= !empty($health['checks']['storage']['storage_writable']) ? 'Yes' : 'No' ?></p>
                <p class="mb-0"><strong>Logs Writable:</strong> <?= !empty($health['checks']['storage']['logs_writable']) ? 'Yes' : 'No' ?></p>
            </div>
        </div>
    </div>

    <!-- System Memory & Extensions -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-microchip me-2" aria-hidden="true"></i> System Environment
                </h6>
                <?php $sysStatus = $health['checks']['system']['status'] ?? 'unknown'; ?>
                <span class="badge bg-<?= $sysStatus === 'healthy' ? 'success' : 'danger' ?>">
                    <?= ucfirst($sysStatus) ?>
                </span>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Memory Usage:</strong> <?= round(($health['checks']['system']['memory_usage'] ?? 0) / 1024 / 1024, 2) ?> MB</p>
                <p class="mb-0"><strong>Memory Peak:</strong> <?= round(($health['checks']['system']['memory_peak'] ?? 0) / 1024 / 1024, 2) ?> MB</p>
            </div>
        </div>
    </div>
</div>
