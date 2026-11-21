<?php $title = 'TSIG Keys Management - PHP-PDNSManager'; ?>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-reset">
                <i class="fas fa-key text-primary me-2" aria-hidden="true"></i>TSIG Cryptographic Keys
            </h1>
            <p class="text-muted small mb-0">Manage Transaction Signature (TSIG) keys for secure AXFR zone transfers between primary and secondary nameservers.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTsigModal">
            <i class="fas fa-plus me-1" aria-hidden="true"></i> Create TSIG Key
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Key Name</th>
                            <th scope="col">Algorithm</th>
                            <th scope="col">Secret Key</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($keys)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="fas fa-key fa-2x mb-2 d-block text-secondary"></i>
                                    No TSIG keys configured on this PowerDNS server.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($keys as $k): ?>
                                <tr>
                                    <td class="fw-semibold font-monospace"><?= htmlspecialchars($k['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge bg-secondary font-monospace"><?= htmlspecialchars($k['algorithm'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td class="font-monospace small text-muted">
                                        <?= htmlspecialchars(substr((string) ($k['key'] ?? '••••••••'), 0, 10)) ?>••••••••
                                    </td>
                                    <td class="text-end">
                                        <form action="/tsig-keys/<?= urlencode($k['id'] ?? $k['name']) ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Delete this TSIG key?');">
                                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete TSIG Key">
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

<!-- Create TSIG Key Modal -->
<div class="modal fade" id="createTsigModal" tabindex="-1" aria-labelledby="createTsigModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/tsig-keys" method="POST">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTsigModalLabel"><i class="fas fa-key me-2 text-primary"></i>Create TSIG Key</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tsigName" class="form-label">Key Name</label>
                        <input type="text" id="tsigName" name="name" class="form-control font-monospace" placeholder="e.g. transfer-slave.example.com." required>
                        <div class="form-text">FQDN identifying the key. Must match secondary server configuration.</div>
                    </div>
                    <div class="mb-3">
                        <label for="tsigAlgo" class="form-label">Algorithm</label>
                        <select id="tsigAlgo" name="algorithm" class="form-select font-monospace">
                            <?php foreach ($algorithms as $algo): ?>
                                <option value="<?= htmlspecialchars($algo, ENT_QUOTES, 'UTF-8') ?>" <?= $algo === 'hmac-sha256' ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($algo, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tsigSecret" class="form-label">Secret Key (Base64)</label>
                        <div class="input-group">
                            <input type="text" id="tsigSecret" name="secret" class="form-control font-monospace" placeholder="Leave empty to auto-generate 256-bit key">
                            <button class="btn btn-outline-secondary" type="button" id="btnGenSecret">Generate</button>
                        </div>
                        <div class="form-text">If left empty, a cryptographically secure 256-bit random key will be generated.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Create Key</button>
                </div>
            </form>
        </div>
    </div>
</div>
