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

                        <!-- Hidden contribution ID and payment sequence -->
                        <input type="hidden" id="modal_contribution_id" name="contribution_id">
                        <input type="hidden" id="modal_payment_sequence" name="payment_sequence">

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
                        
                        <!-- Payment Method Specific Instructions -->
                        <div class="col-12" id="payment_method_instructions" style="display: none;">
                            <div class="alert alert-info" id="payment_instructions_content">
                                <!-- Dynamic content will be inserted here -->
                            </div>
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
    const paymentMethodSelect = document.getElementById('modal_payment_method');
    const paymentInstructions = document.getElementById('payment_method_instructions');
    const paymentInstructionsContent = document.getElementById('payment_instructions_content');
    
    // Function to open payment request modal with contribution data
    window.openPaymentRequestModal = function(contribution) {
        // Populate contribution details
        document.getElementById('modal_contribution_id').value = contribution.id;
        document.getElementById('modal_contribution_title').textContent = contribution.title;
        document.getElementById('modal_contribution_description').textContent = contribution.description;
        document.getElementById('modal_contribution_amount').textContent = '₱' + parseFloat(contribution.amount).toLocaleString('en-US', {minimumFractionDigits: 2});
        
        // Set payment sequence if provided (for payment groups)
        if (contribution.payment_sequence) {
            document.getElementById('modal_payment_sequence').value = contribution.payment_sequence;
        } else {
            document.getElementById('modal_payment_sequence').value = '';
        }
        
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
    
    // Payment method handler
    paymentMethodSelect.addEventListener('change', function() {
        const selectedMethod = this.value;
        const requestedAmount = document.getElementById('modal_requested_amount').value;
        
        if (selectedMethod) {
            showPaymentInstructions(selectedMethod, requestedAmount);
        } else {
            paymentInstructions.style.display = 'none';
        }
    });
    
    // Function to show payment method specific instructions
    function showPaymentInstructions(method, amount) {
        let instructions = '';
        
        switch(method) {
            case 'gcash':
                instructions = `
                    <div class="row">
                        <div class="col-12">
                            <h6><i class="fas fa-qrcode me-2"></i>GCash QR Code 
                                <button type="button" class="btn btn-link btn-sm p-0 ms-2" data-bs-toggle="collapse" data-bs-target="#gcashInstructions" aria-expanded="false" aria-controls="gcashInstructions" title="Payment Instructions">
                                    <i class="fas fa-info-circle text-info"></i>
                                </button>
                            </h6>
                            <h6 class="text-center">Use the QR code above to pay your contribution.</h6>
                            <div class="text-center mb-3">
                                <img src="/images/gcashcodesample.png" alt="GCash QR Code" style="width: 200px; height: 400px; cursor: pointer;" class="img-fluid border rounded" onclick="showQRCodeFullscreen('/images/gcashcodesample.png')" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" title="Click to view full screen">
                            </div>
                            
                            <!-- Collapsible Instructions -->
                            <div class="collapse" id="gcashInstructions">
                                <div class="card card-body mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="alert alert-info mb-2">
                                                <h6 class="mb-2"><i class="fas fa-mobile-alt me-1"></i>How to Pay</h6>
                                                <ol class="mb-0 small">
                                                    <li>Open GCash app</li>
                                                    <li>Tap "Scan QR"</li>
                                                    <li>Scan the QR code</li>
                                                    <li>Enter amount: <strong>₱${amount}</strong></li>
                                                    <li>Add reference: <strong>CP-${Date.now()}</strong></li>
                                                    <li>Confirm payment</li>
                                                </ol>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="alert alert-warning mb-2">
                                                <h6 class="mb-2"><i class="fas fa-mobile-alt me-1"></i>Single Device</h6>
                                                <ol class="mb-0 small">
                                                    <li>Take a screenshot</li>
                                                    <li>Open GCash app</li>
                                                    <li>Tap "Scan QR" → "From Gallery"</li>
                                                    <li>Select screenshot</li>
                                                    <li>Enter amount: <strong>₱${amount}</strong></li>
                                                    <li>Confirm payment</li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="showGCashManual('${amount}')">
                                    <i class="fas fa-hand-holding-usd me-1"></i>Manual Transfer Instead
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'paymaya':
                instructions = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-mobile-alt me-2"></i>PayMaya Payment Options</h6>
                            <div class="mb-3">
                                <strong>Option 1: QR Code Payment</strong>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="showPayMayaQR('${amount}')">
                                        <i class="fas fa-qrcode me-1"></i>Show QR Code
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <strong>Option 2: Manual Transfer</strong>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="showPayMayaManual('${amount}')">
                                        <i class="fas fa-hand-holding-usd me-1"></i>Manual Transfer Details
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-info-circle me-2"></i>Single Device Solution</h6>
                                <p class="mb-1">If using the same device:</p>
                                <ul class="mb-0">
                                    <li>Take a screenshot of the QR code</li>
                                    <li>Open PayMaya app</li>
                                    <li>Go to "Scan QR" → "From Gallery"</li>
                                    <li>Select the screenshot</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'bank_transfer':
                instructions = `
                    <div class="alert alert-info">
                        <h6><i class="fas fa-university me-2"></i>Bank Transfer Instructions</h6>
                        <p class="mb-2">Please transfer the amount to:</p>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Bank:</strong> BDO<br>
                                <strong>Account Name:</strong> ClearPay School<br>
                                <strong>Account Number:</strong> 1234567890
                            </div>
                            <div class="col-md-6">
                                <strong>Reference:</strong> <span id="bank_reference">CP-${Date.now()}</span><br>
                                <button type="button" class="btn btn-outline-primary btn-sm mt-1" onclick="copyToClipboard('bank_reference')">
                                    <i class="fas fa-copy me-1"></i>Copy Reference
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'online':
                instructions = `
                    <div class="alert alert-info">
                        <h6><i class="fas fa-laptop me-2"></i>Online Banking Instructions</h6>
                        <p class="mb-2">Please use your online banking to transfer the amount to:</p>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Bank:</strong> BDO<br>
                                <strong>Account Name:</strong> ClearPay School<br>
                                <strong>Account Number:</strong> 1234567890
                            </div>
                            <div class="col-md-6">
                                <strong>Reference:</strong> <span id="online_reference">CP-${Date.now()}</span><br>
                                <button type="button" class="btn btn-outline-primary btn-sm mt-1" onclick="copyToClipboard('online_reference')">
                                    <i class="fas fa-copy me-1"></i>Copy Reference
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                break;
        }
        
        paymentInstructionsContent.innerHTML = instructions;
        paymentInstructions.style.display = 'block';
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

// Global functions for payment methods
window.showGCashQR = function(amount) {
    const modalHtml = `
        <div class="modal fade" id="gcashQRModal" tabindex="-1" aria-labelledby="gcashQRModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="gcashQRModalLabel">
                            <i class="fas fa-qrcode me-2"></i>GCash QR Code Payment
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">QR Code</h6>
                                        <div class="qr-code-placeholder bg-light p-4 mb-3" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                                            <div class="text-muted">
                                                <i class="fas fa-qrcode fa-3x mb-2"></i><br>
                                                QR Code for ₱${amount}
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="viewQRCode('images/gcashcodesample.png')">
                                            <i class="fas fa-qrcode me-1"></i>Show QR Code
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">Payment Details</h6>
                                        <div class="text-start">
                                            <p><strong>Amount:</strong> ₱${amount}</p>
                                            <p><strong>Recipient:</strong> ClearPay School</p>
                                            <p><strong>Reference:</strong> CP-${Date.now()}</p>
                                        </div>
                                        <div class="alert alert-info mt-3">
                                            <h6><i class="fas fa-mobile-alt me-2"></i>Single Device Instructions</h6>
                                            <ol class="mb-0 text-start">
                                                <li>Take a screenshot of the QR code</li>
                                                <li>Open GCash app</li>
                                                <li>Tap "Scan QR"</li>
                                                <li>Select "From Gallery"</li>
                                                <li>Choose the screenshot</li>
                                                <li>Confirm payment</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="showGCashManual('${amount}')">
                            <i class="fas fa-hand-holding-usd me-1"></i>Manual Transfer Instead
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('gcashQRModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('gcashQRModal'));
    modal.show();
    
    // Clean up modal when hidden
    document.getElementById('gcashQRModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
};

window.showGCashManual = function(amount) {
    const modalHtml = `
        <div class="modal fade" id="gcashManualModal" tabindex="-1" aria-labelledby="gcashManualModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="gcashManualModalLabel">
                            <i class="fas fa-hand-holding-usd me-2"></i>GCash Manual Transfer
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Manual Transfer Instructions</h6>
                            <p>Please follow these steps to complete your payment:</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Transfer Details</h6>
                                <div class="card">
                                    <div class="card-body">
                                        <p><strong>Amount:</strong> ₱${amount}</p>
                                        <p><strong>Recipient:</strong> ClearPay School</p>
                                        <p><strong>GCash Number:</strong> <span id="gcash_number">09123456789</span></p>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="copyToClipboard('gcash_number')">
                                            <i class="fas fa-copy me-1"></i>Copy Number
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Steps to Follow</h6>
                                <ol>
                                    <li>Open GCash app</li>
                                    <li>Tap "Send Money"</li>
                                    <li>Enter the GCash number above</li>
                                    <li>Enter amount: ₱${amount}</li>
                                    <li>Add reference: CP-${Date.now()}</li>
                                    <li>Confirm and send</li>
                                </ol>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mt-3">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Important</h6>
                            <p class="mb-0">Please use the exact reference number above for proper tracking of your payment.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('gcashManualModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('gcashManualModal'));
    modal.show();
    
    // Clean up modal when hidden
    document.getElementById('gcashManualModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
};

window.showPayMayaQR = function(amount) {
    // Use the same QR code viewer for PayMaya
    viewQRCode('images/paymayacodesample.png');
};

window.showPayMayaManual = function(amount) {
    const modalHtml = `
        <div class="modal fade" id="paymayaManualModal" tabindex="-1" aria-labelledby="paymayaManualModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="paymayaManualModalLabel">
                            <i class="fas fa-hand-holding-usd me-2"></i>PayMaya Manual Transfer
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Manual Transfer Instructions</h6>
                            <p>Please follow these steps to complete your payment:</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Transfer Details</h6>
                                <div class="card">
                                    <div class="card-body">
                                        <p><strong>Amount:</strong> ₱${amount}</p>
                                        <p><strong>Recipient:</strong> ClearPay School</p>
                                        <p><strong>PayMaya Number:</strong> <span id="paymaya_number">09123456789</span></p>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="copyToClipboard('paymaya_number')">
                                            <i class="fas fa-copy me-1"></i>Copy Number
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Steps to Follow</h6>
                                <ol>
                                    <li>Open PayMaya app</li>
                                    <li>Tap "Send Money"</li>
                                    <li>Enter the PayMaya number above</li>
                                    <li>Enter amount: ₱${amount}</li>
                                    <li>Add reference: CP-${Date.now()}</li>
                                    <li>Confirm and send</li>
                                </ol>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mt-3">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Important</h6>
                            <p class="mb-0">Please use the exact reference number above for proper tracking of your payment.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="showPayMayaQR('${amount}')">
                            <i class="fas fa-qrcode me-1"></i>Use QR Code Instead
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('paymayaManualModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('paymayaManualModal'));
    modal.show();
    
    // Clean up modal when hidden
    document.getElementById('paymayaManualModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
};

window.copyToClipboard = function(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        navigator.clipboard.writeText(element.textContent).then(() => {
            showNotification('Copied to clipboard!', 'success');
        }).catch(() => {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = element.textContent;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showNotification('Copied to clipboard!', 'success');
        });
    }
};

// Function to show QR code in fullscreen
window.showQRCodeFullscreen = function(imagePath) {
    const modalHtml = `
        <div class="modal fade" id="qrCodeFullscreenModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content bg-dark">
                    <div class="modal-header border-0 justify-content-end">
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body d-flex align-items-center justify-content-center">
                        <div class="text-center">
                            <img src="${imagePath}" alt="QR Code Fullscreen" class="img-fluid" style="max-width: 90vw; max-height: 90vh;">
                            <div class="mt-3">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i>Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('qrCodeFullscreenModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('qrCodeFullscreenModal'));
    modal.show();
    
    // Clean up modal when hidden
    document.getElementById('qrCodeFullscreenModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
};


// Function to view QR code with the actual image
window.viewQRCode = function(imagePath) {
    const modalHtml = `
        <div class="modal fade" id="qrCodeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header justify-content-end">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center p-4">
                        <img src="${imagePath}" alt="QR Code" style="width: 250px; height: 250px;" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('qrCodeModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('qrCodeModal'));
    modal.show();
    
    // Clean up modal when hidden
    document.getElementById('qrCodeModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
};

// Keep the old function for backward compatibility but update it to use viewQRCode
window.generateQRCode = function(method, amount) {
    const qrPlaceholder = document.querySelector('.qr-code-placeholder');
    if (qrPlaceholder) {
        // Show the actual static GCash QR code
        qrPlaceholder.innerHTML = showStaticGCashQR(amount);
    }
};

function showStaticGCashQR(amount) {
    return `
        <div class="text-center">
            <div class="bg-white p-3 rounded shadow-sm mb-3">
                <img src="images/gcashcodesample.png" alt="GCash QR Code" style="width: 200px; height: 200px;" class="img-fluid">
            </div>
            <div class="alert alert-success">
                <h6><i class="fas fa-qrcode me-2"></i>GCash QR Code</h6>
                <p class="mb-1"><strong>Recipient:</strong> ClearPay School</p>
                <p class="mb-1"><strong>GCash Number:</strong> 09123456789</p>
                <p class="mb-0"><strong>Amount to Pay:</strong> ₱${amount}</p>
            </div>
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle me-2"></i>How to Pay</h6>
                <ol class="mb-0 text-start">
                    <li>Open GCash app</li>
                    <li>Tap "Scan QR"</li>
                    <li>Scan this QR code</li>
                    <li>Enter amount: <strong>₱${amount}</strong></li>
                    <li>Add reference: <strong>CP-${Date.now()}</strong></li>
                    <li>Confirm payment</li>
                </ol>
            </div>
            <div class="alert alert-warning">
                <h6><i class="fas fa-mobile-alt me-2"></i>Single Device Solution</h6>
                <ol class="mb-0 text-start">
                    <li>Take a screenshot of this QR code</li>
                    <li>Open GCash app</li>
                    <li>Tap "Scan QR" → "From Gallery"</li>
                    <li>Select the screenshot</li>
                    <li>Enter amount: <strong>₱${amount}</strong></li>
                    <li>Add reference: <strong>CP-${Date.now()}</strong></li>
                    <li>Confirm payment</li>
                </ol>
            </div>
        </div>
    `;
}
</script>
