<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Import BIND Zone File</h1>
        <p class="text-muted small mb-0">Migrate existing DNS zones from BIND 9 format (RFC 1035)</p>
    </div>
    <div>
        <a href="/zones" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1" aria-hidden="true"></i> Back to Zones
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2" aria-hidden="true"></i>
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form method="POST" action="/zones/import" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <div class="mb-3">
                <label for="zoneOrigin" class="form-label fw-semibold">Zone Origin (Domain Name)</label>
                <input type="text" class="form-control" id="zoneOrigin" name="origin" placeholder="e.g. example.com. (Optional if $ORIGIN is defined in file)">
                <div class="form-text">If not specified here, the parser will use the <code>$ORIGIN</code> directive or SOA record from the file.</div>
            </div>

            <div class="mb-3">
                <label for="zoneFile" class="form-label fw-semibold">Upload BIND Zone File (.db, .zone, .txt)</label>
                <input type="file" class="form-control" id="zoneFile" accept=".db,.zone,.txt" onchange="readFileContent(this)">
            </div>

            <div class="mb-3">
                <label for="zoneContent" class="form-label fw-semibold">BIND Zone Content <span class="text-danger">*</span></label>
                <textarea class="form-control font-monospace small" id="zoneContent" name="zone_content" rows="12" placeholder="$ORIGIN example.com.
$TTL 3600
@   IN  SOA ns1.example.com. hostmaster.example.com. ( 2026092101 10800 3600 604800 3600 )
@   IN  NS  ns1.example.com.
@   IN  A   192.0.2.1
www IN  CNAME @" required></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="/zones" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-file-import me-1" aria-hidden="true"></i> Parse & Import Zone
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function readFileContent(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('zoneContent').value = e.target.result;
        };
        reader.readAsText(input.files[0]);
    }
}
</script>
