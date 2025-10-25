<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Stats Cards Row -->
<div class="container-fluid mb-4">
    <div class="row g-3">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-peso-sign',
                'iconColor' => 'text-primary',
                'title' => 'Total Collected',
                'text' => '₱' . number_format($totalAmount ?? 0, 2)
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-check-circle',
                'iconColor' => 'text-success',
                'title' => 'Verified',
                'text' => ($verifiedCount ?? 0)
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-clock',
                'iconColor' => 'text-warning',
                'title' => 'Pending',
                'text' => ($pendingCount ?? 0)
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-calendar-day',
                'iconColor' => 'text-info',
                'title' => 'Today',
                'text' => ($todayCount ?? 0)
            ]) ?>
        </div>
    </div>
</div>

<!-- Quick Actions Section -->
<?= view('partials/container-card', [
    'title' => '<i class="fas fa-bolt me-2"></i>Quick Actions',
    'subtitle' => 'Common tasks for payment management',
    'cardClass' => 'shadow-sm border-0',
    'bodyClass' => 'p-0',
    'content' => view('partials/history_quick_actions')
]) ?>

<!-- Payment Records Section -->
<?= view('partials/container-card', [
    'title' => '<i class="fas fa-history me-2"></i>Payment Records',
    'subtitle' => count($payments ?? []) . ' payments found',
    'cardClass' => 'shadow-sm border-0',
    'bodyClass' => 'p-3',
    'content' => view('partials/payment_history_list', [
        'payments' => $payments ?? []
    ])
]) ?>

<!-- Payment Details Modal -->
<div id="paymentDetailsModal" class="modal fade" tabindex="-1" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-receipt me-2"></i>
                    Payment Receipt
                </h5>
                <button type="button" class="btn-close" onclick="closePaymentModal()"></button>
            </div>
            <div class="modal-body">
                <!-- Student Information Card -->
                <div class="card bg-light border-0 mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="student-avatar-large" style="width: 60px; height: 60px; background: linear-gradient(135deg, #3b82f6, #0ea5e9); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h5 id="modalStudentName" class="mb-1"></h5>
                                <p id="modalStudentId" class="text-muted mb-1"></p>
                                <p id="modalPaymentDate" class="text-muted mb-0 small"></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Summary Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-success bg-opacity-10 rounded border border-success">
                            <div class="h5 text-success mb-1" id="modalTotalPaid">₱0.00</div>
                            <small class="text-success text-uppercase">Amount Paid</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-warning bg-opacity-10 rounded border border-warning">
                            <div class="h5 text-warning mb-1" id="modalRemainingBalance">₱0.00</div>
                            <small class="text-warning text-uppercase">Remaining</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-info bg-opacity-10 rounded border border-info">
                            <div class="h5 text-info mb-1" id="modalPaymentStatus">-</div>
                            <small class="text-info text-uppercase">Status</small>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="card border-0 mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Transaction Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Payment Type:</small>
                                <span id="modalPaymentType" class="fw-medium">-</span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Payment Method:</small>
                                <span id="modalPaymentMethod" class="fw-medium">-</span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Transaction ID:</small>
                                <span id="modalTransactionId" class="fw-medium font-monospace">-</span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Verified On:</small>
                                <span id="modalVerifiedDate" class="fw-medium">-</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- QR Code Section -->
                <div class="card bg-light border-0">
                    <div class="card-body text-center">
                        <h6 class="card-title mb-3">QR Receipt Code</h6>
                        <div id="modalQrCode" class="mb-3 d-flex justify-content-center align-items-center bg-white rounded border" style="min-height: 200px;">
                            <div class="text-muted">
                                <i class="fas fa-qrcode fs-1 mb-2"></i>
                                <p class="mb-0">QR Code will appear here</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closePaymentModal()">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <button type="button" class="btn btn-success" onclick="downloadQR()">
                    <i class="fas fa-download me-1"></i>Download QR
                </button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">
                    <i class="fas fa-print me-1"></i>Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>



<script>
// Global variable to store current payment data
let currentPaymentData = null;

// Search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchPayments');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const paymentItems = document.querySelectorAll('.payment-item');
            
            paymentItems.forEach(item => {
                const studentName = item.querySelector('h5').textContent.toLowerCase();
                const studentId = item.querySelector('.payment-time').textContent.toLowerCase();
                const paymentType = item.querySelector('.payment-type').textContent.toLowerCase();
                
                const matches = studentName.includes(searchTerm) || 
                              studentId.includes(searchTerm) || 
                              paymentType.includes(searchTerm);
                
                item.closest('.col-12').style.display = matches ? 'block' : 'none';
            });
        });
    }
    
    // Status filter functionality
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const selectedStatus = this.value;
            const paymentItems = document.querySelectorAll('.payment-item');
            
            paymentItems.forEach(item => {
                const itemStatus = item.dataset.status;
                const shouldShow = selectedStatus === '' || itemStatus === selectedStatus;
                item.closest('.col-12').style.display = shouldShow ? 'block' : 'none';
            });
        });
    }
});

