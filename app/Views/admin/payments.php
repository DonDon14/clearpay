<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

  <div class="container-fluid">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center">
             <h5 class="card-title mb-0">All Payments</h5>
            <button 
              class="btn btn-primary btn-sm"
              data-bs-toggle="modal"
              data-bs-target="#addPaymentModal"
          >
              <i class="fas fa-plus me-2"></i>Add Payment
          </button>
          </div>
          <div class="card-body">
             <!-- Search and Filter Row -->
             <div class="row mb-3">
                 <div class="col-md-6">
                     <div class="input-group">
                         <input type="text" id="searchStudentName" class="form-control" placeholder="Search by name or scan ID...">
                         <button type="button" class="btn btn-outline-primary" onclick="scanIDInPaymentsPage()" title="Scan School ID">
                             <i class="fas fa-qrcode"></i>
                         </button>
                     </div>
                 </div>
                 <div class="col-md-4">
                     <select id="statusFilter" class="form-select">
                         <option value="">All Status</option>
                         <option value="fully paid">Fully Paid</option>
                         <option value="partial">Partial</option>
                     </select>
                 </div>
             </div>
             
             <!-- Table with scrollable body -->
             <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
              <table class="table table-hover">
                 <thead class="table-light sticky-top">
                  <tr>
                     <th>Payer ID</th>
                     <th>Payer Name</th>
                    <th>Amount</th>
                    <th>Payment Type</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                 <tbody id="paymentsTableBody">
                    <?php if (!empty($recentPayments)): ?>
                        <?php foreach ($recentPayments as $payment): ?>
                             <tr data-payment-status="<?= esc($payment['payment_status']) ?>" data-payer-id="<?= esc($payment['payer_student_id'] ?? $payment['payer_id'] ?? '') ?>">
                                 <td><?= esc($payment['payer_student_id'] ?? $payment['payer_id'] ?? '') ?></td>
                                <td><?= esc($payment['payer_name']) ?></td>
                                <td>₱<?= number_format($payment['amount_paid'], 2) ?></td>
                                <td><?= esc($payment['contribution_title'] ?? 'N/A') ?></td>
                                <td><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                                <td>
                                    <?php if ($payment['payment_status'] === 'fully paid'): ?>
                                         <span class="badge bg-success">Fully Paid</span>
                                    <?php elseif ($payment['payment_status'] === 'partial'): ?>
                                        <span class="badge bg-warning">Partial</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewPaymentReceipt(<?= $payment['id'] ?>)">
                                        <i class="fas fa-eye me-1"></i>View Receipt
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="editPayment(<?= $payment['id'] ?>)">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                    <?php if ($payment['payment_status'] === 'partial'): ?>
                                        <button class="btn btn-sm btn-outline-info mt-1" onclick="addPaymentToPartial(<?= $payment['id'] ?>)" title="Add Payment">
                                            <i class="fas fa-plus me-1"></i>Add Payment
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No payment records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="bg-primary text-white rounded p-3">
                  <i class="fas fa-money-bill-wave fa-lg"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <h6 class="card-title mb-1">Today's Payments</h6>
                <h4 class="text-primary mb-0">₱15,750</h4>
                <small class="text-muted">+12% from yesterday</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="bg-success text-white rounded p-3">
                  <i class="fas fa-check-circle fa-lg"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <h6 class="card-title mb-1">Completed</h6>
                <h4 class="text-success mb-0">25</h4>
                <small class="text-muted">transactions today</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="bg-warning text-white rounded p-3">
                  <i class="fas fa-clock fa-lg"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <h6 class="card-title mb-1">Pending</h6>
                <h4 class="text-warning mb-0">8</h4>
                <small class="text-muted">awaiting confirmation</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Payment Modal -->
   <?php $contributions = $contributions ?? []; ?>
   <?= view('partials/modal-add-payment', [
    'action' => base_url('payments/save'),  // controller route to handle form submission
    'title' => 'Add Payment',
    'contributions' => $contributions // array of contributions for the dropdown
]) ?>

 <!-- Add Payment to Partial Payment Modal -->
 <?= view('partials/modal-add-payment-to-partial') ?>

 <!-- QR Receipt Modal -->
 <?= view('partials/modal-qr-receipt', [
     'title' => 'Payment Receipt',
 ]) ?>

<script>
// Define base URL for payment.js
window.APP_BASE_URL = '<?= base_url() ?>';

// Function to view payment receipt
function viewPaymentReceipt(paymentId) {
    // Fetch payment data
    fetch(`${window.APP_BASE_URL}/payments/recent`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.payments) {
                // Find the specific payment
                const payment = data.payments.find(p => p.id == paymentId);
                if (payment) {
                    // Show QR receipt modal
                    if (typeof showQRReceipt === 'function') {
                        showQRReceipt(payment);
                    } else {
                        console.error('showQRReceipt function not found');
                        showNotification('QR Receipt modal not available', 'danger');
                    }
                } else {
                    showNotification('Payment not found', 'warning');
                }
            } else {
                showNotification('Error fetching payment data', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading payment', 'danger');
        });
}

