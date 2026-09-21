<?php
/** @var array $logs */
/** @var int $total */
/** @var int $page */
/** @var int $totalPages */
/** @var array $filters */
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Audit Trail & Security Logs</h1>
        <p class="text-muted small mb-0">Complete tamper-evident record of all administrative and API mutations</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/audit-logs/export?format=csv&<?= http_build_query($filters) ?>" class="btn btn-outline-success shadow-sm btn-sm">
            <i class="fas fa-file-csv me-1" aria-hidden="true"></i> Export CSV
        </a>
        <a href="/audit-logs/export?format=json&<?= http_build_query($filters) ?>" class="btn btn-outline-primary shadow-sm btn-sm">
            <i class="fas fa-file-code me-1" aria-hidden="true"></i> Export JSON
        </a>
    </div>
</div>

<!-- Search and Filter Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="/audit-logs" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="filterAction" class="form-label small fw-semibold mb-1">Action</label>
                <input type="text" class="form-control form-control-sm" id="filterAction" name="action" value="<?= htmlspecialchars($filters['action'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. zone.create, record.delete">
            </div>
            <div class="col-md-2">
                <label for="filterUserId" class="form-label small fw-semibold mb-1">User ID</label>
                <input type="number" class="form-control form-control-sm" id="filterUserId" name="user_id" value="<?= htmlspecialchars((string) ($filters['user_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="User ID">
            </div>
            <div class="col-md-3">
                <label for="filterDateFrom" class="form-label small fw-semibold mb-1">From Date</label>
                <input type="date" class="form-control form-control-sm" id="filterDateFrom" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label for="filterDateTo" class="form-label small fw-semibold mb-1">To Date</label>
                <input type="date" class="form-control form-control-sm" id="filterDateTo" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Audit Entries (<?= $total ?> total)</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 60px;">ID</th>
                        <th scope="col">User</th>
                        <th scope="col">Action</th>
                        <th scope="col">Status</th>
                        <th scope="col">IP Address</th>
                        <th scope="col">Payload</th>
                        <th scope="col">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No audit logs matching current filter.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>#<?= htmlspecialchars((string) ($log['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <i class="fas fa-user-circle text-secondary me-1" aria-hidden="true"></i>
                                    <?= htmlspecialchars((string) ($log['user_id'] ?? 'System'), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td><code><?= htmlspecialchars($log['action'] ?? '', ENT_QUOTES, 'UTF-8') ?></code></td>
                                <td>
                                    <?php $code = (int) ($log['status_code'] ?? 200); ?>
                                    <span class="badge bg-<?= $code < 300 ? 'success' : ($code < 400 ? 'info' : 'danger') ?>">
                                        <?= $code ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($log['ip_address'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="max-width: 250px;" class="text-truncate">
                                    <?php
                                    $payload = $log['payload'] ?? null;
                                    $payloadStr = is_array($payload) ? json_encode($payload) : (string) $payload;
                                    ?>
                                    <span title="<?= htmlspecialchars($payloadStr, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($payloadStr, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($log['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-light py-2 d-flex justify-content-between align-items-center">
                <span class="text-muted small">Page <?= $page ?> of <?= $totalPages ?></span>
                <nav aria-label="Audit log pagination">
                    <ul class="pagination pagination-sm mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="/audit-logs?page=<?= $page - 1 ?>&<?= http_build_query($filters) ?>">Previous</a>
                            </li>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="/audit-logs?page=<?= $page + 1 ?>&<?= http_build_query($filters) ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>
