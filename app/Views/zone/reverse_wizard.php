<?php $title = 'Reverse DNS Zone Generator Wizard - PHP-PDNSManager'; ?>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-reset">
                <i class="fas fa-undo-alt text-primary me-2" aria-hidden="true"></i>Reverse DNS Zone Wizard
            </h1>
            <p class="text-muted small mb-0">Generate standard RFC-compliant reverse zones (IPv4 <code>in-addr.arpa</code> and IPv6 <code>ip6.arpa</code>) automatically from CIDR subnet notation.</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger shadow-sm mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm max-w-lg mx-auto" style="max-width: 650px;">
        <div class="card-header bg-transparent fw-semibold py-3">
            <i class="fas fa-magic text-info me-2"></i> Reverse Zone Creation Wizard
        </div>
        <div class="card-body p-4">
            <form action="/zones/reverse-wizard" method="POST">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">IP Protocol Version</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ip_version" id="ipv4Radio" value="4" checked>
                            <label class="form-check-label" for="ipv4Radio">IPv4 (in-addr.arpa)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ip_version" id="ipv6Radio" value="6">
                            <label class="form-check-label" for="ipv6Radio">IPv6 (ip6.arpa)</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="cidrInput" class="form-label fw-semibold">Subnet / Prefix (CIDR Notation)</label>
                    <input type="text" id="cidrInput" name="cidr" class="form-control font-monospace" placeholder="e.g. 192.0.2.0/24 or 2001:db8::/64" required>
                    <div class="form-text">
                        IPv4 supported boundaries: <code>/24</code>, <code>/16</code>, <code>/8</code>.<br>
                        IPv6 supported nibble boundaries: <code>/64</code>, <code>/48</code>, <code>/32</code>.
                    </div>
                </div>

                <div class="mb-4">
                    <label for="nsInput" class="form-label fw-semibold">Nameservers (Optional, comma-separated)</label>
                    <input type="text" id="nsInput" name="nameservers" class="form-control font-monospace" placeholder="ns1.example.com, ns2.example.com">
                    <div class="form-text">If left empty, default authoritative nameservers will be provisioned.</div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="/zones" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus-circle me-1"></i> Provision Reverse Zone</button>
                </div>
            </form>
        </div>
    </div>
</div>
