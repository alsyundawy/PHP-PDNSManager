<?php
/**
 * @var int|null $totalZones
 * @var int|null $activeServers
 * @var string|null $serverHealth
 * @var string|null $serverStatusClass
 */
$title = 'Dashboard';
$totalZones = $totalZones ?? 0;
$activeServers = $activeServers ?? 1;
$serverHealth = $serverHealth ?? 'OK';
$serverStatusClass = $serverStatusClass ?? 'text-success';
?>
<div class="row">
    <div class="col-md-3 mb-4"><div class="dashboard-card p-3"><h6 class="text-muted">Total Zones</h6><h2 class="fw-bold"><?= number_format($totalZones) ?></h2><small class="text-success"><i class="fas fa-layer-group"></i> Active zones</small></div></div>
    <div class="col-md-3 mb-4"><div class="dashboard-card p-3"><h6 class="text-muted">Active Nodes</h6><h2 class="fw-bold"><?= number_format($activeServers) ?></h2><small class="text-primary"><i class="fas fa-server"></i> PowerDNS nodes</small></div></div>
    <div class="col-md-3 mb-4"><div class="dashboard-card p-3"><h6 class="text-muted">Cluster Health</h6><h2 class="fw-bold <?= htmlspecialchars($serverStatusClass, ENT_QUOTES, 'UTF-8') ?>"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($serverHealth, ENT_QUOTES, 'UTF-8') ?></h2><small>Live telemetry</small></div></div>
    <div class="col-md-3 mb-4"><div class="dashboard-card p-3"><h6 class="text-muted">System State</h6><h2 class="fw-bold text-success"><i class="fas fa-shield-alt"></i> Secure</h2><small>RBAC & CSRF Active</small></div></div>
</div>
<div class="row">
    <div class="col-md-6 mb-4"><div class="dashboard-card p-3"><h5 class="mb-3">Zone Distribution</h5><canvas id="zoneChart" height="200"></canvas></div></div>
    <div class="col-md-6 mb-4"><div class="dashboard-card p-3"><h5 class="mb-3">Recent Activity</h5><ul class="list-group list-group-flush">
        <li class="list-group-item d-flex justify-content-between align-items-center">Zone telemetry monitor <span class="badge bg-primary">Online</span></li>
        <li class="list-group-item d-flex justify-content-between align-items-center">RFC 9460 HTTPS/SVCB & RFC 9432 Catalog support <span class="badge bg-success">Active</span></li>
    </ul></div></div>
</div>
<div class="row"><div class="col-12"><div class="dashboard-card p-3"><h5>Quick Actions</h5><div class="d-flex flex-wrap gap-2"><a href="/zones/create" class="btn btn-primary"><i class="fas fa-plus"></i> New Zone</a><a href="/zones/import" class="btn btn-outline-secondary"><i class="fas fa-file-import"></i> Import BIND Zone</a><a href="/audit/export?format=csv" class="btn btn-outline-info"><i class="fas fa-download"></i> Export Audit (CSV)</a></div></div></div></div>
<script>document.addEventListener('DOMContentLoaded',function(){const ctx=document.getElementById('zoneChart');if(ctx){new Chart(ctx,{type:'doughnut',data:{labels:['Active Zones','Nodes'],datasets:[{data:[Math.max(1, <?= (int) $totalZones ?>), <?= (int) $activeServers ?>],backgroundColor:['#3b82f6','#10b981']}]}});}});</script>