// Modal functions
function viewPaymentDetails(paymentData) {
    const modal = document.getElementById('paymentDetailsModal');
    currentPaymentData = paymentData;
    
    // Populate modal data
    document.getElementById('modalStudentName').textContent = paymentData.student_name || '-';
    document.getElementById('modalStudentId').textContent = 'ID: ' + (paymentData.student_id || '-');
    document.getElementById('modalPaymentDate').textContent = paymentData.created_at ? new Date(paymentData.created_at).toLocaleString() : '-';
    document.getElementById('modalTotalPaid').textContent = '₱' + parseFloat(paymentData.amount_paid || 0).toFixed(2);
    document.getElementById('modalRemainingBalance').textContent = '₱' + parseFloat(paymentData.remaining_balance || 0).toFixed(2);
    document.getElementById('modalPaymentStatus').textContent = (paymentData.payment_status || 'Unknown').toUpperCase();
    document.getElementById('modalPaymentType').textContent = paymentData.payment_type || 'General Payment';
    document.getElementById('modalPaymentMethod').textContent = paymentData.payment_method || 'Not specified';
    document.getElementById('modalTransactionId').textContent = paymentData.id || 'N/A';
    document.getElementById('modalVerifiedDate').textContent = paymentData.verified_at || 'Not verified';
    
    // Generate QR code
    generateQRCode(paymentData);
    
    // Show modal
    modal.style.display = 'block';
}

function closePaymentModal() {
    const modal = document.getElementById('paymentDetailsModal');
    modal.style.display = 'none';
}

function generateQRCode(paymentData) {
    const qrContainer = document.getElementById('modalQrCode');
    
    if (paymentData.qr_receipt_path && paymentData.qr_receipt_path.trim() !== '') {
        const qrImage = document.createElement('img');
        qrImage.src = '<?= base_url('payments/downloadReceipt/') ?>' + paymentData.id;
        qrImage.style.cssText = 'width: 150px; height: 150px; object-fit: contain;';
        qrImage.alt = 'QR Receipt for Payment #' + paymentData.id;
        
        qrImage.onerror = function() {
            qrContainer.innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-triangle fs-1 mb-2"></i><p>QR Image Not Found</p></div>';
        };
        
        qrContainer.innerHTML = '';
        qrContainer.appendChild(qrImage);
    } else {
        qrContainer.innerHTML = `
            <div class="text-warning">
                <i class="fas fa-qrcode fs-1 mb-3"></i>
                <p class="fw-bold mb-1">Payment #${paymentData.id || 'N/A'}</p>
                <p class="mb-0 small">QR code not available</p>
            </div>
        `;
    }
}

// Helper functions
function refreshHistory() {
    window.location.reload();
}

function exportPayments() {
    const exportBtn = event.target.closest('.card');
    if (exportBtn) {
        const original = exportBtn.innerHTML;
        exportBtn.innerHTML = '<div class="card-body d-flex align-items-center gap-3 h-100"><div class="icon-circle"><i class="fas fa-spinner fa-spin fs-4"></i></div><div class="flex-grow-1"><h6 class="mb-1 fw-semibold">Generating PDF...</h6><small class="text-white-75">Please wait</small></div></div>';
        
        setTimeout(() => {
            exportBtn.innerHTML = original;
        }, 3000);
    }
    
    window.location.href = '<?= base_url('payments/export') ?>';
}

function filterPayments(status) {
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.value = status;
        statusFilter.dispatchEvent(new Event('change'));
    }
}

function downloadReceipt(paymentId) {
    if (!paymentId) {
        alert('Payment ID is required for downloading receipt.');
        return;
    }
    
    const downloadUrl = '<?= base_url('payments/downloadReceipt/') ?>' + paymentId;
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.target = '_blank';
    link.download = 'qr_receipt_' + paymentId + '.png';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function verifyPayment(paymentId) {
    if (confirm('Are you sure you want to verify this payment?')) {
        alert('Payment verification for ID: ' + paymentId + ' - Feature coming soon!');
    }
}

function downloadQR() {
    if (currentPaymentData && currentPaymentData.id) {
        downloadReceipt(currentPaymentData.id);
    } else {
        alert('Unable to download QR code. Payment data not available.');
    }
}

function printReceipt() {
    window.print();
}
</script>
<?= $this->endSection() ?>