<?php
/**
 * @var string|null $csrfToken
 * @var array{name?: string, kind?: string, masters?: array<string>, nameservers?: array<string>, catalog?: string}|null $zone
 */
$csrfToken = $csrfToken ?? '';
$zone = $zone ?? ['name' => '', 'kind' => 'Native', 'masters' => [], 'nameservers' => [], 'catalog' => ''];
$title = 'Edit Zone';
?>
<div class="card">
    <div class="card-header">
        <h5>Edit Zone</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <div class="mb-3">
                <label for="zoneName" class="form-label">Name</label>
                <input type="text" id="zoneName" class="form-control" value="<?= htmlspecialchars((string) ($zone['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" disabled>
            </div>
            <div class="mb-3">
                <label for="zoneKind" class="form-label">Kind</label>
                <select id="zoneKind" name="kind" class="form-select">
                    <option value="Native" <?= ($zone['kind'] ?? 'Native') === 'Native' ? 'selected' : '' ?>>Native</option>
                    <option value="Master" <?= ($zone['kind'] ?? '') === 'Master' ? 'selected' : '' ?>>Master</option>
                    <option value="Slave" <?= ($zone['kind'] ?? '') === 'Slave' ? 'selected' : '' ?>>Slave</option>
                    <option value="Producer" <?= ($zone['kind'] ?? '') === 'Producer' ? 'selected' : '' ?>>Producer (RFC 9432 Catalog)</option>
                    <option value="Consumer" <?= ($zone['kind'] ?? '') === 'Consumer' ? 'selected' : '' ?>>Consumer (RFC 9432 Catalog)</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="zoneCatalog" class="form-label">Catalog Zone (Optional, RFC 9432)</label>
                <input type="text" id="zoneCatalog" name="catalog" class="form-control" value="<?= htmlspecialchars((string) ($zone['catalog'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="catalog.example.com">
            </div>
            <div class="mb-3">
                <label for="zoneMasters" class="form-label">Masters</label>
                <input type="text" id="zoneMasters" name="masters" class="form-control" value="<?= htmlspecialchars(implode(',', $zone['masters'] ?? []), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="mb-3">
                <label for="zoneNameservers" class="form-label">Nameservers</label>
                <input type="text" id="zoneNameservers" name="nameservers" class="form-control" value="<?= htmlspecialchars(implode(',', $zone['nameservers'] ?? []), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="/zones" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
