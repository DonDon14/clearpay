<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>" />

<div class="container-fluid ui-page-shell">
    <div class="ui-page-intro">
        <div>
            <h6>Products</h6>
            <p>Manage optional individual products, track unit cost, and monitor product income.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="ui-stat-pill"><i class="fas fa-box-open"></i>Total <?= number_format((int)($totalCount ?? 0)) ?></span>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#productModal">
                <i class="fas fa-plus me-1"></i>Add Product
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><?= view('partials/card', ['icon' => 'fas fa-box-open', 'iconColor' => 'text-primary', 'title' => 'Total', 'text' => (string)($totalCount ?? 0)]) ?></div>
        <div class="col-md-3"><?= view('partials/card', ['icon' => 'fas fa-check-circle', 'iconColor' => 'text-success', 'title' => 'Active', 'text' => (string)($activeCount ?? 0)]) ?></div>
        <div class="col-md-3"><?= view('partials/card', ['icon' => 'fas fa-shopping-bag', 'iconColor' => 'text-info', 'title' => 'Units Sold', 'text' => (string)($totalUnitsSold ?? 0)]) ?></div>
        <div class="col-md-3"><?= view('partials/card', ['icon' => 'fas fa-coins', 'iconColor' => 'text-warning', 'title' => 'Income', 'text' => 'PHP ' . number_format((float)($totalIncome ?? 0), 2)]) ?></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Products</h5>
            <input type="text" class="form-control" id="productSearch" placeholder="Search products..." style="max-width: 280px;">
        </div>
        <div class="card-body">
            <?php if (empty($products)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-box-open fa-3x mb-3"></i>
                    <p class="mb-0">No products found.</p>
                </div>
            <?php else: ?>
                <div class="row g-3" id="productsContainer">
                    <?php foreach ($products as $product): ?>
                        <div class="col-12 product-item" data-title="<?= strtolower(esc($product['title'])) ?>">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1"><?= esc($product['title']) ?></h5>
                                        <p class="text-muted mb-2"><?= esc($product['description'] ?? 'No description provided') ?></p>
                                        <div class="d-flex gap-3 flex-wrap">
                                            <span class="badge bg-primary">PHP <?= number_format((float)($product['amount'] ?? 0), 2) ?></span>
                                            <span class="badge bg-dark">Cost PHP <?= number_format((float)($product['cost_price'] ?? 0), 2) ?></span>
                                            <span class="badge bg-success">Income PHP <?= number_format((float)($product['income_per_unit'] ?? 0), 2) ?>/unit</span>
                                            <?php if (!empty($product['category'])): ?><span class="badge bg-info"><?= esc($product['category']) ?></span><?php endif; ?>
                                            <span class="badge <?= ($product['status'] ?? 'active') === 'active' ? 'bg-success' : 'bg-secondary' ?>"><?= ucfirst($product['status'] ?? 'active') ?></span>
                                            <span class="text-muted small">Units sold: <?= number_format((int)($product['total_quantity_sold'] ?? 0)) ?></span>
                                            <span class="text-muted small">Sales: PHP <?= number_format((float)($product['total_collected'] ?? 0), 2) ?></span>
                                            <span class="text-muted small">Income: PHP <?= number_format((float)($product['total_income'] ?? 0), 2) ?></span>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-warning" onclick="editProduct(<?= (int)$product['id'] ?>)"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm <?= ($product['status'] ?? 'active') === 'active' ? 'btn-outline-success' : 'btn-outline-secondary' ?>" onclick="toggleProductStatus(<?= (int)$product['id'] ?>)"><i class="fas fa-toggle-<?= ($product['status'] ?? 'active') === 'active' ? 'on' : 'off' ?>"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(<?= (int)$product['id'] ?>)"><i class="fas fa-trash"></i></button>
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

<?= view('partials/modal-product', ['title' => 'Add Product', 'action' => base_url('products/save')]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('productSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            document.querySelectorAll('.product-item').forEach(item => {
                const title = item.getAttribute('data-title') || '';
                item.style.display = term === '' || title.includes(term) ? '' : 'none';
            });
        });
    }

    const form = document.getElementById('productForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to save product');
            }
        });
    });

    document.getElementById('productModal').addEventListener('hidden.bs.modal', function() {
        form.reset();
        form.action = '<?= base_url('products/save') ?>';
        document.getElementById('productModalLabel').textContent = 'Add Product';
        document.getElementById('productId').value = '';
        document.getElementById('productCostPrice').value = '0';
    });
});

function editProduct(id) {
    fetch(`<?= base_url('products/get/') ?>${id}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) return alert(data.message || 'Product not found');
            const product = data.product;
            document.getElementById('productId').value = product.id;
            document.getElementById('productTitle').value = product.title || '';
            document.getElementById('productDescription').value = product.description || '';
            document.getElementById('productAmount').value = product.amount || '';
            document.getElementById('productCostPrice').value = product.cost_price || 0;
            document.getElementById('productCategory').value = product.category || '';
            document.getElementById('productStatus').value = product.status || 'active';
            document.getElementById('productForm').action = `<?= base_url('products/update/') ?>${id}`;
            document.getElementById('productModalLabel').textContent = 'Edit Product';
            new bootstrap.Modal(document.getElementById('productModal')).show();
        });
}

function toggleProductStatus(id) {
    fetch(`<?= base_url('products/toggle-status/') ?>${id}`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(() => location.reload());
}

function deleteProduct(id) {
    if (!confirm('Delete this product?')) return;
    fetch(`<?= base_url('products/delete/') ?>${id}`, {
        method: 'DELETE',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(() => location.reload());
}
</script>
<?= $this->endSection() ?>
