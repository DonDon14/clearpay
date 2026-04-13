<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="productForm" method="post" enctype="multipart/form-data" action="<?= isset($action) ? $action : '' ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel"><?= isset($title) ? $title : 'Add Product' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="productId" value="">
                    <input type="hidden" name="remove_image" id="productRemoveImage" value="0">
                    <div class="mb-3">
                        <label for="productTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="productTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="productImage" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="productImage" name="image" accept="image/jpeg,image/jpg,image/png,image/webp,image/gif">
                        <div class="form-text">Recommended: square or landscape image, up to 4MB.</div>
                        <div class="mt-3 d-none" id="productImagePreviewWrap">
                            <img src="" alt="Product preview" id="productImagePreview" class="img-fluid rounded-4 border" style="max-height: 220px;">
                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="productRemoveImageBtn">Remove Image</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="productDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="productDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productAmount" class="form-label">Price</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="productAmount" name="amount" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="productCostPrice" class="form-label">Cost Price</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="productCostPrice" name="cost_price" required>
                            <div class="form-text">Used to compute product income.</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productCategory" class="form-label">Category</label>
                            <input type="text" class="form-control" id="productCategory" name="category" placeholder="e.g. Uniform, ID, Lanyard">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="productStatus" class="form-label">Status</label>
                        <select class="form-select" id="productStatus" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
