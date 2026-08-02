<?php $title = 'Create Zone'; ?>
<div class="card"><div class="card-header"><h5>Create New Zone</h5></div><div class="card-body">
<?php if (isset($error)): ?><div class="alert alert-danger"><?= htmlspecialchars(json_encode($error)) ?></div><?php endif; ?>
<form method="POST"><input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
    <div class="mb-3"><label for="name" class="form-label">Zone Name</label><input type="text" class="form-control" id="name" name="name" required placeholder="example.com"></div>
    <div class="mb-3"><label for="kind" class="form-label">Kind</label><select class="form-select" id="kind" name="kind"><option value="Native">Native</option><option value="Master">Master</option><option value="Slave">Slave</option></select></div>
    <div class="mb-3"><label for="masters" class="form-label">Masters (comma separated IPs)</label><input type="text" class="form-control" id="masters" name="masters" placeholder="192.168.1.1"></div>
    <div class="mb-3"><label for="nameservers" class="form-label">Nameservers (comma separated)</label><input type="text" class="form-control" id="nameservers" name="nameservers" placeholder="ns1.example.com"></div>
    <div class="form-check mb-3"><input type="checkbox" class="form-check-input" id="dnssec" name="dnssec" value="1"><label class="form-check-label" for="dnssec">Enable DNSSEC</label></div>
    <button type="submit" class="btn btn-primary">Create Zone</button><a href="/zones" class="btn btn-secondary">Cancel</a>
</form></div></div>
