<?php
/**
 * @var array{name: string, kind?: string, dnssec?: bool, masters?: array<string>}|null $zone
 * @var array<int, array{name: string, type: string, ttl: int, records?: array<int, array{content: string}>}>|null $records
 * @var array<int, array{id: string, keytype: string, active: bool}>|null $keys
 * @var string|null $csrfToken
 */
$zone = $zone ?? ['name' => ''];
$records = $records ?? [];
$keys = $keys ?? [];
$csrfToken = $csrfToken ?? '';
$zoneName = (string) ($zone['name'] ?? '');
$title = 'Zone: ' . $zoneName;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Zone: <?= htmlspecialchars($zoneName, ENT_QUOTES, 'UTF-8') ?></h1>
    <div>
        <a href="/zones/<?= urlencode($zoneName) ?>/edit" class="btn btn-warning"><i class="fas fa-edit me-1" aria-hidden="true"></i> Edit</a>
        <a href="/zones/<?= urlencode($zoneName) ?>/clone" class="btn btn-secondary"><i class="fas fa-copy me-1" aria-hidden="true"></i> Clone</a>
        <a href="/zones/<?= urlencode($zoneName) ?>/export" class="btn btn-info"><i class="fas fa-file-export me-1" aria-hidden="true"></i> Export</a>
    </div>
