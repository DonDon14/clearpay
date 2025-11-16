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
                                        <button type="button" class="btn btn-outline-primary" onclick="onScanSchoolIDFromNewPayer()" title="Scan School ID">
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
                                    <input type="tel" class="form-control" id="payerPhone" name="payer_phone" 
                                           placeholder="09123456789" maxlength="11">
                                    <small class="form-text text-muted">Must be exactly 11 digits (numbers only)</small>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="payerCourse" class="form-label">Course/Department <small class="text-muted">(Optional)</small></label>
                                    <input type="text" class="form-control" id="payerCourse" name="payer_course" placeholder="e.g., BS Computer Science, IT Department">
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
                            <label for="amountPaid" class="form-label">Amount To Pay</label>
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
                            <input type="datetime-local" class="form-control" id="paymentDate" name="payment_date" value="<?= format_date_for_input() ?>" required>
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
                <div class="scanner-container mb-4">
                    <div id="schoolIDReader" style="position: relative;">
                        <video id="schoolIDVideo" autoplay playsinline style="width: 100%; border: 2px solid #0dcaf0; border-radius: 8px;"></video>
                        <div class="scanner-overlay-school">
                            <div class="scan-line-school"></div>
                        </div>
                    </div>
                </div>
                <div id="schoolIDScannerStatus" class="text-center">
                    <p class="text-muted">
                        <i class="fas fa-camera me-2"></i>
                        Point camera at school ID QR code
                    </p>
                </div>
                <div class="text-center mt-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleSchoolIDManualInput()">
                        <i class="fas fa-keyboard me-1"></i>Or Enter Student ID Manually
                    </button>
                </div>
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

/* Fix: Make addPaymentModal stack properly above dashboard, and schoolIDScannerModal always at top */
#addPaymentModal {
    z-index: 1060 !important;
}
#addPaymentModal .modal-dialog {
    z-index: 1061 !important;
}
#addPaymentModal .modal-content {
    z-index: 1062 !important;
    position: relative;
}

.modal-backdrop {
    z-index: 1055 !important;
}

/* School ID Scanner Modal should always be on top */
#schoolIDScannerModal {
    z-index: 2000 !important;
}
#schoolIDScannerModal .modal-dialog {
    z-index: 2001 !important;
}
#schoolIDScannerModal .modal-content {
    z-index: 2002 !important;
    position: relative;
}
body:has(#schoolIDScannerModal.show) .modal-backdrop.show:last-of-type {
    z-index: 1999 !important;
    background-color: rgba(0,0,0,0.8) !important;
}
/* Slight fade for add payment modal when schoolIDScanner shows */
body:has(#schoolIDScannerModal.show) #addPaymentModal {
    opacity: 0.4 !important;
    pointer-events: none !important;
}

/* Remove padding/margin issues for overlapping modals/dialogs */
#addPaymentModal .modal-dialog,
#schoolIDScannerModal .modal-dialog {
    margin: 2rem auto;
}
</style>

