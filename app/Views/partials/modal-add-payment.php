<!-- Add Payment Modal -->

<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addPaymentModalLabel"><?= esc($title ?? 'Add Payment') ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="paymentForm" action="<?= esc($action) ?>" method="post">
        <div class="modal-body">
          <input type="hidden" name="id" id="paymentId" value="<?= isset($payment['id']) ? $payment['id'] : '' ?>">
          <input type="hidden" name="parent_payment_id" id="parentPaymentId" value="<?= isset($payment['parent_payment_id']) ? $payment['parent_payment_id'] : '' ?>">
          <input type="hidden" id="existingPayerId" name="existing_payer_id" value="">

          <!-- Payer Selection: Existing or New -->
          <div class="mb-3">
            <label class="form-label">Select Payer</label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="payerType" id="existingPayer" value="existing" checked>
              <label class="form-check-label" for="existingPayer">
                Existing Payer
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="payerType" id="newPayer" value="new">
              <label class="form-check-label" for="newPayer">
                New Payer
              </label>
            </div>
          </div>

          <!-- Existing Payer Selection -->
          <div class="mb-3" id="existingPayerFields">
            <label for="payerSelect" class="form-label">Search Payer</label>
            <input type="text" class="form-control" id="payerSelect" placeholder="Type to search payers..." autocomplete="off">
            <div id="payerDropdown" class="list-group position-absolute w-100" style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;"></div>
          </div>

          <!-- New Payer Fields (Initially Hidden) -->
          <div id="newPayerFields" style="display: none;">
            <div class="mb-3">
              <label for="payerName" class="form-label">Payer Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="payerName" name="payer_name" value="<?= isset($payment['payer_name']) ? $payment['payer_name'] : '' ?>">
            </div>

            <div class="mb-3">
              <label for="payerId" class="form-label">Payer ID <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="payerId" name="payer_id" value="<?= isset($payment['payer_id']) ? $payment['payer_id'] : '' ?>">
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
              <label for="paymentStatus" class="form-label">Payment Status</label>
              <select class="form-select" id="paymentStatus" name="payment_status" readonly>
                <option value="fully paid">Full Payment</option>
                <option value="partial">Partial Payment</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label for="remainingBalance" class="form-label">Remaining Balance</label>
            <input type="number" step="0.01" class="form-control" id="remainingBalance" name="remaining_balance" readonly value="<?= isset($payment['remaining_balance']) ? $payment['remaining_balance'] : '0.00' ?>">
          </div>

          <div class="mb-3">
            <input type="hidden" id="isPartialPayment" name="is_partial_payment" value="0">
            <input type="hidden" id="paymentStatusHidden" name="payment_status" value="fully paid">
          </div>

          <div class="mb-3">
            <label for="paymentDate" class="form-label">Payment Date</label>
            <input type="datetime-local" class="form-control" id="paymentDate" name="payment_date" value="<?= isset($payment['payment_date']) ? date('Y-m-d\TH:i', strtotime($payment['payment_date'])) : date('Y-m-d\TH:i') ?>" required>
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

/* Payment Status Display Styles */
#paymentStatus {
    cursor: not-allowed;
    pointer-events: none;
}

#paymentStatus.bg-success {
    background-color: #28a745 !important;
    color: white !important;
}

#paymentStatus.bg-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

#paymentStatus option {
    background-color: white;
    color: black;
}
</style>


<script>
document.addEventListener("DOMContentLoaded", function() {
  // Bootstrap's data-bs-toggle="modal" handles modal opening automatically
  // Regular <a> tags will navigate to their href URLs automatically
});

