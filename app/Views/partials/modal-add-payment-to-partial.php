<!-- Add Payment to Partial Payment Modal -->
<div class="modal fade" id="addPaymentToPartialModal" tabindex="-1" aria-labelledby="addPaymentToPartialModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="addPaymentToPartialModalLabel">
          <i class="fas fa-money-bill-wave me-2"></i>Add Payment to Partial Payment
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body">
        <!-- Payment Summary -->
        <div class="alert alert-info mb-4">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 class="alert-heading mb-1">Current Payment Status</h6>
              <p class="mb-0"><strong>Payer:</strong> <span id="summaryPayerName">-</span></p>
              <p class="mb-0"><strong>Contribution:</strong> <span id="summaryContribution">-</span></p>
              <p class="mb-0"><strong>Total Amount:</strong> <span id="summaryTotalAmount" class="text-primary">-</span></p>
              <p class="mb-0"><strong>Amount Already Paid:</strong> <span id="summaryAmountPaid" class="text-success">-</span></p>
            </div>
            <div class="text-end">
              <h6 class="text-muted mb-1">Remaining Balance</h6>
              <h3 class="text-danger mb-0" id="summaryRemainingBalance">₱0.00</h3>
            </div>
          </div>
        </div>

        <form id="addPaymentToPartialForm">
          <input type="hidden" id="originalPaymentId" name="original_payment_id">
          <input type="hidden" id="contributionId" name="contribution_id">
          
          <!-- Amount to Pay -->
          <div class="mb-3">
            <label for="newPaymentAmount" class="form-label">Amount to Pay <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">₱</span>
              <input type="number" class="form-control" id="newPaymentAmount" name="amount_paid" step="0.01" min="0.01" required>
            </div>
            <small class="text-muted">Maximum amount: <span id="maxAmount" class="fw-bold"></span></small>
          </div>

          <!-- Payment Method -->
          <div class="mb-3">
            <label for="paymentMethod" class="form-label">Payment Method <span class="text-danger">*</span></label>
            <?= payment_method_dropdown_with_icons('payment_method', null, [
                'id' => 'paymentMethod',
                'required' => 'required'
            ]) ?>
          </div>

          <!-- Payment Date -->
          <div class="mb-3">
            <label for="paymentDate" class="form-label">Payment Date <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control" id="paymentDate" name="payment_date" required>
          </div>

          <!-- New Remaining Balance (Auto-calculated) -->
          <div class="mb-3">
            <label class="form-label">New Remaining Balance (After This Payment)</label>
            <div class="input-group">
              <span class="input-group-text bg-warning text-dark">₱</span>
              <input type="text" class="form-control bg-light" id="newRemainingBalance" readonly>
            </div>
            <small class="text-success" id="fullyPaidMessage" style="display: none;">
              <i class="fas fa-check-circle me-1"></i>This payment will complete the balance!
            </small>
          </div>

          <!-- Payment Status -->
          <div class="mb-3">
            <label for="paymentStatus" class="form-label">Payment Status</label>
            <select class="form-select" id="paymentStatus" name="payment_status" disabled>
              <option value="partial">Partial Payment</option>
              <option value="fully paid">Fully Paid</option>
            </select>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-1"></i>Cancel
        </button>
        <button type="button" class="btn btn-primary" onclick="savePartialPayment()">
          <i class="fas fa-save me-1"></i>Record Payment
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Store current payment data
let currentPartialPayment = null;

