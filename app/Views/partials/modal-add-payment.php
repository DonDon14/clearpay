<?php
// Set default values for variables
$title = $title ?? 'Add Payment';
$action = $action ?? base_url('payments/save');
$contributions = $contributions ?? [];
$payment = $payment ?? [];
?>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
    <div class="modal-content">
            <div class="modal-header" id="addPaymentModalHeader">
                <h5 class="modal-title" id="addPaymentModalLabel"><?= $title ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
        <div class="modal-body">
                <form id="paymentForm" action="<?= $action ?>" method="POST">
                    <div class="row">
                        <!-- Payer Selection -->
                        <div class="col-12 mb-3">
            <label class="form-label">Select Payer</label>
            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payer_type" id="existingPayer" value="existing" checked>
              <label class="form-check-label" for="existingPayer">
                Existing Payer
              </label>
            </div>
            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payer_type" id="newPayer" value="new">
              <label class="form-check-label" for="newPayer">
                New Payer
              </label>
            </div>
          </div>

                        <!-- Existing Payer Fields -->
                        <div id="existingPayerFields" class="col-12 mb-3">
            <label for="payerSelect" class="form-label">Search Payer</label>
                            <div id="existingPayerFields" class="position-relative">
                                <input type="text" class="form-control" id="payerSelect" placeholder="Type payer name or ID...">
                                <div id="payerDropdown" class="list-group position-absolute w-100" style="display: none; z-index: 1050;"></div>
            </div>
                            <input type="hidden" id="existingPayerId" name="payer_id" value="">
          </div>

                        <!-- New Payer Fields -->
                        <div id="newPayerFields" class="col-12 mb-3" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="payerName" class="form-label">Payer Name</label>
                                    <input type="text" class="form-control" id="payerName" name="payer_name" required>
            </div>
                                <div class="col-md-6 mb-3">
                                    <label for="payerId" class="form-label">Payer ID</label>
              <div class="input-group">
                                        <input type="text" class="form-control" id="payerId" name="new_payer_id" required>
                                        <button type="button" class="btn btn-outline-primary" onclick="openSchoolIDScanner()" title="Scan School ID">
                  <i class="fas fa-qrcode"></i>
                </button>
              </div>
            </div>
              </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="payerEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="payerEmail" name="payer_email" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="payerPhone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="payerPhone" name="payer_phone" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="payerCourse" class="form-label">Course/Program <small class="text-muted">(Optional)</small></label>
                                    <input type="text" class="form-control" id="payerCourse" name="payer_course" placeholder="e.g., BSIT1, CS, IT">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <!-- Empty column for layout balance -->
              </div>
            </div>
          </div>

                        <!-- Contribution Selection -->
                        <div class="col-12 mb-3">
              <label for="contributionId" class="form-label">Contribution</label>
                            <select class="form-control" id="contributionId" name="contribution_id" required>
                <option value="">Select a contribution...</option>
                                <?php foreach ($contributions as $contribution): ?>
                                    <option value="<?= $contribution['id'] ?>" data-amount="<?= $contribution['amount'] ?>">
                                        <?= $contribution['title'] ?> - ₱<?= number_format($contribution['amount'], 2) ?>
                    </option>
                  <?php endforeach; ?>
              </select>
            </div>

                        <!-- Amount Paid -->
                        <div class="col-md-6 mb-3">
                            <label for="amountPaid" class="form-label">Amount Paid</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="amountPaid" name="amount_paid" step="0.01" min="0" required>
                                <button type="button" class="btn btn-outline-success" id="fullyPaidBtn" title="Fill with remaining balance">
                                    <i class="fas fa-check-circle"></i> Fully Paid
                                </button>
                            </div>
                        </div>

                        <!-- Remaining Balance -->
                        <div class="col-md-6 mb-3">
                            <label for="remainingBalance" class="form-label">Remaining Balance</label>
                            <input type="number" class="form-control" id="remainingBalance" name="remaining_balance" step="0.01" readonly>
                        </div>

                        <!-- Payment Method -->
                        <div class="col-md-6 mb-3">
                            <label for="paymentMethod" class="form-label">Payment Method</label>
                            <?= payment_method_dropdown_with_icons('payment_method', null, [
                                'id' => 'paymentMethod',
                                'required' => 'required'
                            ]) ?>
                        </div>

                        <!-- Payment Status -->
                        <div class="col-md-6 mb-3">
              <label for="paymentStatus" class="form-label">Payment Status</label>
                            <select class="form-control" id="paymentStatus" name="payment_status" required>
                <option value="partial">Partial Payment</option>
                                <option value="fully paid">Fully Paid</option>
              </select>
          </div>

                        <!-- Payment Date -->
                        <div class="col-12 mb-3">
            <label for="paymentDate" class="form-label">Payment Date</label>
                            <input type="datetime-local" class="form-control" id="paymentDate" name="payment_date" required>
          </div>
        </div>
      </form>
      </div>
      <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitPayment()">Save Payment</button>
      </div>
    </div>
  </div>
</div>

<style>
/* Fix z-index layering issues */
#payerDropdown {
  z-index: 1050 !important;
  position: absolute !important;
  top: 100% !important;
  left: 0 !important;
  right: 0 !important;
  background: white !important;
  border: 1px solid #dee2e6 !important;
  border-radius: 0.375rem !important;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

