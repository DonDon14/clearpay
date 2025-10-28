<?php
/**
 * Payment Methods Management Modal Partial
 * 
 * This partial contains all the modals and functionality for managing payment methods.
 * It can be included in any page that needs payment method management functionality.
 * 
 * Required variables:
 * - $paymentMethods: Array of payment methods data
 * 
 * Usage:
 * <?= view('partials/modals/payment_methods_management', ['paymentMethods' => $paymentMethods]) ?>
 */

// Ensure paymentMethods is defined
$paymentMethods = $paymentMethods ?? [];
?>

<!-- Payment Methods Management Modal -->
<div class="modal fade" id="paymentMethodsModal" tabindex="-1" aria-labelledby="paymentMethodsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentMethodsModalLabel">
                    <i class="fas fa-credit-card me-2"></i>Payment Methods Management
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Add New Payment Method Button -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Available Payment Methods</h6>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPaymentMethodModal">
                        <i class="fas fa-plus me-1"></i>Add New Payment Method
                    </button>
                </div>

                <!-- Payment Methods Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Account Details</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($paymentMethods)): ?>
                                <?php foreach ($paymentMethods as $method): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($method['icon']) && file_exists(FCPATH . $method['icon'])): ?>
                                                    <img src="<?= base_url($method['icon']) ?>" alt="<?= esc($method['name']) ?> Icon" style="width: 32px; height: 32px; object-fit: cover; border-radius: 4px;" class="me-2">
                                                <?php else: ?>
                                                    <div class="payment-method-placeholder me-2" style="width: 32px; height: 32px; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 10px;" data-method="<?= esc($method['name']) ?>">
                                                        <?= strtoupper(substr($method['name'], 0, 2)) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <strong><?= esc($method['name']) ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($method['description'])): ?>
                                                <span class="text-muted"><?= esc($method['description']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic">No description</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($method['account_details'])): ?>
                                                <span class="text-muted"><?= esc($method['account_details']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic">No details</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($method['status'] === 'active'): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>Active
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-times-circle me-1"></i>Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editPaymentMethod(<?= $method['id'] ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-<?= $method['status'] === 'active' ? 'warning' : 'success' ?>" 
                                                        onclick="togglePaymentMethodStatus(<?= $method['id'] ?>, '<?= $method['status'] ?>')">
                                                    <i class="fas fa-<?= $method['status'] === 'active' ? 'pause' : 'play' ?>"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deletePaymentMethod(<?= $method['id'] ?>, '<?= esc($method['name']) ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-credit-card fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No payment methods found</p>
                                        <small class="text-muted">Add your first payment method to get started</small>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Method Modal -->
<div class="modal fade" id="addPaymentMethodModal" tabindex="-1" aria-labelledby="addPaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPaymentMethodModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add New Payment Method
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPaymentMethodForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="paymentMethodName" class="form-label">Payment Method Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="paymentMethodName" name="name" required placeholder="e.g., GCash, PayMaya, Bank Transfer">
                    </div>
                    <div class="mb-3">
                        <label for="paymentMethodIcon" class="form-label">Icon</label>
                        <div class="input-group">
                            <input type="file" class="form-control" id="paymentMethodIcon" name="icon" accept="image/*" onchange="previewIcon(this)">
                            <label class="input-group-text" for="paymentMethodIcon">
                                <i class="fas fa-upload"></i>
                            </label>
                        </div>
                        <div class="form-text">Upload an image file (JPG, PNG, GIF, WebP) - Max 2MB</div>
                        <div id="iconPreview" class="mt-2" style="display: none;">
                            <img id="iconPreviewImg" src="" alt="Icon Preview" style="max-width: 64px; max-height: 64px; border-radius: 4px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="paymentMethodDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="paymentMethodDescription" name="description" rows="3" placeholder="Optional description for this payment method"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="paymentMethodAccountDetails" class="form-label">Account Details</label>
                        <input type="text" class="form-control" id="paymentMethodAccountDetails" name="account_details" placeholder="e.g., Account Number, Mobile Number, etc.">
                    </div>
                    <div class="mb-3">
                        <label for="paymentMethodStatus" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="paymentMethodStatus" name="status" required>
                            <option value="">Select Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Payment Method
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Payment Method Modal -->
<div class="modal fade" id="editPaymentMethodModal" tabindex="-1" aria-labelledby="editPaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPaymentMethodModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Payment Method
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPaymentMethodForm">
                <input type="hidden" id="editPaymentMethodId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editPaymentMethodName" class="form-label">Payment Method Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editPaymentMethodName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPaymentMethodIcon" class="form-label">Icon</label>
                        <div class="input-group">
                            <input type="file" class="form-control" id="editPaymentMethodIcon" name="icon" accept="image/*" onchange="previewEditIcon(this)">
                            <label class="input-group-text" for="editPaymentMethodIcon">
                                <i class="fas fa-upload"></i>
                            </label>
                        </div>
                        <div class="form-text">Upload an image file (JPG, PNG, GIF, WebP) - Max 2MB</div>
                        <div id="editIconPreview" class="mt-2">
                            <img id="editIconPreviewImg" src="" alt="Current Icon" style="max-width: 64px; max-height: 64px; border-radius: 4px;">
                            <div class="mt-1">
                                <small class="text-muted">Current icon</small>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editPaymentMethodDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editPaymentMethodDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editPaymentMethodAccountDetails" class="form-label">Account Details</label>
                        <input type="text" class="form-control" id="editPaymentMethodAccountDetails" name="account_details">
                    </div>
                    <div class="mb-3">
                        <label for="editPaymentMethodStatus" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="editPaymentMethodStatus" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Payment Method
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payment Methods Management Styles -->
<style>
.payment-method-placeholder {
    background: linear-gradient(135deg, #007bff, #0056b3);
}

.payment-method-placeholder[data-method="GCash"] {
    background: linear-gradient(135deg, #009639, #007a2e);
}

.payment-method-placeholder[data-method="PayMaya"] {
    background: linear-gradient(135deg, #ffc107, #e0a800);
}

.payment-method-placeholder[data-method="Bank Transfer"] {
    background: linear-gradient(135deg, #007bff, #0056b3);
}

.payment-method-placeholder[data-method="Cash"] {
    background: linear-gradient(135deg, #28a745, #1e7e34);
}

.payment-method-placeholder[data-method="Online Banking"] {
    background: linear-gradient(135deg, #6c757d, #545b62);
}
</style>
