<!-- Payment Request Modal -->
<div class="modal fade" id="paymentRequestModal" tabindex="-1" aria-labelledby="paymentRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="paymentRequestModalLabel">
                    <i class="fas fa-paper-plane me-2"></i>Submit Payment Request
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentRequestForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <!-- Contribution Info (Read-only) -->
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Contribution Details</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Contribution:</strong> <span id="modal_contribution_title">-</span><br>
                                        <strong>Description:</strong> <span id="modal_contribution_description">-</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Total Amount:</strong> <span id="modal_contribution_amount">₱0.00</span><br>
                                        <strong>Remaining Balance:</strong> <span id="modal_remaining_balance" class="text-warning">₱0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden contribution ID -->
                        <input type="hidden" id="modal_contribution_id" name="contribution_id">

                        <div class="col-md-6">
                            <label for="modal_requested_amount" class="form-label">Requested Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="modal_requested_amount" name="requested_amount" 
                                       step="0.01" min="0" required>
                            </div>
                            <div class="form-text">Maximum: <span id="modal_max_amount">₱0.00</span></div>
                            <div class="invalid-feedback" id="modal_requested_amount_error"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="modal_payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select" id="modal_payment_method" name="payment_method" required>
                                <option value="">Select Payment Method</option>
                                <option value="online">Online Banking</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="gcash">GCash</option>
                                <option value="paymaya">PayMaya</option>
                            </select>
                            <div class="invalid-feedback" id="modal_payment_method_error"></div>
                        </div>
                        
                        <div class="col-12">
                            <label for="modal_proof_of_payment" class="form-label">Proof of Payment</label>
                            <input type="file" class="form-control" id="modal_proof_of_payment" name="proof_of_payment" 
                                   accept="image/*,.pdf">
                            <div class="form-text">Upload screenshot or receipt (JPG, PNG, PDF)</div>
                            <div class="invalid-feedback" id="modal_proof_of_payment_error"></div>
                        </div>
                        
                        <div class="col-12">
                            <label for="modal_notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="modal_notes" name="notes" rows="3" 
                                      placeholder="Any additional information about your payment..."></textarea>
                            <div class="form-text">Optional: Add any relevant details about your payment</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="modal_submitBtn">
                    <i class="fas fa-paper-plane me-2"></i>Submit Payment Request
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Payment Request Modal JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const paymentRequestModal = document.getElementById('paymentRequestModal');
    const paymentRequestForm = document.getElementById('paymentRequestForm');
    const submitBtn = document.getElementById('modal_submitBtn');
    
    // Function to open payment request modal with contribution data
    window.openPaymentRequestModal = function(contribution) {
        // Populate contribution details
        document.getElementById('modal_contribution_id').value = contribution.id;
        document.getElementById('modal_contribution_title').textContent = contribution.title;
        document.getElementById('modal_contribution_description').textContent = contribution.description;
        document.getElementById('modal_contribution_amount').textContent = '₱' + parseFloat(contribution.amount).toLocaleString('en-US', {minimumFractionDigits: 2});
        
        const remainingBalance = parseFloat(contribution.remaining_balance || contribution.amount);
        document.getElementById('modal_remaining_balance').textContent = '₱' + remainingBalance.toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('modal_max_amount').textContent = '₱' + remainingBalance.toLocaleString('en-US', {minimumFractionDigits: 2});
        
        // Set max amount for requested amount input
        const requestedAmountInput = document.getElementById('modal_requested_amount');
        requestedAmountInput.max = remainingBalance;
        requestedAmountInput.value = remainingBalance; // Pre-fill with remaining balance
        
        // Clear form validation
        clearFormValidation();
        
        // Show modal
        const modal = new bootstrap.Modal(paymentRequestModal);
        modal.show();
    };
    
    // Function to clear form validation
    function clearFormValidation() {
        const form = paymentRequestForm;
        form.classList.remove('was-validated');
        
        // Clear all error messages
        const errorElements = form.querySelectorAll('.invalid-feedback');
        errorElements.forEach(element => {
            element.textContent = '';
            element.style.display = 'none';
        });
        
        // Remove invalid classes
        const inputs = form.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            input.classList.remove('is-invalid');
        });
    }
    
    // Handle form submission
    submitBtn.addEventListener('click', function() {
        if (paymentRequestForm.checkValidity()) {
            submitPaymentRequest();
        } else {
            paymentRequestForm.classList.add('was-validated');
        }
    });
    
    // Function to submit payment request
    function submitPaymentRequest() {
        const formData = new FormData(paymentRequestForm);
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
        
        fetch('<?= base_url('payer/submit-payment-request') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showNotification(data.message, 'success');
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(paymentRequestModal);
                modal.hide();
                
                // Reset form
                paymentRequestForm.reset();
                clearFormValidation();
                
                // Refresh payment requests page if we're on it
                if (typeof loadPaymentRequests === 'function') {
                    loadPaymentRequests();
                }
            } else {
                // Show error message
                showNotification(data.message, 'error');
                
                // Show validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorElement = document.getElementById(`modal_${field}_error`);
                        if (errorElement) {
                            errorElement.textContent = data.errors[field];
                            errorElement.style.display = 'block';
                            const inputElement = document.getElementById(`modal_${field}`);
                            if (inputElement) {
                                inputElement.classList.add('is-invalid');
                            }
                        }
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while submitting the payment request', 'error');
        })
        .finally(() => {
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Payment Request';
        });
    }
    
    // Validate requested amount
    document.getElementById('modal_requested_amount').addEventListener('input', function() {
        const maxAmount = parseFloat(this.max);
        const requestedAmount = parseFloat(this.value);
        
        if (requestedAmount > maxAmount) {
            this.setCustomValidity(`Amount cannot exceed ₱${maxAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}`);
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Reset form when modal is hidden
    paymentRequestModal.addEventListener('hidden.bs.modal', function() {
        paymentRequestForm.reset();
        clearFormValidation();
    });
});

// Notification function (if not already defined)
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}
</script>