#contributionId {
  z-index: 1040 !important;
  position: relative !important;
}

.contribution-option {
  z-index: 1040;
}

/* Ensure modal backdrop doesn't interfere */
.modal {
    z-index: 1055;
}

.modal-backdrop {
    z-index: 1050;
}

/* Ensure proper positioning for the payer search container */
#existingPayerFields {
    position: relative;
}

/* Style dropdown items */
#payerDropdown .list-group-item {
  cursor: pointer;
  border: none;
  border-bottom: 1px solid #dee2e6;
}

#payerDropdown .list-group-item:last-child {
  border-bottom: none;
}

#payerDropdown .list-group-item:hover {
  background-color: #f8f9fa;
}

/* Draggable modal styles */
#addPaymentModalHeader {
  user-select: none;
  transition: cursor 0.1s ease;
}

#addPaymentModalHeader:hover {
  background-color: rgba(0, 0, 0, 0.02);
}

#addPaymentModalHeader:active {
  background-color: rgba(0, 0, 0, 0.05);
}

/* Prevent text selection during drag */
.modal-dialog.dragging {
  user-select: none;
}
</style>

<script>
// Single comprehensive DOMContentLoaded listener for all modal functionality
document.addEventListener("DOMContentLoaded", function() {
  // Get all modal elements
  const addPaymentModal = document.getElementById('addPaymentModal');
  const modalDialog = addPaymentModal ? addPaymentModal.querySelector('.modal-dialog') : null;
  const modalHeader = document.getElementById('addPaymentModalHeader');
  const amountPaidEl = document.getElementById('amountPaid');
  const contributionSelect = document.getElementById('contributionId');
  const existingPayerRadio = document.getElementById('existingPayer');
  const newPayerRadio = document.getElementById('newPayer');
  const existingPayerFields = document.getElementById('existingPayerFields');
  const newPayerFields = document.getElementById('newPayerFields');
  const payerSelectInput = document.getElementById('payerSelect');
  const payerDropdown = document.getElementById('payerDropdown');

  // Reset modal when closed
  if (addPaymentModal) {
    addPaymentModal.addEventListener('hidden.bs.modal', function() {
      resetPaymentModal();
    });
  }

  // Set default payment date to current date and time
  const paymentDateInput = document.getElementById('paymentDate');
  if (paymentDateInput && !paymentDateInput.value) {
    const now = new Date();
    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    paymentDateInput.value = localDateTime;
  }

  // Initial status update only if contribution is already selected
  setTimeout(() => {
  const contributionSelect = document.getElementById('contributionId');
    if (contributionSelect && contributionSelect.value !== '') {
      updatePaymentStatus();
    }
  }, 100);

// Payer Type Toggle Functionality
  if (existingPayerRadio && newPayerRadio && existingPayerFields && newPayerFields) {
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
        
        // Generate unique payer ID when new payer is selected
        const timestamp = Date.now().toString().slice(-6); // Last 6 digits of timestamp
        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0'); // 3-digit random
        const uniqueId = 'PAY' + timestamp + random; // Format: PAY123456789
        
        document.getElementById('payerId').value = uniqueId;
        
        // Generate unique email if payer name is filled
        const payerName = document.getElementById('payerName').value.trim();
        if (payerName) {
          const emailBase = payerName.toLowerCase().replace(/\s+/g, '');
          const uniqueEmail = emailBase + timestamp + '@example.com';
          document.getElementById('payerEmail').value = uniqueEmail;
          console.log('Generated unique email:', uniqueEmail);
        }
        
        console.log('Generated unique payer ID:', uniqueId);
      }
    });
    
    // Auto-generate email when payer name is typed (for new payers)
    const payerNameInput = document.getElementById('payerName');
    if (payerNameInput) {
      payerNameInput.addEventListener('input', function() {
        if (newPayerRadio && newPayerRadio.checked) {
          const payerName = this.value.trim();
          if (payerName) {
            const timestamp = Date.now().toString().slice(-6);
            const emailBase = payerName.toLowerCase().replace(/\s+/g, '');
            const uniqueEmail = emailBase + timestamp + '@example.com';
            document.getElementById('payerEmail').value = uniqueEmail;
          }
        }
      });
    }
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
            if (data.success && data.results && data.results.length > 0) {
              payerDropdown.innerHTML = '';
              data.results.forEach(payer => {
                const item = document.createElement('a');
                item.className = 'list-group-item list-group-item-action';
                item.href = '#';
                item.innerHTML = `<strong>${payer.payer_name}</strong> (ID: ${payer.payer_id})<br><small class="text-muted">${payer.contact_number || 'N/A'} | ${payer.email_address || 'N/A'}</small>`;
                item.addEventListener('click', function(e) {
                  e.preventDefault();
                  payerSelectInput.value = `${payer.payer_name} (${payer.payer_id})`;
                  document.getElementById('existingPayerId').value = payer.id;
                  payerDropdown.style.display = 'none';
                  
                  // Store payer ID for later use when contribution is selected
                  window.selectedPayerId = payer.id;
                  
                  // Clear any existing warnings
                  const existingAlert = document.getElementById('unpaidContributionsAlert');
                  if (existingAlert) {
                    existingAlert.remove();
                  }
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
            payerDropdown.innerHTML = '<div class="list-group-item text-danger">Error searching payers</div>';
            payerDropdown.style.display = 'block';
          });
      }, 300);
    });
  }

  // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
    if (payerDropdown && !payerSelectInput.contains(e.target) && !payerDropdown.contains(e.target)) {
        payerDropdown.style.display = 'none';
      }
    });

  // Amount paid and contribution event listeners
  if (amountPaidEl) {
    amountPaidEl.addEventListener('input', updatePaymentStatus);
  }
  
  // Fully Paid button event listener
  const fullyPaidBtn = document.getElementById('fullyPaidBtn');
  if (fullyPaidBtn) {
    fullyPaidBtn.addEventListener('click', function() {
      const remainingBalanceEl = document.getElementById('remainingBalance');
      if (remainingBalanceEl && remainingBalanceEl.value) {
        const remainingBalance = parseFloat(remainingBalanceEl.value);
        if (remainingBalance > 0) {
          amountPaidEl.value = remainingBalance.toFixed(2);
          updatePaymentStatus();
        }
      }
    });
  }
  
  if (contributionSelect) {
    contributionSelect.addEventListener('change', function() {
      updatePaymentStatus();
      
      // Check contribution status if payer is selected
      if (window.selectedPayerId && this.value) {
        checkSpecificContribution(window.selectedPayerId, this.value);
      }
    });
  }

  // Make modal draggable
  if (modalDialog && modalHeader) {
    // Remove modal-dialog-centered class to allow free positioning
    modalDialog.classList.remove('modal-dialog-centered');
    
    let isDragging = false;
    let currentX;
    let currentY;
    let initialX;
    let initialY;
    let xOffset = 0;
    let yOffset = 0;
    
    // Add event listeners for drag functionality
    modalHeader.addEventListener('mousedown', dragStart);
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', dragEnd);
    
    function dragStart(e) {
      if (e.target.classList.contains('btn-close')) {
        return; // Don't drag when clicking close button
      }
      
      initialX = e.clientX - xOffset;
      initialY = e.clientY - yOffset;
      
      if (e.target === modalHeader || modalHeader.contains(e.target)) {
        isDragging = true;
        modalHeader.style.cursor = 'grabbing';
        modalDialog.classList.add('dragging');
      }
    }
    
    function drag(e) {
      if (isDragging) {
        e.preventDefault();
        
        currentX = e.clientX - initialX;
        currentY = e.clientY - initialY;
        
        xOffset = currentX;
        yOffset = currentY;
        
        modalDialog.style.transform = `translate(${currentX}px, ${currentY}px)`;
      }
    }
    
    function dragEnd(e) {
      initialX = currentX;
      initialY = currentY;
      
      isDragging = false;
      modalHeader.style.cursor = 'move';
      modalDialog.classList.remove('dragging');
    }
    
    // Reset position when modal is hidden
    addPaymentModal.addEventListener('hidden.bs.modal', function() {
      modalDialog.style.transform = '';
      modalDialog.classList.remove('dragging');
      xOffset = 0;
      yOffset = 0;
      currentX = 0;
      currentY = 0;
    });
  }
});

