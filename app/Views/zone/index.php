<?php
declare(strict_types=1);

/**
 * @var array $zones
 * @var string $csrfToken
 */
$zones = $zones ?? [];
$csrfToken = $csrfToken ?? (function_exists('csrf_token') ? csrf_token() : '');
$title = 'Zones';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1 class="h3 mb-0">Zones</h1>
    <div class="d-flex flex-wrap gap-2">
        <a href="/zones/import" class="btn btn-outline-primary"><i class="fas fa-file-import me-1" aria-hidden="true"></i> Import BIND</a>
        <a href="/zones/bulk-records" class="btn btn-outline-secondary"><i class="fas fa-layer-group me-1" aria-hidden="true"></i> Bulk Records</a>
        <a href="/zones/create" class="btn btn-primary"><i class="fas fa-plus me-1" aria-hidden="true"></i> New Zone</a>
    </div>
</div>
<form method="GET" class="mb-3 row g-3">
    <div class="col-md-3">
        <label for="filter-zone-name" class="visually-hidden">Filter by name</label>
        <input type="text" id="filter-zone-name" name="name" class="form-control" placeholder="Filter by name" value="<?= htmlspecialchars($_GET['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="col-md-2">
        <label for="filter-zone-type" class="visually-hidden">Filter by type</label>
        <select id="filter-zone-type" name="type" class="form-select">
            <option value="">All Types</option>
            <option value="Native" <?= ($_GET['type'] ?? '') === 'Native' ? 'selected' : '' ?>>Native</option>
            <option value="Master" <?= ($_GET['type'] ?? '') === 'Master' ? 'selected' : '' ?>>Master</option>
            <option value="Slave" <?= ($_GET['type'] ?? '') === 'Slave' ? 'selected' : '' ?>>Slave</option>
        </select>
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-secondary"><i class="fas fa-filter me-1" aria-hidden="true"></i> Filter</button>
    </div>
</form>
<div class="table-responsive">
<table class="table table-striped table-hover"><thead><tr><th>Name</th><th>Kind</th><th>DNSSEC</th><th>Records</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($zones as $zone): ?><tr>
    <td><a href="/zones/<?= urlencode($zone['name']) ?>"><?= htmlspecialchars($zone['name']) ?></a></td>
    <td><?= htmlspecialchars($zone['kind'] ?? 'Native') ?></td>
    <td><?= ($zone['dnssec'] ?? false) ? '<span class="badge bg-success">Enabled</span>' : '<span class="badge bg-secondary">Disabled</span>' ?></td>
    <td><?= count($zone['records'] ?? []) ?></td>
    <td><a href="/zones/<?= urlencode($zone['name']) ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
        <a href="/zones/<?= urlencode($zone['name']) ?>/edit" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
        <a href="/zones/<?= urlencode($zone['name']) ?>/clone" class="btn btn-sm btn-secondary"><i class="fas fa-copy"></i></a>
        <form method="POST" action="/zones/<?= urlencode($zone['name']) ?>/delete" style="display:inline;" onsubmit="return confirm('Delete zone?')"><input type="hidden" name="_csrf" value="<?= $csrfToken ?>"><button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
    </td>
</tr><?php endforeach; ?>
<?php if (empty($zones)): ?><tr><td colspan="5" class="text-center">No zones found</td></tr><?php endif; ?>
</tbody></table>
</div>
