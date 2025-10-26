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
            <div class="input-group">
              <input type="text" class="form-control" id="payerSelect" placeholder="Type to search payers or scan ID..." autocomplete="off">
              <button type="button" class="btn btn-outline-primary" onclick="scanIDForExistingPayer()" title="Scan School ID">
                <i class="fas fa-qrcode"></i>
              </button>
            </div>
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
              <div class="input-group">
                <input type="text" class="form-control" id="payerId" name="payer_id" value="<?= isset($payment['payer_id']) ? $payment['payer_id'] : '' ?>">
                <button type="button" class="btn btn-outline-primary" onclick="scanIDForNewPayer()" title="Scan School ID">
                  <i class="fas fa-qrcode"></i>
                </button>
              </div>
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

<!-- ID Scanner Modal -->
<div class="modal fade" id="idScannerModal" tabindex="-1" aria-labelledby="idScannerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="idScannerModalLabel">
          <i class="fas fa-id-card me-2"></i>Scan School ID
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="idScannerContainer" style="position: relative;">
          <video id="idVideo" autoplay playsinline style="width: 100%; border: 2px solid #0d6efd; border-radius: 8px; max-height: 400px;"></video>
          <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 200px; height: 200px; border: 3px solid #0d6efd; border-radius: 8px; pointer-events: none;"></div>
        </div>
        <div id="idScannerStatus" class="text-center mt-3">
          <p class="text-muted">
            <i class="fas fa-camera me-2"></i>
            Point camera at School ID QR code
          </p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="stopIDScanner()" data-bs-dismiss="modal">
          <i class="fas fa-times me-1"></i>Close
        </button>
      </div>
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
  
  // Add event listener to reset modal when it's closed
  const addPaymentModal = document.getElementById('addPaymentModal');
  
  if (addPaymentModal) {
    addPaymentModal.addEventListener('hidden.bs.modal', function() {
      // Reset modal to add mode
      resetPaymentModal();
    });
  }
});

// Function to reset payment modal to add mode
function resetPaymentModal() {
  // Reset modal title
  document.getElementById('addPaymentModalLabel').textContent = 'Add Payment';
  
  // Reset form
  const form = document.getElementById('paymentForm');
  form.reset();
  form.action = '<?= base_url('payments/save') ?>';
  
  // Reset modal title
  document.getElementById('addPaymentModalLabel').textContent = 'Add Payment';
  
  // Reset form fields
  document.getElementById('paymentId').value = '';
  document.getElementById('existingPayerId').value = '';
  
  // Reset payer type to existing
  document.querySelector('input[name="payerType"][value="existing"]').checked = true;
  document.getElementById('existingPayerFields').style.display = 'block';
  document.getElementById('newPayerFields').style.display = 'none';
  
  // Reset payment status
  const statusSelect = document.getElementById('paymentStatus');
  statusSelect.value = 'fully paid';
  statusSelect.className = 'form-select';
  document.getElementById('isPartialPayment').value = '0';
  document.getElementById('paymentStatusHidden').value = 'fully paid';
  
  // Reset contribution dropdown
  const contributionSelect = document.getElementById('contributionId');
  contributionSelect.value = '';
  
  // Clear backdrop if any
  const backdrops = document.querySelectorAll('.modal-backdrop');
  backdrops.forEach(backdrop => backdrop.remove());
  
  // Remove modal-open class from body if it exists
  document.body.classList.remove('modal-open');
  document.body.style.overflow = '';
  document.body.style.paddingRight = '';
}

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

// ID Scanner Variables
let idScannerStream = null;
let idScannerCanvas = null;
let idScannerContext = null;
let isScanningForNewPayer = false;

// Function to scan ID for existing payer
async function scanIDForExistingPayer() {
  isScanningForNewPayer = false;
  await startIDScanner();
}

// Function to scan ID for new payer
async function scanIDForNewPayer() {
  isScanningForNewPayer = true;
  await startIDScanner();
}

// Start ID Scanner
async function startIDScanner() {
  try {
    const modal = new bootstrap.Modal(document.getElementById('idScannerModal'));
    modal.show();
    
    idScannerStream = await navigator.mediaDevices.getUserMedia({ 
      video: { 
        facingMode: 'environment',
        width: { ideal: 640 },
        height: { ideal: 480 }
      } 
    });
    
    const video = document.getElementById('idVideo');
    video.srcObject = idScannerStream;
    
    video.onloadedmetadata = () => {
      video.play();
      
      idScannerCanvas = document.createElement('canvas');
      idScannerCanvas.width = video.videoWidth;
      idScannerCanvas.height = video.videoHeight;
      idScannerContext = idScannerCanvas.getContext('2d');
      
      scanIDCode();
    };
    
  } catch (error) {
    console.error('Error accessing camera:', error);
    showNotification('Unable to access camera. Please check permissions.', 'error');
  }
}

// Function to scan for ID codes
function scanIDCode() {
  const video = document.getElementById('idVideo');
  
  if (video.readyState === video.HAVE_ENOUGH_DATA) {
    idScannerContext.drawImage(video, 0, 0, idScannerCanvas.width, idScannerCanvas.height);
    const imageData = idScannerContext.getImageData(0, 0, idScannerCanvas.width, idScannerCanvas.height);
    
    if (typeof jsQR !== 'undefined') {
      const code = jsQR(imageData.data, imageData.width, imageData.height);
      
      if (code) {
        console.log('ID QR Code detected:', code.data);
        stopIDScanner();
        processIDCode(code.data);
        return;
      }
    }
  }
  
  requestAnimationFrame(scanIDCode);
}

