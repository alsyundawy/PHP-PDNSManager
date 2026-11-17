<?php
/**
 * @var string|null $csrfToken
 * @var array{name?: string}|null $zone
 */
$csrfToken = $csrfToken ?? '';
$zone = $zone ?? ['name' => ''];
$title = 'Clone Zone';
?>
<div class="card">
    <div class="card-header">
        <h5>Clone Zone</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <div class="mb-3">
                <label for="sourceZoneName" class="form-label">Source Zone</label>
                <input type="text" id="sourceZoneName" class="form-control" value="<?= htmlspecialchars((string) ($zone['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" disabled>
            </div>
            <div class="mb-3">
                <label for="newZoneName" class="form-label">New Zone Name</label>
                <input type="text" id="newZoneName" name="new_name" class="form-control" required placeholder="example.net">
            </div>
            <button type="submit" class="btn btn-primary">Clone</button>
            <a href="/zones" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
