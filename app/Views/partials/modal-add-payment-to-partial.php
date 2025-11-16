<?php
// Set default values for variables
$title = $title ?? 'Additional Payment';
$action = $action ?? base_url('payments/save');
?>

<!-- Additional Payment Modal -->
<div class="modal fade" id="addPaymentToPartialModal" tabindex="-1" aria-labelledby="addPaymentToPartialModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="addPaymentToPartialModalLabel">
                    <i class="fas fa-money-bill-wave me-2"></i><?= $title ?>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body">
        <!-- Payment Summary -->
        <div class="alert alert-info mb-4">
          <div class="d-flex justify-content-between align-items-center">
            <div>
                            <h6 class="alert-heading mb-1">Payment Summary</h6>
              <p class="mb-0"><strong>Payer:</strong> <span id="summaryPayerName">-</span></p>
              <p class="mb-0"><strong>Contribution:</strong> <span id="summaryContribution">-</span></p>
              <p class="mb-0"><strong>Total Amount:</strong> <span id="summaryTotalAmount" class="text-primary">-</span></p>
                            <p class="mb-0"><strong>Amount Paid:</strong> <span id="summaryAmountPaid" class="text-success">-</span></p>
            </div>
            <div class="text-end">
              <h6 class="text-muted mb-1">Remaining Balance</h6>
              <h3 class="text-danger mb-0" id="summaryRemainingBalance">₱0.00</h3>
            </div>
          </div>
        </div>

                <form id="addPaymentToPartialForm" action="<?= $action ?>" method="POST">
                    <!-- Hidden fields -->
                    <input type="hidden" id="partialPayerId" name="payer_id">
                    <input type="hidden" id="partialPayerName" name="payer_name">
          <input type="hidden" id="partialContributionId" name="contribution_id">
                    <input type="hidden" id="partialPaymentDate" name="payment_date" value="<?= get_current_datetime('Y-m-d H:i:s') ?>">
          
          <!-- Amount to Pay -->
          <div class="mb-3">
                        <label for="partialAmountPaid" class="form-label">Amount to Pay <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="partialAmountPaid" name="amount_paid" step="0.01" min="0.01" required>
                            <button type="button" class="btn btn-outline-success" id="partialFullyPaidBtn" title="Fill with remaining balance">
                                <i class="fas fa-check-circle"></i> Fully Paid
                            </button>
            </div>
                        <small class="text-muted">Maximum: <span id="maxAmount" class="fw-bold text-danger"></span></small>
          </div>

          <!-- Payment Method -->
          <div class="mb-3">
                        <label for="partialPaymentMethod" class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <?= payment_method_dropdown_with_icons('payment_method', null, [
                            'id' => 'partialPaymentMethod',
                            'required' => 'required'
                        ]) ?>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-1"></i>Cancel
        </button>
                <button type="button" class="btn btn-primary" onclick="submitPartialPayment()">
                    <i class="fas fa-save me-1"></i>Save Payment
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Store current payment data (isolated from main modal)
let currentPartialPaymentData = null;

