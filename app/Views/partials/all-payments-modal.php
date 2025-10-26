<!-- All Payments Modal -->
<style>
/* Global fix for backdrop z-index issue */
body.modal-open .modal-backdrop.fade.show {
    z-index: 1040 !important;
}

/* Override for All Payments specifically */
body.modal-open #allPaymentsModal ~ .modal-backdrop.fade.show {
    z-index: 1045 !important;
    /* Ensure backdrop is semi-transparent but visible */
    opacity: 0.5 !important;
}

/* Ensure dashboard content behind modals is not faded */
body.modal-open .content,
body.modal-open .main-content {
    opacity: 1 !important;
}

.payment-row {
    transition: background-color 0.2s ease;
}

.payment-row:hover {
    background-color: #f0f9ff !important;
}

.payment-row:active {
    background-color: #dbeafe !important;
}

.payment-cell-clickable {
    cursor: pointer;
}

.payment-actions-cell {
    cursor: default !important;
    width: 120px;
}

/* Ensure All Payments modal is above background but below QR modal */
#allPaymentsModal {
    z-index: 1055 !important;
}

#allPaymentsModal .modal-dialog {
    z-index: 1056 !important;
    margin: 1rem auto !important;
}

#allPaymentsModal .modal-content {
    z-index: 1057 !important;
    position: relative;
}

/* Ensure backdrop is below modal content */
#allPaymentsModal ~ .modal-backdrop {
    z-index: 9999 !important;
}
</style>

<div class="modal fade" id="allPaymentsModal" tabindex="-1" aria-labelledby="allPaymentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title" id="allPaymentsModalLabel">All Payments</h5>
                    <small class="text-white-50">
                        <i class="fas fa-mouse-pointer me-1"></i>
                        Click any payment to view QR receipt
                    </small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <?php if (!empty($allPayments)): ?>
                    <!-- Search Input -->
                    <div class="mb-3">
                        <div class="input-group">
                            <input 
                                type="text"
                                id="searchStudent" 
                                class="form-control" 
                                placeholder="Search by name or scan ID..."
                            >
                            <button type="button" class="btn btn-outline-primary" onclick="scanIDInAllPayments()" title="Scan School ID">
                                <i class="fas fa-qrcode"></i>
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle" id="paymentsTable">
                            <thead>
                                <tr>
                                    <th>Payer Name</th>
                                    <th>Contribution</th>
                                    <th>Amount Paid</th>
                                    <th>Status</th>
                                    <th>Payment Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allPayments as $payment): ?>
                                    <tr class="payment-row" data-payment-id="<?= esc($payment['id']) ?>" data-payer-id="<?= esc($payment['payer_student_id'] ?? $payment['payer_id'] ?? '') ?>" data-payment-data="<?= esc(json_encode($payment)) ?>">
                                        <td class="payment-cell-clickable"><?= esc($payment['payer_name']) ?></td>
                                        <td class="payment-cell-clickable"><?= esc($payment['contribution_title']) ?></td>
                                        <td class="payment-cell-clickable">₱<?= number_format($payment['amount_paid'], 2) ?></td>
                                        <td class="payment-cell-clickable">
                                            <span class="badge 
                                                <?= $payment['payment_status'] === 'fully paid' 
                                                    ? 'bg-success' 
                                                    : ($payment['payment_status'] === 'partial' ? 'bg-warning' : 'bg-danger') ?>">
                                                <?= strtoupper($payment['payment_status']) ?>
                                            </span>
                                        </td>
                                        <td class="payment-cell-clickable"><?= date('M d, Y h:i A', strtotime($payment['payment_date'])) ?></td>
                                        <td class="payment-actions-cell" onclick="event.stopPropagation();">
                                            <button class="btn btn-sm btn-outline-primary me-1" onclick="viewPaymentReceiptInAllPayments(<?= $payment['id'] ?>)" title="View Receipt">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="editPaymentInAllPayments(<?= $payment['id'] ?>)" title="Edit Payment">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted">No payment records found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- 🧠 JavaScript for Prefix-Based Search -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchStudent');

    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            filterPayments();
        });
    }
    
    // Function to filter payments
    function filterPayments() {
        const searchValue = searchInput.value.toLowerCase().trim();
        const tableRows = document.querySelectorAll('#paymentsTable tbody tr');

        tableRows.forEach(row => {
            const payerName = row.querySelector('td:first-child').textContent.toLowerCase().trim();
            
            // Also try to get payer ID from data attribute if available
            const payerId = row.getAttribute('data-payer-id') || '';
            const payerIdLower = payerId.toLowerCase();

            // Show if payer name or ID matches
            if (payerName.includes(searchValue) || payerIdLower.includes(searchValue) || searchValue === '') {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Handle payment cell clicks to show QR receipt (but not action cells)
    const clickableCells = document.querySelectorAll('.payment-cell-clickable');
    clickableCells.forEach(cell => {
        cell.addEventListener('click', function(e) {
            const row = this.closest('.payment-row');
            const paymentId = row.getAttribute('data-payment-id');
            
            if (paymentId) {
                fetch(`${window.APP_BASE_URL}/payments/recent`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.payments) {
                            const payment = data.payments.find(p => p.id == paymentId);
                            if (payment) {
                                if (typeof showQRReceipt === 'function') {
                                    showQRReceipt(payment);
                                } else {
                                    console.error('showQRReceipt function not found');
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
        });
    });
    
    // Make filterPayments available globally
    window.filterPayments = filterPayments;
});

// Function to scan ID in all payments modal
async function scanIDInAllPayments() {
    try {
        const modal = new bootstrap.Modal(document.getElementById('idScannerModal'));
        modal.show();
        
        // Use the existing global ID scanner variables from modal-add-payment
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
                            processScannedIDForSearch(code.data);
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

// Function to process scanned ID for search
function processScannedIDForSearch(idText) {
    // Extract ID number from scanned text
    const match = idText.match(/^(\d+)/);
    
    if (!match) {
        showNotification('Invalid ID format. Please scan again.', 'error');
        return;
    }
    
    const idNumber = match[1];
    console.log('Scanned ID number:', idNumber);
    
    // Set the search input and filter
    const searchInput = document.getElementById('searchStudent');
    if (searchInput) {
        searchInput.value = idNumber;
        
        // Trigger filter
        if (window.filterPayments) {
            window.filterPayments();
        }
        
        showNotification('Filtering by ID: ' + idNumber, 'success');
    }
}

// Function to view payment receipt in all payments modal
function viewPaymentReceiptInAllPayments(paymentId) {
    event.stopPropagation();
    
    fetch(`${window.APP_BASE_URL}/payments/recent`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.payments) {
                const payment = data.payments.find(p => p.id == paymentId);
                if (payment) {
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

// Function to edit payment in all payments modal
function editPaymentInAllPayments(paymentId) {
    event.stopPropagation();
    
    // Fetch payment details
    fetch(`${window.APP_BASE_URL}/payments/recent`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.payments) {
                const payment = data.payments.find(p => p.id == paymentId);
                if (payment) {
                    // Open the add payment modal in edit mode
                    openEditPaymentModal(payment);
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

// Function to open edit payment modal with payment data
function openEditPaymentModal(payment) {
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
</script>