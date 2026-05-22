<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>" />

<div class="container-fluid ui-page-shell">
    <?php
    $productCategories = [];
    foreach (($products ?? []) as $productForCategory) {
        $category = trim((string) ($productForCategory['category'] ?? ''));
        if ($category !== '') {
            $productCategories[$category] = $category;
        }
    }
    ksort($productCategories);
    ?>
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

    <div class="card shadow-sm border-0 ui-surface-card">
        <div class="card-header ui-surface-card-header">
            <h5 class="card-title mb-1">Products</h5>
            <small class="text-muted d-block ui-surface-subtitle">Search, filter, and open product purchase history.</small>
        </div>
        <div class="card-body p-3 ui-surface-card-body">
            <?= view('partials/list-controls', [
                'controlId' => 'productControls',
                'searchId' => 'productSearch',
                'searchLabel' => 'Search products',
                'placeholder' => 'Title, description, category...',
                'resultId' => 'productResultsCount',
                'chipsId' => 'productFilterChips',
                'clearId' => 'clearProductFilters',
                'filters' => [
                    [
                        'id' => 'productCategoryFilter',
                        'label' => 'Category',
                        'options' => ['' => 'All categories'] + $productCategories,
                    ],
                    [
                        'id' => 'productStatusFilter',
                        'label' => 'Status',
                        'options' => ['' => 'All statuses', 'active' => 'Active', 'inactive' => 'Inactive'],
                    ],
                ],
                'sort' => [
                    'id' => 'productSort',
                    'label' => 'Sort',
                    'options' => [
                        'newest' => 'Newest',
                        'name_asc' => 'Name A-Z',
                        'sales_desc' => 'Sales high-low',
                        'income_desc' => 'Income high-low',
                    ],
                ],
            ]) ?>
            <?php if (empty($products)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-box-open fa-3x mb-3"></i>
                    <p class="mb-0">No products found.</p>
                </div>
            <?php else: ?>
                <div class="row g-3" id="productsContainer">
                    <?php foreach ($products as $product): ?>
                        <?php
                            $productSearchText = strtolower(trim(implode(' ', [
                                $product['title'] ?? '',
                                $product['description'] ?? '',
                                $product['category'] ?? '',
                                $product['status'] ?? '',
                            ])));
                        ?>
                        <div class="col-12 product-item"
                             data-search="<?= esc($productSearchText) ?>"
                             data-title="<?= strtolower(esc($product['title'])) ?>"
                             data-category="<?= strtolower(esc($product['category'] ?? '')) ?>"
                             data-status="<?= strtolower(esc($product['status'] ?? 'active')) ?>"
                             data-created="<?= esc($product['created_at'] ?? '') ?>"
                             data-sales="<?= esc((string) ($product['total_collected'] ?? 0)) ?>"
                             data-income="<?= esc((string) ($product['total_income'] ?? 0)) ?>">
                            <div class="card border-0 shadow-sm overflow-hidden ui-product-card"
                                 onclick="showProductPayments(<?= (int) $product['id'] ?>, <?= htmlspecialchars(json_encode($product['title']), ENT_QUOTES, 'UTF-8') ?>, <?= (float) ($product['amount'] ?? 0) ?>)">
                                <div class="card-body d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                    <div class="d-flex align-items-start gap-3 flex-grow-1">
                                        <div class="ui-item-media">
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
                                                    class="ui-item-thumb"
                                                    onerror="this.onerror=null; this.style.display='none'; this.parentElement.insertAdjacentHTML('beforeend', '<div class=&quot;ui-item-thumb ui-item-thumb--placeholder&quot;><i class=&quot;fas fa-box-open&quot;></i></div>');"
                                                    onclick="event.stopPropagation(); openImagePreview('<?= esc($productImageUrl) ?>', '<?= esc($product['title']) ?>')">
                                            <?php else: ?>
                                                <div class="ui-item-thumb ui-item-thumb--placeholder">
                                                    <i class="fas fa-box-open"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
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
                                    </div>
                                    <div class="d-flex gap-2" onclick="event.stopPropagation();">
                                        <button class="btn btn-sm btn-outline-warning" onclick="editProduct(<?= (int)$product['id'] ?>)"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm <?= ($product['status'] ?? 'active') === 'active' ? 'btn-outline-success' : 'btn-outline-secondary' ?>" onclick="toggleProductStatus(<?= (int)$product['id'] ?>)"><i class="fas fa-toggle-<?= ($product['status'] ?? 'active') === 'active' ? 'on' : 'off' ?>"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(<?= (int)$product['id'] ?>)"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="col-12 d-none" id="noProductResults">
                        <div class="ui-empty-filter-state">
                            <div class="ui-empty-filter-icon"><i class="fas fa-search"></i></div>
                            <h5>No products found</h5>
                            <p>Try changing your search or clearing filters.</p>
                            <button type="button" class="btn btn-primary btn-sm" id="clearProductEmptyFilters">Clear filters</button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= view('partials/modal-product', ['title' => 'Add Product', 'action' => base_url('products/save')]) ?>
<?= view('partials/modal-contribution-payments') ?>
<?= view('partials/modal-add-payment-to-partial') ?>
<?= view('partials/modal-qr-receipt', [
    'title' => 'Payment Receipt',
]) ?>
<div class="modal fade" id="itemImagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="itemImagePreviewTitle">Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" alt="" id="itemImagePreviewModalImage" class="img-fluid rounded-4">
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('js/list-controls.js') ?>"></script>
<script>
window.APP_BASE_URL = '<?= base_url() ?>';

document.addEventListener('DOMContentLoaded', function() {
    const previewWrap = document.getElementById('productImagePreviewWrap');
    const previewImage = document.getElementById('productImagePreview');
    const imageInput = document.getElementById('productImage');
    const removeImageInput = document.getElementById('productRemoveImage');
    const productListControls = createListControls({
        searchId: 'productSearch',
        resultId: 'productResultsCount',
        chipsId: 'productFilterChips',
        clearId: 'clearProductFilters',
        itemSelector: '.product-item',
        containerSelector: '#productsContainer',
        emptySelector: '#noProductResults',
        label: 'products',
        filters: [
            { id: 'productCategoryFilter', key: 'category', label: 'Category', attribute: 'data-category' },
            { id: 'productStatusFilter', key: 'status', label: 'Status', attribute: 'data-status' },
        ],
        sort: {
            id: 'productSort',
            options: {
                newest: { attribute: 'data-created', direction: 'desc' },
                name_asc: { attribute: 'data-title', direction: 'asc' },
                sales_desc: { attribute: 'data-sales', direction: 'desc', type: 'number' },
                income_desc: { attribute: 'data-income', direction: 'desc', type: 'number' },
            },
        },
    });
    const clearProductEmptyFilters = document.getElementById('clearProductEmptyFilters');
    if (clearProductEmptyFilters) {
        clearProductEmptyFilters.addEventListener('click', function() {
            document.getElementById('clearProductFilters')?.click();
        });
    }

    const form = document.getElementById('productForm');
    const showProductPreview = function(src) {
        if (!previewWrap || !previewImage) return;
        previewImage.src = src;
        previewWrap.classList.remove('d-none');
    };

    if (imageInput) {
        imageInput.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (!file) {
                return;
            }

            removeImageInput.value = '0';
            showProductPreview(URL.createObjectURL(file));
        });
    }

    const removeImageBtn = document.getElementById('productRemoveImageBtn');
    if (removeImageBtn) {
        removeImageBtn.addEventListener('click', function() {
            if (imageInput) {
                imageInput.value = '';
            }
            if (previewImage) {
                previewImage.src = '';
            }
            if (previewWrap) {
                previewWrap.classList.add('d-none');
            }
            removeImageInput.value = '1';
        });
    }

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
        document.getElementById('productRemoveImage').value = '0';
        if (previewWrap) {
            previewWrap.classList.add('d-none');
        }
        if (previewImage) {
            previewImage.src = '';
        }
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
            document.getElementById('productRemoveImage').value = '0';
            if (product.image_path) {
                const imagePath = String(product.image_path);
                const imageUrl = /^https?:\/\//i.test(imagePath)
                    ? imagePath
                    : `<?= rtrim(base_url(), '/') ?>/` + imagePath.replace(/^\/+/, '');
                document.getElementById('productImagePreview').src = imageUrl;
                document.getElementById('productImagePreviewWrap').classList.remove('d-none');
            } else {
                document.getElementById('productImagePreviewWrap').classList.add('d-none');
                document.getElementById('productImagePreview').src = '';
            }
            document.getElementById('productForm').action = `<?= base_url('products/update/') ?>${id}`;
            document.getElementById('productModalLabel').textContent = 'Edit Product';
            new bootstrap.Modal(document.getElementById('productModal')).show();
        });
}

function openImagePreview(src, title) {
    document.getElementById('itemImagePreviewTitle').textContent = title || 'Image Preview';
    document.getElementById('itemImagePreviewModalImage').src = src;
    new bootstrap.Modal(document.getElementById('itemImagePreviewModal')).show();
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
<style>
.ui-product-card {
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    border: 1px solid rgba(12, 82, 145, 0.08);
    cursor: pointer;
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
}

.ui-product-card:hover {
    border-color: rgba(59, 130, 246, 0.18);
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.1) !important;
    transform: translateY(-2px);
}

.ui-item-thumb {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 1rem;
    cursor: pointer;
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.12);
}

.ui-item-thumb--placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #dbeafe, #eff6ff);
    color: #2563eb;
    font-size: 2rem;
}
</style>
<?= $this->endSection() ?>
