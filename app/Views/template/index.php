<?php
/** @var \App\Models\ZoneTemplate[] $templates */
/** @var array $zones */
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Zone Record Templates</h1>
        <p class="text-muted small mb-0">Pre-configured DNS record sets for instant domain provisioning</p>
    </div>
    <div>
        <a href="/templates/create" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-1" aria-hidden="true"></i> Create Template
        </a>
    </div>
</div>

<!-- Quick Apply Box -->
<div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body py-3">
        <form method="POST" action="/templates/apply" class="row g-3 align-items-end" onsubmit="return confirm('Apply this template to the selected zone? Existing records with the same name and type may be overwritten.');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div class="col-md-5">
                <label for="applyTemplateId" class="form-label fw-semibold small mb-1">Select Template</label>
                <select class="form-select" id="applyTemplateId" name="template_id" required>
                    <option value="">-- Choose DNS Template --</option>
                    <?php foreach ($templates as $t): ?>
                        <option value="<?= $t->id ?>"><?= htmlspecialchars($t->name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label for="applyZoneName" class="form-label fw-semibold small mb-1">Target Zone</label>
                <select class="form-select" id="applyZoneName" name="zone_name" required>
                    <option value="">-- Select Destination Zone --</option>
                    <?php foreach ($zones as $z): ?>
                        <option value="<?= htmlspecialchars($z['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($z['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-bolt me-1" aria-hidden="true"></i> Apply
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Templates List -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-transparent py-3">
        <h6 class="m-0 font-weight-bold text-primary">Available DNS Templates</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 50px;">#</th>
                        <th scope="col">Template Name</th>
                        <th scope="col">Description</th>
                        <th scope="col">Records</th>
                        <th scope="col">Default</th>
                        <th scope="col" class="text-end" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($templates)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No DNS templates defined yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($templates as $idx => $t): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td class="fw-bold">
                                    <i class="fas fa-file-invoice text-primary me-2" aria-hidden="true"></i>
                                    <?= htmlspecialchars($t->name, ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td><?= htmlspecialchars($t->description ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary">
                                        <?= count($t->records) ?> Records
                                    </span>
                                </td>
                                <td>
                                    <?php if ($t->is_default): ?>
                                        <span class="badge bg-primary">Default</span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="/templates/<?= $t->id ?>/delete" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this template?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Template">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
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
