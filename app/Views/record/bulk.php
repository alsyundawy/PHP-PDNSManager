<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Bulk DNS Record Operations</h1>
        <p class="text-muted small mb-0">Search, update, or delete records simultaneously across all managed DNS zones</p>
    </div>
    <div>
        <a href="/zones" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1" aria-hidden="true"></i> Back to Zones
        </a>
    </div>
</div>

<?php if (!empty($results)): ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-transparent py-3">
            <h6 class="m-0 font-weight-bold text-success">Operation Results</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Zone</th>
                            <th>Record Name</th>
                            <th>Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $res): ?>
                            <tr>
                                <td><?= htmlspecialchars($res['zone_id'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><code><?= htmlspecialchars($res['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></code></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($res['type'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td>
                                    <?php if (($res['status'] ?? '') === 'success'): ?>
                                        <span class="badge bg-success">Success</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Failed: <?= htmlspecialchars($res['error'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Search Form -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="/zones/bulk-records" class="row g-2 align-items-center">
            <div class="col-md-7">
                <label for="searchQuery" class="visually-hidden">Search term</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search" aria-hidden="true"></i></span>
                    <input type="text" class="form-control" id="searchQuery" name="q" value="<?= htmlspecialchars($query ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Search by name, hostname, or IP address (e.g. 192.0.2.1)...">
                </div>
            </div>
            <div class="col-md-3">
                <label for="searchType" class="visually-hidden">Record Type</label>
                <select class="form-select" id="searchType" name="type">
                    <option value="">All Record Types</option>
                    <?php foreach (['A', 'AAAA', 'CNAME', 'MX', 'TXT', 'NS', 'SRV', 'CAA', 'PTR'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($type ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Search Records</button>
            </div>
        </form>
    </div>
</div>

<!-- Search Results -->
<?php if (!empty($records)): ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Matching Records (<?= count($records) ?> found)</h6>
        </div>
        <div class="card-body p-0">
            <form method="POST" action="/zones/bulk-records" id="bulkActionForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" id="selectAll" onclick="toggleAll(this)">
                                </th>
                                <th scope="col">Zone</th>
                                <th scope="col">Record Name</th>
                                <th scope="col">Type</th>
                                <th scope="col">Current Content</th>
                                <th scope="col" style="min-width: 200px;">New Content (for update)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $i => $rec): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input row-select" name="updates[<?= $i ?>][selected]" value="1" checked>
                                        <input type="hidden" name="updates[<?= $i ?>][zone_id]" value="<?= htmlspecialchars($rec['zone_id'], ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="updates[<?= $i ?>][name]" value="<?= htmlspecialchars($rec['name'], ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="updates[<?= $i ?>][type]" value="<?= htmlspecialchars($rec['type'], ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="updates[<?= $i ?>][ttl]" value="<?= (int) $rec['ttl'] ?>">
                                    </td>
                                    <td><strong><?= htmlspecialchars($rec['zone_name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                    <td><code><?= htmlspecialchars($rec['name'], ENT_QUOTES, 'UTF-8') ?></code></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($rec['type'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td><small class="text-muted font-monospace"><?= htmlspecialchars($rec['content'], ENT_QUOTES, 'UTF-8') ?></small></td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="updates[<?= $i ?>][new_content]" value="<?= htmlspecialchars($rec['content'], ENT_QUOTES, 'UTF-8') ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-light d-flex justify-content-between align-items-center py-3">
                    <span class="text-muted small">Select records above to batch update or delete</span>
                    <div class="d-flex gap-2">
                        <button type="submit" name="action" value="update" class="btn btn-success btn-sm px-3" onclick="return confirm('Update selected records across all zones?');">
                            <i class="fas fa-sync-alt me-1" aria-hidden="true"></i> Batch Update Selected
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php elseif (isset($query)): ?>
    <div class="card shadow-sm border-0 text-center p-5 text-muted">
        <i class="fas fa-search fa-2x mb-2 text-secondary" aria-hidden="true"></i>
        <p class="mb-0">No DNS records found matching your search query across any zone.</p>
    </div>
<?php endif; ?>

<script>
function toggleAll(master) {
    document.querySelectorAll('.row-select').forEach(cb => cb.checked = master.checked);
}
</script>
