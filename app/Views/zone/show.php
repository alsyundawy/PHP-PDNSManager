<?php $title = 'Zone: ' . $zone['name']; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Zone: <?= htmlspecialchars($zone['name']) ?></h1>
    <div><a href="/zones/<?= urlencode($zone['name']) ?>/edit" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
    <a href="/zones/<?= urlencode($zone['name']) ?>/clone" class="btn btn-secondary"><i class="fas fa-copy"></i> Clone</a>
    <a href="/zones/<?= urlencode($zone['name']) ?>/export" class="btn btn-info"><i class="fas fa-file-export"></i> Export</a></div>
</div>
<div class="row mb-4"><div class="col-md-6"><div class="card"><div class="card-header">Zone Info</div><div class="card-body">
    <p><strong>Kind:</strong> <?= htmlspecialchars($zone['kind']??'Native') ?></p>
    <p><strong>DNSSEC:</strong> <?= ($zone['dnssec']??false)?'Enabled':'Disabled' ?></p>
    <p><strong>Masters:</strong> <?= htmlspecialchars(implode(', ', $zone['masters']??[])) ?></p>
</div></div></div>
<div class="col-md-6"><div class="card"><div class="card-header">DNSSEC Keys</div><div class="card-body">
    <?php if (empty($keys)): ?><p>No DNSSEC keys.</p><?php else: ?><table class="table table-sm"><thead><tr><th>ID</th><th>Type</th><th>Active</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($keys as $key): ?><tr><td><?= htmlspecialchars($key['id']) ?></td><td><?= htmlspecialchars($key['keytype']) ?></td><td><?= $key['active']?'Yes':'No' ?></td><td>
        <form method="POST" action="/zones/<?= urlencode($zone['name']) ?>/dnssec/keys/<?= urlencode($key['id']) ?>/delete" style="display:inline;"><input type="hidden" name="_csrf" value="<?= $csrfToken ?>"><button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete key?')">Delete</button></form>
    </td></tr><?php endforeach; ?></tbody></table><?php endif; ?>
    <form method="POST" action="/zones/<?= urlencode($zone['name']) ?>/dnssec/keys"><input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
        <div class="row g-2"><div class="col-auto"><select name="keytype" class="form-select form-select-sm"><option value="ksk">KSK</option><option value="zsk">ZSK</option></select></div>
        <div class="col-auto"><input type="number" name="bits" class="form-control form-control-sm" placeholder="2048" value="2048"></div>
        <div class="col-auto"><button type="submit" class="btn btn-sm btn-success">Add Key</button></div></div>
    </form>
</div></div></div></div>
<div class="card"><div class="card-header d-flex justify-content-between align-items-center"><span>Records</span><button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addRecordModal"><i class="fas fa-plus"></i> Add Record</button></div>
<div class="card-body">
    <form method="POST" action="/zones/<?= urlencode($zone['name']) ?>/records/bulk"><input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
        <table class="table table-striped table-sm"><thead><tr><th><input type="checkbox" id="selectAll"></th><th>Name</th><th>Type</th><th>Content</th><th>TTL</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($records as $record): ?><tr>
            <td><input type="checkbox" name="selected[]" value="<?= htmlspecialchars($record['name']) . '|' . htmlspecialchars($record['type']) ?>"></td>
            <td><?= htmlspecialchars($record['name']) ?></td><td><?= htmlspecialchars($record['type']) ?></td>
            <td><?= htmlspecialchars(implode(', ', array_column($record['records']??[], 'content'))) ?></td>
            <td><?= htmlspecialchars($record['ttl']) ?></td>
            <td><button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editRecordModal" data-name="<?= htmlspecialchars($record['name']) ?>" data-type="<?= htmlspecialchars($record['type']) ?>" data-content="<?= htmlspecialchars(implode(', ', array_column($record['records']??[], 'content'))) ?>" data-ttl="<?= htmlspecialchars($record['ttl']) ?>"><i class="fas fa-edit"></i></button>
                <form method="POST" action="/zones/<?= urlencode($zone['name']) ?>/records/<?= urlencode($record['name']) ?>/<?= urlencode($record['type']) ?>/delete" style="display:inline;"><input type="hidden" name="_csrf" value="<?= $csrfToken ?>"><button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete record?')"><i class="fas fa-trash"></i></button></form>
            </td>
        </tr><?php endforeach; ?>
        </tbody></table>
        <div class="row g-2 align-items-center"><div class="col-auto"><select name="action" class="form-select form-select-sm"><option value="">Bulk Action</option><option value="delete">Delete Selected</option><option value="update_ttl">Update TTL</option></select></div>
        <div class="col-auto"><input type="number" name="ttl" class="form-control form-control-sm" placeholder="TTL"></div>
        <div class="col-auto"><button type="submit" class="btn btn-sm btn-secondary">Apply</button></div></div>
    </form>
</div></div>

<!-- Add Record Modal -->
<div class="modal fade" id="addRecordModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
<form method="POST" action="/zones/<?= urlencode($zone['name']) ?>/records"><input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
<div class="modal-header"><h5 class="modal-title">Add Record</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="mb-3"><label>Name</label><input type="text" name="name" class="form-control" required></div>
<div class="mb-3"><label>Type</label><select name="type" class="form-select"><option value="A">A</option><option value="AAAA">AAAA</option><option value="CNAME">CNAME</option><option value="MX">MX</option><option value="TXT">TXT</option><option value="SRV">SRV</option></select></div>
<div class="mb-3"><label>Content</label><input type="text" name="content" class="form-control" required></div>
<div class="mb-3"><label>TTL</label><input type="number" name="ttl" class="form-control" value="3600"></div></div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Record</button></div>
</form></div></div></div>

<!-- Edit Record Modal -->
<div class="modal fade" id="editRecordModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
<form id="editRecordForm" method="POST"><input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
<div class="modal-header"><h5 class="modal-title">Edit Record</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="mb-3"><label>Name</label><input type="text" id="editName" class="form-control" readonly></div>
<div class="mb-3"><label>Type</label><input type="text" id="editType" class="form-control" readonly></div>
<div class="mb-3"><label>Content</label><input type="text" name="content" id="editContent" class="form-control" required></div>
<div class="mb-3"><label>TTL</label><input type="number" name="ttl" id="editTtl" class="form-control" required></div></div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update Record</button></div>
</form></div></div></div>

<script>
document.getElementById('selectAll').addEventListener('change', function(e) {
    document.querySelectorAll('input[name="selected[]"]').forEach(cb => cb.checked = e.target.checked);
});
document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#editRecordModal"]').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('editName').value = this.dataset.name;
        document.getElementById('editType').value = this.dataset.type;
        document.getElementById('editContent').value = this.dataset.content;
        document.getElementById('editTtl').value = this.dataset.ttl;
        document.getElementById('editRecordForm').action = '/zones/<?= urlencode($zone['name']) ?>/records/' + encodeURIComponent(this.dataset.name) + '/' + encodeURIComponent(this.dataset.type) + '/update';
    });
});
</script>
