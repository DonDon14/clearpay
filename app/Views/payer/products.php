<?= $this->extend('layouts/payer-layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-box-open me-2"></i>Products</h5>
                    <div class="input-group" style="width: 300px;">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="productSearch" placeholder="Search products...">
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($products)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Products Available</h5>
                            <p class="text-muted">There are currently no products available for payment.</p>
                        </div>
                    <?php else: ?>
                        <div class="row" id="productsGrid">
                            <?php foreach ($products as $product): ?>
                                <div class="col-lg-6 mb-3 product-item" data-title="<?= strtolower(esc($product['title'])) ?>">
                                    <div class="card h-100 border">
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h6 class="card-title mb-1">
                                                        <i class="fas fa-box-open text-primary me-2"></i><?= esc($product['title']) ?>
                                                    </h6>
                                                    <?php if (!empty($product['category'])): ?>
                                                        <span class="badge bg-info"><?= esc($product['category']) ?></span>
                                                    <?php endif; ?>
                                                    <span class="badge <?= ($product['status'] ?? 'active') === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                        <?= ucfirst($product['status'] ?? 'active') ?>
                                                    </span>
                                                </div>
                                                <span class="badge bg-primary">
                                                    Bought <?= number_format((int)($product['total_quantity'] ?? 0)) ?>
                                                </span>
                                            </div>

                                            <p class="text-muted small mb-3">
                                                <?= esc($product['description'] ?: 'No description provided.') ?>
                                            </p>

                                            <div class="row mb-3">
                                                <div class="col-6">
                                                    <small class="text-muted">Price</small>
                                                    <div class="fw-bold">PHP <?= number_format((float)$product['amount'], 2) ?></div>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Total Spent</small>
                                                    <div class="fw-bold text-success">PHP <?= number_format((float)($product['total_paid'] ?? 0), 2) ?></div>
                                                </div>
                                            </div>

                                            <div class="mt-auto">
                                                <?php if (($product['status'] ?? 'active') === 'active'): ?>
                                                    <button
                                                        class="btn btn-primary btn-sm w-100"
                                                        onclick='openPaymentRequestModal(<?= json_encode([
                                                            'id' => (int)$product['id'],
                                                            'title' => $product['title'],
                                                            'description' => $product['description'],
                                                            'amount' => (float)$product['amount'],
                                                            'remaining_balance' => (float)($product['amount'] ?? 0),
                                                            'item_type' => 'product',
                                                        ]) ?>)'>
                                                        <i class="fas fa-paper-plane me-2"></i>Buy Product
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-secondary btn-sm w-100" disabled>
                                                        <i class="fas fa-ban me-2"></i>Unavailable
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('partials/modal-payment-request') ?>

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
</script>
<?= $this->endSection() ?>
