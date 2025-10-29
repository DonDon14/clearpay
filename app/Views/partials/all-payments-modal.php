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

.payment-actions-cell button {
    pointer-events: auto !important;
    z-index: 10;
    position: relative;
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
                    <h5 class="modal-title" id="allPaymentsModalLabel">Recent Payments</h5>
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
                                    <th>Profile</th>
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
                                        <td class="payment-cell-clickable">
                                            <?php if (!empty($payment['profile_picture'])): ?>
                                                <img src="<?= base_url($payment['profile_picture']) ?>" 
                                                     alt="Profile Picture" 
                                                     class="rounded-circle" 
                                                     style="width: 35px; height: 35px; object-fit: cover; border: 2px solid #e9ecef;">
                                            <?php else: ?>
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="payment-cell-clickable"><?= esc($payment['payer_name']) ?></td>
                                        <td class="payment-cell-clickable"><?= esc($payment['contribution_title']) ?></td>
                                        <td class="payment-cell-clickable">₱<?= number_format($payment['amount_paid'], 2) ?></td>
                                        <td class="payment-cell-clickable">
                                            <?php 
                                                $status = $payment['computed_status'] ?? $payment['payment_status'] ?? 'unpaid';
                                                $badgeClass = match($status) {
                                                    'fully paid' => 'bg-primary text-white',
                                                    'partial' => 'bg-warning text-dark',
                                                    'unpaid' => 'bg-secondary text-white',
                                                    default => 'bg-danger text-white'
                                                };
                                                $statusText = match($status) {
                                                    'fully paid' => 'COMPLETED',
                                                    'partial' => 'PARTIAL',
                                                    'unpaid' => 'UNPAID',
                                                    default => strtoupper($status)
                                                };
                                            ?>
                                            <span class="badge <?= $badgeClass ?>">
                                                <?= $statusText ?>
                                            </span>
                                        </td>
                                        <td class="payment-cell-clickable"><?= date('M d, Y h:i A', strtotime($payment['payment_date'])) ?></td>
                                        <td class="payment-actions-cell">
                                            <button type="button" class="btn btn-sm btn-outline-primary me-1 view-receipt-btn" 
                                                    data-payment-id="<?= $payment['id'] ?>" 
                                                    title="View Receipt">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning edit-payment-all-btn" 
                                                    data-payment-id="<?= $payment['id'] ?>" 
                                                    title="Edit Payment">
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
            const payerName = row.querySelector('td:nth-child(2)').textContent.toLowerCase().trim();
            
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
            // Don't trigger if clicking on action buttons
            if (e.target.closest('.payment-actions-cell') || e.target.closest('button')) {
                return;
            }
            
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
    
    // Handle view receipt button clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.view-receipt-btn')) {
            e.stopPropagation();
            e.preventDefault();
            const btn = e.target.closest('.view-receipt-btn');
            const paymentId = btn.getAttribute('data-payment-id');
            if (paymentId) {
                viewPaymentReceiptInAllPayments(paymentId);
            }
        }
        
        if (e.target.closest('.edit-payment-all-btn')) {
            e.stopPropagation();
            e.preventDefault();
            const btn = e.target.closest('.edit-payment-all-btn');
            const paymentId = btn.getAttribute('data-payment-id');
            if (paymentId) {
                editPaymentInAllPayments(paymentId);
            }
        }
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
    if (!paymentId) {
        console.error('No payment ID provided');
        return;
    }
    
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
                        if (typeof showNotification === 'function') {
                            showNotification('QR Receipt modal not available', 'danger');
                        }
                    }
                } else {
                    if (typeof showNotification === 'function') {
                        showNotification('Payment not found', 'warning');
                    }
                }
            } else {
                if (typeof showNotification === 'function') {
                    showNotification('Error fetching payment data', 'danger');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showNotification === 'function') {
                showNotification('Error loading payment', 'danger');
            }
        });
}

