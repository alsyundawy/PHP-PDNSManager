<?php
/**
 * PHP-PDNSManager Enterprise Edition
 * Zone Metadata Management View
 *
 * @var array{name: string, kind?: string, dnssec?: bool, masters?: array<string>}|null $zone
 * @var array<int, array{kind: string, metadata: array<int, string>}>|null $metadata
 * @var array<string, string>|null $supportedKinds
 * @var string|null $csrfToken
 */

$zone = $zone ?? ['name' => ''];
$metadata = $metadata ?? [];
$supportedKinds = $supportedKinds ?? [];
$csrfToken = $csrfToken ?? '';
$zoneName = (string) ($zone['name'] ?? '');
$title = 'Zone Metadata: ' . $zoneName;
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/zones">Zones</a></li>
                <li class="breadcrumb-item"><a href="/zones/<?= urlencode($zoneName) ?>"><?= htmlspecialchars($zoneName, ENT_QUOTES, 'UTF-8') ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Metadata</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0"><i class="fas fa-tags text-primary me-2" aria-hidden="true"></i>Zone Metadata: <?= htmlspecialchars($zoneName, ENT_QUOTES, 'UTF-8') ?></h1>
    </div>
    <div>
        <a href="/zones/<?= urlencode($zoneName) ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1" aria-hidden="true"></i> Back to Zone
        </a>
    </div>
</div>

<div class="row">
    <!-- Active Metadata Table -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="fas fa-list-check me-2 text-info" aria-hidden="true"></i>Configured PowerDNS Directives</span>
                <span class="badge bg-secondary"><?= count($metadata) ?> active</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($metadata)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-info-circle fa-2x mb-2 d-block text-secondary" aria-hidden="true"></i>
                        <p class="mb-0">No custom metadata directives are configured for this zone.</p>
                        <small>Use the form on the right to configure AXFR ACLs, TSIG keys, or SOA-EDIT serial behavior.</small>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width: 28%;">Directive (Kind)</th>
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
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1">
                                                <?= htmlspecialchars($kind, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                            <?php if (isset($supportedKinds[$kind])): ?>
                                                <small class="d-block text-muted mt-1"><?= htmlspecialchars($supportedKinds[$kind], ENT_QUOTES, 'UTF-8') ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (empty($values)): ?>
                                                <span class="text-muted fst-italic">None (Empty)</span>
                                            <?php else: ?>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <?php foreach ($values as $val): ?>
                                                        <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8') ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="/zones/<?= urlencode($zoneName) ?>/metadata/<?= urlencode($kind) ?>/delete" class="d-inline" onsubmit="return confirm('Are you sure you want to remove directive <?= htmlspecialchars($kind, ENT_QUOTES, 'UTF-8') ?>?');">
                                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete metadata directive">
                                                    <i class="fas fa-trash" aria-hidden="true"></i> Delete
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
    </div>

    <!-- Add / Update Metadata Form -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent fw-bold">
                <i class="fas fa-plus-circle text-success me-2" aria-hidden="true"></i>Set Zone Directive
            </div>
            <div class="card-body">
                <form method="POST" action="/zones/<?= urlencode($zoneName) ?>/metadata">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="mb-3">
                        <label for="metaKindSelect" class="form-label fw-semibold">Metadata Kind</label>
                        <select id="metaKindSelect" name="kind" class="form-select font-monospace" required>
                            <option value="">-- Choose Directive --</option>
                            <?php foreach ($supportedKinds as $k => $desc): ?>
                                <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="metaValuesInput" class="form-label fw-semibold">
                            Directive Values <small class="text-muted fw-normal">(One per line)</small>
                        </label>
                        <textarea id="metaValuesInput" name="values" class="form-control font-monospace" rows="5" placeholder="e.g.&#10;192.0.2.1/32&#10;198.51.100.0/24" required></textarea>
                        <div class="form-text">
                            For <code>ALLOW-AXFR-FROM</code>: IP/CIDR masks.<br>
                            For <code>TSIG-ALLOW-AXFR</code>: Name of authorized TSIG key.<br>
                            For <code>SOA-EDIT</code>: e.g. <code>INCEPTION-INCREMENT</code>, <code>DEFAULT</code>.
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1" aria-hidden="true"></i> Save Directive
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
