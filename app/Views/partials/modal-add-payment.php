<!-- Add Payment Modal -->

<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addPaymentModalLabel"><?= esc($title ?? 'Add Payment') ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="<?= esc($action) ?>" method="post">
        <div class="modal-body">

          <div class="mb-3">
            <label for="studentId" class="form-label">Student ID</label>
            <input type="text" class="form-control" id="studentId" name="student_id" required>
          </div>

          <div class="mb-3">
            <label for="studentName" class="form-label">Student Name</label>
            <input type="text" class="form-control" id="studentName" name="student_name" required>
          </div>

          <div class="mb-3">
            <label for="contributionId" class="form-label">Select Contribution</label>
            <select id="contributionId" name="contribution_id" class="form-select" required>
              <option value="">-- Select Contribution --</option>
              <?php foreach ($contributions as $contribution): ?>
                <option value="<?= esc($contribution['id']) ?>">
                  <?= esc($contribution['title']) ?> - ₱<?= number_format($contribution['amount'], 2) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" class="form-control" id="amount" name="amount" required>
          </div>

          <div class="mb-3">
            <label for="paymentDate" class="form-label">Payment Date</label>
            <input type="date" class="form-control" id="paymentDate" name="payment_date" required>
          </div>

          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select" required>
              <option value="Pending">Pending</option>
              <option value="Completed">Completed</option>
            </select>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Payment</button>
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