// Function to edit payment
function editPayment(paymentId) {
    // Fetch payment details
    fetch(`${window.APP_BASE_URL}/payments/recent`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.payments) {
                const payment = data.payments.find(p => p.id == paymentId);
                if (payment) {
                    // Use the shared openEditPaymentModal function if it exists
                    if (typeof openEditPaymentModal === 'function') {
                        openEditPaymentModal(payment);
                    } else {
                        // Fallback: Open the payment modal with edit mode
                        openEditPaymentModalLocal(payment);
                    }
                } else {
                    showNotification('Payment not found', 'warning');
                }
            } else {
                showNotification('Error fetching payment data', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading payment', 'danger');
        });
}

// Local function to open edit payment modal (if shared function not available)
function openEditPaymentModalLocal(payment) {
    // Update modal title
    document.getElementById('addPaymentModalLabel').textContent = 'Edit Payment';
    
    // Set payment ID
    document.getElementById('paymentId').value = payment.id || '';
    
    // Populate payer fields
    if (payment.payer_id) {
        // Existing payer
        document.querySelector('input[name="payerType"][value="existing"]').checked = true;
        document.getElementById('payerSelect').value = `${payment.payer_name} (${payment.payer_id})`;
        document.getElementById('existingPayerId').value = payment.payer_id;
        document.getElementById('existingPayerFields').style.display = 'block';
        document.getElementById('newPayerFields').style.display = 'none';
    } else {
        // New payer
        document.querySelector('input[name="payerType"][value="new"]').checked = true;
        document.getElementById('payerName').value = payment.payer_name || '';
        document.getElementById('payerId').value = payment.payer_id || '';
        document.getElementById('contactNumber').value = payment.contact_number || '';
        document.getElementById('emailAddress').value = payment.email_address || '';
        document.getElementById('existingPayerFields').style.display = 'none';
        document.getElementById('newPayerFields').style.display = 'block';
    }
    
    // Set contribution (this should show the current contribution in the dropdown)
    if (payment.contribution_id) {
        const contributionSelect = document.getElementById('contributionId');
        if (contributionSelect) {
            contributionSelect.value = payment.contribution_id;
            // Trigger change to update payment status if needed
            contributionSelect.dispatchEvent(new Event('change'));
        }
    }
    
    // Set payment method
    if (payment.payment_method) {
        document.getElementById('paymentMethod').value = payment.payment_method;
    }
    
    // Set amount paid
    if (payment.amount_paid) {
        document.getElementById('amountPaid').value = payment.amount_paid;
    }
    
    // Set payment status
    if (payment.payment_status) {
        const statusSelect = document.getElementById('paymentStatus');
        if (statusSelect) {
            statusSelect.value = payment.payment_status;
            // Apply the appropriate class
            if (payment.payment_status === 'fully paid') {
                statusSelect.className = 'form-select bg-success text-white';
            } else if (payment.payment_status === 'partial') {
                statusSelect.className = 'form-select bg-warning text-dark';
            }
        }
    }
    
    // Set payment date - need to convert to datetime-local format
    if (payment.payment_date) {
        const paymentDate = new Date(payment.payment_date);
        const year = paymentDate.getFullYear();
        const month = String(paymentDate.getMonth() + 1).padStart(2, '0');
        const day = String(paymentDate.getDate()).padStart(2, '0');
        const hours = String(paymentDate.getHours()).padStart(2, '0');
        const minutes = String(paymentDate.getMinutes()).padStart(2, '0');
        const formattedDate = `${year}-${month}-${day}T${hours}:${minutes}`;
        document.getElementById('paymentDate').value = formattedDate;
    }
    
    // Set remaining balance if exists
    if (payment.remaining_balance !== undefined) {
        document.getElementById('remainingBalance').value = payment.remaining_balance || '0.00';
    }
    
    // Set partial payment flag
    const isPartialPayment = payment.payment_status === 'partial' && payment.remaining_balance > 0;
    document.getElementById('isPartialPayment').value = isPartialPayment ? '1' : '0';
    document.getElementById('paymentStatusHidden').value = payment.payment_status || 'fully paid';
    
    // Update form action to edit
    const form = document.getElementById('paymentForm');
    form.action = `${window.APP_BASE_URL}/payments/update/${payment.id}`;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addPaymentModal'));
    modal.show();
}

