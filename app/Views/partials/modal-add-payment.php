<!-- Add Payment Modal -->

<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="paymentForm" method="post" action="<?= isset($action) ? $action : '' ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPaymentModalLabel"><?= isset($title) ? $title : 'Add Payment' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="paymentId" value="<?= isset($payment['id']) ? $payment['id'] : '' ?>">
                    <input type="hidden" name="parent_payment_id" id="parentPaymentId" value="<?= isset($payment['parent_payment_id']) ? $payment['parent_payment_id'] : '' ?>">

                    <div class="mb-3">
                        <label for="payerName" class="form-label">Payer Name</label>
                        <input type="text" class="form-control" id="payerName" name="payer_name" value="<?= isset($payment['payer_name']) ? $payment['payer_name'] : '' ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="payerId" class="form-label">Payer ID</label>
                        <input type="text" class="form-control" id="payerId" name="payer_id" value="<?= isset($payment['payer_id']) ? $payment['payer_id'] : '' ?>" required>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-md-6">
                            <label for="contactNumber" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contactNumber" name="contact_number" value="<?= isset($payment['contact_number']) ? $payment['contact_number'] : '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="emailAddress" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="emailAddress" name="email_address" value="<?= isset($payment['email_address']) ? $payment['email_address'] : '' ?>">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-md-6">
                            <label for="contributionId" class="form-label">Contribution</label>
                            <select class="form-select" id="contributionId" name="contribution_id" required>
                                <option value="">Select a contribution...</option>
                                <?php if (isset($contributions) && !empty($contributions)): ?>
                                    <?php foreach($contributions as $contribution): ?>
                                        <option 
                                            value="<?= $contribution['id'] ?>" 
                                            data-amount="<?= $contribution['amount'] ?>"
                                        >
                                            <?= esc($contribution['title']) ?> - ₱<?= number_format($contribution['amount'], 2) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No active contributions found</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="paymentMethod" class="form-label">Payment Method</label>
                            <select class="form-select" id="paymentMethod" name="payment_method" required>
                                <option value="cash">Cash</option>
                                <option value="online">Online</option>
                                <option value="check">Check</option>
                                <option value="bank">Bank</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-md-6">
                            <label for="amountPaid" class="form-label">Amount Paid</label>
                            <input type="number" step="0.01" class="form-control" id="amountPaid" name="amount_paid" value="<?= isset($payment['amount_paid']) ? $payment['amount_paid'] : '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="isPartialPayment" class="form-label">Partial Payment?</label>
                            <select class="form-select" id="isPartialPayment" name="is_partial_payment">
                                <option value="0">No (Full Payment)</option>
                                <option value="1">Yes (Partial Payment)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="remainingBalance" class="form-label">Remaining Balance</label>
                        <input type="number" step="0.01" class="form-control" id="remainingBalance" name="remaining_balance" readonly value="<?= isset($payment['remaining_balance']) ? $payment['remaining_balance'] : '0.00' ?>">
                    </div>

                    <div class="mb-3">
                        <label for="paymentDate" class="form-label">Payment Date</label>
                        <input type="datetime-local" class="form-control" id="paymentDate" name="payment_date" value="<?= isset($payment['payment_date']) ? date('Y-m-d\TH:i', strtotime($payment['payment_date'])) : date('Y-m-d\TH:i') ?>" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><?= isset($payment['id']) ? 'Update Payment' : 'Add Payment' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>


<style>
/* Fix dropdown z-index issues in modals */
.modal-dialog {
    z-index: 1055;
}

.modal-backdrop {
    z-index: 1050;
}

/* Ensure select dropdowns appear above modal */
.modal .form-select {
    position: relative;
    z-index: 1060;
}

/* Fix dropdown menu positioning */
.modal-body .form-select option {
    background-color: white;
    color: black;
}

/* Alternative: Use Bootstrap's dropdown component for better control */
.modal .dropdown-menu {
    z-index: 1070 !important;
    position: absolute !important;
}

/* Prevent modal body overflow issues */
.modal-body {
    overflow: visible;
}

.modal-content {
    overflow: visible;
}
</style>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modal event handlers
    const modal = document.getElementById('addPaymentModal');
    const contributionSelect = document.getElementById('contributionId');
    
    // Fix dropdown rendering issues
    if (modal && contributionSelect) {
        modal.addEventListener('shown.bs.modal', function() {
            // Force re-render of select dropdown
            contributionSelect.style.zIndex = '1060';
            contributionSelect.focus();
            contributionSelect.blur();
        });
    }
    
    // Add event listeners
    document.getElementById('amountPaid').addEventListener('input', updatePaymentStatus);
    document.getElementById('isPartialPayment').addEventListener('change', updatePaymentStatus);
    document.getElementById('contributionId').addEventListener('change', updatePaymentStatus);
    
    // Initialize payment status on modal open
    modal.addEventListener('shown.bs.modal', function() {
        updatePaymentStatus();
    });
});

function updatePaymentStatus() {
    const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;
    const contributionSelect = document.getElementById('contributionId');
    
    if (contributionSelect.selectedIndex >= 0) {
        const contributionAmount = parseFloat(contributionSelect.options[contributionSelect.selectedIndex].dataset.amount) || 0;
        const isPartial = document.getElementById('isPartialPayment').value == '1';

        let remaining = isPartial ? (contributionAmount - amountPaid) : 0;
        if (remaining < 0) remaining = 0;

        document.getElementById('remainingBalance').value = remaining.toFixed(2);
        
        // Auto-fill amount if not partial payment
        if (!isPartial && contributionAmount > 0) {
            document.getElementById('amountPaid').value = contributionAmount.toFixed(2);
            document.getElementById('remainingBalance').value = '0.00';
        }
    }
}
</script>
