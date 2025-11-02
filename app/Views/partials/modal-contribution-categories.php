<?php
/**
 * Contribution Categories Management Modal Partial
 * 
 * This is the HTML-only partial for the contribution categories management modal.
 * JavaScript functionality is handled separately.
 * 
 * Usage:
 * <?= view('partials/modal-contribution-categories') ?>
 */
?>

<!-- Contribution Categories Management Modal -->
<div class="modal fade" id="contributionCategoriesModal" tabindex="-1" aria-labelledby="contributionCategoriesModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contributionCategoriesModalLabel">
                    <i class="fas fa-folder-open me-2"></i>Contribution Categories Management
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Add New Category Button -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Available Categories</h6>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addContributionCategoryModal">
                        <i class="fas fa-plus me-1"></i>Add New Category
                    </button>
                </div>

                <!-- Categories Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="contributionCategoriesTableBody">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-spinner fa-spin fa-2x mb-2"></i><br>
                                    Loading categories...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Contribution Category Modal -->
<div class="modal fade" id="addContributionCategoryModal" tabindex="-1" aria-labelledby="addContributionCategoryModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addContributionCategoryModalLabel">
                    <i class="fas fa-plus me-2"></i>Add New Contribution Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addContributionCategoryForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addCategoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="addCategoryName" name="name" required placeholder="e.g., Tuition Fee, Library Fee">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="addCategoryCode" class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="addCategoryCode" name="code" required placeholder="e.g., tuition, library">
                        <div class="form-text">System code (lowercase, alphanumeric with dashes/underscores only)</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="addCategoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="addCategoryDescription" name="description" rows="3" placeholder="Optional description of this category"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="addCategoryStatus" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="addCategoryStatus" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="addCategorySortOrder" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="addCategorySortOrder" name="sort_order" value="0" min="0">
                                <div class="form-text">Lower numbers appear first</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Contribution Category Modal -->
<div class="modal fade" id="editContributionCategoryModal" tabindex="-1" aria-labelledby="editContributionCategoryModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editContributionCategoryModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Contribution Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editContributionCategoryForm">
                <input type="hidden" id="editCategoryId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editCategoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editCategoryName" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editCategoryCode" class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editCategoryCode" name="code" required>
                        <div class="form-text">System code (lowercase, alphanumeric with dashes/underscores only)</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editCategoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editCategoryDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCategoryStatus" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="editCategoryStatus" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCategorySortOrder" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="editCategorySortOrder" name="sort_order" min="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