// Reset modal function
function resetPaymentModal() {
  // Reset form fields
  const form = document.getElementById('paymentForm');
  if (form) {
    form.reset();
  }
  
  // Reset radio buttons to existing payer
  const existingPayerRadio = document.getElementById('existingPayer');
  if (existingPayerRadio) {
    existingPayerRadio.checked = true;
  }
  
  // Show existing payer fields, hide new payer fields
  const existingPayerFields = document.getElementById('existingPayerFields');
  const newPayerFields = document.getElementById('newPayerFields');
  if (existingPayerFields) existingPayerFields.style.display = 'block';
  if (newPayerFields) {
    newPayerFields.style.display = 'none';
    // Clear new payer input fields
    const newPayerInputs = ['payerName', 'payerId', 'payerEmail', 'payerPhone', 'payerCourse'];
    newPayerInputs.forEach(fieldId => {
      const field = document.getElementById(fieldId);
      if (field) field.value = '';
    });
  }
  
  // Clear payer search
  const payerSelectInput = document.getElementById('payerSelect');
  if (payerSelectInput) payerSelectInput.value = '';
  
  // Clear payer dropdown
  const payerDropdown = document.getElementById('payerDropdown');
  if (payerDropdown) payerDropdown.style.display = 'none';
  
  // Clear hidden payer ID
  document.getElementById('existingPayerId').value = '';
  
  // Clear selected payer ID
  window.selectedPayerId = null;
  
  // Clear unpaid balance data
  window.unpaidBalanceData = null;
  
  // Clear any contribution warnings
  const existingAlert = document.getElementById('unpaidContributionsAlert');
  if (existingAlert) {
    existingAlert.remove();
  }
  
  // Reset payment status
  updatePaymentStatus();
  
  // Clear any validation errors
  clearAmountValidationError();
  
  // Clear payment status and styling
  const paymentStatusEl = document.getElementById('paymentStatus');
  if (paymentStatusEl) {
    paymentStatusEl.value = '';
    paymentStatusEl.classList.remove('text-success', 'border-success', 'text-warning', 'border-warning');
  }
  
  // Set default payment date to current date and time
  const paymentDateInput = document.getElementById('paymentDate');
  if (paymentDateInput) {
    const now = new Date();
    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    paymentDateInput.value = localDateTime;
  }
}