function updatePaymentStatus() {
  const amountPaidEl = document.getElementById('amountPaid');
  const contributionSelect = document.getElementById('contributionId');
  const paymentStatusEl = document.getElementById('paymentStatus');
  const paymentStatusHidden = document.getElementById('paymentStatusHidden');
  const isPartialEl = document.getElementById('isPartialPayment');
  const remainingBalanceEl = document.getElementById('remainingBalance');

  if (!amountPaidEl || !contributionSelect || !paymentStatusEl || !remainingBalanceEl) {
    return; // Elements not found
  }

  const amountPaid = parseFloat(amountPaidEl.value) || 0;

  if (contributionSelect.selectedIndex > 0) {
    const selectedOption = contributionSelect.options[contributionSelect.selectedIndex];
    const contributionAmount = parseFloat(selectedOption.dataset.amount) || 0;

    let remaining = contributionAmount - amountPaid;
    if (remaining < 0) remaining = 0;

    remainingBalanceEl.value = remaining.toFixed(2);

    // Determine if payment is partial or full
    const isPartial = remaining > 0 && amountPaid > 0;
    
    // Update payment status
    if (isPartial) {
      paymentStatusEl.value = 'partial';
      paymentStatusEl.className = 'form-select bg-warning text-dark';
      paymentStatusHidden.value = 'partial';
      isPartialEl.value = '1';
    } else if (amountPaid > 0 && amountPaid >= contributionAmount) {
      paymentStatusEl.value = 'fully paid';
      paymentStatusEl.className = 'form-select bg-success text-white';
      paymentStatusHidden.value = 'fully paid';
      isPartialEl.value = '0';
      remainingBalanceEl.value = '0.00';
    } else {
      paymentStatusEl.value = 'fully paid';
      paymentStatusEl.className = 'form-select bg-success text-white';
      paymentStatusHidden.value = 'fully paid';
      isPartialEl.value = '0';
    }

    // If amount is empty, set remaining balance to contribution amount
    if (amountPaid === 0 && contributionAmount > 0) {
      remainingBalanceEl.value = contributionAmount.toFixed(2);
      paymentStatusEl.value = 'partial';
      paymentStatusEl.className = 'form-select bg-warning text-dark';
      paymentStatusHidden.value = 'partial';
      isPartialEl.value = '1';
    }
  }
}

// Payer Type Toggle Functionality
document.addEventListener("DOMContentLoaded", function() {
  const amountPaidEl = document.getElementById('amountPaid');
  const contributionSelect = document.getElementById('contributionId');
  const existingPayerRadio = document.getElementById('existingPayer');
  const newPayerRadio = document.getElementById('newPayer');
  const existingPayerFields = document.getElementById('existingPayerFields');
  const newPayerFields = document.getElementById('newPayerFields');
  const payerSelectInput = document.getElementById('payerSelect');
  const payerDropdown = document.getElementById('payerDropdown');

  // Toggle between existing and new payer
  if (existingPayerRadio && newPayerRadio) {
    existingPayerRadio.addEventListener('change', function() {
      if (this.checked) {
        existingPayerFields.style.display = 'block';
        newPayerFields.style.display = 'none';
        payerSelectInput.required = false;
        document.getElementById('payerName').required = false;
        document.getElementById('payerId').required = false;
      }
    });

    newPayerRadio.addEventListener('change', function() {
      if (this.checked) {
        existingPayerFields.style.display = 'none';
        newPayerFields.style.display = 'block';
        payerSelectInput.required = false;
        document.getElementById('payerName').required = true;
        document.getElementById('payerId').required = true;
      }
    });
  }

  // Payer Search Functionality
  let searchTimeout;
  if (payerSelectInput) {
    payerSelectInput.addEventListener('input', function() {
      const searchTerm = this.value.trim();
      
      clearTimeout(searchTimeout);
      
      if (searchTerm.length < 2) {
        payerDropdown.style.display = 'none';
        return;
      }

      searchTimeout = setTimeout(function() {
        // Fetch payers from database
        fetch(`${window.APP_BASE_URL || ''}/payments/search-payers?term=${encodeURIComponent(searchTerm)}`)
          .then(response => response.json())
          .then(data => {
            if (data.success && data.payers && data.payers.length > 0) {
              payerDropdown.innerHTML = '';
              data.payers.forEach(payer => {
                const item = document.createElement('a');
                item.className = 'list-group-item list-group-item-action';
                item.href = '#';
                item.innerHTML = `<strong>${payer.payer_name}</strong> (ID: ${payer.payer_id})<br><small class="text-muted">${payer.contact_number || 'N/A'} | ${payer.email_address || 'N/A'}</small>`;
                item.addEventListener('click', function(e) {
                  e.preventDefault();
                  payerSelectInput.value = `${payer.payer_name} (${payer.payer_id})`;
                  document.getElementById('existingPayerId').value = payer.payer_id;
                  payerDropdown.style.display = 'none';
                });
                payerDropdown.appendChild(item);
              });
              payerDropdown.style.display = 'block';
            } else {
              payerDropdown.innerHTML = '<div class="list-group-item text-muted">No payers found</div>';
              payerDropdown.style.display = 'block';
            }
          })
          .catch(error => {
            console.error('Error searching payers:', error);
          });
      }, 300);
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!payerSelectInput.contains(e.target) && !payerDropdown.contains(e.target)) {
        payerDropdown.style.display = 'none';
      }
    });
  }

  // Amount paid and contribution event listeners
  if (amountPaidEl) {
    amountPaidEl.addEventListener('input', updatePaymentStatus);
  }
  
  if (contributionSelect) {
    contributionSelect.addEventListener('change', updatePaymentStatus);
  }
});
</script>



