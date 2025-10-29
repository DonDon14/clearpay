<?php
/**
 * Refund Methods Management Modal Partial
 * 
 * This is the HTML-only partial for the refund methods management modal.
 * JavaScript functionality is handled separately.
 * 
 * Usage:
 * <?= view('partials/modal-refund-methods') ?>
 */
?>

<!-- Refund Methods Management Modal -->
<div class="modal fade" id="refundMethodsModal" tabindex="-1" aria-labelledby="refundMethodsModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="refundMethodsModalLabel">
                    <i class="fas fa-undo me-2"></i>Refund Methods Management
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Add New Refund Method Button -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Available Refund Methods</h6>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRefundMethodModal">
                        <i class="fas fa-plus me-1"></i>Add New Refund Method
                    </button>
                </div>

                <!-- Refund Methods Table -->
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
                        <tbody id="refundMethodsTableBody">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-spinner fa-spin fa-2x mb-2"></i><br>
                                    Loading refund methods...
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

<!-- Add Refund Method Modal -->
<div class="modal fade" id="addRefundMethodModal" tabindex="-1" aria-labelledby="addRefundMethodModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRefundMethodModalLabel">
                    <i class="fas fa-plus me-2"></i>Add New Refund Method
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addRefundMethodForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addRefundMethodName" class="form-label">Refund Method Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="addRefundMethodName" name="name" required placeholder="e.g., Cash, Bank Transfer">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="addRefundMethodCode" class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="addRefundMethodCode" name="code" required placeholder="e.g., cash, bank_transfer">
                        <div class="form-text">System code (lowercase, alphanumeric with dashes/underscores only)</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="addRefundMethodDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="addRefundMethodDescription" name="description" rows="3" placeholder="Optional description of this refund method"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="addRefundMethodStatus" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="addRefundMethodStatus" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="addRefundMethodSortOrder" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="addRefundMethodSortOrder" name="sort_order" value="0" min="0">
                                <div class="form-text">Lower numbers appear first</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Refund Method
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Refund Method Modal -->
<div class="modal fade" id="editRefundMethodModal" tabindex="-1" aria-labelledby="editRefundMethodModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRefundMethodModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Refund Method
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editRefundMethodForm">
                <input type="hidden" id="editRefundMethodId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editRefundMethodName" class="form-label">Refund Method Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editRefundMethodName" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editRefundMethodCode" class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editRefundMethodCode" name="code" required>
                        <div class="form-text">System code (lowercase, alphanumeric with dashes/underscores only)</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editRefundMethodDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editRefundMethodDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editRefundMethodStatus" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="editRefundMethodStatus" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editRefundMethodSortOrder" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="editRefundMethodSortOrder" name="sort_order" min="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Refund Method
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