// Function to add payment to a partial payment
function addPaymentToPartial(paymentId) {
    console.log('Adding payment to partial, paymentId:', paymentId);
    
    // Fetch the existing payment details
    fetch(`${window.APP_BASE_URL}/payments/recent`)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Fetched data:', data);
            
            if (data.success && data.payments) {
                console.log('Total payments fetched:', data.payments.length);
                const payment = data.payments.find(p => p.id == paymentId);
                console.log('Found payment:', payment);
                
                if (payment && payment.payment_status === 'partial') {
                    // Open the add payment to partial modal
                    if (typeof openAddPaymentToPartialModal === 'function') {
                        openAddPaymentToPartialModal(payment);
                    } else {
                        console.error('openAddPaymentToPartialModal function not found');
                        showNotification('Add Payment to Partial modal not available', 'danger');
                    }
                } else if (payment && payment.payment_status !== 'partial') {
                    showNotification('Only partial payments can have additional payments added', 'warning');
                } else {
                    showNotification('Payment not found in records', 'warning');
                }
            } else {
                console.error('Failed to fetch payments:', data);
                showNotification('Error fetching payment data: ' + (data.message || 'Unknown error'), 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading payment: ' + error.message, 'danger');
        });
}



// Search and Filter Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchStudentName');
    const statusFilter = document.getElementById('statusFilter');
    
                   function filterTable() {
         const searchValue = searchInput.value.toLowerCase().trim();
         const statusValue = statusFilter.value;
         const tableRows = document.querySelectorAll('#paymentsTableBody tr');
         
         tableRows.forEach(row => {
             const payerId = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
             const payerName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
             
             // Also get payer_id from data attribute
             const dataPayerId = row.getAttribute('data-payer-id') || '';
             const dataPayerIdLower = dataPayerId.toLowerCase();
             
             // Get status directly from data attribute
             const rowStatus = row.getAttribute('data-payment-status') || '';
             
             // Check if row matches search and status filter
             const matchesSearch = searchValue === '' || payerId.includes(searchValue) || payerName.includes(searchValue) || dataPayerIdLower.includes(searchValue);
             const matchesStatus = statusValue === '' || rowStatus === statusValue;
             
             if (matchesSearch && matchesStatus) {
                 row.style.display = '';
             } else {
                 row.style.display = 'none';
             }
         });
     }
    
    if (searchInput) {
        searchInput.addEventListener('input', filterTable);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterTable);
    }
    
    // Make filterTable available globally
    window.filterTablePaymentsPage = filterTable;
});

// Function to scan ID in payments page
async function scanIDInPaymentsPage() {
    try {
        const modal = new bootstrap.Modal(document.getElementById('idScannerModal'));
        modal.show();
        
        let idScannerStream = null;
        let idScannerCanvas = null;
        let idScannerContext = null;
        
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
            
            function scanIDCode() {
                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    idScannerContext.drawImage(video, 0, 0, idScannerCanvas.width, idScannerCanvas.height);
                    const imageData = idScannerContext.getImageData(0, 0, idScannerCanvas.width, idScannerCanvas.height);
                    
                    if (typeof jsQR !== 'undefined') {
                        const code = jsQR(imageData.data, imageData.width, imageData.height);
                        
                        if (code) {
                            console.log('ID QR Code detected:', code.data);
                            
                            // Stop scanner
                            if (idScannerStream) {
                                idScannerStream.getTracks().forEach(track => track.stop());
                                idScannerStream = null;
                            }
                            
                            // Close scanner modal
                            const scannerModal = bootstrap.Modal.getInstance(document.getElementById('idScannerModal'));
                            if (scannerModal) {
                                scannerModal.hide();
                            }
                            
                            // Process scanned ID
                            processScannedIDForPaymentsPage(code.data);
                            return;
                        }
                    }
                }
                
                requestAnimationFrame(scanIDCode);
            }
            
            scanIDCode();
        };
        
    } catch (error) {
        console.error('Error accessing camera:', error);
        showNotification('Unable to access camera. Please check permissions.', 'error');
    }
}

// Function to process scanned ID for payments page search
function processScannedIDForPaymentsPage(idText) {
    // Extract ID number from scanned text
    const match = idText.match(/^(\d+)/);
    
    if (!match) {
        showNotification('Invalid ID format. Please scan again.', 'error');
        return;
    }
    
    const idNumber = match[1];
    console.log('Scanned ID number:', idNumber);
    
    // Set the search input and filter
    const searchInput = document.getElementById('searchStudentName');
    if (searchInput) {
        searchInput.value = idNumber;
        
        // Trigger filter
        if (window.filterTablePaymentsPage) {
            window.filterTablePaymentsPage();
        }
        
        showNotification('Filtering by ID: ' + idNumber, 'success');
    }
}
</script>

<script src="<?= base_url('js/payment.js') ?>"></script>

<?= $this->endSection() ?>