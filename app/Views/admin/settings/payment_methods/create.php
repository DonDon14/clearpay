<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800"><?= esc($pageTitle) ?></h1>
                    <p class="text-muted mb-0"><?= esc($pageSubtitle) ?></p>
                </div>
                <div>
                    <a href="<?= base_url('admin/settings/payment-methods') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Payment Methods
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Create Form -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2 text-primary"></i>
                        Payment Method Details
                    </h5>
                </div>
                <div class="card-body">
                    <?= form_open('admin/settings/payment-methods/store', ['class' => 'needs-validation', 'novalidate' => true]) ?>
                        
                        <!-- Name Field -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-tag me-1"></i>Payment Method Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control <?= session()->getFlashdata('validation_errors.name') ? 'is-invalid' : '' ?>" 
                                   id="name" 
                                   name="name" 
                                   value="<?= old('name') ?>" 
                                   placeholder="e.g., GCash, PayMaya, Bank Transfer"
                                   required>
                            <?php if (session()->getFlashdata('validation_errors.name')): ?>
                                <div class="invalid-feedback">
                                    <?= session()->getFlashdata('validation_errors.name') ?>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Enter a unique name for this payment method.</div>
                        </div>

                        <!-- Description Field -->
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Description
                            </label>
                            <textarea class="form-control <?= session()->getFlashdata('validation_errors.description') ? 'is-invalid' : '' ?>" 
                                      id="description" 
                                      name="description" 
                                      rows="3" 
                                      placeholder="Optional description for this payment method"><?= old('description') ?></textarea>
                            <?php if (session()->getFlashdata('validation_errors.description')): ?>
                                <div class="invalid-feedback">
                                    <?= session()->getFlashdata('validation_errors.description') ?>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Provide additional details about this payment method.</div>
                        </div>

                        <!-- Account Details Field -->
                        <div class="mb-3">
                            <label for="account_details" class="form-label">
                                <i class="fas fa-info-circle me-1"></i>Account Details
                            </label>
                            <input type="text" 
                                   class="form-control <?= session()->getFlashdata('validation_errors.account_details') ? 'is-invalid' : '' ?>" 
                                   id="account_details" 
                                   name="account_details" 
                                   value="<?= old('account_details') ?>" 
                                   placeholder="e.g., Account Number, Mobile Number, etc.">
                            <?php if (session()->getFlashdata('validation_errors.account_details')): ?>
                                <div class="invalid-feedback">
                                    <?= session()->getFlashdata('validation_errors.account_details') ?>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Enter account details or contact information for this payment method.</div>
                        </div>

                        <!-- Status Field -->
                        <div class="mb-4">
                            <label for="status" class="form-label">
                                <i class="fas fa-toggle-on me-1"></i>Status <span class="text-danger">*</span>
                            </label>
                            <select class="form-select <?= session()->getFlashdata('validation_errors.status') ? 'is-invalid' : '' ?>" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="">Select Status</option>
                                <option value="active" <?= old('status') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                            <?php if (session()->getFlashdata('validation_errors.status')): ?>
                                <div class="invalid-feedback">
                                    <?= session()->getFlashdata('validation_errors.status') ?>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Choose whether this payment method is currently available.</div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= base_url('admin/settings/payment-methods') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Payment Method
                            </button>
                        </div>

                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Bootstrap form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>

<?= $this->endSection() ?>