// Function to open additional payment modal
function openAddPaymentToPartialModal(payment) {
    // Validate payment data
    if (!payment) {
        alert('No payment data provided');
        return;
    }
    
    if (!payment.id || !payment.contribution_id || !payment.payer_name) {
        alert('Missing required payment data');
        return;
    }
    
    currentPartialPaymentData = payment;
    
    // Show modal first
    const modal = new bootstrap.Modal(document.getElementById('addPaymentToPartialModal'));
    modal.show();
    
    // Wait for modal to be shown before populating
    modal._element.addEventListener('shown.bs.modal', function() {
        // Update modal title with payer name and contribution group
        const groupText = payment.payment_sequence ? ` - Group ${payment.payment_sequence}` : '';
        const titleElement = document.getElementById('addPaymentToPartialModalLabel');
        if (titleElement) {
            titleElement.innerHTML = `<i class="fas fa-money-bill-wave me-2"></i>Additional Payment for ${payment.payer_name}${groupText}`;
        }
    
    // Populate summary
        const summaryPayerName = document.getElementById('summaryPayerName');
        const summaryContribution = document.getElementById('summaryContribution');
        const summaryTotalAmount = document.getElementById('summaryTotalAmount');
        const summaryAmountPaid = document.getElementById('summaryAmountPaid');
        const summaryRemainingBalance = document.getElementById('summaryRemainingBalance');
        
        if (summaryPayerName) summaryPayerName.textContent = payment.payer_name || 'N/A';
        if (summaryContribution) summaryContribution.textContent = payment.contribution_title || 'N/A';
        if (summaryTotalAmount) summaryTotalAmount.textContent = '₱' + parseFloat(payment.contribution_amount || 0).toFixed(2);
        if (summaryAmountPaid) summaryAmountPaid.textContent = '₱' + parseFloat(payment.total_paid || 0).toFixed(2);
        if (summaryRemainingBalance) {
    const remainingBalance = parseFloat(payment.remaining_balance || 0);
            summaryRemainingBalance.textContent = '₱' + remainingBalance.toFixed(2);
            // Store original balance for real-time calculation
            summaryRemainingBalance.dataset.originalBalance = remainingBalance;
        }
    
    // Set max amount
        const remainingBalance = parseFloat(payment.remaining_balance || 0);
        const maxAmountElement = document.getElementById('maxAmount');
        const amountPaidElement = document.getElementById('partialAmountPaid');
        
        if (maxAmountElement) maxAmountElement.textContent = '₱' + remainingBalance.toFixed(2);
        if (amountPaidElement) {
            amountPaidElement.max = remainingBalance;
        }
        
        // CRITICAL: Populate hidden fields with multiple attempts
        // Try multiple times to ensure elements exist
        setTimeout(() => {
            const payerIdElement = document.getElementById('partialPayerId');
            const payerNameElement = document.getElementById('partialPayerName');
            const contributionIdElement = document.getElementById('partialContributionId');
            
            if (payerIdElement) {
                payerIdElement.value = payment.id || '';
            }
            
            if (payerNameElement) {
                payerNameElement.value = payment.payer_name || '';
            }
            
            if (contributionIdElement) {
                contributionIdElement.value = payment.contribution_id || '';
            }
        }, 100);
        
        // Backup population attempt after longer delay
        setTimeout(() => {
            const payerIdElement = document.getElementById('partialPayerId');
            const payerNameElement = document.getElementById('partialPayerName');
            const contributionIdElement = document.getElementById('partialContributionId');
            
            if (payerIdElement && !payerIdElement.value) {
                payerIdElement.value = payment.id || '';
            }
            
            if (payerNameElement && !payerNameElement.value) {
                payerNameElement.value = payment.payer_name || '';
            }
            
            if (contributionIdElement && !contributionIdElement.value) {
                contributionIdElement.value = payment.contribution_id || '';
            }
        }, 500);
        
        // Reset form fields
        if (amountPaidElement) amountPaidElement.value = '';
        
        const paymentMethodElement = document.getElementById('partialPaymentMethod');
        if (paymentMethodElement) paymentMethodElement.value = '';
    }, { once: true });
}

// Function to submit additional payment (isolated from main modal)
function submitPartialPayment() {
    // CRITICAL: Ensure hidden fields are populated before form submission
    if (currentPartialPaymentData) {
        const payerIdElement = document.getElementById('partialPayerId');
        const payerNameElement = document.getElementById('partialPayerName');
        const contributionIdElement = document.getElementById('partialContributionId');
        
        if (payerIdElement) {
            payerIdElement.value = currentPartialPaymentData.id || '';
        }
        
        if (payerNameElement) {
            payerNameElement.value = currentPartialPaymentData.payer_name || '';
        }
        
        if (contributionIdElement) {
            contributionIdElement.value = currentPartialPaymentData.contribution_id || '';
        }
        
        // Force DOM update
        payerIdElement?.dispatchEvent(new Event('input', { bubbles: true }));
        payerNameElement?.dispatchEvent(new Event('input', { bubbles: true }));
        contributionIdElement?.dispatchEvent(new Event('input', { bubbles: true }));
    }
    
    // Create FormData AFTER populating fields
    const form = document.getElementById('addPaymentToPartialForm');
    const formData = new FormData(form);
    
    // MANUALLY ADD REQUIRED FIELDS TO FORMDATA
    if (currentPartialPaymentData) {
        formData.set('payer_id', currentPartialPaymentData.id || '');
        formData.set('payer_name', currentPartialPaymentData.payer_name || '');
        formData.set('contribution_id', currentPartialPaymentData.contribution_id || '');
    }
    
    // Validate required fields
    const amountPaid = parseFloat(formData.get('amount_paid')) || 0;
    let paymentMethod = formData.get('payment_method');
    
    // Additional validation for payment method helper
    const paymentMethodInput = document.getElementById('partialPaymentMethod_input');
    if (paymentMethodInput && paymentMethodInput.value) {
        paymentMethod = paymentMethodInput.value;
        formData.set('payment_method', paymentMethod);
    } else {
        // Try alternative IDs that the helper might be using
        const altInput = document.querySelector('input[name="payment_method"]');
        const altButton = document.querySelector('button[id*="paymentMethod"]');
        
        if (altInput && altInput.value) {
            paymentMethod = altInput.value;
            formData.set('payment_method', paymentMethod);
        } else {
            // Try to get the selected text from the button
            const altButton = document.querySelector('button[id*="paymentMethod"]');
            if (altButton && altButton.textContent && altButton.textContent !== 'Select Payment Method') {
                // Extract payment method name from button text
                const buttonText = altButton.textContent.trim();
                const paymentMethodName = buttonText.split(' ')[0]; // Get first word (e.g., "BPI" from "BPI Bank")
                paymentMethod = paymentMethodName;
                formData.set('payment_method', paymentMethod);
            }
        }
    }
    
    if (!amountPaid || !paymentMethod) {
        alert('Please fill in all required fields');
        return;
    }
    
    // Calculate remaining balance
    const remainingBalance = parseFloat(document.getElementById('summaryRemainingBalance').textContent.replace('₱', ''));
    const newRemaining = remainingBalance - amountPaid;
    
    formData.set('is_partial_payment', newRemaining > 0 ? '1' : '0');
    formData.set('remaining_balance', Math.max(0, newRemaining).toString());
    
    // Disable button and show loading
    const saveBtn = document.querySelector('#addPaymentToPartialModal .btn-primary');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
    }
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Payment added successfully!');
            const modal = bootstrap.Modal.getInstance(document.getElementById('addPaymentToPartialModal'));
            modal.hide();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to add payment'));
        }
    })
    .catch(error => {
        alert('An error occurred while adding the payment');
    })
    .finally(() => {
        // Re-enable button
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Payment';
        }
    });
}

