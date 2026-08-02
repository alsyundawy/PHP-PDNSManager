<?php $title = 'Dashboard'; ?>
<div class="row">
    <div class="col-md-3 mb-4"><div class="dashboard-card p-3"><h6 class="text-muted">Total Zones</h6><h2 class="fw-bold">143</h2><small class="text-success"><i class="fas fa-arrow-up"></i> +12%</small></div></div>
    <div class="col-md-3 mb-4"><div class="dashboard-card p-3"><h6 class="text-muted">Records</h6><h2 class="fw-bold">2,847</h2><small class="text-success"><i class="fas fa-arrow-up"></i> +5%</small></div></div>
    <div class="col-md-3 mb-4"><div class="dashboard-card p-3"><h6 class="text-muted">DNSSEC Keys</h6><h2 class="fw-bold">12</h2><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> 2 expiring</small></div></div>
    <div class="col-md-3 mb-4"><div class="dashboard-card p-3"><h6 class="text-muted">Server Health</h6><h2 class="fw-bold text-success"><i class="fas fa-check-circle"></i> OK</h2><small>Uptime 99.98%</small></div></div>
</div>
<div class="row">
    <div class="col-md-6 mb-4"><div class="dashboard-card p-3"><h5 class="mb-3">Zone Distribution</h5><canvas id="zoneChart" height="200"></canvas></div></div>
    <div class="col-md-6 mb-4"><div class="dashboard-card p-3"><h5 class="mb-3">Recent Activity</h5><ul class="list-group list-group-flush">
        <li class="list-group-item d-flex justify-content-between align-items-center">Zone <strong>example.com</strong> created <span class="badge bg-primary">2 min ago</span></li>
        <li class="list-group-item d-flex justify-content-between align-items-center">Record A updated <span class="badge bg-secondary">10 min ago</span></li>
    </ul></div></div>
</div>
<div class="row"><div class="col-12"><div class="dashboard-card p-3"><h5>Quick Actions</h5><div class="d-flex flex-wrap gap-2"><a href="/zones/create" class="btn btn-primary"><i class="fas fa-plus"></i> New Zone</a><a href="#" class="btn btn-outline-secondary"><i class="fas fa-file-import"></i> Import Zone</a></div></div></div></div>
<script>document.addEventListener('DOMContentLoaded',function(){const ctx=document.getElementById('zoneChart');if(ctx){new Chart(ctx,{type:'doughnut',data:{labels:['Active','Inactive','Secondary'],datasets:[{data:[120,15,8],backgroundColor:['#3b82f6','#ef4444','#10b981']}]}});}});</script>
