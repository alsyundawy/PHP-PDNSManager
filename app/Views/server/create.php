<?php
declare(strict_types=1);

/**
 * @var string|null $csrfToken
 */
$viewVars = get_defined_vars();
$csrfToken = $viewVars['csrfToken'] ?? null;
if ($csrfToken === null && function_exists('csrf_token')) {
    $csrfToken = (string) csrf_token();
}
$csrfToken = (string) ($csrfToken ?? '');
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Add PowerDNS Server</h1>
        <p class="text-muted small mb-0">Register a new PowerDNS Authoritative server node</p>
    </div>
    <div>
        <a href="/servers" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1" aria-hidden="true"></i> Back to Servers
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form method="POST" action="/servers/create">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

                    <div class="mb-3">
                        <label for="serverName" class="form-label fw-semibold">Server Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="serverName" name="name" placeholder="e.g. EU-Cluster-01" required>
                    </div>

                    <div class="mb-3">
                        <label for="apiUrl" class="form-label fw-semibold">PowerDNS API URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="apiUrl" name="api_url" placeholder="https://10.0.0.1:8081" required>
                        <div class="form-text">Base HTTP/HTTPS URL where PowerDNS API is listening.</div>
                    </div>

                    <div class="mb-3">
                        <label for="apiKey" class="form-label fw-semibold">API Key / Secret <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="apiKey" name="api_key" placeholder="PowerDNS API Key (api-key directive)" required autocomplete="off">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="serverId" class="form-label fw-semibold">Server ID</label>
                            <!-- DevSkim: ignore DS137138 - PowerDNS API default server ID is 'localhost' -->
                            <input type="text" class="form-control" id="serverId" name="server_id" value="localhost">
                            <div class="form-text">Default server ID in PowerDNS API is usually <code>localhost</code>.</div>
                        </div>
                        <div class="col-md-6 mb-3 d-flex flex-column justify-content-center">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="isActive" name="is_active" value="1" checked>
                                <label class="form-check-label" for="isActive">Enable Server Node</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isDefault" name="is_default" value="1">
                                <label class="form-check-label" for="isDefault">Set as Default Server Node</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="/servers" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1" aria-hidden="true"></i> Save Server Node
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
