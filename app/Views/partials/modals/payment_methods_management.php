<?php
/**
 * Payment Methods Management Modal Partial
 * 
 * This partial contains all the modals and functionality for managing payment methods
 * with custom instructions support.
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
                                <th>Account Number</th>
                                <th>Account Name</th>
                                <th>QR Code</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="paymentMethodsTableBody">
                            <?php if (empty($paymentMethods)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-credit-card fa-2x mb-2"></i><br>
                                        No payment methods found. Add your first payment method to get started.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($paymentMethods as $method): ?>
                                    <tr data-id="<?= $method['id'] ?>">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($method['icon']): ?>
                                                    <img src="<?= base_url($method['icon']) ?>" alt="<?= esc($method['name']) ?>" class="me-2" style="width: 24px; height: 24px; object-fit: contain;">
                                                <?php endif; ?>
                                                <strong><?= esc($method['name']) ?></strong>
                                            </div>
                                        </td>
                                        <td><?= esc($method['description']) ?></td>
                                        <td><?= esc($method['account_number'] ?? 'N/A') ?></td>
                                        <td><?= esc($method['account_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if ($method['qr_code_path']): ?>
                                                <span class="badge bg-success">Available</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $method['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                <?= ucfirst($method['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary" onclick="editPaymentMethod(<?= $method['id'] ?>)" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-<?= $method['status'] === 'active' ? 'warning' : 'success' ?>" onclick="togglePaymentMethodStatus(<?= $method['id'] ?>)" title="<?= $method['status'] === 'active' ? 'Deactivate' : 'Activate' ?>">
                                                    <i class="fas fa-<?= $method['status'] === 'active' ? 'pause' : 'play' ?>"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" onclick="deletePaymentMethod(<?= $method['id'] ?>)" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Method Modal -->
<div class="modal fade" id="addPaymentMethodModal" tabindex="-1" aria-labelledby="addPaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPaymentMethodModalLabel">
                    <i class="fas fa-plus me-2"></i>Add New Payment Method
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPaymentMethodForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Basic Information</h6>
                            
                            <div class="mb-3">
                                <label for="addName" class="form-label">Payment Method Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="addName" name="name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="addDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="addDescription" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="addAccountDetails" class="form-label">Account Details</label>
                                <input type="text" class="form-control" id="addAccountDetails" name="account_details">
                            </div>
                            
                            <div class="mb-3">
                                <label for="addStatus" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="addStatus" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Custom Instructions -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Custom Instructions</h6>
                            
                            <div class="mb-3">
                                <label for="addAccountNumber" class="form-label">Account Number</label>
                                <input type="text" class="form-control" id="addAccountNumber" name="account_number" placeholder="e.g., 0917-123-4567">
                                <div class="form-text">GCash number, bank account, etc.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="addAccountName" class="form-label">Account Name</label>
                                <input type="text" class="form-control" id="addAccountName" name="account_name" placeholder="e.g., ClearPay School">
                            </div>
                            
                            <div class="mb-3">
                                <label for="addQrCode" class="form-label">QR Code Image</label>
                                <input type="file" class="form-control" id="addQrCode" name="qr_code" accept="image/png,image/jpg,image/jpeg">
                                <div class="form-text">Upload QR code for easy scanning (PNG, JPG, JPEG)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="addReferencePrefix" class="form-label">Reference Prefix</label>
                                <input type="text" class="form-control" id="addReferencePrefix" name="reference_prefix" placeholder="CP" maxlength="20">
                                <div class="form-text">Prefix for payment reference numbers (default: CP)</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User-Friendly Payment Instructions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">Payment Instructions</h6>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Standardized Layout:</strong> All payment methods will use the same professional layout with QR code at the top and instructions below.
                            </div>
                            
                            <!-- Instruction Steps -->
                            <div class="mb-3">
                                <label class="form-label">Payment Instructions (Step by Step)</label>
                                <div id="addInstructionSteps">
                                    <div class="instruction-step mb-2">
                                        <div class="input-group">
                                            <span class="input-group-text">1</span>
                                            <input type="text" class="form-control instruction-step-input" placeholder="e.g., Open your GCash app" data-step="1">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-step" style="display: none;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addInstructionStep">
                                    <i class="fas fa-plus me-1"></i>Add Step
                                </button>
                                <div class="form-text">Add step-by-step instructions for users to follow</div>
                            </div>
                            
                            <!-- Additional Information -->
                            <div class="mb-3">
                                <label for="addAdditionalInfo" class="form-label">Additional Information</label>
                                <textarea class="form-control" id="addAdditionalInfo" name="additional_info" rows="3" placeholder="Any additional notes or special instructions..."></textarea>
                                <div class="form-text">Optional: Add any special notes or additional information</div>
                            </div>
                            
                            <!-- Preview Section -->
                            <div class="mb-3">
                                <label class="form-label">Preview</label>
                                <div class="border rounded p-3 bg-light" id="addPreviewArea">
                                    <div class="text-center text-muted">
                                        <i class="fas fa-eye me-2"></i>Preview will appear here as you fill in the details
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden field for generated HTML -->
                            <input type="hidden" id="addCustomInstructions" name="custom_instructions">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Payment Method
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Payment Method Modal -->
<div class="modal fade" id="editPaymentMethodModal" tabindex="-1" aria-labelledby="editPaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPaymentMethodModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Payment Method
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPaymentMethodForm" enctype="multipart/form-data">
                <input type="hidden" id="editId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Basic Information</h6>
                            
                            <div class="mb-3">
                                <label for="editName" class="form-label">Payment Method Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editName" name="name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="editDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="editAccountDetails" class="form-label">Account Details</label>
                                <input type="text" class="form-control" id="editAccountDetails" name="account_details">
                            </div>
                            
                            <div class="mb-3">
                                <label for="editStatus" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="editStatus" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Custom Instructions -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Custom Instructions</h6>
                            
                            <div class="mb-3">
                                <label for="editAccountNumber" class="form-label">Account Number</label>
                                <input type="text" class="form-control" id="editAccountNumber" name="account_number" placeholder="e.g., 0917-123-4567">
                                <div class="form-text">GCash number, bank account, etc.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="editAccountName" class="form-label">Account Name</label>
                                <input type="text" class="form-control" id="editAccountName" name="account_name" placeholder="e.g., ClearPay School">
                            </div>
                            
                            <div class="mb-3">
                                <label for="editQrCode" class="form-label">QR Code Image</label>
                                <input type="file" class="form-control" id="editQrCode" name="qr_code" accept="image/png,image/jpg,image/jpeg">
                                <div class="form-text">Upload new QR code to replace existing one</div>
                                <div id="currentQrCode" class="mt-2"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="editReferencePrefix" class="form-label">Reference Prefix</label>
                                <input type="text" class="form-control" id="editReferencePrefix" name="reference_prefix" placeholder="CP" maxlength="20">
                                <div class="form-text">Prefix for payment reference numbers</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User-Friendly Payment Instructions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">Payment Instructions</h6>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Standardized Layout:</strong> All payment methods will use the same professional layout with QR code at the top and instructions below.
                            </div>
                            
                            <!-- Instruction Steps -->
                            <div class="mb-3">
                                <label class="form-label">Payment Instructions (Step by Step)</label>
                                <div id="editInstructionSteps">
                                    <div class="instruction-step mb-2">
                                        <div class="input-group">
                                            <span class="input-group-text">1</span>
                                            <input type="text" class="form-control instruction-step-input" placeholder="e.g., Open your GCash app" data-step="1">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-step" style="display: none;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="editAddInstructionStep">
                                    <i class="fas fa-plus me-1"></i>Add Step
                                </button>
                                <div class="form-text">Add step-by-step instructions for users to follow</div>
                            </div>
                            
                            <!-- Additional Information -->
                            <div class="mb-3">
                                <label for="editAdditionalInfo" class="form-label">Additional Information</label>
                                <textarea class="form-control" id="editAdditionalInfo" name="additional_info" rows="3" placeholder="Any additional notes or special instructions..."></textarea>
                                <div class="form-text">Optional: Add any special notes or additional information</div>
                            </div>
                            
                            <!-- Preview Section -->
                            <div class="mb-3">
                                <label class="form-label">Preview</label>
                                <div class="border rounded p-3 bg-light" id="editPreviewArea">
                                    <div class="text-center text-muted">
                                        <i class="fas fa-eye me-2"></i>Preview will appear here as you fill in the details
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden field for generated HTML -->
                            <input type="hidden" id="editCustomInstructions" name="custom_instructions">
                        </div>
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