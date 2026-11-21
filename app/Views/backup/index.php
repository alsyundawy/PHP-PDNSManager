<?php $title = 'Cluster Backups & Restore - PHP-PDNSManager'; ?>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-reset">
                <i class="fas fa-shield-alt text-primary me-2" aria-hidden="true"></i>Cluster Backups & Restore
            </h1>
            <p class="text-muted small mb-0">Create encrypted snapshots of all zones and records with AES-256-GCM and verify integrity via SHA-256 checksums.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBackupModal">
            <i class="fas fa-camera me-1" aria-hidden="true"></i> Create New Backup
        </button>
    </div>

    <?php if (!empty($restoreResult)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-success text-white py-2 px-3 fw-semibold">
                <i class="fas fa-clipboard-check me-2"></i> Restore Execution Plan <?= !empty($restoreResult['dry_run']) ? '(Dry-Run Preview)' : '(Completed)' ?>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Total Zones in Snapshot:</strong> <?= $restoreResult['total_zones'] ?></p>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-success"><i class="fas fa-plus-circle me-1"></i> Zones to be Created (<?= count($restoreResult['created']) ?>):</h6>
                        <ul class="small font-monospace">
                            <?php foreach ($restoreResult['created'] as $z): ?>
                                <li><?= htmlspecialchars($z, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="fas fa-sync-alt me-1"></i> Zones to be Updated/Reconciled (<?= count($restoreResult['updated']) ?>):</h6>
                        <ul class="small font-monospace">
                            <?php foreach ($restoreResult['updated'] as $z): ?>
                                <li><?= htmlspecialchars($z, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($restoreError)): ?>
        <div class="alert alert-danger shadow-sm mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i><strong>Restore Failed:</strong> <?= htmlspecialchars($restoreError, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Filename</th>
                            <th scope="col">Encryption</th>
                            <th scope="col">Size</th>
                            <th scope="col">SHA-256 Checksum</th>
                            <th scope="col">Created At</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($backups)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-archive fa-2x mb-2 d-block text-secondary"></i>
                                    No backups found in local storage.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($backups as $b): ?>
                                <tr>
                                    <td class="fw-semibold font-monospace">
                                        <i class="fas <?= $b['is_encrypted'] ? 'fa-lock text-warning' : 'fa-file-code text-primary' ?> me-2"></i>
                                        <?= htmlspecialchars($b['filename'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td>
                                        <?php if ($b['is_encrypted']): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-key me-1"></i> AES-256-GCM</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Plain JSON</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($b['size_bytes'] / 1024, 2) ?> KB</td>
                                    <td class="font-monospace small text-muted" title="<?= htmlspecialchars($b['checksum_sha256'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars(substr($b['checksum_sha256'], 0, 16)) ?>...
                                    </td>
                                    <td class="small text-muted"><?= htmlspecialchars($b['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-end">
                                        <a href="/backups/<?= urlencode($b['filename']) ?>/download" class="btn btn-sm btn-outline-primary" title="Download Backup">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-success btn-restore" data-bs-toggle="modal" data-bs-target="#restoreModal" data-filename="<?= htmlspecialchars($b['filename'], ENT_QUOTES, 'UTF-8') ?>" data-encrypted="<?= $b['is_encrypted'] ? '1' : '0' ?>" title="Restore Backup">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                        <form action="/backups/<?= urlencode($b['filename']) ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Delete this backup snapshot?');">
                                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Backup">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Backup Modal -->
<div class="modal fade" id="createBackupModal" tabindex="-1" aria-labelledby="createBackupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/backups" method="POST">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="createBackupModalLabel"><i class="fas fa-shield-alt text-primary me-2"></i>Create Cluster Backup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">This will export all Authoritative Zones, RRSets, and settings into an authenticated snapshot file.</p>
                    <div class="mb-3">
                        <label for="backupPassphrase" class="form-label">Encryption Passphrase (Optional)</label>
                        <input type="password" id="backupPassphrase" name="passphrase" class="form-control" placeholder="Enter passphrase to encrypt with AES-256-GCM">
                        <div class="form-text">If left empty, backup will be saved as plain JSON.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Start Backup</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Restore Backup Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/backups/restore" method="POST">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="filename" id="restoreFilenameInput" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="restoreModalLabel"><i class="fas fa-undo text-success me-2"></i>Restore Backup Snapshot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small">
                        Target File: <strong id="restoreFilenameDisplay" class="font-monospace"></strong>
                    </div>
                    <div class="mb-3" id="restorePassphraseGroup">
                        <label for="restorePassphrase" class="form-label">Decryption Passphrase</label>
                        <input type="password" id="restorePassphrase" name="passphrase" class="form-control" placeholder="Required if backup is encrypted">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="restoreDryRun" name="dry_run" value="1" checked>
                        <label class="form-check-label fw-semibold" for="restoreDryRun">Dry-Run Preview (inspect and calculate diff without making changes)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-play me-1"></i> Execute Restore</button>
                </div>
            </form>
        </div>
    </div>
</div>
