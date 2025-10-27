<?= $this->extend('layouts/payer-layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Payment Requests</h5>
                </div>
                <div class="card-body">
                    <!-- Payment Request Form -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Submit New Payment Request</h6>
                                </div>
                                <div class="card-body">
                                    <form id="paymentRequestForm" enctype="multipart/form-data">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="contribution_id" class="form-label">Contribution Type <span class="text-danger">*</span></label>
                                                <select class="form-select" id="contribution_id" name="contribution_id" required>
                                                    <option value="">Select Contribution</option>
                                                    <?php foreach ($contributions as $contribution): ?>
                                                        <option value="<?= $contribution['id'] ?>" 
                                                                data-amount="<?= $contribution['amount'] ?>"
                                                                data-description="<?= esc($contribution['description']) ?>">
                                                            <?= esc($contribution['title']) ?> - ₱<?= number_format($contribution['amount'], 2) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="invalid-feedback" id="contribution_id_error"></div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="requested_amount" class="form-label">Requested Amount <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">₱</span>
                                                    <input type="number" class="form-control" id="requested_amount" name="requested_amount" 
                                                           step="0.01" min="0" required>
                                                </div>
                                                <div class="form-text" id="amount_info">Select a contribution first</div>
                                                <div class="invalid-feedback" id="requested_amount_error"></div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                                <select class="form-select" id="payment_method" name="payment_method" required>
                                                    <option value="">Select Payment Method</option>
                                                    <option value="online">Online Banking</option>
                                                    <option value="bank_transfer">Bank Transfer</option>
                                                    <option value="gcash">GCash</option>
                                                    <option value="paymaya">PayMaya</option>
                                                </select>
                                                <div class="invalid-feedback" id="payment_method_error"></div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="proof_of_payment" class="form-label">Proof of Payment</label>
                                                <input type="file" class="form-control" id="proof_of_payment" name="proof_of_payment" 
                                                       accept="image/*,.pdf">
                                                <div class="form-text">Upload screenshot or receipt (JPG, PNG, PDF)</div>
                                                <div class="invalid-feedback" id="proof_of_payment_error"></div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <label for="notes" class="form-label">Additional Notes</label>
                                                <textarea class="form-control" id="notes" name="notes" rows="3" 
                                                          placeholder="Any additional information about your payment..."></textarea>
                                                <div class="form-text">Optional: Add any relevant details about your payment</div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                                    <i class="fas fa-paper-plane me-2"></i>Submit Payment Request
                                                </button>
                                                <button type="reset" class="btn btn-secondary ms-2">
                                                    <i class="fas fa-undo me-2"></i>Reset Form
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Requests History -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-3"><i class="fas fa-history me-2"></i>Your Payment Requests</h6>
                            
                            <?php if (empty($paymentRequests)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">No Payment Requests</h6>
                                    <p class="text-muted">Your submitted payment requests will appear here</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Contribution</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                                <th>Status</th>
                                                <th>Reference</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($paymentRequests as $request): ?>
                                                <tr>
                                                    <td><?= date('M d, Y', strtotime($request['requested_at'])) ?></td>
                                                    <td><?= esc($request['contribution_title']) ?></td>
                                                    <td><strong>₱<?= number_format($request['requested_amount'], 2) ?></strong></td>
                                                    <td><?= esc(ucfirst(str_replace('_', ' ', $request['payment_method']))) ?></td>
                                                    <td>
                                                        <?php
                                                        $statusClass = match($request['status']) {
                                                            'pending' => 'bg-warning text-dark',
                                                            'approved' => 'bg-success text-white',
                                                            'rejected' => 'bg-danger text-white',
                                                            'processed' => 'bg-primary text-white',
                                                            default => 'bg-secondary text-white'
                                                        };
                                                        $statusText = match($request['status']) {
                                                            'pending' => 'PENDING',
                                                            'approved' => 'APPROVED',
                                                            'rejected' => 'REJECTED',
                                                            'processed' => 'PROCESSED',
                                                            default => strtoupper($request['status'])
                                                        };
                                                        ?>
                                                        <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                                    </td>
                                                    <td><code><?= esc($request['reference_number']) ?></code></td>
                                                    <td>
                                                        <?php if ($request['proof_of_payment_path']): ?>
                                                            <button class="btn btn-sm btn-outline-info" 
                                                                    onclick="viewProofOfPayment('<?= base_url($request['proof_of_payment_path']) ?>')">
                                                                <i class="fas fa-eye me-1"></i>View Proof
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($request['admin_notes']): ?>
                                                            <button class="btn btn-sm btn-outline-secondary" 
                                                                    onclick="viewAdminNotes('<?= esc($request['admin_notes']) ?>')">
                                                                <i class="fas fa-comment me-1"></i>Admin Notes
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Proof of Payment Modal -->
<div class="modal fade" id="proofModal" tabindex="-1" aria-labelledby="proofModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="proofModalLabel">Proof of Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="proofImage" src="" alt="Proof of Payment" class="img-fluid" style="max-height: 500px;">
            </div>
        </div>
    </div>
</div>

<!-- Admin Notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notesModalLabel">Admin Notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="notesContent"></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contributionSelect = document.getElementById('contribution_id');
    const amountInput = document.getElementById('requested_amount');
    const amountInfo = document.getElementById('amount_info');
    const form = document.getElementById('paymentRequestForm');
    const submitBtn = document.getElementById('submitBtn');

    // Handle contribution selection
    contributionSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const amount = parseFloat(selectedOption.dataset.amount);
            const description = selectedOption.dataset.description;
            
            amountInput.max = amount;
            amountInput.placeholder = `Max: ₱${amount.toFixed(2)}`;
            amountInfo.innerHTML = `
                <strong>${selectedOption.textContent}</strong><br>
                <small class="text-muted">${description}</small>
            `;
        } else {
            amountInput.max = '';
            amountInput.placeholder = '';
            amountInfo.textContent = 'Select a contribution first';
        }
    });

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        clearErrors();
        
        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
        
        // Create FormData
        const formData = new FormData(form);
        
        // Submit via AJAX
        console.log('Submitting payment request...');
        fetch('<?= base_url('payer/submit-payment-request') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            console.log('Response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            if (data.success) {
                // Show success message
                showAlert('success', data.message);
                
                // Reset form
                form.reset();
                amountInfo.textContent = 'Select a contribution first';
                
                // Reload page to show updated requests
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                // Show error message
                showAlert('danger', data.message);
                
                // Show validation errors
                if (data.errors) {
                    showValidationErrors(data.errors);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred. Please try again.');
        })
        .finally(() => {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Payment Request';
        });
    });
});

function clearErrors() {
    // Remove invalid classes
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    
    // Clear error messages
    document.querySelectorAll('[id$="_error"]').forEach(el => {
        el.textContent = '';
    });
}

function showValidationErrors(errors) {
    Object.keys(errors).forEach(field => {
        const input = document.getElementById(field);
        const errorDiv = document.getElementById(field + '_error');
        
        if (input && errorDiv) {
            input.classList.add('is-invalid');
            errorDiv.textContent = errors[field];
        }
    });
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at the top of the card body
    const cardBody = document.querySelector('.card-body');
    cardBody.insertBefore(alertDiv, cardBody.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

function viewProofOfPayment(imagePath) {
    const modal = new bootstrap.Modal(document.getElementById('proofModal'));
    document.getElementById('proofImage').src = imagePath;
    modal.show();
}

function viewAdminNotes(notes) {
    const modal = new bootstrap.Modal(document.getElementById('notesModal'));
    document.getElementById('notesContent').textContent = notes;
    modal.show();
}
</script>

<?= $this->endSection() ?>
