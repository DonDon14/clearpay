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
                            <?= payment_method_dropdown_with_icons('payment_method', null, [
                                'id' => 'modal_payment_method',
                                'required' => 'required'
                            ]) ?>
                            <div class="invalid-feedback" id="modal_payment_method_error"></div>
                        </div>

                        <!-- Payment Instructions (Dynamic) -->
                        <div class="col-12" id="payment_method_instructions" style="display: none;">
                            <div class="alert alert-info" id="payment_instructions_content">
                                <!-- Dynamic content will be loaded here -->
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="modal_proof_of_payment" class="form-label">Proof of Payment</label>
                            <input type="file" class="form-control" id="modal_proof_of_payment" name="proof_of_payment" 
                                   accept="image/jpeg,image/jpg,image/png,application/pdf">
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
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="submitPaymentRequest()">
                    <i class="fas fa-paper-plane me-1"></i>Submit Payment Request
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables for payment instructions
let paymentInstructions = null;
let paymentInstructionsContent = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    paymentInstructions = document.getElementById('payment_method_instructions');
    paymentInstructionsContent = document.getElementById('payment_instructions_content');
    
    // Reset button state when modal is hidden (safety measure)
    const paymentRequestModal = document.getElementById('paymentRequestModal');
    if (paymentRequestModal) {
        paymentRequestModal.addEventListener('hidden.bs.modal', function() {
            const submitBtn = document.querySelector('#paymentRequestModal .btn-primary');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Submit Payment Request';
            }
        });
    }
    
    // Listen for payment method changes
    document.addEventListener('click', function(e) {
        // Check if a payment method item was clicked
        if (e.target.closest('[data-value]') && e.target.closest('[id*="modal_payment_method"]')) {
            setTimeout(handlePaymentMethodChange, 500); // Longer delay to ensure modal is fully rendered
        }
        
        // Also check for button clicks within the payment method dropdown
        if (e.target.closest('[id*="modal_payment_method"]') && e.target.tagName === 'BUTTON') {
            setTimeout(handlePaymentMethodChange, 500);
        }
    });
    
    // Also listen for input changes (backup)
    document.addEventListener('input', function(e) {
        if (e.target.id && e.target.id.includes('modal_payment_method') && e.target.id.includes('input')) {
            handlePaymentMethodChange();
        }
    });
    
    // Listen for requested amount changes to update instructions
    document.getElementById('modal_requested_amount').addEventListener('input', function() {
        handlePaymentMethodChange();
    });
});

// Function to handle payment method changes
function handlePaymentMethodChange() {
    // Check if modal is visible and elements are ready
    const modal = document.getElementById('paymentRequestModal');
    if (!modal || !modal.classList.contains('show')) {
        return;
    }
    
    // Get the selected payment method from the helper dropdown
    const paymentMethodButton = document.querySelector('[id*="modal_payment_method"][id*="button"]');
    const paymentMethodInput = document.querySelector('[id*="modal_payment_method"][id*="input"]');
    const requestedAmount = document.getElementById('modal_requested_amount').value;
    
    let selectedMethod = '';
    
    if (paymentMethodInput && paymentMethodInput.value) {
        selectedMethod = paymentMethodInput.value;
    } else if (paymentMethodButton && paymentMethodButton.textContent) {
        // Extract method name from button text
        const buttonText = paymentMethodButton.textContent.trim();
        
        // Check if it's a placeholder/default text
        if (buttonText === 'Select Payment Method' || buttonText === 'Choose Payment Method' || buttonText === '') {
            selectedMethod = '';
        } else {
            // For now, just use the button text as the method name
            selectedMethod = buttonText;
        }
    }
    
    if (selectedMethod && requestedAmount) {
        showPaymentInstructions(selectedMethod, requestedAmount);
    } else {
        if (paymentInstructions) {
            paymentInstructions.style.display = 'none';
        }
    }
}

// Function to show payment method specific instructions (FULLY DYNAMIC)
function showPaymentInstructions(method, amount) {
    // Check if elements exist
    if (!paymentInstructions || !paymentInstructionsContent) {
        return;
    }
    
    // First, try to get custom instructions from the database
    const apiUrl = `${window.APP_BASE_URL || ''}/admin/settings/payment-methods/instructions/${encodeURIComponent(method)}`;
    
    fetch(apiUrl, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        return response.json();
    })
    .then(data => {
        if (data.success && data.method && data.method.custom_instructions) {
            displayCustomInstructions(data.method, amount);
        } else {
            displayGenericInstructions(method, amount);
        }
    })
    .catch(error => {
        displayGenericInstructions(method, amount);
    });
}