</div>
<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <div class="card h-100">
            <div class="card-header">Zone Info</div>
            <div class="card-body">
                <p><strong>Kind:</strong> <?= htmlspecialchars((string) ($zone['kind'] ?? 'Native'), ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>DNSSEC:</strong> <?= !empty($zone['dnssec']) ? 'Enabled' : 'Disabled' ?></p>
                <p><strong>Masters:</strong> <?= htmlspecialchars(implode(', ', $zone['masters'] ?? []), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">DNSSEC Keys</div>
            <div class="card-body">
                <?php if (empty($keys)): ?>
                    <p class="text-muted">No DNSSEC keys.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>ID</th><th>Type</th><th>Active</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($keys as $key): ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) $key['id'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) $key['keytype'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= !empty($key['active']) ? 'Yes' : 'No' ?></td>
                                        <td>
                                            <form method="POST" action="/zones/<?= urlencode($zoneName) ?>/dnssec/keys/<?= urlencode((string) $key['id']) ?>/delete" style="display:inline;">
                                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete key?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                <form method="POST" action="/zones/<?= urlencode($zoneName) ?>/dnssec/keys" class="mt-2">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="row g-2 align-items-center">
                        <div class="col-auto">
                            <label for="dnssecKeyType" class="visually-hidden">Key Type</label>
                            <select id="dnssecKeyType" name="keytype" aria-label="Key Type" class="form-select form-select-sm">
                                <option value="ksk">KSK</option>
                                <option value="zsk">ZSK</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="dnssecKeyBits" class="visually-hidden">Key Bits</label>
                            <input type="number" id="dnssecKeyBits" name="bits" aria-label="Key Bits" class="form-control form-control-sm" placeholder="2048" value="2048">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-success">Add Key</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Records</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addRecordModal">
            <i class="fas fa-plus me-1" aria-hidden="true"></i> Add Record
        </button>
    </div>
    <div class="card-body">
        <form method="POST" action="/zones/<?= urlencode($zoneName) ?>/records/bulk">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 40px;">
                                <input type="checkbox" id="selectAll" aria-label="Select all records">
                            </th>
                            <th scope="col">Name</th>
                            <th scope="col">Type</th>
                            <th scope="col">Content</th>
                            <th scope="col">TTL</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $i => $record): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" id="recordSelect<?= $i ?>" aria-label="Select record <?= htmlspecialchars((string) $record['name'], ENT_QUOTES, 'UTF-8') ?>" name="selected[]" value="<?= htmlspecialchars((string) $record['name'] . '|' . (string) $record['type'], ENT_QUOTES, 'UTF-8') ?>">
                                </td>
                                <td><?= htmlspecialchars((string) $record['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) $record['type'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars(implode(', ', array_column($record['records'] ?? [], 'content')), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) $record['ttl'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editRecordModal" data-name="<?= htmlspecialchars((string) $record['name'], ENT_QUOTES, 'UTF-8') ?>" data-type="<?= htmlspecialchars((string) $record['type'], ENT_QUOTES, 'UTF-8') ?>" data-content="<?= htmlspecialchars(implode(', ', array_column($record['records'] ?? [], 'content')), ENT_QUOTES, 'UTF-8') ?>" data-ttl="<?= htmlspecialchars((string) $record['ttl'], ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                    </button>
                                    <form method="POST" action="/zones/<?= urlencode($zoneName) ?>/records/<?= urlencode((string) $record['name']) ?>/<?= urlencode((string) $record['type']) ?>/delete" style="display:inline;">
                                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete record?')">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="row g-2 align-items-center mt-2">
                <div class="col-auto">
                    <label for="bulkActionSelect" class="visually-hidden">Bulk Action</label>
                    <select id="bulkActionSelect" name="action" aria-label="Bulk Action" class="form-select form-select-sm">
                        <option value="">Bulk Action</option>
                        <option value="delete">Delete Selected</option>
                        <option value="update_ttl">Update TTL</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label for="bulkActionTtl" class="visually-hidden">Bulk Action TTL</label>
                    <input type="number" id="bulkActionTtl" name="ttl" aria-label="Bulk Action TTL" class="form-control form-control-sm" placeholder="TTL">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-secondary">Apply</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Add Record Modal -->
<div class="modal fade" id="addRecordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/zones/<?= urlencode($zoneName) ?>/records">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addRecordName" class="form-label">Name</label>
                        <input type="text" id="addRecordName" name="name" class="form-control" required placeholder="subdomain or @">
                    </div>
                    <div class="mb-3">
                        <label for="addRecordType" class="form-label">Type</label>
                        <select id="addRecordType" name="type" class="form-select">
                            <option value="A">A</option>
                            <option value="AAAA">AAAA</option>
                            <option value="CNAME">CNAME</option>
                            <option value="MX">MX</option>
                            <option value="TXT">TXT</option>
                            <option value="NS">NS</option>
                            <option value="PTR">PTR</option>
                            <option value="SRV">SRV</option>
                            <option value="CAA">CAA</option>
                            <option value="HTTPS">HTTPS (RFC 9460)</option>
                            <option value="SVCB">SVCB (RFC 9460)</option>
                            <option value="DS">DS</option>
                            <option value="DNSKEY">DNSKEY</option>
                            <option value="TLSA">TLSA</option>
                            <option value="SSHFP">SSHFP</option>
                            <option value="URI">URI</option>
                            <option value="LOC">LOC</option>
                            <option value="NAPTR">NAPTR</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="addRecordContent" class="form-label">Content</label>
                        <input type="text" id="addRecordContent" name="content" class="form-control" required placeholder="192.0.2.1">
                    </div>
                    <div class="mb-3">
                        <label for="addRecordTtl" class="form-label">TTL</label>
                        <input type="number" id="addRecordTtl" name="ttl" class="form-control" value="3600">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Record Modal -->
<div class="modal fade" id="editRecordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editRecordForm" method="POST">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Name</label>
                        <input type="text" id="editName" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="editType" class="form-label">Type</label>
                        <input type="text" id="editType" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="editContent" class="form-label">Content</label>
                        <input type="text" name="content" id="editContent" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTtl" class="form-label">TTL</label>
                        <input type="number" name="ttl" id="editTtl" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('selectAll')?.addEventListener('change', function(e) {
    document.querySelectorAll('input[name="selected[]"]').forEach(cb => cb.checked = e.target.checked);
});
document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#editRecordModal"]').forEach(btn => {
    btn.addEventListener('click', function() {
        const editName = document.getElementById('editName');
        const editType = document.getElementById('editType');
        const editContent = document.getElementById('editContent');
        const editTtl = document.getElementById('editTtl');
        const editRecordForm = document.getElementById('editRecordForm');

        if (editName) editName.value = this.dataset.name || '';
        if (editType) editType.value = this.dataset.type || '';
        if (editContent) editContent.value = this.dataset.content || '';
        if (editTtl) editTtl.value = this.dataset.ttl || '3600';
        if (editRecordForm) {
            editRecordForm.action = '/zones/<?= urlencode($zoneName) ?>/records/' + encodeURIComponent(this.dataset.name || '') + '/' + encodeURIComponent(this.dataset.type || '') + '/update';
        }
    });
});
</script>