// Function to open add payment to partial modal
function openAddPaymentToPartialModal(payment) {
    console.log('Opening add payment modal for:', payment);
    
    currentPartialPayment = payment;
    
    // Update modal title
    document.getElementById('addPaymentToPartialModalLabel').innerHTML = 
        '<i class="fas fa-money-bill-wave me-2"></i>Add Payment to Partial Payment';
    
    // Populate summary
    document.getElementById('summaryPayerName').textContent = payment.payer_name || 'N/A';
    document.getElementById('summaryContribution').textContent = payment.contribution_title || 'N/A';
    
    // Calculate summary amounts
    const totalAmount = parseFloat(payment.amount_paid || 0);
    const amountPaid = parseFloat(payment.amount_paid || 0);
    const remainingBalance = parseFloat(payment.remaining_balance || 0);
    
    document.getElementById('summaryTotalAmount').textContent = '₱' + totalAmount.toFixed(2);
    document.getElementById('summaryAmountPaid').textContent = '₱' + amountPaid.toFixed(2);
    document.getElementById('summaryRemainingBalance').textContent = '₱' + remainingBalance.toFixed(2);
    
    // Set max amount
    document.getElementById('maxAmount').textContent = '₱' + remainingBalance.toFixed(2);
    document.getElementById('newPaymentAmount').max = remainingBalance;
    
         // Populate hidden fields
     document.getElementById('originalPaymentId').value = payment.id || '';
     document.getElementById('contributionId').value = payment.contribution_id || '';
    
    // Reset form
    document.getElementById('newPaymentAmount').value = '';
    document.getElementById('paymentMethod').value = 'cash';
    
    // Set current date/time
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    document.getElementById('paymentDate').value = `${year}-${month}-${day}T${hours}:${minutes}`;
    
    // Reset new remaining balance
    updateNewRemainingBalance();
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addPaymentToPartialModal'));
    modal.show();
}

// Function to update new remaining balance when amount changes
function updateNewRemainingBalance() {
    const remainingBalance = parseFloat(document.getElementById('summaryRemainingBalance').textContent.replace('₱', ''));
    const newPaymentAmount = parseFloat(document.getElementById('newPaymentAmount').value || 0);
    const newRemaining = remainingBalance - newPaymentAmount;
    
    document.getElementById('newRemainingBalance').value = '₱' + Math.max(0, newRemaining).toFixed(2);
    
    // Update payment status
    const statusSelect = document.getElementById('paymentStatus');
    if (newRemaining <= 0.01) { // Allow small rounding differences
        statusSelect.value = 'fully paid';
        statusSelect.className = 'form-select bg-success text-white';
        document.getElementById('fullyPaidMessage').style.display = 'block';
    } else {
        statusSelect.value = 'partial';
        statusSelect.className = 'form-select bg-warning text-dark';
        document.getElementById('fullyPaidMessage').style.display = 'none';
    }
}

// Event listener for amount input
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('newPaymentAmount');
    if (amountInput) {
        amountInput.addEventListener('input', updateNewRemainingBalance);
    }
});

// Function to save partial payment
function savePartialPayment() {
    const form = document.getElementById('addPaymentToPartialForm');
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const paymentData = Object.fromEntries(formData);
    
    // Add original payment info
    paymentData.original_payment_id = currentPartialPayment.id;
    paymentData.contribution_id = currentPartialPayment.contribution_id;
    paymentData.payment_status = document.getElementById('paymentStatus').value;
    
    // Calculate new remaining balance
    const currentRemaining = parseFloat(currentPartialPayment.remaining_balance || 0);
    const newPaymentAmount = parseFloat(paymentData.amount_paid);
    const newRemaining = currentRemaining - newPaymentAmount;
    
    paymentData.remaining_balance = Math.max(0, newRemaining);
    paymentData.is_partial_payment = newRemaining > 0 ? '1' : '0';
    
    console.log('=== PAYMENT DATA DEBUG ===');
    console.log('Current Partial Payment:', currentPartialPayment);
    console.log('Payment Data to Send:', paymentData);
    console.log('=======================');
    
    // Disable button and show loading
    const saveBtn = event.target;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
    
    // Send to backend
    fetch(`${window.APP_BASE_URL}/payments/add-to-partial`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(paymentData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Payment recorded successfully!', 'success');
            
            // Close the add payment modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addPaymentToPartialModal'));
            modal.hide();
            
            // Refresh the contribution payments modal data
            // Check if contribution payments modal function exists
            if (typeof showContributionPayments === 'function' && window.currentContributionId) {
                // Get the contribution data from the modal
                const contributionTitle = document.getElementById('contributionModalTitle').textContent;
                const contributionAmount = window.currentContributionData.amount || 0;
                
                // Refresh the payments data
                showContributionPayments(window.currentContributionId, contributionTitle, contributionAmount);
            } else {
                // Fallback: reload page after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        } else {
            showNotification(data.message || 'Failed to record payment', 'error');
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Record Payment';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while recording payment', 'error');
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Record Payment';
    });
}
</script>
