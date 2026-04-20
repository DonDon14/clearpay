<?= $this->extend('layouts/payer-layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid ui-page-shell payer-page-shell">
    <?= view('partials/payer-page-intro', [
        'title' => 'Products',
        'subtitle' => 'Buy uniforms, lanyards, and other optional items with quantity-based requests.',
        'actionsHtml' => '
            <div class="input-group payer-search">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="productSearch" placeholder="Search products...">
            </div>
        ',
    ]) ?>

    <?php if (empty($products)): ?>
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Products Available</h5>
                <p class="text-muted mb-0">Products available for purchase will appear here.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4" id="productsGrid">
            <?php foreach ($products as $product): ?>
                <div class="col-xl-6 product-item" data-title="<?= strtolower(esc($product['title'])) ?>">
                    <div class="card border-0 shadow-sm h-100 payer-grid-card">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex gap-3 align-items-start flex-wrap">
                                <div class="contribution-visual">
                                    <?php if (!empty($product['image_path'])): ?>
                                        <?php
                                            $productImagePath = (string) $product['image_path'];
                                            $productImageUrl = preg_match('#^https?://#i', $productImagePath)
                                                ? $productImagePath
                                                : base_url($productImagePath);
                                        ?>
                                        <img
                                            src="<?= esc($productImageUrl) ?>"
                                            alt="<?= esc($product['title']) ?>"
                                            class="payer-item-image"
                                            onerror="this.onerror=null; this.style.display='none'; this.parentElement.insertAdjacentHTML('beforeend', '<div class=&quot;payer-item-image payer-item-image--placeholder&quot;><i class=&quot;fas fa-box-open&quot;></i></div>');"
                                            onclick="openPayerImagePreview('<?= esc($productImageUrl) ?>', '<?= esc($product['title']) ?>')">
                                    <?php else: ?>
                                        <div class="payer-item-image payer-item-image--placeholder">
                                            <i class="fas fa-box-open"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between gap-3 flex-wrap">
                                        <div>
                                            <h5 class="mb-1"><?= esc($product['title']) ?></h5>
                                            <div class="d-flex gap-2 flex-wrap mb-2">
                                                <?php if (!empty($product['category'])): ?>
                                                    <span class="badge bg-light text-dark border"><?= esc($product['category']) ?></span>
                                                <?php endif; ?>
                                                <span class="badge <?= ($product['status'] ?? 'active') === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= ucfirst($product['status'] ?? 'active') ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="payer-metric-label">Unit Price</div>
                                            <div class="payer-metric-value">PHP <?= number_format((float)($product['amount'] ?? 0), 2) ?></div>
                                        </div>
                                    </div>
                                    <p class="text-muted mb-3"><?= esc($product['description'] ?: 'No description provided.') ?></p>
                                </div>
                            </div>

                            <div class="payer-stats-grid mt-3">
                                <div class="payer-stat-card">
                                    <span class="payer-metric-label">Units Bought</span>
                                    <strong><?= number_format((int)($product['total_quantity'] ?? 0)) ?></strong>
                                </div>
                                <div class="payer-stat-card">
                                    <span class="payer-metric-label">Total Spent</span>
                                    <strong>PHP <?= number_format((float)($product['total_paid'] ?? 0), 2) ?></strong>
                                </div>
                                <div class="payer-stat-card">
                                    <span class="payer-metric-label">Availability</span>
                                    <strong><?= ucfirst($product['status'] ?? 'active') ?></strong>
                                </div>
                            </div>

                            <div class="mt-4 d-flex gap-2 flex-wrap">
                                <?php if (($product['status'] ?? 'active') === 'active'): ?>
                                    <button
                                        class="btn btn-primary"
                                        onclick='openPaymentRequestModal(<?= json_encode([
                                            'id' => (int)$product['id'],
                                            'title' => $product['title'],
                                            'description' => $product['description'],
                                            'amount' => (float)$product['amount'],
                                            'remaining_balance' => (float)($product['amount'] ?? 0),
                                            'item_type' => 'product',
                                            'image_path' => !empty($product['image_path']) ? $productImageUrl : null,
                                        ]) ?>)'>
                                        <i class="fas fa-shopping-bag me-2"></i>Buy Product
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-outline-secondary" disabled>
                                        <i class="fas fa-ban me-2"></i>Unavailable
                                    </button>
                                <?php endif; ?>
                                <a class="btn btn-outline-dark" href="<?= base_url('payer/payment-history') ?>">
                                    <i class="fas fa-history me-2"></i>Purchase History
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
    const searchInput = document.getElementById('productSearch');
    if (!searchInput) {
        return;
    }

    searchInput.addEventListener('input', function() {
        const term = this.value.toLowerCase().trim();
        document.querySelectorAll('.product-item').forEach(item => {
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

<?= $this->endSection() ?>
