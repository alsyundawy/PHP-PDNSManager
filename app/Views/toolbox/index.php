<?php $title = 'DNS Security & Diagnostic Toolbox - PHP-PDNSManager'; ?>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-reset">
                <i class="fas fa-toolbox text-primary me-2" aria-hidden="true"></i>DNS Security & Diagnostic Toolbox
            </h1>
            <p class="text-muted small mb-0">Validate and construct RFC-compliant email authentication (SPF, DKIM, DMARC) and check IP reputation across DNS blacklists.</p>
        </div>
    </div>

    <!-- Toolbox Navigation Pills -->
    <ul class="nav nav-pills mb-4 gap-2" id="toolboxTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= ($action ?? 'spf') === 'spf' || ($action ?? '') === 'spf_validate' || ($action ?? '') === 'spf_build' ? 'active' : '' ?>" id="spf-tab" data-bs-toggle="pill" data-bs-target="#spf-panel" type="button" role="tab">
                <i class="fas fa-envelope-open-text me-1"></i> SPF Tools
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= ($action ?? '') === 'dkim_inspect' ? 'active' : '' ?>" id="dkim-tab" data-bs-toggle="pill" data-bs-target="#dkim-panel" type="button" role="tab">
                <i class="fas fa-key me-1"></i> DKIM Inspector
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= ($action ?? '') === 'dmarc_validate' || ($action ?? '') === 'dmarc_build' ? 'active' : '' ?>" id="dmarc-tab" data-bs-toggle="pill" data-bs-target="#dmarc-panel" type="button" role="tab">
                <i class="fas fa-user-shield me-1"></i> DMARC Tools
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= ($action ?? '') === 'rbl_check' ? 'active' : '' ?>" id="rbl-tab" data-bs-toggle="pill" data-bs-target="#rbl-panel" type="button" role="tab">
                <i class="fas fa-ban me-1"></i> RBL Blacklist Check
            </button>
        </li>
    </ul>

    <!-- Results Display if present -->
    <?php if (!empty($result)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3">
                <span class="fw-semibold"><i class="fas fa-poll me-2"></i> Diagnostic Output</span>
                <span class="badge bg-light text-primary"><?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="card-body">
                <?php if (isset($result['built_spf'])): ?>
                    <div class="alert alert-success d-flex justify-content-between align-items-center mb-0">
                        <div>
                            <strong>Generated RFC 7208 SPF Record:</strong>
                            <div class="font-monospace mt-1 p-2 bg-dark text-light rounded"><?= htmlspecialchars($result['built_spf'], ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    </div>
                <?php elseif (isset($result['built_dmarc'])): ?>
                    <div class="alert alert-success d-flex justify-content-between align-items-center mb-0">
                        <div>
                            <strong>Generated RFC 7489 DMARC Record:</strong>
                            <div class="font-monospace mt-1 p-2 bg-dark text-light rounded"><?= htmlspecialchars($result['built_dmarc'], ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    </div>
                <?php elseif (isset($result['providers'])): ?>
                    <h6 class="fw-semibold">RBL Reputation for <?= htmlspecialchars($result['ip'], ENT_QUOTES, 'UTF-8') ?>:</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Blacklist Provider</th>
                                    <th>DNSBL Host</th>
                                    <th>Status</th>
                                    <th>Return Code</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($result['providers'] as $prov): ?>
                                    <tr>
                                        <td class="fw-medium"><?= htmlspecialchars($prov['provider'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="font-monospace small"><?= htmlspecialchars($prov['host'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <?php if ($prov['listed']): ?>
                                                <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i> LISTED</span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> CLEAN</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="font-monospace small"><?= htmlspecialchars($prov['return_code'] ?? 'None', ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="mb-3">
                        <strong>Record Content:</strong>
                        <div class="font-monospace p-2 bg-dark text-light rounded mt-1"><?= htmlspecialchars($result['record'] ?? 'None', ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    <?php if (isset($result['lookup_count'])): ?>
                        <p class="mb-2"><strong>DNS Lookup Count:</strong> <span class="badge <?= $result['lookup_count'] > 10 ? 'bg-danger' : 'bg-info' ?>"><?= $result['lookup_count'] ?> / 10 lookups</span></p>
                    <?php endif; ?>
                    <?php if (!empty($result['warnings'])): ?>
                        <div class="alert alert-warning py-2 mb-2">
                            <strong class="d-block"><i class="fas fa-exclamation-triangle me-1"></i> Warnings:</strong>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($result['warnings'] as $w): ?>
                                    <li><?= htmlspecialchars($w, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($result['errors'])): ?>
                        <div class="alert alert-danger py-2 mb-0">
                            <strong class="d-block"><i class="fas fa-times-circle me-1"></i> Errors:</strong>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($result['errors'] as $err): ?>
                                    <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tab Panels -->
    <div class="tab-content" id="toolboxContent">
        <!-- SPF Panel -->
        <div class="tab-pane fade <?= ($action ?? 'spf') === 'spf' || ($action ?? '') === 'spf_validate' || ($action ?? '') === 'spf_build' ? 'show active' : '' ?>" id="spf-panel" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent fw-semibold"><i class="fas fa-check-double text-success me-2"></i>Validate Existing SPF Record</div>
                        <div class="card-body">
                            <form action="/toolbox" method="POST">
                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="spf_validate">
                                <div class="mb-3">
                                    <label for="spfDomain" class="form-label">Domain Name</label>
                                    <input type="text" id="spfDomain" name="domain" class="form-control" placeholder="e.g. example.com" required>
                                </div>
                                <div class="mb-3">
                                    <label for="spfRecord" class="form-label">SPF String (Optional - leave empty to query live DNS)</label>
                                    <textarea id="spfRecord" name="record" class="form-control font-monospace" rows="3" placeholder="v=spf1 a mx include:_spf.google.com ~all"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Validate SPF</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent fw-semibold"><i class="fas fa-magic text-info me-2"></i>SPF Record Builder Wizard</div>
                        <div class="card-body">
                            <form action="/toolbox" method="POST">
                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="spf_build">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="spfA" name="a" value="1" checked>
                                    <label class="form-check-label" for="spfA">Include Domain 'a' Record</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="spfMx" name="mx" value="1" checked>
                                    <label class="form-check-label" for="spfMx">Include Domain 'mx' Servers</label>
                                </div>
                                <div class="mb-3">
                                    <label for="spfIp4" class="form-label">Authorized IPv4 Addresses/CIDRs (comma separated)</label>
                                    <input type="text" id="spfIp4" name="ip4" class="form-control font-monospace" placeholder="192.0.2.1, 198.51.100.0/24">
                                </div>
                                <div class="mb-3">
                                    <label for="spfInclude" class="form-label">Authorized Third-Party Includes (comma separated)</label>
                                    <input type="text" id="spfInclude" name="include" class="form-control font-monospace" placeholder="_spf.google.com, mailgun.org">
                                </div>
                                <div class="mb-3">
                                    <label for="spfQualifier" class="form-label">Enforcement Policy</label>
                                    <select id="spfQualifier" name="qualifier" class="form-select">
                                        <option value="~all" selected>SoftFail (~all) - Recommended initial policy</option>
                                        <option value="-all">HardFail (-all) - Strict rejection of unauthorized senders</option>
                                        <option value="?all">Neutral (?all) - No policy assertion</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success"><i class="fas fa-hammer me-1"></i> Generate SPF String</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DKIM Panel -->
        <div class="tab-pane fade <?= ($action ?? '') === 'dkim_inspect' ? 'show active' : '' ?>" id="dkim-panel" role="tabpanel">
            <div class="card border-0 shadow-sm max-w-lg mx-auto" style="max-width: 600px;">
                <div class="card-header bg-transparent fw-semibold"><i class="fas fa-search-plus text-primary me-2"></i>DKIM Selector Inspector</div>
                <div class="card-body">
                    <form action="/toolbox" method="POST">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="action" value="dkim_inspect">
                        <div class="mb-3">
                            <label for="dkimDomain" class="form-label">Domain Name</label>
                            <input type="text" id="dkimDomain" name="domain" class="form-control" placeholder="example.com" required>
                        </div>
                        <div class="mb-3">
                            <label for="dkimSelector" class="form-label">DKIM Selector</label>
                            <input type="text" id="dkimSelector" name="selector" class="form-control" placeholder="e.g. google, default, k1, s1" required>
                            <div class="form-text">Will query DNS TXT record at: <code>&lt;selector&gt;._domainkey.&lt;domain&gt;</code></div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-microscope me-1"></i> Query DKIM Key</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- DMARC Panel -->
        <div class="tab-pane fade <?= ($action ?? '') === 'dmarc_validate' || ($action ?? '') === 'dmarc_build' ? 'show active' : '' ?>" id="dmarc-panel" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent fw-semibold"><i class="fas fa-shield-virus text-success me-2"></i>Validate DMARC Record</div>
                        <div class="card-body">
                            <form action="/toolbox" method="POST">
                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="dmarc_validate">
                                <div class="mb-3">
                                    <label for="dmarcDomain" class="form-label">Domain Name</label>
                                    <input type="text" id="dmarcDomain" name="domain" class="form-control" placeholder="example.com" required>
                                </div>
                                <div class="mb-3">
                                    <label for="dmarcRecord" class="form-label">DMARC String (Optional - leave empty to query _dmarc.domain)</label>
                                    <textarea id="dmarcRecord" name="record" class="form-control font-monospace" rows="3" placeholder="v=DMARC1; p=reject; rua=mailto:dmarc@example.com"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle me-1"></i> Validate DMARC</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent fw-semibold"><i class="fas fa-tools text-info me-2"></i>DMARC Policy Builder</div>
                        <div class="card-body">
                            <form action="/toolbox" method="POST">
                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="dmarc_build">
                                <div class="mb-3">
                                    <label for="dmarcPolicy" class="form-label">Domain Policy (p=)</label>
                                    <select id="dmarcPolicy" name="p" class="form-select">
                                        <option value="quarantine" selected>Quarantine (Spam folder)</option>
                                        <option value="reject">Reject (Hard bounce unauthorized email)</option>
                                        <option value="none">None (Monitoring mode only)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="dmarcrua" class="form-label">Aggregate Reports Destination (rua=)</label>
                                    <input type="email" id="dmarcrua" name="rua" class="form-control" placeholder="dmarc-reports@example.com">
                                </div>
                                <div class="mb-3">
                                    <label for="dmarcpct" class="form-label">Percentage of Applied Traffic (pct=)</label>
                                    <input type="number" id="dmarcpct" name="pct" class="form-control" value="100" min="1" max="100">
                                </div>
                                <button type="submit" class="btn btn-success"><i class="fas fa-hammer me-1"></i> Generate DMARC String</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RBL Panel -->
        <div class="tab-pane fade <?= ($action ?? '') === 'rbl_check' ? 'show active' : '' ?>" id="rbl-panel" role="tabpanel">
            <div class="card border-0 shadow-sm max-w-lg mx-auto" style="max-width: 600px;">
                <div class="card-header bg-transparent fw-semibold"><i class="fas fa-ban text-danger me-2"></i>DNS Blacklist (RBL/DNSBL) Multi-Provider Checker</div>
                <div class="card-body">
                    <form action="/toolbox" method="POST">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="action" value="rbl_check">
                        <div class="mb-3">
                            <label for="rblIp" class="form-label">Mail Server IPv4 Address</label>
                            <input type="text" id="rblIp" name="ip" class="form-control font-monospace" placeholder="e.g. 198.51.100.25" required>
                            <div class="form-text">Queries Spamhaus ZEN, Barracuda, and SpamCop in real-time.</div>
                        </div>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-radar me-1"></i> Scan Blacklists</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
