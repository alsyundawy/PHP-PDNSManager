<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Create Zone Template</h1>
        <p class="text-muted small mb-0">Define reusable DNS record blueprints for new zones</p>
    </div>
    <div>
        <a href="/templates" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1" aria-hidden="true"></i> Back to Templates
        </a>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <form method="POST" action="/templates/create" id="templateForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label for="tplName" class="form-label fw-semibold">Template Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="tplName" name="name" placeholder="e.g. Standard Corporate Mail Cluster" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tplDesc" class="form-label fw-semibold">Description</label>
                    <input type="text" class="form-control" id="tplDesc" name="description" placeholder="Short description of this template">
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="isDefault" name="is_default" value="1">
                        <label class="form-check-label" for="isDefault">Set as Default Template for new zones</label>
                    </div>
                </div>
            </div>

            <h5 class="fw-bold mb-3 border-bottom pb-2">DNS Records in Template</h5>
            <p class="text-muted small">Use <code>@</code> for the zone apex, or <code>{zone}</code> to interpolate the zone domain name.</p>

            <div id="recordsContainer">
                <div class="row g-2 mb-2 align-items-center record-row">
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="rec_name[]" value="@" placeholder="Name (@)" required>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="rec_type[]">
                            <option value="A">A</option>
                            <option value="AAAA">AAAA</option>
                            <option value="CNAME">CNAME</option>
                            <option value="MX">MX</option>
                            <option value="TXT">TXT</option>
                            <option value="NS">NS</option>
                            <option value="SRV">SRV</option>
                            <option value="CAA">CAA</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="rec_content[]" placeholder="Content / Value (e.g. 192.0.2.1)" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control" name="rec_ttl[]" value="3600" placeholder="TTL">
                    </div>
                    <div class="col-md-1">
                        <input type="number" class="form-control" name="rec_priority[]" placeholder="Prio">
                    </div>
                    <div class="col-md-1 text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn" title="Remove Record">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addRowBtn">
                <i class="fas fa-plus me-1" aria-hidden="true"></i> Add Another Record
            </button>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="/templates" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1" aria-hidden="true"></i> Save Template
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('recordsContainer');
    const addBtn = document.getElementById('addRowBtn');

    addBtn.addEventListener('click', function() {
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 align-items-center record-row';
        row.innerHTML = `
            <div class="col-md-2">
                <input type="text" class="form-control" name="rec_name[]" value="" placeholder="Name (e.g. www)" required>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="rec_type[]">
                    <option value="A">A</option>
                    <option value="AAAA">AAAA</option>
                    <option value="CNAME">CNAME</option>
                    <option value="MX">MX</option>
                    <option value="TXT">TXT</option>
                    <option value="NS">NS</option>
                    <option value="SRV">SRV</option>
                    <option value="CAA">CAA</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" name="rec_content[]" placeholder="Content" required>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="rec_ttl[]" value="3600" placeholder="TTL">
            </div>
            <div class="col-md-1">
                <input type="number" class="form-control" name="rec_priority[]" placeholder="Prio">
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn" title="Remove Record">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
    });

    container.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row-btn')) {
            const rows = container.querySelectorAll('.record-row');
            if (rows.length > 1) {
                e.target.closest('.record-row').remove();
            }
        }
    });
});
</script>