// Update payment status function
function updatePaymentStatus() {
  const amountPaidEl = document.getElementById('amountPaid');
  const contributionSelect = document.getElementById('contributionId');
  const remainingBalanceEl = document.getElementById('remainingBalance');
  const paymentStatusEl = document.getElementById('paymentStatus');
  
  if (amountPaidEl && contributionSelect && remainingBalanceEl && paymentStatusEl) {
    const amountPaid = parseFloat(amountPaidEl.value) || 0;
    const selectedOption = contributionSelect.options[contributionSelect.selectedIndex];
    
    // Don't update status if no contribution is selected
    if (!selectedOption || selectedOption.value === '' || selectedOption.textContent.includes('Select a contribution')) {
      // Clear status and styling when no contribution is selected
      paymentStatusEl.value = '';
      paymentStatusEl.classList.remove('text-success', 'border-success', 'text-warning', 'border-warning');
      remainingBalanceEl.value = '0.00';
      return;
    }
    
    // Don't update status if contribution amount is 0 or invalid
    const contributionAmount = parseFloat(selectedOption.dataset.amount) || 0;
    if (contributionAmount <= 0) {
      paymentStatusEl.value = '';
      paymentStatusEl.classList.remove('text-success', 'border-success', 'text-warning', 'border-warning');
      remainingBalanceEl.value = '0.00';
      return;
    }
    
    let remainingBalance;
    
    // If we have unpaid balance data (from warning), use it for calculation
    if (window.unpaidBalanceData) {
      // Calculate remaining balance considering existing payments
      const totalPaidAfterThisPayment = window.unpaidBalanceData.alreadyPaid + amountPaid;
      remainingBalance = window.unpaidBalanceData.contributionAmount - totalPaidAfterThisPayment;
    } else {
      // No existing payments, calculate normally
      remainingBalance = contributionAmount - amountPaid;
    }
    
    // Ensure remaining balance doesn't go below 0
    if (remainingBalance < 0) {
      remainingBalance = 0;
    }
    
    remainingBalanceEl.value = remainingBalance.toFixed(2);
    
    // Update payment status dynamically
    if (remainingBalance <= 0) {
      if (paymentStatusEl) {
        paymentStatusEl.value = 'fully paid';
        // Add green styling for fully paid
        paymentStatusEl.classList.remove('text-warning', 'border-warning');
        paymentStatusEl.classList.add('text-success', 'border-success');
      }
    } else {
      if (paymentStatusEl) {
        paymentStatusEl.value = 'partial';
        // Add amber styling for partial payment
        paymentStatusEl.classList.remove('text-success', 'border-success');
        paymentStatusEl.classList.add('text-warning', 'border-warning');
      }
    }
    
    // Validate amount paid doesn't exceed remaining balance
    validateAmountPaid(amountPaid, remainingBalance);
  }
}

// Validate amount paid doesn't exceed remaining balance
function validateAmountPaid(amountPaid, remainingBalance) {
  const amountPaidEl = document.getElementById('amountPaid');
  const contributionSelect = document.getElementById('contributionId');
  
  if (amountPaidEl && contributionSelect) {
    const selectedOption = contributionSelect.options[contributionSelect.selectedIndex];
    const contributionAmount = parseFloat(selectedOption.dataset.amount) || 0;
    
    let maxAllowedAmount;
    
    // Calculate maximum allowed amount based on existing payments
    if (window.unpaidBalanceData) {
      // If there are existing payments, max amount is the remaining balance
      maxAllowedAmount = window.unpaidBalanceData.remainingAmount;
    } else {
      // If no existing payments, max amount is the full contribution amount
      maxAllowedAmount = contributionAmount;
    }
    
    // Check if amount exceeds maximum allowed
    if (amountPaid > maxAllowedAmount) {
      // Show validation error
      showAmountValidationError(`Amount cannot exceed ₱${maxAllowedAmount.toFixed(2)}`);
      
      // Reset amount to maximum allowed
      amountPaidEl.value = maxAllowedAmount.toFixed(2);
      
      // Recalculate with corrected amount
      updatePaymentStatus();
    } else {
      // Clear any existing validation error
      clearAmountValidationError();
    }
  }
}

