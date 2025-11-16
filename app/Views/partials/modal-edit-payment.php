<!-- Edit Payment Modal -->
<div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editPaymentModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editPaymentForm">
                    <input type="hidden" id="editPaymentId" name="payment_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editPayerName" class="form-label">Payer Name</label>
                            <input type="text" class="form-control" id="editPayerName" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editContribution" class="form-label">Contribution</label>
                            <input type="text" class="form-control" id="editContribution" readonly>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editAmountPaid" class="form-label">Amount Paid <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="editAmountPaid" name="amount_paid" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editPaymentMethod" class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <?php
                            $paymentMethodModel = new \App\Models\PaymentMethodModel();
                            $paymentMethods = $paymentMethodModel->getActiveMethods();
                            ?>
                            <select class="form-control" id="editPaymentMethod" name="payment_method" required>
                                <option value="">Select Payment Method</option>
                                <?php foreach ($paymentMethods as $method): ?>
                                    <option value="<?= esc($method['name']) ?>"><?= esc($method['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editPaymentDate" class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="editPaymentDate" name="payment_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editRemainingBalance" class="form-label">Remaining Balance</label>
                            <input type="number" class="form-control" id="editRemainingBalance" name="remaining_balance" step="0.01" readonly>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editPaymentStatus" class="form-label">Payment Status</label>
                            <select class="form-control" id="editPaymentStatus" name="payment_status">
                                <option value="fully paid">Fully Paid</option>
                                <option value="partial">Partial</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editReceiptNumber" class="form-label">Receipt Number</label>
                            <input type="text" class="form-control" id="editReceiptNumber" readonly>
                        </div>
                    </div>
                    
                    <input type="hidden" id="editContributionId" name="contribution_id">
                    <input type="hidden" id="editContributionAmount" name="contribution_amount">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-warning" id="confirmEditPayment">
                    <i class="fas fa-save me-2"></i>Update Payment
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize edit payment modal (using vanilla JavaScript to avoid jQuery dependency)
(function() {
    'use strict';
    
    // Wait for DOM to be ready
    function initEditPaymentModal() {
        const confirmBtn = document.getElementById('confirmEditPayment');
        const editAmountPaid = document.getElementById('editAmountPaid');
        const editModal = document.getElementById('editPaymentModal');
        
        if (!confirmBtn || !editModal) return;
        
        // Only attach handlers once
        if (confirmBtn.dataset.handlersAttached === 'true') return;
        confirmBtn.dataset.handlersAttached = 'true';
        
        // Handle confirm edit payment button click
        confirmBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const paymentId = document.getElementById('editPaymentId').value;
            if (!paymentId) {
                alert('No payment selected for editing');
                return;
            }
            
            // Validate form
            const amountPaid = document.getElementById('editAmountPaid').value;
            const paymentMethod = document.getElementById('editPaymentMethod').value;
            const paymentDate = document.getElementById('editPaymentDate').value;
            
            if (!amountPaid || !paymentMethod || !paymentDate) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Disable button to prevent double-click
            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
            
            // Prepare update data
            const updateData = {
                contribution_id: document.getElementById('editContributionId').value,
                amount_paid: amountPaid,
                payment_method: paymentMethod,
                payment_date: paymentDate,
                payment_status: document.getElementById('editPaymentStatus').value,
                is_partial_payment: document.getElementById('editPaymentStatus').value === 'partial' ? '1' : '0',
                remaining_balance: document.getElementById('editRemainingBalance').value
            };
            
            // Send update request
            fetch(`${window.APP_BASE_URL || ''}/payments/update/${paymentId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(updateData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal using Bootstrap
                    const modal = bootstrap.Modal.getInstance(editModal);
                    if (modal) {
                        modal.hide();
                    } else {
                        // If no instance, manually close
                        editModal.classList.remove('show');
                        editModal.setAttribute('aria-hidden', 'true');
                        editModal.setAttribute('style', 'display: none');
                    }
                    
                    // Clean up backdrop immediately
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => backdrop.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                    
                    // Show success message
                    if (typeof showNotification === 'function') {
                        showNotification('Payment updated successfully!', 'success');
                    } else if (typeof window.showNotification === 'function') {
                        window.showNotification('Payment updated successfully!', 'success');
                    } else {
                        alert('Payment updated successfully!');
                    }
                    
                    // Reload page to show updated data
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    alert('Error: ' + (data.message || 'Failed to update payment'));
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                alert('An error occurred while updating the payment.');
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalText;
            });
        });
        
        // Update remaining balance when amount changes
        if (editAmountPaid) {
            editAmountPaid.addEventListener('input', function() {
                // Try server-based calculation first, fallback to client-side
                if (typeof updateEditRemainingBalanceFromServer === 'function') {
                    updateEditRemainingBalanceFromServer();
                } else {
                    updateEditRemainingBalance();
                }
            });
        }
        
        // Reset form when modal is hidden and clean up backdrop
        editModal.addEventListener('hidden.bs.modal', function() {
            const form = document.getElementById('editPaymentForm');
            if (form) {
                form.reset();
            }
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-save me-2"></i>Update Payment';
            confirmBtn.dataset.handlersAttached = 'false'; // Reset flag
            
            // Clean up any lingering backdrop elements
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
            
            // Remove modal-open class from body if no modals are open
            const openModals = document.querySelectorAll('.modal.show');
            if (openModals.length === 0) {
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
        });
        
        // Handle close button and cancel button to ensure proper cleanup
        // Use event delegation to catch clicks on close buttons
        editModal.addEventListener('click', function(e) {
            const target = e.target.closest('[data-bs-dismiss="modal"], .btn-close');
            if (target) {
                // Allow Bootstrap to handle the close, then clean up after a short delay
                setTimeout(() => {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    const openModals = document.querySelectorAll('.modal.show');
                    
                    if (openModals.length === 0) {
                        backdrops.forEach(backdrop => backdrop.remove());
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                    }
                }, 150);
            }
        });
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initEditPaymentModal();
            // Also try again when modal is shown (in case it's dynamically loaded)
            const editModal = document.getElementById('editPaymentModal');
            if (editModal) {
                editModal.addEventListener('shown.bs.modal', function() {
                    setTimeout(initEditPaymentModal, 100);
                });
            }
        });
    } else {
        initEditPaymentModal();
        // Also try again when modal is shown
        const editModal = document.getElementById('editPaymentModal');
        if (editModal) {
            editModal.addEventListener('shown.bs.modal', function() {
                setTimeout(initEditPaymentModal, 100);
            });
        }
    }
})();

// Update remaining balance function (global, using vanilla JS)
// This now calculates based on payment sequence group total, not just this payment
function updateEditRemainingBalance() {
    const contributionAmount = parseFloat(document.getElementById('editContributionAmount')?.value) || 0;
    const amountPaid = parseFloat(document.getElementById('editAmountPaid')?.value) || 0;
    const paymentId = document.getElementById('editPaymentId')?.value;
    const groupTotalPaid = parseFloat(document.getElementById('editPaymentId')?.dataset.groupTotalPaid) || 0;
    const currentPaymentAmount = parseFloat(document.getElementById('editPaymentId')?.dataset.currentAmount) || 0;
    
    // Get other payments total in the sequence group
    // If we have group total data, use it; otherwise calculate from scratch
    let otherPaymentsTotal = 0;
    if (groupTotalPaid > 0 && currentPaymentAmount > 0) {
        // Calculate: group total - current payment = other payments total
        otherPaymentsTotal = groupTotalPaid - currentPaymentAmount;
    }
    
    // New group total = other payments + new amount for this payment
    const newGroupTotal = otherPaymentsTotal + amountPaid;
    
    // Remaining balance = contribution amount - new group total
    const remainingBalance = Math.max(0, contributionAmount - newGroupTotal);
    
    const remainingBalanceEl = document.getElementById('editRemainingBalance');
    const paymentStatusEl = document.getElementById('editPaymentStatus');
    
    if (remainingBalanceEl) {
        remainingBalanceEl.value = remainingBalance.toFixed(2);
    }
    
    // Update payment status
    if (paymentStatusEl) {
        if (remainingBalance <= 0.01) {
            paymentStatusEl.value = 'fully paid';
        } else {
            paymentStatusEl.value = 'partial';
        }
    }
}

// Function to fetch and calculate remaining balance from server for accurate calculation
async function updateEditRemainingBalanceFromServer() {
    const paymentId = document.getElementById('editPaymentId')?.value;
    const amountPaid = parseFloat(document.getElementById('editAmountPaid')?.value) || 0;
    
    if (!paymentId) return;
    
    try {
        // Fetch payment details to get group total
        const response = await fetch(`${window.APP_BASE_URL || ''}/payments/get-details/${paymentId}`);
        const data = await response.json();
        
        if (data.success && data.payment) {
            const payment = data.payment;
            const contributionAmount = parseFloat(payment.contribution_amount || 0);
            
            // Get group total paid from server data
            const groupTotalPaid = parseFloat(payment.group_total_paid || 0);
            const currentPaymentAmount = parseFloat(payment.amount_paid || 0);
            
            // Calculate other payments total
            const otherPaymentsTotal = groupTotalPaid - currentPaymentAmount;
            
            // New group total = other payments + new amount
            const newGroupTotal = otherPaymentsTotal + amountPaid;
            
            // Remaining balance = contribution - new group total
            const remainingBalance = Math.max(0, contributionAmount - newGroupTotal);
            
            const remainingBalanceEl = document.getElementById('editRemainingBalance');
            const paymentStatusEl = document.getElementById('editPaymentStatus');
            
            if (remainingBalanceEl) {
                remainingBalanceEl.value = remainingBalance.toFixed(2);
            }
            
            if (paymentStatusEl) {
                if (remainingBalance <= 0.01) {
                    paymentStatusEl.value = 'fully paid';
                } else {
                    paymentStatusEl.value = 'partial';
                }
            }
        }
    } catch (error) {
        // Fallback to simple calculation if server call fails
        updateEditRemainingBalance();
    }
}

// Make functions globally available
window.updateEditRemainingBalance = updateEditRemainingBalance;
window.updateEditRemainingBalanceFromServer = updateEditRemainingBalanceFromServer;
</script>

