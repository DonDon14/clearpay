<?= $this->extend('layouts/payer-layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid payer-showcase">
    <div class="payer-hero mb-4">
        <div>
            <p class="payer-eyebrow">Section-Wide Fees</p>
            <h2 class="payer-heading">Contributions</h2>
            <p class="payer-copy">Track your running balance per contribution and submit payments without the old group-payment clutter.</p>
        </div>
        <div class="payer-toolbar">
            <div class="input-group payer-search">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="contributionSearch" placeholder="Search contributions...">
            </div>
        </div>
    </div>

    <?php if (empty($contributions)): ?>
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Contributions Available</h5>
                <p class="text-muted mb-0">Active section contributions will appear here.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4" id="contributionsGrid">
            <?php foreach ($contributions as $contribution): ?>
                <div class="col-xl-6 contribution-item" data-title="<?= strtolower(esc($contribution['title'])) ?>">
                    <div class="card border-0 shadow-sm h-100 contribution-panel">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex gap-3 align-items-start flex-wrap">
                                <div class="contribution-visual">
                                    <?php if (!empty($contribution['image_path'])): ?>
                                        <?php
                                            $contributionImagePath = (string) $contribution['image_path'];
                                            $contributionImageUrl = preg_match('#^https?://#i', $contributionImagePath)
                                                ? $contributionImagePath
                                                : base_url($contributionImagePath);
                                        ?>
                                        <img
                                            src="<?= esc($contributionImageUrl) ?>"
                                            alt="<?= esc($contribution['title']) ?>"
                                            class="payer-item-image"
                                            onerror="this.onerror=null; this.style.display='none'; this.parentElement.insertAdjacentHTML('beforeend', '<div class=&quot;payer-item-image payer-item-image--placeholder&quot;><i class=&quot;fas fa-file-invoice-dollar&quot;></i></div>');"
                                            onclick="openPayerImagePreview('<?= esc($contributionImageUrl) ?>', '<?= esc($contribution['title']) ?>')">
                                    <?php else: ?>
                                        <div class="payer-item-image payer-item-image--placeholder">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between gap-3 flex-wrap">
                                        <div>
                                            <h5 class="mb-1"><?= esc($contribution['title']) ?></h5>
                                            <div class="d-flex gap-2 flex-wrap mb-2">
                                                <span class="badge bg-info">Contribution</span>
                                                <?php if (!empty($contribution['category'])): ?>
                                                    <span class="badge bg-light text-dark border"><?= esc(ucfirst($contribution['category'])) ?></span>
                                                <?php endif; ?>
                                                <span class="badge <?= ($contribution['status'] ?? 'active') === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= ucfirst($contribution['status'] ?? 'active') ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="metric-label">Per Payer</div>
                                            <div class="metric-value">PHP <?= number_format((float)($contribution['amount'] ?? 0), 2) ?></div>
                                        </div>
                                    </div>
                                    <p class="text-muted mb-3"><?= esc($contribution['description'] ?: 'No description provided.') ?></p>
                                </div>
                            </div>

                            <?php
                                $paid = (float)($contribution['total_paid'] ?? 0);
                                $amount = (float)($contribution['amount'] ?? 0);
                                $remaining = (float)($contribution['remaining_balance'] ?? max(0, $amount - $paid));
                                $progress = $amount > 0 ? min(100, round(($paid / $amount) * 100, 1)) : 0;
                            ?>

                            <div class="payer-stats-grid mt-3">
                                <div class="payer-stat-card">
                                    <span class="metric-label">Paid</span>
                                    <strong>PHP <?= number_format($paid, 2) ?></strong>
                                </div>
                                <div class="payer-stat-card">
                                    <span class="metric-label">Remaining</span>
                                    <strong>PHP <?= number_format($remaining, 2) ?></strong>
                                </div>
                                <div class="payer-stat-card">
                                    <span class="metric-label">Progress</span>
                                    <strong><?= number_format($progress, 1) ?>%</strong>
                                </div>
                            </div>

                            <div class="progress mt-3 payer-progress" role="progressbar" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar <?= $progress >= 100 ? 'bg-success' : 'bg-primary' ?>" style="width: <?= $progress ?>%"></div>
                            </div>

                            <div class="mt-4 d-flex gap-2 flex-wrap">
                                <?php if (($contribution['status'] ?? 'active') === 'active' && $remaining > 0): ?>
                                    <button
                                        class="btn btn-primary"
                                        onclick='openPaymentRequestModal(<?= json_encode([
                                            'id' => (int)$contribution['id'],
                                            'title' => $contribution['title'],
                                            'description' => $contribution['description'],
                                            'amount' => (float)$contribution['amount'],
                                            'remaining_balance' => $remaining,
                                            'item_type' => 'contribution',
                                            'image_path' => !empty($contribution['image_path']) ? $contributionImageUrl : null,
                                        ]) ?>)'>
                                        <i class="fas fa-paper-plane me-2"></i>Submit Payment
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-outline-secondary" disabled>
                                        <i class="fas fa-check-circle me-2"></i><?= $remaining <= 0 ? 'Fully Paid' : 'Unavailable' ?>
                                    </button>
                                <?php endif; ?>
                                <a class="btn btn-outline-dark" href="<?= base_url('payer/payment-history') ?>">
                                    <i class="fas fa-history me-2"></i>Payment History
                                </a>
                                <a class="btn btn-outline-danger" href="<?= base_url('payer/refund-requests') ?>">
                                    <i class="fas fa-undo me-2"></i>Refunds
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?= $this->include('partials/modal-payment-request') ?>