// Show amount validation error
function showAmountValidationError(message) {
  const amountPaidEl = document.getElementById('amountPaid');
  if (amountPaidEl) {
    // Remove existing error styling
    amountPaidEl.classList.remove('is-valid');
    amountPaidEl.classList.add('is-invalid');
    
    // Show error message
    let errorDiv = document.getElementById('amountPaidError');
    if (!errorDiv) {
      errorDiv = document.createElement('div');
      errorDiv.id = 'amountPaidError';
      errorDiv.className = 'invalid-feedback';
      amountPaidEl.parentNode.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
  }
}

// Clear amount validation error
function clearAmountValidationError() {
  const amountPaidEl = document.getElementById('amountPaid');
  if (amountPaidEl) {
    amountPaidEl.classList.remove('is-invalid');
    amountPaidEl.classList.add('is-valid');
    
    const errorDiv = document.getElementById('amountPaidError');
    if (errorDiv) {
      errorDiv.remove();
    }
  }
}

// Check specific contribution when selected
function checkSpecificContribution(payerId, contributionId) {
  // Clear any existing warnings first
  const existingAlert = document.getElementById('unpaidContributionsAlert');
  if (existingAlert) {
    existingAlert.remove();
  }
  
  // Clear unpaid balance data when checking a new contribution
  window.unpaidBalanceData = null;
  
  // Get contribution details from dropdown
  const contributionSelect = document.getElementById('contributionId');
  const selectedOption = contributionSelect.options[contributionSelect.selectedIndex];
  const contributionTitle = selectedOption.textContent.split(' - ₱')[0];
  const contributionAmount = parseFloat(selectedOption.dataset.amount) || 0;
  
  // Check the specific contribution
  checkContributionStatus(payerId, contributionId)
    .then(result => {
      if (result.success) {
        if (result.status === 'unpaid') {
          showUnpaidContributionWarning(result);
        } else if (result.status === 'fully_paid') {
          showFullyPaidContributionWarning(result);
        }
        // If status is 'none', no warning needed
      }
    })
    .catch(error => {
      console.error('Error checking contribution:', error);
    });
}

function checkContributionStatus(payerId, contributionId) {
  return fetch(`${window.APP_BASE_URL || ''}/payments/get-contribution-warning-data?payer_id=${payerId}&contribution_id=${contributionId}`, {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    }
  })
  .then(response => response.json())
  .catch(error => {
    console.error('Error checking contribution status:', error);
    return { success: false };
  });
}

function showUnpaidContributionWarning(data) {
  const modalBody = document.querySelector('#addPaymentModal .modal-body');
  const existingAlert = document.getElementById('unpaidContributionsAlert');
  
  if (existingAlert) {
    existingAlert.remove();
  }

  // Store the unpaid balance data for use in updatePaymentStatus
  window.unpaidBalanceData = {
    contributionAmount: parseFloat(data.contribution.amount),
    alreadyPaid: parseFloat(data.unpaid_group.total_paid),
    remainingAmount: parseFloat(data.unpaid_group.remaining_amount)
  };

  const alertHtml = `
    <div id="unpaidContributionsAlert" class="alert alert-warning alert-dismissible fade show">
      <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Incomplete Contribution</h6>
      <p class="mb-2">This payer has started but not completed this contribution:</p>
      <ul class="mb-2">
        <li><strong>${data.contribution.title}</strong> - ₱${parseFloat(data.contribution.amount).toFixed(2)} (₱${parseFloat(data.unpaid_group.remaining_amount).toFixed(2)} remaining)</li>
      </ul>
      <div class="small text-muted mb-2">
        <strong>Payment Group ${data.unpaid_group.sequence}:</strong><br>
        • Total Paid: ₱${parseFloat(data.unpaid_group.total_paid).toFixed(2)}<br>
        • Payment Count: ${data.unpaid_group.payment_count}<br>
        • Last Payment: ${new Date(data.unpaid_group.last_payment_date).toLocaleDateString()}
      </div>
      <small class="text-muted">You can add payments to complete this contribution, but duplicate payments within the same contribution will require confirmation.</small>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  `;

  modalBody.insertAdjacentHTML('afterbegin', alertHtml);
  
  // Update the remaining balance field to show the actual remaining amount
  const remainingBalanceEl = document.getElementById('remainingBalance');
  if (remainingBalanceEl) {
    remainingBalanceEl.value = window.unpaidBalanceData.remainingAmount.toFixed(2);
  }
}

function showFullyPaidContributionWarning(data) {
  const modalBody = document.querySelector('#addPaymentModal .modal-body');
  const existingAlert = document.getElementById('unpaidContributionsAlert');
  
  if (existingAlert) {
    existingAlert.remove();
  }

  const alertHtml = `
    <div id="unpaidContributionsAlert" class="alert alert-info alert-dismissible fade show">
      <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Contribution Already Fully Paid</h6>
      <p class="mb-2">This payer has already fully paid this contribution:</p>
      <ul class="mb-2">
        <li><strong>${data.contribution.title}</strong> - ₱${parseFloat(data.contribution.amount).toFixed(2)} (Fully Paid)</li>
      </ul>
      <div class="small text-muted mb-2">
        <strong>Payment Group ${data.fully_paid_groups[0].sequence}:</strong><br>
        • Total Paid: ₱${parseFloat(data.fully_paid_groups[0].total_paid).toFixed(2)}<br>
        • Payment Count: ${data.fully_paid_groups[0].payment_count}<br>
        • Last Payment: ${new Date(data.fully_paid_groups[0].last_payment_date).toLocaleDateString()}
      </div>
      <small class="text-muted">Are you sure you want to add another payment group for this contribution?</small>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  `;

  modalBody.insertAdjacentHTML('afterbegin', alertHtml);
}