// Function to edit payment in all payments modal
function editPaymentInAllPayments(paymentId) {
    if (!paymentId) {
        console.error('No payment ID provided');
        return;
    }
    
    // Fetch payment details (includes contribution amount)
    fetch(`${window.APP_BASE_URL}/payments/get-details/${paymentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.payment) {
                const payment = data.payment;
                
                // Open edit payment modal
                openEditPaymentModal(payment);
            } else {
                if (typeof showNotification === 'function') {
                    showNotification('Payment not found', 'warning');
                } else {
                    alert('Payment not found');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showNotification === 'function') {
                showNotification('Error loading payment', 'danger');
            } else {
                alert('Error loading payment');
            }
        });
}

// Function to open edit payment modal with payment data
function openEditPaymentModal(payment) {
    // Check if edit payment modal exists
    const editModal = document.getElementById('editPaymentModal');
    if (!editModal) {
        // Fallback: use add payment modal if edit modal doesn't exist
        if (typeof showNotification === 'function') {
            showNotification('Edit payment modal not available. Please refresh the page.', 'error');
        } else {
            alert('Edit payment modal not available. Please refresh the page.');
        }
        return;
    }
    
    // Populate edit form using vanilla JavaScript
    const editPaymentId = document.getElementById('editPaymentId');
    const editPayerName = document.getElementById('editPayerName');
    const editContribution = document.getElementById('editContribution');
    const editAmountPaid = document.getElementById('editAmountPaid');
    const editPaymentMethod = document.getElementById('editPaymentMethod');
    const editReceiptNumber = document.getElementById('editReceiptNumber');
    const editContributionId = document.getElementById('editContributionId');
    const editContributionAmount = document.getElementById('editContributionAmount');
    const editPaymentStatus = document.getElementById('editPaymentStatus');
    const editPaymentDate = document.getElementById('editPaymentDate');
    
    if (editPaymentId) editPaymentId.value = payment.id || '';
    if (editPayerName) editPayerName.value = payment.payer_name || '';
    if (editContribution) editContribution.value = payment.contribution_title || '';
    if (editAmountPaid) editAmountPaid.value = parseFloat(payment.amount_paid || 0).toFixed(2);
    if (editPaymentMethod) editPaymentMethod.value = payment.payment_method || '';
    if (editReceiptNumber) editReceiptNumber.value = payment.receipt_number || '';
    if (editContributionId) editContributionId.value = payment.contribution_id || '';
    if (editContributionAmount) editContributionAmount.value = payment.contribution_amount || 0;
    if (editPaymentStatus) editPaymentStatus.value = payment.payment_status || 'fully paid';
    
    // Format payment date for datetime-local input
    if (editPaymentDate && payment.payment_date) {
        const paymentDate = new Date(payment.payment_date);
        const formattedDate = paymentDate.toISOString().slice(0, 16);
        editPaymentDate.value = formattedDate;
    }
    
    // Calculate remaining balance
    if (typeof updateEditRemainingBalance === 'function') {
        updateEditRemainingBalance();
    }
    
    // Re-initialize handlers in case modal was just created
    const confirmBtn = document.getElementById('confirmEditPayment');
    if (confirmBtn) {
        confirmBtn.dataset.handlersAttached = 'false'; // Reset to allow re-attachment
    }
    
    // Show modal using Bootstrap
    const modal = new bootstrap.Modal(editModal);
    modal.show();
    
    // Re-initialize handlers after modal is shown
    setTimeout(() => {
        const initScript = editModal.querySelector('script');
        if (initScript && typeof window.updateEditRemainingBalance === 'function') {
            window.updateEditRemainingBalance();
        }
    }, 200);
}

// Update remaining balance when amount changes (for edit modal)
function updateEditRemainingBalance() {
    const contributionAmountEl = document.getElementById('editContributionAmount');
    const amountPaidEl = document.getElementById('editAmountPaid');
    const remainingBalanceEl = document.getElementById('editRemainingBalance');
    const paymentStatusEl = document.getElementById('editPaymentStatus');
    
    if (!contributionAmountEl || !amountPaidEl || !remainingBalanceEl) {
        return; // Elements not found yet
    }
    
    const contributionAmount = parseFloat(contributionAmountEl.value) || 0;
    const amountPaid = parseFloat(amountPaidEl.value) || 0;
    const remainingBalance = Math.max(0, contributionAmount - amountPaid);
    
    remainingBalanceEl.value = remainingBalance.toFixed(2);
    
    // Update payment status
    if (paymentStatusEl) {
        if (remainingBalance <= 0.01) {
            paymentStatusEl.value = 'fully paid';
        } else {
            paymentStatusEl.value = 'partial';
        }
    }
}

// Make functions globally available
window.updateEditRemainingBalance = updateEditRemainingBalance;
window.editPaymentInAllPayments = editPaymentInAllPayments;
window.viewPaymentReceiptInAllPayments = viewPaymentReceiptInAllPayments;
window.openEditPaymentModal = openEditPaymentModal;
</script>