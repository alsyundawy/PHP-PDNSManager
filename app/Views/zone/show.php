<?php
/**
 * @var array{name: string, kind?: string, dnssec?: bool, masters?: array<string>}|null $zone
 * @var array<int, array{name: string, type: string, ttl: int, records?: array<int, array{content: string}>}>|null $records
 * @var array<int, array{id: string, keytype: string, active: bool, ds?: array<string>, dnskey?: string, algorithm?: string, bits?: int}>|null $keys
 * @var array<int, array{kind: string, metadata: array<int, string>}>|null $metadata
 * @var array<int, array{id: string, name?: string, algorithm?: string}>|null $tsigKeys
 * @var string|null $csrfToken
 */
$zone = $zone ?? ['name' => ''];
$records = $records ?? [];
$keys = $keys ?? [];
$metadata = $metadata ?? [];
$tsigKeys = $tsigKeys ?? [];
$csrfToken = $csrfToken ?? '';
$zoneName = (string) ($zone['name'] ?? '');
$title = 'Zone: ' . $zoneName;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/zones">Zones</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($zoneName, ENT_QUOTES, 'UTF-8') ?></li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Zone: <?= htmlspecialchars($zoneName, ENT_QUOTES, 'UTF-8') ?></h1>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <form method="POST" action="/zones/<?= urlencode($zoneName) ?>/notify" class="d-inline">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn btn-outline-info" title="Send DNS NOTIFY packets to secondaries"><i class="fas fa-bell me-1" aria-hidden="true"></i> Notify</button>
        </form>
        <?php if (in_array(strtolower((string) ($zone['kind'] ?? '')), ['slave', 'consumer'], true)): ?>
            <form method="POST" action="/zones/<?= urlencode($zoneName) ?>/axfr-retrieve" class="d-inline">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-outline-primary" title="Force AXFR pull from master"><i class="fas fa-cloud-download-alt me-1" aria-hidden="true"></i> Retrieve AXFR</button>
            </form>
        <?php endif; ?>
        <form method="POST" action="/zones/<?= urlencode($zoneName) ?>/rectify" class="d-inline">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn btn-outline-secondary" title="Rectify DNSSEC metadata & order"><i class="fas fa-wrench me-1" aria-hidden="true"></i> Rectify</button>
        </form>
        <a href="/zones/<?= urlencode($zoneName) ?>/edit" class="btn btn-warning"><i class="fas fa-edit me-1" aria-hidden="true"></i> Edit</a>
        <a href="/zones/<?= urlencode($zoneName) ?>/clone" class="btn btn-secondary"><i class="fas fa-copy me-1" aria-hidden="true"></i> Clone</a>
        <a href="/zones/<?= urlencode($zoneName) ?>/export" class="btn btn-info"><i class="fas fa-file-export me-1" aria-hidden="true"></i> Export</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-transparent fw-bold"><i class="fas fa-circle-info text-primary me-2" aria-hidden="true"></i>Zone Info</div>
            <div class="card-body">
                <p><strong>Kind:</strong> <span class="badge bg-secondary"><?= htmlspecialchars((string) ($zone['kind'] ?? 'Native'), ENT_QUOTES, 'UTF-8') ?></span></p>
                <p><strong>DNSSEC:</strong> <?= !empty($zone['dnssec']) ? '<span class="badge bg-success"><i class="fas fa-shield-alt me-1"></i>Enabled</span>' : '<span class="badge bg-secondary">Disabled</span>' ?></p>
                <p><strong>Masters:</strong> <?= empty($zone['masters']) ? '<span class="text-muted">None</span>' : htmlspecialchars(implode(', ', $zone['masters']), ENT_QUOTES, 'UTF-8') ?></p>
                <p class="mb-0"><strong>Total Records:</strong> <?= count($records) ?> resource record sets</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-transparent fw-bold d-flex justify-content-between align-items-center">
                <span><i class="fas fa-key text-warning me-2" aria-hidden="true"></i>DNSSEC Keys</span>
                <span class="badge bg-secondary"><?= count($keys) ?> keys</span>
            </div>
            <div class="card-body">
                <?php if (empty($keys)): ?>
                    <p class="text-muted mb-2">No DNSSEC keys active on this zone.</p>
                <?php else: ?>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead>
                                <tr><th>ID</th><th>Type</th><th>Algorithm</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($keys as $key): ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) $key['id'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><span class="badge bg-primary-subtle text-primary border"><?= htmlspecialchars(strtoupper((string) $key['keytype']), ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td><small class="font-monospace"><?= htmlspecialchars((string) ($key['algorithm'] ?? 'ECDSA256'), ENT_QUOTES, 'UTF-8') ?></small></td>
                                        <td><?= !empty($key['active']) ? '<span class="text-success"><i class="fas fa-check-circle me-1"></i>Active</span>' : '<span class="text-muted">Inactive</span>' ?></td>
                                        <td>
                                            <form method="POST" action="/zones/<?= urlencode($zoneName) ?>/dnssec/keys/<?= urlencode((string) $key['id']) ?>/delete" class="d-inline">
                                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete DNSSEC key #<?= htmlspecialchars((string) $key['id'], ENT_QUOTES, 'UTF-8') ?>?')">
                                                    <i class="fas fa-trash" aria-hidden="true"></i>
                                                </button>
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
                                <option value="ksk">KSK (Key Signing Key)</option>
                                <option value="zsk">ZSK (Zone Signing Key)</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="dnssecKeyBits" class="visually-hidden">Key Bits</label>
                            <input type="number" id="dnssecKeyBits" name="bits" aria-label="Key Bits" class="form-control form-control-sm" placeholder="2048" value="2048">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-plus me-1" aria-hidden="true"></i> Add Key</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// DNSSEC Registrar DS Records helper
$dsRecords = [];
foreach ($keys as $k) {
    if (!empty($k['ds'])) {
        foreach ((array) $k['ds'] as $dsStr) {
            $parts = preg_split('/\s+/', trim((string) $dsStr));
            if (count($parts) >= 4) {
                $dsRecords[] = [
                    'key_tag' => $parts[0],
                    'algorithm' => $parts[1],
                    'digest_type' => $parts[2],
                    'digest' => $parts[3],
                    'raw' => trim((string) $dsStr),
                ];
            }
        }
    }
}
?>

<?php if (!empty($dsRecords)): ?>
    <!-- Registrar DS Record Export Widget -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-transparent fw-bold d-flex justify-content-between align-items-center">
            <span><i class="fas fa-passport text-info me-2" aria-hidden="true"></i>Registrar Delegation Signer (DS) Records</span>
            <span class="badge bg-info-subtle text-info border"><?= count($dsRecords) ?> DS digests</span>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">Copy these Delegation Signer (DS) records to submit to your domain registrar (e.g. Cloudflare, GoDaddy, Namecheap, IDNIC/PANDI) to complete the DNSSEC chain of trust.</p>
            <div class="row g-3">
                <?php foreach ($dsRecords as $idx => $ds): ?>
                    <div class="col-lg-6">
                        <div class="p-3 border rounded bg-light-subtle">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold font-monospace text-primary">Digest Type <?= htmlspecialchars($ds['digest_type'], ENT_QUOTES, 'UTF-8') ?> (<?= $ds['digest_type'] === '2' ? 'SHA-256' : ($ds['digest_type'] === '1' ? 'SHA-1' : 'SHA-384') ?>)</span>
                                <button type="button" class="btn btn-sm btn-outline-primary copy-btn" data-clipboard="<?= htmlspecialchars($zoneName . ' IN DS ' . $ds['raw'], ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="fas fa-copy me-1" aria-hidden="true"></i> Copy RFC 4034
                                </button>
                            </div>
                            <div class="row g-2 small font-monospace">
                                <div class="col-4 text-muted">Key Tag:</div>
                                <div class="col-8 fw-semibold"><?= htmlspecialchars($ds['key_tag'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="col-4 text-muted">Algorithm:</div>
                                <div class="col-8"><?= htmlspecialchars($ds['algorithm'], ENT_QUOTES, 'UTF-8') ?> (<?= $ds['algorithm'] === '13' ? 'ECDSA P-256' : 'RSA/SHA-256' ?>)</div>
                                <div class="col-4 text-muted">Digest Type:</div>
                                <div class="col-8"><?= htmlspecialchars($ds['digest_type'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="col-4 text-muted">Digest:</div>
                                <div class="col-8 text-break fw-semibold text-dark"><?= htmlspecialchars($ds['digest'], ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Zone Metadata Directives Card -->
<div class="card mb-4 shadow-sm border-0" id="metadata">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="fas fa-tags text-primary me-2" aria-hidden="true"></i>PowerDNS Zone Metadata Directives</span>
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addMetadataModal">
            <i class="fas fa-plus me-1" aria-hidden="true"></i> Add Directive
        </button>
    </div>
    <div class="card-body p-0">
        <?php if (empty($metadata)): ?>
            <div class="p-3 text-muted text-center small">
                No custom metadata directives configured. Directives allow granular control over AXFR ACLs (<code>ALLOW-AXFR-FROM</code>), TSIG keys (<code>TSIG-ALLOW-AXFR</code>), and SOA serial increments (<code>SOA-EDIT</code>).
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width: 25%;">Directive (Kind)</th>
                            <th scope="col">Configured Values</th>
                            <th scope="col" class="text-end" style="width: 15%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($metadata as $item): ?>
                            <?php
                            $kind = (string) ($item['kind'] ?? '');
                            $values = (array) ($item['metadata'] ?? []);
                            ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border font-monospace px-2 py-1">
                                        <?= htmlspecialchars($kind, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (empty($values)): ?>
                                        <span class="text-muted fst-italic">None</span>
                                    <?php else: ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($values as $val): ?>
                                                <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="/zones/<?= urlencode($zoneName) ?>/metadata/<?= urlencode($kind) ?>/delete" class="d-inline" onsubmit="return confirm('Remove directive <?= htmlspecialchars($kind, ENT_QUOTES, 'UTF-8') ?>?');">
                                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete metadata directive">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="fas fa-database text-primary me-2" aria-hidden="true"></i>Records</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addRecordModal">
            <i class="fas fa-plus me-1" aria-hidden="true"></i> Add Record
        </button>
    </div>
    <div class="card-body">
        <form method="POST" action="/zones/<?= urlencode($zoneName) ?>/records/bulk">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <div class="table-responsive">
                <table class="table table-striped table-sm align-middle">
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
                                <td><strong><?= htmlspecialchars((string) $record['name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><span class="badge bg-secondary font-monospace"><?= htmlspecialchars((string) $record['type'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td class="text-break"><?= htmlspecialchars(implode(', ', array_column($record['records'] ?? [], 'content')), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) $record['ttl'], ENT_QUOTES, 'UTF-8') ?>s</td>
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
<div class="modal fade" id="addRecordModal" tabindex="-1" aria-labelledby="addRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/zones/<?= urlencode($zoneName) ?>/records">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRecordModalLabel"><i class="fas fa-plus text-primary me-2"></i>Add Record</h5>
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
                    <div class="mb-3 form-check" id="autoPtrGroup">
                        <input type="checkbox" class="form-check-input" id="addRecordAutoPtr" name="auto_ptr" value="1">
                        <label class="form-check-label" for="addRecordAutoPtr">
                            <i class="fas fa-magic text-info me-1" aria-hidden="true"></i> <strong>Auto-create reverse PTR record</strong>
                            <span class="d-block text-muted small">Automatically creates matching PTR in the corresponding reverse zone (<code>in-addr.arpa</code> / <code>ip6.arpa</code>) if hosted.</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Record Modal -->
<div class="modal fade" id="editRecordModal" tabindex="-1" aria-labelledby="editRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editRecordForm" method="POST">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRecordModalLabel">Edit Record</h5>
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

<!-- Add Metadata Directive Modal -->
<div class="modal fade" id="addMetadataModal" tabindex="-1" aria-labelledby="addMetadataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/zones/<?= urlencode($zoneName) ?>/metadata">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMetadataModalLabel"><i class="fas fa-tags text-primary me-2"></i>Add / Set Zone Directive</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modalKindSelect" class="form-label fw-semibold">Metadata Kind</label>
                        <select id="modalKindSelect" name="kind" class="form-select font-monospace" required>
                            <option value="">-- Choose Directive --</option>
                            <option value="ALLOW-AXFR-FROM">ALLOW-AXFR-FROM (IP / Subnet ACL)</option>
                            <option value="TSIG-ALLOW-AXFR">TSIG-ALLOW-AXFR (Authorized TSIG Key)</option>
                            <option value="AXFR-SOURCE">AXFR-SOURCE (Outbound Source IP)</option>
                            <option value="SOA-EDIT">SOA-EDIT (Serial Increment Rule)</option>
                            <option value="SOA-EDIT-API">SOA-EDIT-API (API Serial Increment Rule)</option>
                            <option value="ALSO-NOTIFY">ALSO-NOTIFY (Extra NOTIFY Recipients)</option>
                            <option value="NOTIFY-DNSUPDATE">NOTIFY-DNSUPDATE (Notify on Dynamic Update)</option>
                            <option value="LUA-AXFR-SCRIPT">LUA-AXFR-SCRIPT (LUA Script Path)</option>
                        </select>
                    </div>

                    <?php if (!empty($tsigKeys)): ?>
                        <div class="mb-3 d-none" id="tsigPickerWrapper">
                            <label for="tsigPickerSelect" class="form-label fw-semibold">Select Existing TSIG Key</label>
                            <select id="tsigPickerSelect" class="form-select font-monospace">
                                <option value="">-- Choose Key --</option>
                                <?php foreach ($tsigKeys as $tk): ?>
                                    <?php $tkName = (string) ($tk['id'] ?? ($tk['name'] ?? '')); ?>
                                    <option value="<?= htmlspecialchars($tkName, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($tkName, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="modalValuesInput" class="form-label fw-semibold">
                            Directive Values <small class="text-muted fw-normal">(One per line)</small>
                        </label>
                        <textarea id="modalValuesInput" name="values" class="form-control font-monospace" rows="4" placeholder="e.g.&#10;192.0.2.1/32&#10;198.51.100.0/24" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Directive</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Select all checkbox
    document.getElementById('selectAll')?.addEventListener('change', function(e) {
        document.querySelectorAll('input[name="selected[]"]').forEach(cb => cb.checked = e.target.checked);
    });

    // 2. Edit Record modal pre-fill
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

    // 3. Auto-PTR visibility toggle
    const addType = document.getElementById('addRecordType');
    const autoPtrGroup = document.getElementById('autoPtrGroup');
    if (addType && autoPtrGroup) {
        const checkAutoPtrVisibility = () => {
            const val = addType.value.toUpperCase();
            if (val === 'A' || val === 'AAAA') {
                autoPtrGroup.classList.remove('d-none');
            } else {
                autoPtrGroup.classList.add('d-none');
                const chk = document.getElementById('addRecordAutoPtr');
                if (chk) chk.checked = false;
            }
        };
        addType.addEventListener('change', checkAutoPtrVisibility);
        checkAutoPtrVisibility();
    }

    // 4. TSIG quick picker in metadata modal
    const modalKindSelect = document.getElementById('modalKindSelect');
    const tsigPickerWrapper = document.getElementById('tsigPickerWrapper');
    const tsigPickerSelect = document.getElementById('tsigPickerSelect');
    const modalValuesInput = document.getElementById('modalValuesInput');
    if (modalKindSelect && tsigPickerWrapper && tsigPickerSelect && modalValuesInput) {
        modalKindSelect.addEventListener('change', function() {
            if (this.value === 'TSIG-ALLOW-AXFR') {
                tsigPickerWrapper.classList.remove('d-none');
            } else {
                tsigPickerWrapper.classList.add('d-none');
            }
        });
        tsigPickerSelect.addEventListener('change', function() {
            if (this.value) {
                modalValuesInput.value = this.value;
            }
        });
    }

    // 5. Copy to clipboard buttons
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const text = this.getAttribute('data-clipboard');
            if (text && navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    const originalHtml = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-check text-success me-1"></i> Copied!';
                    setTimeout(() => { this.innerHTML = originalHtml; }, 2000);
                });
            }
        });
    });
});
</script>