// Function to submit payment
window.submitPayment = function() {
  const form = document.getElementById('paymentForm');
  const formData = new FormData(form);
  
  // Check if it's a new payer
  const newPayerRadio = document.getElementById('newPayer');
  const isNewPayer = newPayerRadio && newPayerRadio.checked;
  
  if (isNewPayer) {
    // Validate new payer fields
    const newPayerFields = ['payer_name', 'new_payer_id', 'payer_email', 'payer_phone'];
    const missingFields = newPayerFields.filter(field => {
      const value = formData.get(field);
      return !value || value.trim() === '';
    });
    
    if (missingFields.length > 0) {
      console.log('Missing fields:', missingFields);
      console.log('Form data:', Object.fromEntries(formData));
      alert('Please fill in all new payer information. Missing: ' + missingFields.join(', '));
      return;
    }
    
    // First create the new payer
    createNewPayer(formData)
      .then(payerId => {
        if (payerId) {
          // Set the payer_id and proceed with payment
          formData.set('payer_id', payerId);
          processPayment(formData);
        } else {
          alert('Failed to create new payer');
        }
      })
      .catch(error => {
        console.error('Error creating payer:', error);
        alert('An error occurred while creating the payer');
      });
  } else {
    // Existing payer - proceed directly with payment
    processPayment(formData);
  }
};

// Function to create a new payer
function createNewPayer(formData) {
  // Debug: Log all form data entries
  console.log('All form data entries:');
  for (let [key, value] of formData.entries()) {
    console.log(key + ': ' + value);
  }
  
  const payerData = {
    payer_name: formData.get('payer_name'),
    new_payer_id: formData.get('new_payer_id'),
    payer_email: formData.get('payer_email'),
    payer_phone: formData.get('payer_phone')
  };
  
  console.log('Sending payer data:', payerData);
  
  return fetch(`${window.APP_BASE_URL || ''}/payers/create`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify(payerData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      return data.payer_id;
    } else {
      console.error('Payer creation failed:', data);
      if (data.errors) {
        console.error('Validation errors:', data.errors);
        
        // Show specific validation errors to user
        const errorMessages = Object.values(data.errors);
        const errorMessage = errorMessages.join('\n');
        alert('Validation Error:\n' + errorMessage);
      }
      if (data.debug_data) {
        console.error('Debug data:', data.debug_data);
      }
      throw new Error(data.message || 'Failed to create payer');
    }
  });
}

// Function to process the payment
function processPayment(formData) {
  // Get the form element
  const form = document.getElementById('paymentForm');
  
  // Debug: Log all form data
  console.log('=== FORM DATA DEBUG ===');
  for (let [key, value] of formData.entries()) {
    console.log(`${key}: ${value}`);
  }
  
  // Debug: Check payment method specifically
  const paymentMethodInput = document.getElementById('paymentMethod_input');
  console.log('Payment method input element:', paymentMethodInput);
  console.log('Payment method input value:', paymentMethodInput ? paymentMethodInput.value : 'NOT FOUND');
  
  // If payment method is missing from FormData but exists in DOM, add it manually
  if (!formData.get('payment_method') && paymentMethodInput && paymentMethodInput.value) {
    console.log('Adding payment method manually to FormData');
    formData.set('payment_method', paymentMethodInput.value);
  }
  
  // Validate required fields
  const requiredFields = ['contribution_id', 'amount_paid', 'payment_method', 'payment_date'];
  const missingFields = requiredFields.filter(field => !formData.get(field));

  if (missingFields.length > 0) {
    console.log('Missing required fields:', missingFields);
    alert('Please fill in all required fields');
    return;
  }

  // Calculate remaining balance
  const contributionSelect = document.getElementById('contributionId');
  const contributionAmount = parseFloat(contributionSelect.options[contributionSelect.selectedIndex].dataset.amount) || 0;
  const amountPaid = parseFloat(formData.get('amount_paid')) || 0;
  const remainingBalance = contributionAmount - amountPaid;

  formData.set('is_partial_payment', remainingBalance > 0 ? '1' : '0');
  formData.set('remaining_balance', remainingBalance.toString());
  
  // Ensure payer_id is set for existing payers
  if (window.selectedPayerId) {
    formData.set('payer_id', window.selectedPayerId);
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
      const modal = bootstrap.Modal.getInstance(document.getElementById('addPaymentModal'));
      modal.hide();
      location.reload();
    } else {
      // Check if this is a fully paid contribution confirmation case
      if (data.message && data.message.includes('Already Fully Paid')) {
        // Show confirmation dialog instead of error alert
        const confirmed = confirm(data.message + '\n\nDo you want to add another payment group for this contribution?');
        if (confirmed) {
          // Add bypass flag and resubmit
          formData.set('bypass_duplicate_check', '1');
          processPayment(formData);
        }
      } else {
        alert('Error: ' + (data.message || 'Failed to add payment'));
      }
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred while adding the payment');
  });
}
</script>

<!-- School ID Scanner Modal -->
<div class="modal fade" id="schoolIDScannerModal" tabindex="-1" aria-labelledby="schoolIDScannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="schoolIDScannerModalLabel">
                    <i class="fas fa-id-card me-2"></i>Scan School ID
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="text-center mb-4">
                    <p class="text-muted">Scan the QR code from the student's school ID to automatically populate the Payer ID field</p>
                </div>

                <!-- Scanner Container -->
                <div class="scanner-container mb-4">
                    <div id="schoolIDReader" style="position: relative;">
                        <video id="schoolIDVideo" autoplay playsinline style="width: 100%; border: 2px solid #0dcaf0; border-radius: 8px;"></video>
                        
                        <!-- Overlay with scanning area -->
                        <div class="scanner-overlay-school">
                            <div class="scan-line-school"></div>
                        </div>
                    </div>
                </div>

                <!-- Scanning Status -->
                <div id="schoolIDScannerStatus" class="text-center">
                    <p class="text-muted">
                        <i class="fas fa-camera me-2"></i>
                        Point camera at school ID QR code
                    </p>
                </div>

                <!-- Manual Input Option -->
                <div class="text-center mt-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleSchoolIDManualInput()">
                        <i class="fas fa-keyboard me-1"></i>Or Enter Student ID Manually
                    </button>
                </div>

                <!-- Manual Input Form (Initially Hidden) -->
                <div id="schoolIDManualInputSection" style="display: none; margin-top: 20px;">
                    <div class="card border-info">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-keyboard me-2"></i>Enter Student ID
                            </h6>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                <input type="text" class="form-control" id="manualStudentIDInput" placeholder="Enter student ID (e.g., 2024-12345)">
                                <button class="btn btn-info" type="button" onclick="useManualStudentID()">
                                    <i class="fas fa-check me-1"></i>Use This ID
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="stopSchoolIDScanner()" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.scanner-container {
    position: relative;
    max-width: 500px;
    margin: 0 auto;
}

