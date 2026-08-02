<?php $title = 'Clone Zone'; ?>
<div class="card"><div class="card-header"><h5>Clone Zone</h5></div><div class="card-body">
<form method="POST"><input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
    <div class="mb-3"><label>Source Zone</label><input type="text" class="form-control" value="<?= htmlspecialchars($zone['name']) ?>" disabled></div>
    <div class="mb-3"><label>New Zone Name</label><input type="text" name="new_name" class="form-control" required></div>
    <button type="submit" class="btn btn-primary">Clone</button><a href="/zones" class="btn btn-secondary">Cancel</a>
</form></div></div>