// Stop ID Scanner
function stopIDScanner() {
  if (idScannerStream) {
    idScannerStream.getTracks().forEach(track => track.stop());
    idScannerStream = null;
  }
  
  const modalElement = document.getElementById('idScannerModal');
  const modal = bootstrap.Modal.getInstance(modalElement);
  if (modal) {
    modal.hide();
  }
}

// Parse and process ID code
function processIDCode(idText) {
  // Format: IDNUMBERFIRSTNAME MIDDLEINITIAL.LASTNAMECOURSECODE
  // Example: 154989FLoro C.OceroBSIT
  
  console.log('Processing ID:', idText);
  
  // Find where the ID number ends (first letter)
  // Match: ID number (digits) + name and course part
  const match = idText.match(/^(\d+)(.+)$/);
  
  if (!match) {
    showNotification('Invalid ID format. Please scan again.', 'error');
    return;
  }
  
  const [, idNumber, restOfText] = match;
  console.log('ID Number:', idNumber, 'Rest:', restOfText);
  
  // Try to match the pattern: FirstName MiddleInitial.LastNameCourseCode
  // Example: "Floro C.OCEROBSIT" or "Floro C.OceroBSIT"
  // The last name might be in all caps or have mixed case
  // The course code is typically 2-4 uppercase letters (possibly with digits)
  
  // First, let's try to identify where the course code starts
  // Look for patterns like 2-4 uppercase letters at the end (course codes like BSIT, IT, CS, etc.)
  // But be careful not to mistake last names for course codes
  
  // Strategy: Work backwards from the end
  // Course codes are typically short (2-4 characters) and all uppercase
  
  // Try matching: FirstName MiddleInitial.LastNameCOURSECODE
  // Example: "Floro C.OCEROBSIT" - here "OCERO" is part of last name, "BSIT" is course
  // Example: "Floro C.OceroBSIT" - here "Ocero" is last name, "BSIT" is course
  
  // More robust pattern: Look for 2-4 uppercase letters/digits at the very end
  // This should be the course code
  const courseCodeMatch = restOfText.match(/([A-Z]{2,4}\d*)$/);
  
  let namePart = restOfText;
  let courseCode = '';
  
  if (courseCodeMatch) {
    // Found a potential course code at the end
    courseCode = courseCodeMatch[1];
    namePart = restOfText.slice(0, -courseCode.length);
    console.log('Detected course code:', courseCode, 'Name part:', namePart);
  }
  
  // Now parse the name part (e.g., "Floro C.OCERO" or "Floro C.Ocero")
  // Try to match: FirstName MiddleInitial.LastName
  
     // Handle both all-caps last name and mixed case
   // Pattern: FirstName + space + MiddleInitial. + LastName
   // Last name might be: ALLCAPS, MixedCase, or all lowercase
   
  if (isScanningForNewPayer) {
    // For new payer, parse and populate name and ID
    const nameMatchDetailed = namePart.match(/^([A-Z][a-z]+)\s+([A-Z])\.(.+)$/);
    
    if (nameMatchDetailed) {
      // Got a match with middle initial
      const [, firstName, middleInitial, lastName] = nameMatchDetailed;
      
      // Format the last name properly (capitalize first letter, rest lowercase)
      const formattedLastName = lastName.charAt(0).toUpperCase() + lastName.slice(1).toLowerCase();
      
      const fullName = `${firstName} ${middleInitial}. ${formattedLastName}`;
      console.log('Parsed name:', fullName);
      
      document.getElementById('payerId').value = idNumber;
      document.getElementById('payerName').value = fullName;
      showNotification('ID scanned successfully!', 'success');
    } else {
      // Name doesn't match expected format, use as-is
      const fullName = namePart.replace(/\s+/g, ' ').trim();
      
      document.getElementById('payerId').value = idNumber;
      document.getElementById('payerName').value = fullName;
      showNotification('ID scanned successfully!', 'success');
    }
  } else {
    // For existing payer, just use the ID number to search
    console.log('Searching for existing payer with ID:', idNumber);
    
    // Set the ID in the hidden field
    document.getElementById('existingPayerId').value = idNumber;
    
    // Search for the payer by ID
    fetchPayerById(idNumber);
  }
}

// Function to fetch payer by ID
function fetchPayerById(idNumber) {
  fetch(`${window.APP_BASE_URL || ''}/payments/search-payers?term=${encodeURIComponent(idNumber)}`)
    .then(response => response.json())
    .then(data => {
      if (data.success && data.payers && data.payers.length > 0) {
        const payer = data.payers[0]; // Get first match
        
        // Populate the search field and select the payer
        document.getElementById('payerSelect').value = `${payer.payer_name} (${payer.payer_id})`;
        document.getElementById('existingPayerId').value = payer.payer_id;
        
        showNotification('Payer found!', 'success');
      } else {
        showNotification('No payer found with this ID', 'warning');
      }
    })
    .catch(error => {
      console.error('Error searching payers:', error);
      showNotification('Error searching for payer', 'error');
    });
}

// Event listener for ID scanner modal
document.addEventListener('DOMContentLoaded', function() {
  const idScannerModal = document.getElementById('idScannerModal');
  
  if (idScannerModal) {
    idScannerModal.addEventListener('hidden.bs.modal', function() {
      stopIDScanner();
    });
  }
});
</script>



