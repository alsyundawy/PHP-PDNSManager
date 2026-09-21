<?php /** @var \App\Models\PdnsServer[] $servers */ ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0 text-gray-800">PowerDNS Server Clusters</h1>
        <p class="text-muted small mb-0">Manage multiple authoritative PowerDNS server instances and clusters</p>
    </div>
    <div>
        <a href="/servers/create" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-1" aria-hidden="true"></i> Add Server
        </a>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent py-3">
        <h6 class="m-0 font-weight-bold text-primary">Configured PowerDNS Nodes</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 50px;">#</th>
                        <th scope="col">Server Name</th>
                        <th scope="col">API Endpoint</th>
                        <th scope="col">Server ID</th>
                        <th scope="col">Status</th>
                        <th scope="col">Default</th>
                        <th scope="col" class="text-end" style="min-width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($servers)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No PowerDNS server nodes registered yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($servers as $idx => $s): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td class="fw-bold">
                                    <i class="fas fa-server text-primary me-2" aria-hidden="true"></i>
                                    <?= htmlspecialchars($s->name, ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td><code><?= htmlspecialchars($s->apiUrl, ENT_QUOTES, 'UTF-8') ?></code></td>
                                <td><span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($s->serverId, ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td>
                                    <?php if ($s->isActive): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="fas fa-check-circle me-1" aria-hidden="true"></i> Active
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="fas fa-times-circle me-1" aria-hidden="true"></i> Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($s->isDefault): ?>
                                        <span class="badge bg-primary">Default</span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($s->id > 0): ?>
                                        <a href="/servers/<?= $s->id ?>/edit" class="btn btn-sm btn-outline-secondary me-1" title="Edit Server">
                                            <i class="fas fa-pencil-alt" aria-hidden="true"></i>
                                        </a>
                                        <form method="POST" action="/servers/<?= $s->id ?>/delete" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this PowerDNS node?');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Server">
                                                <i class="fas fa-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge bg-info-subtle text-info">Config File</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
