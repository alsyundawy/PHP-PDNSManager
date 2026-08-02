<?php $title = 'Edit Zone'; ?>
<div class="card"><div class="card-header"><h5>Edit Zone</h5></div><div class="card-body">
<form method="POST"><input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
    <div class="mb-3"><label>Name</label><input type="text" class="form-control" value="<?= htmlspecialchars($zone['name']) ?>" disabled></div>
    <div class="mb-3"><label>Kind</label><select name="kind" class="form-select"><option value="Native" <?= ($zone['kind']??'Native')==='Native'?'selected':'' ?>>Native</option><option value="Master" <?= ($zone['kind']??'')==='Master'?'selected':'' ?>>Master</option><option value="Slave" <?= ($zone['kind']??'')==='Slave'?'selected':'' ?>>Slave</option></select></div>
    <div class="mb-3"><label>Masters</label><input type="text" name="masters" class="form-control" value="<?= htmlspecialchars(implode(',', $zone['masters']??[])) ?>"></div>
    <div class="mb-3"><label>Nameservers</label><input type="text" name="nameservers" class="form-control" value="<?= htmlspecialchars(implode(',', $zone['nameservers']??[])) ?>"></div>
    <button type="submit" class="btn btn-primary">Update</button><a href="/zones" class="btn btn-secondary">Cancel</a>
</form></div></div>
