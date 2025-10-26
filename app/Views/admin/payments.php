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
                         <option value="fully paid">Completed</option>
                         <option value="partial">Partial</option>
                         <option value="unpaid">Unpaid</option>
                     </select>
                 </div>
             </div>
             
             <!-- Table with scrollable body -->
             <div class="table-responsive" style="max-height: 600px; overflow-y: auto; overflow-x: hidden;">
              <table class="table table-hover table-fit">
                 <thead class="table-light sticky-top">
                  <tr>
                     <th>Payer ID</th>
                     <th>Payer Name</th>
                    <th>Amount</th>
                    <th>Contribution</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                 <tbody id="paymentsTableBody">
                    <?php if (!empty($recentPayments)): ?>
                        <?php foreach ($recentPayments as $payment): ?>
                             <tr data-payment-status="<?= esc($payment['computed_status'] ?? 'unpaid') ?>" data-payer-id="<?= esc($payment['payer_student_id'] ?? $payment['payer_id'] ?? '') ?>">
                                 <td><?= esc($payment['payer_student_id'] ?? $payment['payer_id'] ?? '') ?></td>
                                <td><?= esc($payment['payer_name']) ?></td>
                                <td>₱<?= number_format($payment['amount_paid'], 2) ?></td>
                                <td><?= esc($payment['contribution_title'] ?? 'N/A') ?></td>
                                <td><?= esc($payment['payment_method'] ?? 'N/A') ?></td>
                                <td>
                                    <?php 
                                        $status = $payment['computed_status'] ?? 'unpaid';
                                        $badgeClass = match($status) {
                                            'fully paid' => 'bg-primary text-white',
                                            'partial' => 'bg-warning text-dark',
                                            'unpaid' => 'bg-secondary text-white',
                                            default => 'bg-light text-dark'
                                        };
                                        $statusText = match($status) {
                                            'fully paid' => 'Completed',
                                            'partial' => 'Partial',
                                            'unpaid' => 'Unpaid',
                                            default => ucfirst($status)
                                        };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                                </td>
                                <td><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                                <td>
                                    <div class="btn-group-vertical" role="group">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewPaymentReceipt(<?= $payment['id'] ?>)" title="View Receipt">
                                                <i class="fas fa-eye me-1"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="editPayment(<?= $payment['id'] ?>)" title="Edit Payment">
                                                <i class="fas fa-edit me-1"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDeletePayment(<?= $payment['id'] ?>, '<?= esc($payment['payer_name']) ?>', '<?= date('M d, Y', strtotime($payment['payment_date'])) ?>', <?= $payment['amount_paid'] ?>)" title="Delete Payment">
                                                <i class="fas fa-trash me-1"></i>
                                            </button>
                                        </div>
                                        <?php if (($payment['computed_status'] ?? 'unpaid') === 'partial'): ?>
                                            <button class="btn btn-sm btn-outline-info mt-1" onclick="addPaymentToPartial(<?= $payment['id'] ?>)" title="Add Payment">
                                                <i class="fas fa-plus me-1"></i>Add Payment
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No payment records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
              </table>
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

 <!-- Delete Payment Confirmation Modal -->
 <div class="modal fade" id="deletePaymentModal" tabindex="-1" aria-labelledby="deletePaymentModalLabel" aria-hidden="true">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header bg-danger text-white">
                 <h5 class="modal-title" id="deletePaymentModalLabel">
                     <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete Payment
                 </h5>
                 <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 <p>Are you sure you want to delete this payment?</p>
                 <div class="alert alert-warning">
                     <strong>Payment Details:</strong><br>
                     Payer: <span id="deletePaymentPayerName"></span><br>
                     Date: <span id="deletePaymentDate"></span><br>
                     Amount: <span id="deletePaymentAmount"></span>
                 </div>
                 <p class="mb-0"><small class="text-danger">This action cannot be undone.</small></p>
             </div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                 <button type="button" class="btn btn-danger" onclick="deletePayment()">
                     <i class="fas fa-trash me-2"></i>Delete Payment
                 </button>
             </div>
         </div>
     </div>
 </div>

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
                
                if (payment && (payment.computed_status === 'partial' || payment.payment_status === 'partial')) {
                    // Open the add payment to partial modal
                    if (typeof openAddPaymentToPartialModal === 'function') {
                        openAddPaymentToPartialModal(payment);
                    } else {
                        console.error('openAddPaymentToPartialModal function not found');
                        showNotification('Add Payment to Partial modal not available', 'danger');
                    }
                } else if (payment && payment.computed_status !== 'partial' && payment.payment_status !== 'partial') {
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

// Store payment ID to delete globally
let paymentIdToDelete = null;

// Function to show delete confirmation modal
function confirmDeletePayment(paymentId, payerName, paymentDate, amountPaid) {
    paymentIdToDelete = paymentId;
    
    // Populate modal with payment details
    document.getElementById('deletePaymentPayerName').textContent = payerName;
    document.getElementById('deletePaymentDate').textContent = paymentDate;
    document.getElementById('deletePaymentAmount').textContent = '₱' + parseFloat(amountPaid).toFixed(2);
    
    // Show modal
    const deleteModal = new bootstrap.Modal(document.getElementById('deletePaymentModal'));
    deleteModal.show();
}

// Function to delete payment
function deletePayment() {
    if (!paymentIdToDelete) {
        showNotification('No payment selected for deletion', 'error');
        return;
    }
    
    // Show loading state
    const deleteBtn = event.target;
    const originalText = deleteBtn.innerHTML;
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Deleting...';
    
    // Send DELETE request
    fetch(`${window.APP_BASE_URL}/payments/delete/${paymentIdToDelete}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Payment deleted successfully', 'success');
            
            // Close modal
            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deletePaymentModal'));
            deleteModal.hide();
            
            // Reload page after short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Error deleting payment', 'error');
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error deleting payment', 'error');
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = originalText;
    });
}

// Helper function for notifications
function showNotification(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}
</script>

<style>
/* Ensure table fits container and removes extra space */
.table-fit {
    width: 100%;
    table-layout: fixed;
}

.table-fit th,
.table-fit td {
    word-wrap: break-word;
    overflow-wrap: break-word;
}

/* Remove extra padding from card body to eliminate spacing */
.card-body {
    padding-right: 15px;
}

/* Ensure table container has no extra space */
.table-responsive {
    width: 100%;
}

/* Remove default margin from table */
.table {
    margin-bottom: 0;
}
</style>

<script src="<?= base_url('js/payment.js') ?>"></script>

<?= $this->endSection() ?>