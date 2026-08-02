<?php $title = 'Zones'; ?>
<div class="d-flex justify-content-between align-items-center mb-4"><h1>Zones</h1><a href="/zones/create" class="btn btn-primary"><i class="fas fa-plus"></i> New Zone</a></div>
<form method="GET" class="mb-3 row g-3">
    <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Filter by name" value="<?= htmlspecialchars($_GET['name'] ?? '') ?>"></div>
    <div class="col-md-2"><select name="type" class="form-select"><option value="">All Types</option><option value="Native" <?= ($_GET['type']??'')==='Native'?'selected':'' ?>>Native</option></select></div>
    <div class="col-md-2"><button type="submit" class="btn btn-secondary">Filter</button></div>
</form>
<table class="table table-striped table-hover"><thead><tr><th>Name</th><th>Kind</th><th>DNSSEC</th><th>Records</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($zones as $zone): ?><tr>
    <td><a href="/zones/<?= urlencode($zone['name']) ?>"><?= htmlspecialchars($zone['name']) ?></a></td>
    <td><?= htmlspecialchars($zone['kind']??'Native') ?></td>
    <td><?= ($zone['dnssec']??false)?'<span class="badge bg-success">Enabled</span>':'<span class="badge bg-secondary">Disabled</span>' ?></td>
    <td><?= count($zone['records']??[]) ?></td>
    <td><a href="/zones/<?= urlencode($zone['name']) ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
        <a href="/zones/<?= urlencode($zone['name']) ?>/edit" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
        <a href="/zones/<?= urlencode($zone['name']) ?>/clone" class="btn btn-sm btn-secondary"><i class="fas fa-copy"></i></a>
        <form method="POST" action="/zones/<?= urlencode($zone['name']) ?>/delete" style="display:inline;" onsubmit="return confirm('Delete zone?')"><input type="hidden" name="_csrf" value="<?= $csrfToken ?>"><button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
    </td>
</tr><?php endforeach; ?>
<?php if (empty($zones)): ?><tr><td colspan="5" class="text-center">No zones found</td></tr><?php endif; ?>
</tbody></table>