<div class="modal fade" id="payerItemImagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="payerItemImagePreviewTitle">Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" alt="" id="payerItemImagePreviewImage" class="img-fluid rounded-4">
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('contributionSearch');
    if (!searchInput) {
        return;
    }

    searchInput.addEventListener('input', function() {
        const term = this.value.toLowerCase().trim();
        document.querySelectorAll('.contribution-item').forEach(item => {
            const title = item.getAttribute('data-title') || '';
            item.style.display = term === '' || title.includes(term) ? '' : 'none';
        });
    });
});

function openPayerImagePreview(src, title) {
    document.getElementById('payerItemImagePreviewTitle').textContent = title || 'Item Image';
    document.getElementById('payerItemImagePreviewImage').src = src;
    new bootstrap.Modal(document.getElementById('payerItemImagePreviewModal')).show();
}
</script>

<style>
.payer-showcase {
    padding-bottom: 2rem;
}

.payer-hero {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: end;
    flex-wrap: wrap;
    padding: 1.75rem;
    border-radius: 1.5rem;
    background: linear-gradient(135deg, #eff6ff 0%, #ffffff 55%, #ecfeff 100%);
    border: 1px solid rgba(14, 116, 144, 0.12);
}

.payer-eyebrow {
    margin: 0 0 .35rem;
    text-transform: uppercase;
    letter-spacing: .14em;
    font-size: .72rem;
    color: #0f766e;
}

.payer-heading {
    margin: 0 0 .35rem;
    font-size: 2rem;
    font-weight: 700;
}

.payer-copy {
    margin: 0;
    max-width: 44rem;
    color: #475569;
}

.payer-search {
    min-width: 280px;
}

.contribution-panel {
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    border: 1px solid rgba(37, 99, 235, 0.08);
}

.payer-item-image {
    width: 112px;
    height: 112px;
    border-radius: 1rem;
    object-fit: cover;
    cursor: pointer;
    box-shadow: 0 20px 36px rgba(15, 23, 42, 0.12);
}

.payer-item-image--placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #dbeafe, #e0f2fe);
    color: #2563eb;
    font-size: 2rem;
}

.payer-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .75rem;
}

.payer-stat-card {
    padding: .85rem 1rem;
    border-radius: 1rem;
    background: #f8fafc;
    border: 1px solid rgba(148, 163, 184, 0.18);
}

.metric-label {
    display: block;
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #64748b;
}

.metric-value {
    font-size: 1.3rem;
    font-weight: 700;
}

.payer-progress {
    height: 10px;
    border-radius: 999px;
    background: #e2e8f0;
}

@media (max-width: 767px) {
    .payer-stats-grid {
        grid-template-columns: 1fr;
    }

    .payer-item-image {
        width: 100%;
        max-width: 180px;
        height: 140px;
    }
}
</style>
<?= $this->endSection() ?>