// Function to display custom instructions from database
function displayCustomInstructions(method, amount) {
    let instructions = method.processed_instructions || method.custom_instructions;
    
    // Replace amount placeholder with actual amount
    if (instructions) {
        instructions = instructions.replace(/{amount}/g, (parseFloat(amount) || 0).toFixed(2));
    }
    
    // Check if QR code should be displayed and add it if not present
    if (method.qr_code_path) {
        const hasQRCodeInInstructions = instructions.includes('<img') || 
                                       instructions.includes('qr_code') || 
                                       instructions.includes('QR') ||
                                       instructions.includes('qr-code');
        
        if (!hasQRCodeInInstructions) {
            // Ensure QR code path is properly formatted
            let qrCodeSrc = method.qr_code_path;
            if (!qrCodeSrc.startsWith('http') && !qrCodeSrc.startsWith('/')) {
                qrCodeSrc = '/' + qrCodeSrc;
            }
            
            const qrCodeHtml = `
                <div class="text-center mb-3">
                    <h6><i class="fas fa-qrcode me-2"></i>QR Code</h6>
                    <img src="${qrCodeSrc}" alt="QR Code" class="img-fluid" style="max-width: 200px; max-height: 200px;">
                    <p class="small text-muted mt-2">Scan this QR code to make payment</p>
                </div>
            `;
            instructions = qrCodeHtml + instructions;
        }
    }
    
    if (!instructions || instructions.length === 0) {
        displayGenericInstructions(method.name, amount);
        return;
    }
    
    try {
        paymentInstructionsContent.innerHTML = instructions;
        paymentInstructions.style.display = 'block';
    } catch (error) {
        displayGenericInstructions(method.name, amount);
    }
}

// Function to display generic instructions (fallback for ANY payment method)
function displayGenericInstructions(method, amount) {
    const instructions = `
        <div class="alert alert-info">
            <h6><i class="fas fa-info-circle me-2"></i>${method} Payment Instructions</h6>
            <p class="mb-2">Please prepare the following for ${method} payment:</p>
            <div class="row">
                <div class="col-md-6">
                    <strong>Amount:</strong> ₱${(parseFloat(amount) || 0).toFixed(2)}<br>
                    <strong>Payment Type:</strong> ${method}<br>
                    <strong>Reference:</strong> CP-${Date.now()}
                </div>
                <div class="col-md-6">
                    <strong>Instructions:</strong><br>
                    <ul class="mb-0 small">
                        <li>Follow your preferred ${method} payment method</li>
                        <li>Enter amount: ₱${(parseFloat(amount) || 0).toFixed(2)}</li>
                        <li>Add reference: CP-${Date.now()}</li>
                        <li>Upload proof of payment below</li>
                    </ul>
                </div>
            </div>
        </div>
    `;
    
    try {
        paymentInstructionsContent.innerHTML = instructions;
        paymentInstructions.style.display = 'block';
    } catch (error) {
        // Silently fail - instructions will not be displayed
    }
}

// Make function globally accessible
window.showPaymentInstructions = showPaymentInstructions;