.scanner-overlay-school {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 200px;
    height: 200px;
    border: 2px solid #0dcaf0;
    border-radius: 8px;
    pointer-events: none;
}

.scanner-overlay-school::before,
.scanner-overlay-school::after {
    content: '';
    position: absolute;
    width: 30px;
    height: 30px;
    border: 3px solid #0dcaf0;
}

.scanner-overlay-school::before {
    top: -3px;
    left: -3px;
    border-right: none;
    border-bottom: none;
    border-top-left-radius: 8px;
}

.scanner-overlay-school::after {
    bottom: -3px;
    right: -3px;
    border-left: none;
    border-top: none;
    border-bottom-right-radius: 8px;
}

.scan-line-school {
    position: absolute;
    top: 10%;
    left: 50%;
    transform: translateX(-50%);
    width: 180px;
    height: 2px;
    background: linear-gradient(to bottom, transparent, #0dcaf0, transparent);
    animation: scanSchool 2s linear infinite;
}

@keyframes scanSchool {
    0% { top: 10%; opacity: 1; }
    50% { top: 90%; opacity: 1; }
    100% { top: 10%; opacity: 0.3; }
}
</style>

<script>
let schoolIDScannerStream = null;
let schoolIDScannerCanvas = null;
let schoolIDScannerContext = null;

// Function to open school ID scanner
function openSchoolIDScanner() {
    const scannerModal = new bootstrap.Modal(document.getElementById('schoolIDScannerModal'));
    scannerModal.show();
}

// Function to start the school ID scanner
async function startSchoolIDScanner() {
    try {
        // Get user media (camera)
        schoolIDScannerStream = await navigator.mediaDevices.getUserMedia({ 
      video: { 
                facingMode: 'environment', // Use back camera on mobile
        width: { ideal: 640 },
        height: { ideal: 480 }
      } 
    });
    
        const video = document.getElementById('schoolIDVideo');
        video.srcObject = schoolIDScannerStream;
    
        // Wait for video to be ready
    video.onloadedmetadata = () => {
      video.play();
      
            // Create canvas for scanning
            schoolIDScannerCanvas = document.createElement('canvas');
            schoolIDScannerCanvas.width = video.videoWidth;
            schoolIDScannerCanvas.height = video.videoHeight;
            schoolIDScannerContext = schoolIDScannerCanvas.getContext('2d');
            
            // Start scanning loop
            scanSchoolIDQRCode();
    };
    
  } catch (error) {
    console.error('Error accessing camera:', error);
    showNotification('Unable to access camera. Please check permissions.', 'error');
        document.getElementById('schoolIDScannerStatus').innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Camera access denied. Please use manual input instead.
            </div>
        `;
    }
}

// Function to scan for school ID QR codes
function scanSchoolIDQRCode() {
    const video = document.getElementById('schoolIDVideo');
  
  if (video.readyState === video.HAVE_ENOUGH_DATA) {
        schoolIDScannerContext.drawImage(video, 0, 0, schoolIDScannerCanvas.width, schoolIDScannerCanvas.height);
        const imageData = schoolIDScannerContext.getImageData(0, 0, schoolIDScannerCanvas.width, schoolIDScannerCanvas.height);
    
        // Use jsQR library to detect QR code
    if (typeof jsQR !== 'undefined') {
      const code = jsQR(imageData.data, imageData.width, imageData.height);
      
      if (code) {
                console.log('School ID QR Code detected:', code.data);
                stopSchoolIDScanner();
                processSchoolIDQRCode(code.data);
        return;
      }
    }
  }
  
    // Continue scanning
    requestAnimationFrame(scanSchoolIDQRCode);
}

// Function to process school ID QR code data
function processSchoolIDQRCode(qrData) {
    console.log('Processing school ID QR data:', qrData);
    
    try {
        // Try to parse as JSON first (if QR contains structured data)
        let studentData;
        try {
            studentData = JSON.parse(qrData);
        } catch (e) {
            // If not JSON, treat as plain text and parse manually
            studentData = parseSchoolIDString(qrData);
        }
        
        // Extract student ID from various possible formats
        let studentID = null;
        let studentName = null;
        let courseCode = null;
        
        if (studentData.student_id) {
            studentID = studentData.student_id;
        } else if (studentData.id) {
            studentID = studentData.id;
        } else if (studentData.payer_id) {
            studentID = studentData.payer_id;
        }
        
        if (studentData.name || studentData.student_name || studentData.payer_name) {
            studentName = studentData.name || studentData.student_name || studentData.payer_name;
        }
        
        if (studentData.course || studentData.course_code) {
            courseCode = studentData.course || studentData.course_code;
        }
        
        if (studentID) {
            // Populate the payer ID field
            document.getElementById('payerId').value = studentID;
            
            // Populate student name if available
            if (studentName) {
                document.getElementById('payerName').value = studentName;
                
                // Generate email based on the name
                const timestamp = Date.now().toString().slice(-6);
                const emailBase = studentName.toLowerCase().replace(/\s+/g, '');
                const uniqueEmail = emailBase + timestamp + '@example.com';
                document.getElementById('payerEmail').value = uniqueEmail;
            }
            
            // Populate course code if available
            if (courseCode) {
                document.getElementById('payerCourse').value = courseCode;
            }
            
            // Show success message with parsed data
            let successMessage = `School ID scanned successfully!\nID: ${studentID}`;
            if (studentName) successMessage += `\nName: ${studentName}`;
            if (courseCode) successMessage += `\nCourse: ${courseCode}`;
            
            showNotification(successMessage, 'success');
            
            // Close the scanner modal
            const scannerModal = bootstrap.Modal.getInstance(document.getElementById('schoolIDScannerModal'));
            if (scannerModal) {
                scannerModal.hide();
            }
            
        } else {
            showNotification('Could not extract student ID from QR code', 'warning');
        }
        
    } catch (error) {
        console.error('Error processing school ID QR code:', error);
        showNotification('Error processing school ID QR code', 'error');
    }
}

// Function to parse school ID string format: IDNumberNameCourseCode
function parseSchoolIDString(qrData) {
    console.log('Parsing school ID string:', qrData);
    
    // Pattern: Start with digits (ID), followed by letters/names, ending with course code
    // Course codes are typically 3-6 characters at the end (BSIT1, CS, IT, etc.)
    
    // First, try to identify the ID number at the beginning
    const idMatch = qrData.match(/^(\d+)/);
    if (!idMatch) {
        return { student_id: qrData }; // If no digits at start, return as-is
    }
    
    const idNumber = idMatch[1];
    const remainingString = qrData.substring(idNumber.length);
    
    console.log('ID Number:', idNumber);
    console.log('Remaining string:', remainingString);
    
    // Try to identify course code at the end
    // Course codes are typically 2-6 characters, often ending with numbers
    const courseCodeMatch = remainingString.match(/([A-Z]{2,6}\d?)$/);
    let courseCode = null;
    let nameString = remainingString;
  
  if (courseCodeMatch) {
    courseCode = courseCodeMatch[1];
        nameString = remainingString.substring(0, remainingString.length - courseCode.length);
        console.log('Course Code:', courseCode);
        console.log('Name String:', nameString);
    }
    
    // Clean up the name string
    let studentName = nameString.trim();
    
    // Handle common name patterns
    // Remove extra spaces and normalize
    studentName = studentName.replace(/\s+/g, ' ').trim();
    
    // If name contains dots (like "C.OCERO"), try to format it better
    if (studentName.includes('.')) {
        // Split by dots and join with spaces, but keep initials
        const parts = studentName.split('.');
        studentName = parts.map(part => part.trim()).join('. ').trim();
    }
    
    console.log('Final parsed data:', {
        student_id: idNumber,
        name: studentName,
        course: courseCode
    });
    
    return {
        student_id: idNumber,
        name: studentName,
        course: courseCode
    };
}

// Function to stop the school ID scanner
function stopSchoolIDScanner() {
    if (schoolIDScannerStream) {
        schoolIDScannerStream.getTracks().forEach(track => track.stop());
        schoolIDScannerStream = null;
    }
}

// Toggle manual input for school ID
function toggleSchoolIDManualInput() {
    const manualSection = document.getElementById('schoolIDManualInputSection');
    manualSection.style.display = manualSection.style.display === 'none' ? 'block' : 'none';
}

// Use manual student ID
function useManualStudentID() {
    const studentID = document.getElementById('manualStudentIDInput').value.trim();
    
    if (!studentID) {
        showNotification('Please enter a student ID', 'warning');
        return;
    }
    
    // Populate the payer ID field
    document.getElementById('payerId').value = studentID;
    
    // Show success message
    showNotification(`Student ID entered: ${studentID}`, 'success');
    
    // Close the scanner modal
    const scannerModal = bootstrap.Modal.getInstance(document.getElementById('schoolIDScannerModal'));
    if (scannerModal) {
        scannerModal.hide();
    }
}

// Event listener for school ID scanner modal
document.addEventListener('DOMContentLoaded', function() {
    const schoolIDScannerModal = document.getElementById('schoolIDScannerModal');
    
    if (schoolIDScannerModal) {
        // Start scanner when modal opens
        schoolIDScannerModal.addEventListener('shown.bs.modal', function() {
            startSchoolIDScanner();
        });
        
        // Stop scanner when modal closes
        schoolIDScannerModal.addEventListener('hidden.bs.modal', function() {
            stopSchoolIDScanner();
            document.getElementById('schoolIDManualInputSection').style.display = 'none';
            document.getElementById('manualStudentIDInput').value = '';
    });
  }
});
</script>
