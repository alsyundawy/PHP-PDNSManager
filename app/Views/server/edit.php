<?php /** @var \App\Models\PdnsServer $server */ ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Edit PowerDNS Server</h1>
        <p class="text-muted small mb-0">Update settings for server node: <?= htmlspecialchars($server->name, ENT_QUOTES, 'UTF-8') ?></p>
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
                <form method="POST" action="/servers/<?= $server->id ?>/edit">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

                    <div class="mb-3">
                        <label for="serverName" class="form-label fw-semibold">Server Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="serverName" name="name" value="<?= htmlspecialchars($server->name, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="apiUrl" class="form-label fw-semibold">PowerDNS API URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="apiUrl" name="api_url" value="<?= htmlspecialchars($server->apiUrl, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="apiKey" class="form-label fw-semibold">API Key / Secret</label>
                        <input type="password" class="form-control" id="apiKey" name="api_key" placeholder="Leave empty to keep existing API key" autocomplete="off">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="serverId" class="form-label fw-semibold">Server ID</label>
                            <input type="text" class="form-control" id="serverId" name="server_id" value="<?= htmlspecialchars($server->serverId, ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-6 mb-3 d-flex flex-column justify-content-center">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="isActive" name="is_active" value="1" <?= $server->isActive ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isActive">Enable Server Node</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isDefault" name="is_default" value="1" <?= $server->isDefault ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isDefault">Set as Default Server Node</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="/servers" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1" aria-hidden="true"></i> Update Server Node
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
