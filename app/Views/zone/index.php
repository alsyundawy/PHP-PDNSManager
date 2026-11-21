<?php
declare(strict_types=1);

/**
 * PHP-PDNSManager Enterprise Edition — Modernized Zones List View
 *
 * @var array<int, array<string, mixed>> $zones
 * @var string $csrfToken
 */
$zones = $zones ?? [];
$csrfToken = $csrfToken ?? (function_exists('csrf_token') ? csrf_token() : '');
$title = __('zones_title');
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold"><?= htmlspecialchars(__('zones_title'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted small mb-0"><?= htmlspecialchars(__('app_subtitle'), ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="/zones/import" class="btn btn-outline-secondary">
            <i class="fas fa-file-import me-1" aria-hidden="true"></i> <?= htmlspecialchars(__('import_bind'), ENT_QUOTES, 'UTF-8') ?>
        </a>
        <a href="/zones/bulk-records" class="btn btn-outline-secondary">
            <i class="fas fa-layer-group me-1" aria-hidden="true"></i> <?= htmlspecialchars(__('nav_bulk_records'), ENT_QUOTES, 'UTF-8') ?>
        </a>
        <a href="/zones/create" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-1" aria-hidden="true"></i> <?= htmlspecialchars(__('add_zone'), ENT_QUOTES, 'UTF-8') ?>
        </a>
    </div>
</div>

<!-- Filters Card -->
<div class="card card-glass shadow-sm border-0 mb-4 p-3">
    <form method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
            <label for="filter-zone-name" class="visually-hidden"><?= htmlspecialchars(__('zone_name'), ENT_QUOTES, 'UTF-8') ?></label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fas fa-search" aria-hidden="true"></i></span>
                <input type="text"
                       id="filter-zone-name"
                       name="name"
                       class="form-control border-start-0"
                       placeholder="<?= htmlspecialchars(__('search'), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars(__('zone_name'), ENT_QUOTES, 'UTF-8') ?>..."
                       value="<?= htmlspecialchars($_GET['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <label for="filter-zone-type" class="visually-hidden"><?= htmlspecialchars(__('zone_kind'), ENT_QUOTES, 'UTF-8') ?></label>
            <select id="filter-zone-type" name="type" class="form-select form-select-sm">
                <option value=""><?= htmlspecialchars(__('zone_kind'), ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars(__('options'), ENT_QUOTES, 'UTF-8') ?> (All)</option>
                <option value="Native" <?= ($_GET['type'] ?? '') === 'Native' ? 'selected' : '' ?>><?= htmlspecialchars(__('zone_native'), ENT_QUOTES, 'UTF-8') ?></option>
                <option value="Master" <?= ($_GET['type'] ?? '') === 'Master' ? 'selected' : '' ?>><?= htmlspecialchars(__('zone_master'), ENT_QUOTES, 'UTF-8') ?></option>
                <option value="Slave" <?= ($_GET['type'] ?? '') === 'Slave' ? 'selected' : '' ?>><?= htmlspecialchars(__('zone_slave'), ENT_QUOTES, 'UTF-8') ?></option>
                <option value="Producer" <?= ($_GET['type'] ?? '') === 'Producer' ? 'selected' : '' ?>><?= htmlspecialchars(__('zone_producer'), ENT_QUOTES, 'UTF-8') ?></option>
                <option value="Consumer" <?= ($_GET['type'] ?? '') === 'Consumer' ? 'selected' : '' ?>><?= htmlspecialchars(__('zone_consumer'), ENT_QUOTES, 'UTF-8') ?></option>
            </select>
        </div>
        <div class="col-12 col-sm-6 col-md-2">
            <button type="submit" class="btn btn-sm btn-secondary w-100">
                <i class="fas fa-filter me-1" aria-hidden="true"></i> <?= htmlspecialchars(__('filter'), ENT_QUOTES, 'UTF-8') ?>
            </button>
        </div>
    </form>
</div>

<!-- Zones Table Card -->
<div class="card card-glass shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th scope="col" class="ps-3"><?= htmlspecialchars(__('zone_name'), ENT_QUOTES, 'UTF-8') ?></th>
                    <th scope="col"><?= htmlspecialchars(__('zone_kind'), ENT_QUOTES, 'UTF-8') ?></th>
                    <th scope="col">DNSSEC</th>
                    <th scope="col"><?= htmlspecialchars(__('serial'), ENT_QUOTES, 'UTF-8') ?></th>
                    <th scope="col" class="text-end pe-3"><?= htmlspecialchars(__('actions'), ENT_QUOTES, 'UTF-8') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($zones as $zone): ?>
                    <tr>
                        <td class="ps-3 fw-semibold">
                            <a href="/zones/<?= urlencode((string) $zone['name']) ?>" class="text-decoration-none">
                                <i class="fas fa-globe me-2 text-primary" aria-hidden="true"></i>
                                <?= htmlspecialchars((string) $zone['name'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                <?= htmlspecialchars((string) ($zone['kind'] ?? 'Native'), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($zone['dnssec'])): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="fas fa-shield-alt me-1" aria-hidden="true"></i> <?= htmlspecialchars(__('dnssec_enabled'), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-muted">
                                    <?= htmlspecialchars(__('dnssec_disabled'), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="font-monospace small text-muted">
                                <?= htmlspecialchars((string) ($zone['serial'] ?? count($zone['records'] ?? [])), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="/zones/<?= urlencode((string) $zone['name']) ?>" class="btn btn-outline-info" title="<?= htmlspecialchars(__('details'), ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </a>
                                <a href="/zones/<?= urlencode((string) $zone['name']) ?>/edit" class="btn btn-outline-warning" title="<?= htmlspecialchars(__('edit'), ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="fas fa-edit" aria-hidden="true"></i>
                                </a>
                                <a href="/zones/<?= urlencode((string) $zone['name']) ?>/clone" class="btn btn-outline-secondary" title="<?= htmlspecialchars(__('clone_zone'), ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="fas fa-copy" aria-hidden="true"></i>
                                </a>
                                <form method="POST" action="/zones/<?= urlencode((string) $zone['name']) ?>/delete" class="d-inline" onsubmit="return confirm('<?= htmlspecialchars(__('confirm'), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars(__('delete'), ENT_QUOTES, 'UTF-8') ?>?')">
                                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="btn btn-outline-danger" title="<?= htmlspecialchars(__('delete'), ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="fas fa-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($zones)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="fas fa-folder-open fa-2x mb-2 d-block opacity-50" aria-hidden="true"></i>
                            No zones found matching filter criteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