// Event listeners (isolated from main modal)
document.addEventListener('DOMContentLoaded', function() {
    // Amount validation and real-time remaining balance update
    const amountInput = document.getElementById('partialAmountPaid');
    if (amountInput) {
        amountInput.addEventListener('input', function() {
            const remainingBalanceElement = document.getElementById('summaryRemainingBalance');
            if (!remainingBalanceElement) {
                return;
            }
            
            // Get original remaining balance (before any payment)
            const originalRemainingBalance = parseFloat(remainingBalanceElement.dataset.originalBalance || remainingBalanceElement.textContent.replace('₱', ''));
            const currentValue = parseFloat(this.value) || 0;
            
            // Calculate new remaining balance
            const newRemainingBalance = originalRemainingBalance - currentValue;
            
            // Update the display with new remaining balance
            if (newRemainingBalance >= 0) {
                remainingBalanceElement.textContent = '₱' + newRemainingBalance.toFixed(2);
                remainingBalanceElement.classList.remove('text-danger');
                remainingBalanceElement.classList.add('text-success');
            } else {
                // If amount exceeds remaining balance, show negative in red
                remainingBalanceElement.textContent = '₱' + newRemainingBalance.toFixed(2);
                remainingBalanceElement.classList.remove('text-success');
                remainingBalanceElement.classList.add('text-danger');
            }
            
            // Prevent exceeding original remaining balance
            if (currentValue > originalRemainingBalance) {
                this.value = originalRemainingBalance.toFixed(2);
                remainingBalanceElement.textContent = '₱0.00';
                remainingBalanceElement.classList.remove('text-danger');
                remainingBalanceElement.classList.add('text-success');
                alert(`Amount cannot exceed remaining balance of ₱${originalRemainingBalance.toFixed(2)}`);
            }
        });
    }
    
    // Fully Paid button event listener
    const fullyPaidBtn = document.getElementById('partialFullyPaidBtn');
    if (fullyPaidBtn) {
        fullyPaidBtn.addEventListener('click', function() {
            const remainingBalanceElement = document.getElementById('summaryRemainingBalance');
            const amountPaidElement = document.getElementById('partialAmountPaid');
            
            if (!remainingBalanceElement || !amountPaidElement) {
                return;
            }
            
            // Get original remaining balance
            const originalRemainingBalance = parseFloat(remainingBalanceElement.dataset.originalBalance || remainingBalanceElement.textContent.replace('₱', ''));
            if (originalRemainingBalance > 0) {
                amountPaidElement.value = originalRemainingBalance.toFixed(2);
                // Trigger the input event to update the remaining balance display
                amountPaidElement.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    }
    
    // Reset modal when closed
    const modal = document.getElementById('addPaymentToPartialModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            // Reset form
            const form = document.getElementById('addPaymentToPartialForm');
            if (form) {
                form.reset();
            }
            
            // Reset remaining balance to original value
            const remainingBalanceElement = document.getElementById('summaryRemainingBalance');
            if (remainingBalanceElement && remainingBalanceElement.dataset.originalBalance) {
                const originalBalance = parseFloat(remainingBalanceElement.dataset.originalBalance);
                remainingBalanceElement.textContent = '₱' + originalBalance.toFixed(2);
                remainingBalanceElement.classList.remove('text-success');
                remainingBalanceElement.classList.add('text-danger');
            }
            
            // Clear current payment data
            currentPartialPaymentData = null;
        });
    }
});

// Make function globally available
window.openAddPaymentToPartialModal = openAddPaymentToPartialModal;
</script>