<script>
// Single comprehensive DOMContentLoaded listener for all modal functionality
document.addEventListener("DOMContentLoaded", function() {
  // CRITICAL: Fix form action immediately on page load (fix for contributions page issue)
  const paymentForm = document.getElementById('paymentForm');
  if (paymentForm) {
    const correctAction = '<?= base_url('payments/save') ?>';
    // Check if action is incorrect (points to contributions/save or doesn't include payments/save)
    if (!paymentForm.action.includes('payments/save')) {
      console.warn('Fixing incorrect form action on page load:', paymentForm.action, '->', correctAction);
      paymentForm.action = correctAction;
    }
    console.log('Form action on page load:', paymentForm.action);
  }
  
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

  // Set default payment date to current date and time (if not already set by PHP)
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

  // Payer Search Functionality - Extract as reusable function
  function searchPayers(query, inputElement, dropdownElement) {
    if (!query || query.trim().length < 2) {
      if (dropdownElement) {
        dropdownElement.style.display = 'none';
      }
      return;
    }

    const searchTerm = query.trim();
    
    // Fetch payers from database
    fetch(`${window.APP_BASE_URL || ''}/payments/search-payers?term=${encodeURIComponent(searchTerm)}`)
      .then(response => response.json())
      .then(data => {
        if (data.success && data.results && data.results.length > 0) {
          if (dropdownElement) {
            dropdownElement.innerHTML = '';
            data.results.forEach(payer => {
              const item = document.createElement('a');
              item.className = 'list-group-item list-group-item-action';
              item.href = '#';
              item.innerHTML = `<strong>${payer.payer_name}</strong> (ID: ${payer.payer_id})<br><small class="text-muted">${payer.contact_number || 'N/A'} | ${payer.email_address || 'N/A'}</small>`;
              item.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (inputElement) {
                  inputElement.value = `${payer.payer_name} (${payer.payer_id})`;
                }
                
                const existingPayerIdEl = document.getElementById('existingPayerId');
                if (existingPayerIdEl) {
                  existingPayerIdEl.value = payer.id;
                }
                
                if (dropdownElement) {
                  dropdownElement.style.display = 'none';
                }
                
                // Store payer ID for later use when contribution is selected
                window.selectedPayerId = payer.id;
                
                // Clear any existing warnings
                const existingAlert = document.getElementById('unpaidContributionsAlert');
                if (existingAlert) {
                  existingAlert.remove();
                }
                
                console.log('Payer selected:', payer.payer_name, 'ID:', payer.id);
              });
              dropdownElement.appendChild(item);
            });
            dropdownElement.style.display = 'block';
          }
        } else {
          if (dropdownElement) {
            dropdownElement.innerHTML = '<div class="list-group-item text-muted">No payers found</div>';
            dropdownElement.style.display = 'block';
          }
        }
      })
      .catch(error => {
        console.error('Error searching payers:', error);
        if (dropdownElement) {
          dropdownElement.innerHTML = '<div class="list-group-item text-danger">Error searching payers</div>';
          dropdownElement.style.display = 'block';
        }
      });
  }
  
  // Make searchPayers globally available
  window.searchPayers = searchPayers;
  
  // Attach payer search functionality
  let searchTimeout;
  if (payerSelectInput && payerDropdown) {
    payerSelectInput.addEventListener('input', function() {
      const searchTerm = this.value.trim();
      
      clearTimeout(searchTimeout);
      
      if (searchTerm.length < 2) {
        payerDropdown.style.display = 'none';
        return;
      }

      searchTimeout = setTimeout(function() {
        searchPayers(searchTerm, payerSelectInput, payerDropdown);
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
  
  // Fully Paid button handler function - reusable
  function handleFullyPaidClick(e) {
    // CRITICAL: Stop all event propagation FIRST before anything else
    if (e) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
    }
    
    console.log('=== FULLY PAID BUTTON CLICKED ==='); // Debug log
    
    const remainingBalanceEl = document.getElementById('remainingBalance');
    const amountPaidEl = document.getElementById('amountPaid');
    
    // Find contribution field - could be select or input, and there might be multiple elements with same ID
    // Search for the visible contribution field by finding the label first
    let contributionSelect = null;
    const modal = document.getElementById('addPaymentModal');
    
    if (modal) {
      // Find the "Contribution" label
      const contributionLabel = Array.from(modal.querySelectorAll('label')).find(
        label => label.textContent.trim().toLowerCase().includes('contribution') && 
                 label.getAttribute('for') === 'contributionId'
      );
      
      if (contributionLabel) {
        // Find the associated field (could be select or input)
        const fieldId = contributionLabel.getAttribute('for');
        const selectField = modal.querySelector(`select#${fieldId}`);
        const inputField = modal.querySelector(`input#${fieldId}:not([type="hidden"])`);
        contributionSelect = selectField || inputField;
        console.log('Found contribution field via label:', contributionSelect);
      }
      
      // Fallback: search for any visible select or input with id="contributionId"
      if (!contributionSelect) {
        contributionSelect = modal.querySelector('select#contributionId') || 
                           modal.querySelector('input#contributionId:not([type="hidden"])');
        console.log('Found contribution field via fallback search:', contributionSelect);
      }
    }
    
    // Final fallback: use getElementById (might return hidden element)
    if (!contributionSelect) {
      contributionSelect = document.getElementById('contributionId');
      console.log('Found contribution field via getElementById:', contributionSelect);
    }
    
    if (!amountPaidEl) {
      console.error('Amount paid element not found');
      alert('Error: Amount paid field not found. Please refresh the page.');
      return false;
    }
    
    console.log('Amount paid element found:', amountPaidEl);
    console.log('Remaining balance element:', remainingBalanceEl);
    console.log('Contribution select element:', contributionSelect);
    
    // Get contribution amount - try multiple methods
    let contributionAmount = 0;
    let remainingBalance = 0;
    
    // Method 1: Try to get from remaining balance field
    if (remainingBalanceEl && remainingBalanceEl.value) {
      remainingBalance = parseFloat(remainingBalanceEl.value);
      console.log('Got remaining balance from field:', remainingBalance);
    }
    
    // Method 2: Get from contribution field (could be select dropdown OR read-only input)
    if (contributionSelect) {
      console.log('Contribution element found:', contributionSelect);
      console.log('Contribution element tagName:', contributionSelect.tagName);
      console.log('Contribution element type:', contributionSelect.type);
      
      // Check if it's a SELECT dropdown
      if (contributionSelect.tagName === 'SELECT') {
        console.log('Contribution is a SELECT dropdown');
        console.log('Contribution select value:', contributionSelect.value);
        console.log('Contribution select selectedIndex:', contributionSelect.selectedIndex);
        
        if (contributionSelect.value && contributionSelect.selectedIndex >= 0) {
          const selectedOption = contributionSelect.options[contributionSelect.selectedIndex];
          console.log('Selected option:', selectedOption);
          console.log('Selected option dataset:', selectedOption.dataset);
          console.log('Selected option data-amount:', selectedOption.dataset.amount);
          
          contributionAmount = parseFloat(selectedOption.dataset.amount || 0);
          console.log('Contribution amount from select:', contributionAmount);
          
          // If we don't have remaining balance, calculate it
          if (!remainingBalance && contributionAmount > 0) {
            const currentAmountPaid = parseFloat(amountPaidEl.value || 0);
            remainingBalance = contributionAmount - currentAmountPaid;
            console.log('Calculated remaining balance:', remainingBalance, '(contribution:', contributionAmount, '- paid:', currentAmountPaid, ')');
          }
        } else {
          console.warn('No contribution selected in dropdown');
        }
      } 
      // Check if it's an INPUT field (read-only)
      else if (contributionSelect.tagName === 'INPUT') {
        console.log('Contribution is a read-only INPUT field');
        const contributionText = contributionSelect.value || contributionSelect.textContent || '';
        console.log('Contribution input value/text:', contributionText);
        console.log('Contribution input value length:', contributionText.length);
        console.log('Contribution input value charCodes:', Array.from(contributionText).map(c => c.charCodeAt(0)));
        
        // Parse amount from text like "Monthly Fee - ₱1,000.00"
        // Try multiple regex patterns to handle different formats
        let amountMatch = null;
        
        // Pattern 1: Match "₱" followed by number (handles "₱1,000.00" or " - ₱1,000.00")
        amountMatch = contributionText.match(/₱\s*([\d,]+(?:\.\d{2})?)/);
        console.log('Pattern 1 (₱...) match:', amountMatch);
        
        // Pattern 2: Match "- ₱" followed by number (handles " - ₱1,000.00")
        if (!amountMatch) {
          amountMatch = contributionText.match(/-\s*₱\s*([\d,]+(?:\.\d{2})?)/);
          console.log('Pattern 2 (- ₱...) match:', amountMatch);
        }
        
        // Pattern 3: Match any number with commas and decimals (fallback)
        if (!amountMatch) {
          amountMatch = contributionText.match(/([\d,]+\.\d{2})/);
          console.log('Pattern 3 (number with decimals) match:', amountMatch);
        }
        
        // Pattern 4: Match any number with commas (no decimals)
        if (!amountMatch) {
          amountMatch = contributionText.match(/([\d,]+)/);
          console.log('Pattern 4 (number only) match:', amountMatch);
        }
        
        if (amountMatch && amountMatch[1]) {
          // Remove commas and parse
          const amountStr = amountMatch[1].replace(/,/g, '');
          contributionAmount = parseFloat(amountStr);
          console.log('Extracted amount string:', amountStr);
          console.log('Parsed contribution amount from input text:', contributionAmount);
          
          // Validate the parsed amount
          if (isNaN(contributionAmount) || contributionAmount <= 0) {
            console.error('Parsed amount is invalid:', contributionAmount);
            contributionAmount = 0;
          } else {
            // If we don't have remaining balance, calculate it
            if (!remainingBalance && contributionAmount > 0) {
              const currentAmountPaid = parseFloat(amountPaidEl.value || 0);
              remainingBalance = contributionAmount - currentAmountPaid;
              console.log('Calculated remaining balance:', remainingBalance, '(contribution:', contributionAmount, '- paid:', currentAmountPaid, ')');
            }
          }
        } else {
          console.error('Could not parse amount from contribution input text:', contributionText);
          console.error('Tried all regex patterns but none matched');
        }
      } else {
        console.warn('Contribution element is neither SELECT nor INPUT:', contributionSelect.tagName);
      }
    } else {
      console.warn('Contribution element not found');
    }
    
    // Determine what amount to set
    let amountToSet = 0;
    
    if (remainingBalance > 0) {
      amountToSet = remainingBalance;
      console.log('Using remaining balance:', amountToSet);
    } else if (contributionAmount > 0) {
      amountToSet = contributionAmount;
      console.log('Using full contribution amount:', amountToSet);
    } else {
      console.error('Cannot determine amount - no contribution selected or amount available');
      alert('Please select a contribution first, or the contribution amount could not be determined.');
      return false;
    }
    
    // Set the amount
    if (amountToSet > 0) {
      amountPaidEl.value = amountToSet.toFixed(2);
      console.log('Set amountPaid value to:', amountPaidEl.value);
      
      // Force update the input (some frameworks need this)
      amountPaidEl.setAttribute('value', amountToSet.toFixed(2));
      
      // Trigger multiple events to ensure all listeners fire
      amountPaidEl.dispatchEvent(new Event('input', { bubbles: true, cancelable: true }));
      amountPaidEl.dispatchEvent(new Event('change', { bubbles: true, cancelable: true }));
      
      // Also trigger focus/blur to ensure validation
      amountPaidEl.focus();
      setTimeout(() => amountPaidEl.blur(), 10);
      
      // Call updatePaymentStatus if available
      if (typeof updatePaymentStatus === 'function') {
        console.log('Calling updatePaymentStatus function');
        updatePaymentStatus();
      } else {
        console.warn('updatePaymentStatus function not available');
      }
      
      console.log('=== SUCCESS: Amount set to', amountToSet.toFixed(2), '===');
    } else {
      console.error('Amount to set is 0 or invalid');
      alert('Unable to determine the amount to set. Please check that a contribution is selected.');
    }
    
    return false; // Prevent any form submission
  }
  
  // CRITICAL: Attach handler with HIGHEST priority using capture phase on window
  // This must run BEFORE any other handlers
  window.addEventListener('click', function(e) {
    const fullyPaidBtn = e.target.closest('#fullyPaidBtn');
    if (fullyPaidBtn) {
      const modal = document.getElementById('addPaymentModal');
      if (modal && modal.classList.contains('show')) {
        handleFullyPaidClick(e);
      }
    }
  }, { capture: true, passive: false });
  
  // Also attach directly to button when modal is shown
  if (addPaymentModal) {
    addPaymentModal.addEventListener('shown.bs.modal', function() {
      console.log('=== MODAL SHOWN - RE-ATTACHING ALL EVENT LISTENERS ===');
      
      // CRITICAL: Ensure form action is always correct (fix for contributions page issue)
      const paymentForm = document.getElementById('paymentForm');
      if (paymentForm) {
        const correctAction = '<?= base_url('payments/save') ?>';
        if (paymentForm.action !== correctAction && !paymentForm.action.includes('payments/save')) {
          console.warn('Fixing incorrect form action:', paymentForm.action, '->', correctAction);
          paymentForm.action = correctAction;
        }
        console.log('Form action verified:', paymentForm.action);
      }
      
      // Re-get elements from the modal (they might be recreated)
      const modalAmountPaidEl = document.getElementById('amountPaid');
      const modalContributionSelect = document.getElementById('contributionId');
      const modalPayerSelectInput = document.getElementById('payerSelect');
      const modalPayerDropdown = document.getElementById('payerDropdown');
      
      // Re-attach amount paid input listener (CRITICAL for contributions page)
      if (modalAmountPaidEl) {
        // Remove any existing listeners by cloning
        const newAmountInput = modalAmountPaidEl.cloneNode(true);
        modalAmountPaidEl.parentNode.replaceChild(newAmountInput, modalAmountPaidEl);
        
        // Re-attach the input event listener
        newAmountInput.addEventListener('input', function() {
          console.log('Amount paid input changed:', newAmountInput.value);
          if (typeof updatePaymentStatus === 'function') {
            updatePaymentStatus();
          }
        });
        console.log('Re-attached amount paid input listener');
      }
      
      // Re-attach contribution change listener
      if (modalContributionSelect) {
        // Remove any existing listeners by cloning if it's a select
        if (modalContributionSelect.tagName === 'SELECT') {
          const newContributionSelect = modalContributionSelect.cloneNode(true);
          modalContributionSelect.parentNode.replaceChild(newContributionSelect, modalContributionSelect);
          
          newContributionSelect.addEventListener('change', function() {
            console.log('Contribution changed:', newContributionSelect.value);
            if (typeof updatePaymentStatus === 'function') {
              updatePaymentStatus();
            }
            
            // Check contribution status if payer is selected
            if (window.selectedPayerId && this.value) {
              if (typeof checkSpecificContribution === 'function') {
                checkSpecificContribution(window.selectedPayerId, this.value);
              }
            }
          });
          console.log('Re-attached contribution select change listener');
        }
        
        // Trigger update if contribution is already selected/pre-filled
        if (modalContributionSelect.value || (modalContributionSelect.tagName === 'INPUT' && modalContributionSelect.value)) {
          console.log('Contribution already selected/pre-filled:', modalContributionSelect.value);
          if (typeof updatePaymentStatus === 'function') {
            setTimeout(() => updatePaymentStatus(), 100);
          }
        }
      }
      
      // Re-attach payer search functionality
      if (modalPayerSelectInput && modalPayerDropdown) {
        // Remove any existing listeners by cloning
        const newPayerInput = modalPayerSelectInput.cloneNode(true);
        modalPayerSelectInput.parentNode.replaceChild(newPayerInput, modalPayerSelectInput);
        
        let searchTimeout;
        newPayerInput.addEventListener('input', function() {
          const query = this.value.trim();
          clearTimeout(searchTimeout);
          
          if (query.length >= 2) {
            searchTimeout = setTimeout(() => {
              if (typeof window.searchPayers === 'function') {
                window.searchPayers(query, newPayerInput, modalPayerDropdown);
              } else {
                console.error('searchPayers function not available');
              }
            }, 300);
          } else {
            modalPayerDropdown.style.display = 'none';
          }
        });
        console.log('Re-attached payer search input listener');
      }
      
      // Re-attach Fully Paid button handlers
      const fullyPaidBtn = document.getElementById('fullyPaidBtn');
      if (fullyPaidBtn) {
        console.log('Fully Paid button found, attaching handlers');
        
        // Remove any existing handlers by cloning
        const newBtn = fullyPaidBtn.cloneNode(true);
        fullyPaidBtn.parentNode.replaceChild(newBtn, fullyPaidBtn);
        
        // Attach multiple event types with highest priority
        newBtn.onclick = handleFullyPaidClick;
        newBtn.onmousedown = function(e) { 
          console.log('Fully Paid button mousedown event');
          handleFullyPaidClick(e); 
          return false; 
        };
        newBtn.addEventListener('click', function(e) {
          console.log('Fully Paid button click event (addEventListener)');
          handleFullyPaidClick(e);
        }, { capture: true, once: false });
        newBtn.addEventListener('mousedown', function(e) {
          console.log('Fully Paid button mousedown event (addEventListener)');
          handleFullyPaidClick(e);
        }, { capture: true, once: false });
        
        console.log('Fully Paid button handlers attached on modal show');
      } else {
        console.error('Fully Paid button NOT found when modal shown!');
      }
    }, { once: false });
  }
  
  // Also attach on DOMContentLoaded as fallback
  const fullyPaidBtn = document.getElementById('fullyPaidBtn');
  if (fullyPaidBtn) {
    fullyPaidBtn.onclick = handleFullyPaidClick;
    fullyPaidBtn.onmousedown = function(e) { handleFullyPaidClick(e); return false; };
    fullyPaidBtn.addEventListener('click', handleFullyPaidClick, { capture: true });
    fullyPaidBtn.addEventListener('mousedown', handleFullyPaidClick, { capture: true });
    console.log('Fully Paid button handlers attached on DOMContentLoaded'); // Debug log
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
      // Don't drag when clicking buttons or input elements
      if (e.target.classList.contains('btn-close') || 
          e.target.tagName === 'BUTTON' || 
          e.target.tagName === 'INPUT' || 
          e.target.tagName === 'SELECT' ||
          e.target.tagName === 'TEXTAREA' ||
          e.target.closest('button') ||
          e.target.closest('input') ||
          e.target.closest('select') ||
          e.target.closest('textarea')) {
        return;
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

// Initialize phone number field for add payment modal
(function() {
  'use strict';
  
  function initPhoneValidation() {
    if (typeof window.initPhoneNumberField === 'function') {
      window.initPhoneNumberField('payerPhone', {
        required: true,
        errorMessage: 'Contact number must be exactly 11 digits'
      });
    }
  }
  
  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPhoneValidation);
  } else {
    initPhoneValidation();
  }
  
  // Also initialize when modal is shown (for dynamic modals)
  const addPaymentModal = document.getElementById('addPaymentModal');
  if (addPaymentModal) {
    addPaymentModal.addEventListener('shown.bs.modal', initPhoneValidation);
  }
})();

// Reset modal function
function resetPaymentModal() {
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
  
  // Set default payment date to current date and time (using date helper format)
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
  const remainingBalanceEl = document.getElementById('remainingBalance');
  const paymentStatusEl = document.getElementById('paymentStatus');
  
  // Find contribution field - could be select or input
  let contributionSelect = null;
  const modal = document.getElementById('addPaymentModal');
  
  if (modal) {
    // Find the "Contribution" label
    const contributionLabel = Array.from(modal.querySelectorAll('label')).find(
      label => label.textContent.trim().toLowerCase().includes('contribution') && 
               label.getAttribute('for') === 'contributionId'
    );
    
    if (contributionLabel) {
      // Find the associated field (could be select or input)
      const fieldId = contributionLabel.getAttribute('for');
      const selectField = modal.querySelector(`select#${fieldId}`);
      const inputField = modal.querySelector(`input#${fieldId}:not([type="hidden"])`);
      contributionSelect = selectField || inputField;
    }
    
    // Fallback: search for any visible select or input with id="contributionId"
    if (!contributionSelect) {
      contributionSelect = modal.querySelector('select#contributionId') || 
                         modal.querySelector('input#contributionId:not([type="hidden"])');
    }
  }
  
  // Final fallback: use getElementById
  if (!contributionSelect) {
    contributionSelect = document.getElementById('contributionId');
  }
  
  if (amountPaidEl && contributionSelect && remainingBalanceEl && paymentStatusEl) {
    const amountPaid = parseFloat(amountPaidEl.value) || 0;
    let contributionAmount = 0;
    
    // Handle SELECT dropdown
    if (contributionSelect.tagName === 'SELECT') {
      const selectedOption = contributionSelect.options[contributionSelect.selectedIndex];
      
      // Don't update status if no contribution is selected
      if (!selectedOption || selectedOption.value === '' || selectedOption.textContent.includes('Select a contribution')) {
        // Clear status and styling when no contribution is selected
        paymentStatusEl.value = '';
        paymentStatusEl.classList.remove('text-success', 'border-success', 'text-warning', 'border-warning');
        remainingBalanceEl.value = '0.00';
        return;
      }
      
      // Get contribution amount from data-amount attribute
      contributionAmount = parseFloat(selectedOption.dataset.amount) || 0;
    }
    // Handle INPUT field (read-only)
    else if (contributionSelect.tagName === 'INPUT') {
      const contributionText = contributionSelect.value || '';
      
      // Parse amount from text like "Monthly Fee - ₱1,000.00"
      let amountMatch = contributionText.match(/₱\s*([\d,]+(?:\.\d{2})?)/);
      if (!amountMatch) {
        amountMatch = contributionText.match(/-\s*₱\s*([\d,]+(?:\.\d{2})?)/);
      }
      if (!amountMatch) {
        amountMatch = contributionText.match(/([\d,]+\.\d{2})/);
      }
      if (!amountMatch) {
        amountMatch = contributionText.match(/([\d,]+)/);
      }
      
      if (amountMatch && amountMatch[1]) {
        const amountStr = amountMatch[1].replace(/,/g, '');
        contributionAmount = parseFloat(amountStr);
      }
      
      // If no contribution amount found, clear and return
      if (!contributionAmount || contributionAmount <= 0) {
        paymentStatusEl.value = '';
        paymentStatusEl.classList.remove('text-success', 'border-success', 'text-warning', 'border-warning');
        remainingBalanceEl.value = '0.00';
        return;
      }
    } else {
      // Unknown element type
      paymentStatusEl.value = '';
      paymentStatusEl.classList.remove('text-success', 'border-success', 'text-warning', 'border-warning');
      remainingBalanceEl.value = '0.00';
      return;
    }
    
    // Don't update status if contribution amount is 0 or invalid
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
  
  // Find contribution field - could be select or input
  let contributionSelect = null;
  const modal = document.getElementById('addPaymentModal');
  
  if (modal) {
    const contributionLabel = Array.from(modal.querySelectorAll('label')).find(
      label => label.textContent.trim().toLowerCase().includes('contribution') && 
               label.getAttribute('for') === 'contributionId'
    );
    
    if (contributionLabel) {
      const fieldId = contributionLabel.getAttribute('for');
      const selectField = modal.querySelector(`select#${fieldId}`);
      const inputField = modal.querySelector(`input#${fieldId}:not([type="hidden"])`);
      contributionSelect = selectField || inputField;
    }
    
    if (!contributionSelect) {
      contributionSelect = modal.querySelector('select#contributionId') || 
                         modal.querySelector('input#contributionId:not([type="hidden"])');
    }
  }
  
  if (!contributionSelect) {
    contributionSelect = document.getElementById('contributionId');
  }
  
  if (amountPaidEl && contributionSelect) {
    let contributionAmount = 0;
    
    // Handle SELECT dropdown
    if (contributionSelect.tagName === 'SELECT') {
      const selectedOption = contributionSelect.options[contributionSelect.selectedIndex];
      if (selectedOption) {
        contributionAmount = parseFloat(selectedOption.dataset.amount) || 0;
      }
    }
    // Handle INPUT field (read-only)
    else if (contributionSelect.tagName === 'INPUT') {
      const contributionText = contributionSelect.value || '';
      
      // Parse amount from text
      let amountMatch = contributionText.match(/₱\s*([\d,]+(?:\.\d{2})?)/);
      if (!amountMatch) {
        amountMatch = contributionText.match(/-\s*₱\s*([\d,]+(?:\.\d{2})?)/);
      }
      if (!amountMatch) {
        amountMatch = contributionText.match(/([\d,]+\.\d{2})/);
      }
      if (!amountMatch) {
        amountMatch = contributionText.match(/([\d,]+)/);
      }
      
      if (amountMatch && amountMatch[1]) {
        const amountStr = amountMatch[1].replace(/,/g, '');
        contributionAmount = parseFloat(amountStr);
      }
    }
    
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
    // Existing payer - ensure payer_id is set (user must select from suggestions)
    const existingPayerIdField = document.getElementById('existingPayerId');
    const existingPayerValue = existingPayerIdField ? existingPayerIdField.value : null;
    if ((!existingPayerValue || existingPayerValue.trim() === '') && !window.selectedPayerId) {
      // Helpful hint for users: they must click a suggestion, not just type the name
      alert('Please select an existing payer from the Search Payer suggestions by clicking a result (not just typing).');
      return;
    }

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
    payer_phone: formData.get('payer_phone') ? (typeof window.sanitizePhoneNumber === 'function' ? window.sanitizePhoneNumber(formData.get('payer_phone')) : formData.get('payer_phone')) : '',
    course_department: formData.get('payer_course') ? formData.get('payer_course').trim() : ''
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
  
  // Debug: Check payment method specifically - lookup by name to cover custom dropdown helper
  const paymentMethodInput = document.querySelector('input[name="payment_method"]');
  console.log('Payment method input element (by name):', paymentMethodInput);
  console.log('Payment method input value (by name):', paymentMethodInput ? paymentMethodInput.value : 'NOT FOUND');
  
  // If payment method is missing from FormData but exists in DOM, add it manually
  if (!formData.get('payment_method') && paymentMethodInput && paymentMethodInput.value) {
    console.log('Adding payment method manually to FormData:', paymentMethodInput.value);
    formData.set('payment_method', paymentMethodInput.value);
  }
  
  // Also check if payment_method is already in FormData but empty
  if (formData.get('payment_method') === '' && paymentMethodInput && paymentMethodInput.value) {
    console.log('Replacing empty payment_method with value from input:', paymentMethodInput.value);
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

  // Calculate remaining balance - handle both SELECT and INPUT contribution fields
  let contributionAmount = 0;
  const modal = document.getElementById('addPaymentModal');
  let contributionSelect = null;
  
  if (modal) {
    // Find the visible contribution field
    const contributionLabel = Array.from(modal.querySelectorAll('label')).find(
      label => label.textContent.trim().toLowerCase().includes('contribution') && 
               label.getAttribute('for') === 'contributionId'
    );
    
    if (contributionLabel) {
      const fieldId = contributionLabel.getAttribute('for');
      const selectField = modal.querySelector(`select#${fieldId}`);
      const inputField = modal.querySelector(`input#${fieldId}:not([type="hidden"])`);
      contributionSelect = selectField || inputField;
    }
    
    if (!contributionSelect) {
      contributionSelect = modal.querySelector('select#contributionId') || 
                         modal.querySelector('input#contributionId:not([type="hidden"])');
    }
  }
  
  if (!contributionSelect) {
    contributionSelect = document.getElementById('contributionId');
  }
  
  if (contributionSelect) {
    // Handle SELECT dropdown
    if (contributionSelect.tagName === 'SELECT') {
      const selectedOption = contributionSelect.options[contributionSelect.selectedIndex];
      if (selectedOption) {
        contributionAmount = parseFloat(selectedOption.dataset.amount) || 0;
      }
    }
    // Handle INPUT field (read-only)
    else if (contributionSelect.tagName === 'INPUT') {
      const contributionText = contributionSelect.value || '';
      
      // Parse amount from text like "Monthly Fee - ₱1,000.00"
      let amountMatch = contributionText.match(/₱\s*([\d,]+(?:\.\d{2})?)/);
      if (!amountMatch) {
        amountMatch = contributionText.match(/-\s*₱\s*([\d,]+(?:\.\d{2})?)/);
      }
      if (!amountMatch) {
        amountMatch = contributionText.match(/([\d,]+\.\d{2})/);
      }
      if (!amountMatch) {
        amountMatch = contributionText.match(/([\d,]+)/);
      }
      
      if (amountMatch && amountMatch[1]) {
        const amountStr = amountMatch[1].replace(/,/g, '');
        contributionAmount = parseFloat(amountStr);
      }
    }
  }
  
  // Also try to get contribution_id from form or contribution field
  let contributionId = formData.get('contribution_id');
  if (!contributionId && contributionSelect) {
    if (contributionSelect.tagName === 'SELECT') {
      contributionId = contributionSelect.value;
    } else if (contributionSelect.tagName === 'INPUT') {
      // For input fields, we might need to get the ID from a hidden field or data attribute
      const hiddenContributionId = document.querySelector('input[name="contribution_id"]');
      if (hiddenContributionId) {
        contributionId = hiddenContributionId.value;
      }
    }
  }
  
  // If contribution_id is still missing, try to extract from contribution field value
  if (!contributionId && contributionSelect && contributionSelect.value) {
    // Try to parse ID from the value (might be in format "id" or "title - amount")
    const valueParts = contributionSelect.value.split(' - ');
    if (valueParts.length > 0 && !isNaN(valueParts[0])) {
      contributionId = valueParts[0];
    } else {
      contributionId = contributionSelect.value;
    }
  }
  
  // Set contribution_id in formData if we found it
  if (contributionId) {
    formData.set('contribution_id', contributionId);
    console.log('Set contribution_id to:', contributionId);
  }
  
  const amountPaid = parseFloat(formData.get('amount_paid')) || 0;
  const remainingBalance = contributionAmount - amountPaid;

  formData.set('is_partial_payment', remainingBalance > 0 ? '1' : '0');
  formData.set('remaining_balance', remainingBalance.toString());
  
  // Ensure payer_id is set for existing payers
  // Check both the hidden field and the window variable
  const existingPayerIdField = document.getElementById('existingPayerId');
  const payerIdValue = existingPayerIdField ? existingPayerIdField.value : (window.selectedPayerId || null);
  
  if (payerIdValue) {
    formData.set('payer_id', payerIdValue);
    console.log('Setting payer_id to:', payerIdValue);
  } else {
    console.error('ERROR: payer_id is not set! Make sure you selected an existing payer.');
    alert('Please select a payer from the search suggestions by clicking on a result.');
    return; // Stop submission if payer_id is missing
  }
  
  // Final validation: Ensure contribution_id is set
  if (!contributionId || contributionId === '' || contributionId === '0') {
    console.error('ERROR: contribution_id is missing or invalid:', contributionId);
    alert('Please select a contribution. If the contribution field is pre-filled, try refreshing the page.');
    return; // Stop submission if contribution_id is missing
  }
  
  // Log all form data entries for debugging
  console.log('=== FINAL FORM DATA BEFORE SUBMISSION ===');
  console.log('contribution_id:', contributionId);
  console.log('payer_id:', payerIdValue);
  console.log('amount_paid:', formData.get('amount_paid'));
  console.log('payment_method:', formData.get('payment_method'));
  console.log('payment_date:', formData.get('payment_date'));
  console.log('is_partial_payment:', formData.get('is_partial_payment'));
  console.log('remaining_balance:', formData.get('remaining_balance'));
  
  // Log ALL form data entries
  console.log('=== ALL FORM DATA ENTRIES ===');
  for (let [key, value] of formData.entries()) {
    console.log(`${key}: ${value} (type: ${typeof value})`);
  }
  
  // Log the form action URL
  console.log('=== FORM ACTION URL ===');
  console.log('Form action:', form.action);
  console.log('Full URL will be:', window.location.origin + form.action);
  
  // Verify the action is correct and make it absolute
  let submitUrl = form.action;
  if (!submitUrl.includes('payments/save')) {
    console.error('ERROR: Form action is not pointing to payments/save!');
    console.error('Current action:', submitUrl);
    alert('ERROR: Form is configured incorrectly. Action should be payments/save but is: ' + submitUrl);
    return;
  }
  
  // Ensure URL is absolute
  if (!submitUrl.startsWith('http://') && !submitUrl.startsWith('https://')) {
    if (submitUrl.startsWith('/')) {
      submitUrl = window.location.origin + submitUrl;
    } else {
      submitUrl = window.location.origin + '/' + submitUrl;
    }
  }
  
  console.log('=== SUBMITTING TO URL ===');
  console.log('Final URL:', submitUrl);

  fetch(submitUrl, {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => {
    // Check if response is OK
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    // Check content type
    const contentType = response.headers.get('content-type');
    if (contentType && contentType.includes('application/json')) {
      return response.json();
    } else {
      // If not JSON, get text and try to parse
      return response.text().then(text => {
        try {
          return JSON.parse(text);
        } catch (e) {
          throw new Error('Server returned non-JSON response: ' + text.substring(0, 200));
        }
      });
    }
  })
  .then(data => {
    if (data.success) {
      alert('Payment added successfully!');
      const modal = bootstrap.Modal.getInstance(document.getElementById('addPaymentModal'));
      modal.hide();
      location.reload();
    } else {
      // Show detailed validation errors
      let errorMessage = data.message || 'An error occurred';
      
      if (data.errors) {
        const errorDetails = Object.entries(data.errors).map(([field, message]) => {
          return `${field}: ${message}`;
        }).join('\n');
        errorMessage += '\n\nValidation Errors:\n' + errorDetails;
      }
      
      if (data.debug) {
        console.error('Server debug info:', data.debug);
        errorMessage += '\n\n(Check console for debug details)';
      }
      
      console.error('Payment submission failed:', data);
      
      // Check if this is a fully paid contribution confirmation case
      if (data.message && data.message.includes('Already Fully Paid')) {
        // Show confirmation dialog instead of error alert
        const confirmed = confirm(data.message);
        if (confirmed) {
          // Add bypass flag and resubmit
          formData.set('bypass_duplicate_check', '1');
          processPayment(formData);
        }
      } else {
        // Show detailed error message (only once)
        alert(errorMessage);
      }
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred while adding the payment');
  });
}
</script>

<script>
window._openSchoolIDFromNewPayer = false;
window._schoolIDScanHandlerAttached = false;
function onScanSchoolIDFromNewPayer() {
  window._openSchoolIDFromNewPayer = true;
  // Forcibly hide with jQuery for bulletproof Bootstrap compability
  if (window.jQuery) {
    $('#addPaymentModal').modal('hide');
  } else {
    const addModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('addPaymentModal'));
    addModal.hide();
  }
}
document.addEventListener('DOMContentLoaded', function() {
  if (window._schoolIDScanHandlerAttached) return;
  window._schoolIDScanHandlerAttached = true;
  const addModalElem = document.getElementById('addPaymentModal');
  if (addModalElem) {
    addModalElem.addEventListener('hidden.bs.modal', function() {
      if (window._openSchoolIDFromNewPayer) {
        // Bulletproof: Remove leftover backdrops before opening scanner
        if (window.jQuery) {
          $('.modal-backdrop').remove();
        } else {
          document.querySelectorAll('.modal-backdrop').forEach(el=>el.parentNode.removeChild(el));
        }
        openSchoolIDScanner();
      }
    });
  }
});
</script>