// Open payment request modal function
window.openPaymentRequestModal = function(contribution) {
    const contributionId = contribution.id;
    
    // Fetch fresh contribution details from server to get accurate remaining balance (accounts for refunds)
    fetch(`${window.APP_BASE_URL || ''}payer/get-contribution-details?contribution_id=${contributionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.contribution) {
                // Use fresh data from server
                contribution = data.contribution;
            }
            // Fallback to passed contribution data if fetch fails
            
            // Populate contribution details
            document.getElementById('modal_contribution_title').textContent = contribution.title || 'N/A';
            document.getElementById('modal_contribution_description').textContent = contribution.description || 'N/A';
            document.getElementById('modal_contribution_amount').textContent = '₱' + (parseFloat(contribution.amount || 0)).toFixed(2);
            document.getElementById('modal_remaining_balance').textContent = '₱' + (parseFloat(contribution.remaining_balance || contribution.remaining_amount || contribution.amount || 0)).toFixed(2);
            document.getElementById('modal_max_amount').textContent = '₱' + (parseFloat(contribution.remaining_balance || contribution.remaining_amount || contribution.amount || 0)).toFixed(2);
            
            // Reset form first
            document.getElementById('paymentRequestForm').reset();
            
            // Set hidden fields
            document.getElementById('modal_contribution_id').value = contribution.id || '';
            // Only set payment_sequence if explicitly provided (for adding to existing group)
            // Otherwise leave it empty/null so backend creates a new payment sequence/group
            if (contribution.payment_sequence !== undefined && contribution.payment_sequence !== null && contribution.payment_sequence !== '') {
                document.getElementById('modal_payment_sequence').value = contribution.payment_sequence;
            } else {
                document.getElementById('modal_payment_sequence').value = '';
            }
            
            // Set max amount for input
            const amountInput = document.getElementById('modal_requested_amount');
            if (amountInput) {
                const remainingBalance = parseFloat(contribution.remaining_balance || contribution.remaining_amount || contribution.amount || 0);
                amountInput.max = remainingBalance;
                amountInput.value = remainingBalance.toFixed(2); // Set to remaining balance
            }
            
            // Hide payment instructions initially
            if (paymentInstructions) {
                paymentInstructions.style.display = 'none';
            }
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('paymentRequestModal'));
            modal.show();
            
            // Trigger payment method change after modal is shown to load instructions
            setTimeout(() => {
                handlePaymentMethodChange();
            }, 500);
        })
        .catch(error => {
            // If fetch fails, use the passed contribution data as fallback
            // Populate contribution details
            document.getElementById('modal_contribution_title').textContent = contribution.title || 'N/A';
            document.getElementById('modal_contribution_description').textContent = contribution.description || 'N/A';
            document.getElementById('modal_contribution_amount').textContent = '₱' + (parseFloat(contribution.amount || 0)).toFixed(2);
            document.getElementById('modal_remaining_balance').textContent = '₱' + (parseFloat(contribution.remaining_balance || contribution.amount || 0)).toFixed(2);
            document.getElementById('modal_max_amount').textContent = '₱' + (parseFloat(contribution.remaining_balance || contribution.amount || 0)).toFixed(2);
            
            // Reset form first
            document.getElementById('paymentRequestForm').reset();
            
            // Set hidden fields
            document.getElementById('modal_contribution_id').value = contribution.id || '';
            if (contribution.payment_sequence !== undefined && contribution.payment_sequence !== null && contribution.payment_sequence !== '') {
                document.getElementById('modal_payment_sequence').value = contribution.payment_sequence;
            } else {
                document.getElementById('modal_payment_sequence').value = '';
            }
            
            // Set max amount for input
            const amountInput = document.getElementById('modal_requested_amount');
            if (amountInput) {
                amountInput.max = contribution.remaining_balance || contribution.amount || 0;
                amountInput.value = (parseFloat(contribution.remaining_balance || contribution.amount || 0)).toFixed(2);
            }
            
            // Hide payment instructions initially
            if (paymentInstructions) {
                paymentInstructions.style.display = 'none';
            }
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('paymentRequestModal'));
            modal.show();
            
            // Trigger payment method change after modal is shown to load instructions
            setTimeout(() => {
                handlePaymentMethodChange();
            }, 500);
        });
};

// Copy to clipboard function
window.copyToClipboard = function(elementId) {
    let textToCopy = '';
    
    // Handle special cases for GCash reference
    if (elementId.startsWith('gcash_ref_')) {
        // Extract the reference number from the element's content or generate a new one if needed
        // For now, we'll just generate a new one as the reference is dynamic
        textToCopy = `CP-${Date.now()}`; 
    } else {
        const element = document.getElementById(elementId);
        if (element) {
            textToCopy = element.textContent;
        }
    }
    
    if (textToCopy) {
        navigator.clipboard.writeText(textToCopy).then(() => {
            showNotification('Copied to clipboard!', 'success');
        }).catch(() => {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = textToCopy;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showNotification('Copied to clipboard!', 'success');
        });
    }
};

// Show QR code fullscreen
window.showQRCodeFullscreen = function(imageSrc) {
    // Create a modal for fullscreen QR code
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">QR Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="${imageSrc}" alt="QR Code" class="img-fluid">
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
};

// Show GCash manual transfer
window.showGCashManual = function(amount) {
    alert(`Manual GCash Transfer Instructions:\n\n1. Open GCash app\n2. Tap "Send Money"\n3. Enter amount: ₱${amount}\n4. Add reference: CP-${Date.now()}\n5. Confirm payment`);
};

// Show notification function
function showNotification(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
}

// Submit payment request function
function submitPaymentRequest() {
    const form = document.getElementById('paymentRequestForm');
    const formData = new FormData(form);
    
    // Validate required fields
    const requestedAmount = formData.get('requested_amount');
    const paymentMethod = formData.get('payment_method');
    const contributionId = formData.get('contribution_id');
    
    if (!requestedAmount || !paymentMethod || !contributionId) {
        alert('Please fill in all required fields');
        return;
    }
    
    // Get submit button and show loading state
    const submitBtn = document.querySelector('#paymentRequestModal .btn-primary');
    const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
    }
    
    // Submit the form
    fetch('<?= base_url('payer/submit-payment-request') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Restore button state first
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
        
        if (data.success) {
            showNotification(data.message, 'success');
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('paymentRequestModal'));
            modal.hide();
            // Reset form
            form.reset();
        } else {
            showNotification(data.message || 'Failed to submit payment request', 'error');
        }
    })
    .catch(error => {
        // Restore button on error
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
        showNotification('An error occurred while submitting the payment request', 'error');
    });
}
</script>