<!-- QR Receipt Partial Modal -->
<div class="modal fade" id="qrReceiptModal" tabindex="-1" aria-labelledby="qrReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white border-0">
                <div class="d-flex align-items-center">
                    <div class="qr-icon me-3">
                        <i class="fas fa-qrcode fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="qrReceiptModalLabel">
                            <span id="qrReceiptTitle">QR Receipt</span>
                        </h5>
                        <small class="opacity-75">Payment Verification</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-0">
                <!-- Receipt Header -->
                <div class="receipt-header bg-light p-4 text-center border-bottom">
                    <div class="receipt-logo mb-3">
                        <i class="fas fa-credit-card fa-3x text-primary"></i>
                    </div>
                    <h4 class="text-primary mb-1">ClearPay</h4>
                    <p class="text-muted mb-0">Payment Receipt</p>
                </div>

                <!-- Payment Details -->
                <div class="receipt-content p-4">
                    <div class="row">
                        <!-- Left Column - Payment Info -->
                        <div class="col-md-6">
                            <div class="receipt-section mb-4">
                                <h6 class="section-title text-primary mb-3">
                                    <i class="fas fa-receipt me-2"></i>Payment Information
                                </h6>
                                <div class="receipt-details">
                                    <div class="detail-item mb-2">
                                        <span class="detail-label">Amount:</span>
                                        <span class="detail-value fw-bold text-success" id="qrAmountPaid">₱0.00</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label">Reference:</span>
                                        <span class="detail-value" id="qrReferenceNumber">N/A</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label">Date:</span>
                                        <span class="detail-value" id="qrPaymentDate">N/A</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label">Method:</span>
                                        <span class="detail-value" id="qrPaymentMethod">N/A</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label">Status:</span>
                                        <span class="detail-value" id="qrPaymentStatus">
                                            <span class="badge bg-success">Completed</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Payer Info -->
                        <div class="col-md-6">
                            <div class="receipt-section mb-4">
                                <h6 class="section-title text-primary mb-3">
                                    <i class="fas fa-user me-2"></i>Payer Information
                                </h6>
                                <div class="receipt-details">
                                    <div class="detail-item mb-2">
                                        <span class="detail-label">Name:</span>
                                        <span class="detail-value fw-bold" id="qrPayerName">N/A</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label">Payer ID:</span>
                                        <span class="detail-value" id="qrPayerId">N/A</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label">Contact:</span>
                                        <span class="detail-value" id="qrPayerContact">N/A</span>
                                    </div>
                                    <div class="detail-item mb-2">
                                        <span class="detail-label">Email:</span>
                                        <span class="detail-value" id="qrPayerEmail">N/A</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contribution Details -->
                    <div class="receipt-section mb-4">
                        <h6 class="section-title text-primary mb-3">
                            <i class="fas fa-hand-holding-usd me-2"></i>Contribution Details
                        </h6>
                        <div class="receipt-details">
                            <div class="detail-item mb-2">
                                <span class="detail-label">Contribution:</span>
                                <span class="detail-value fw-bold" id="qrContributionTitle">N/A</span>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Section -->
                    <div class="qr-section text-center py-4">
                        <div class="qr-container bg-white p-4 rounded-3 shadow-sm border">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-qrcode me-2"></i>Verification QR Code
                            </h6>
                            <div id="qrReceiptContent" class="mb-3">
                                <!-- QR code will be generated here -->
                            </div>
                            <p class="text-muted small mb-0">
                                <i class="fas fa-shield-alt me-1"></i>
                                Scan to verify payment authenticity
                            </p>
                        </div>
                    </div>

                    <!-- Issued By Section -->
                    <div class="receipt-section">
                        <h6 class="section-title text-primary mb-3">
                            <i class="fas fa-user-tie me-2"></i>Issued By
                        </h6>
                        <div class="receipt-details">
                            <div class="detail-item mb-2">
                                <span class="detail-label">Recorded By:</span>
                                <span class="detail-value" id="qrRecordedBy">System Administrator</span>
                            </div>
                            <div class="detail-item mb-2">
                                <span class="detail-label">Issue Date:</span>
                                <span class="detail-value" id="qrIssueDate">N/A</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">
                    <i class="fas fa-print me-2"></i>Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.receipt-section {
    border-left: 3px solid #0d6efd;
    padding-left: 15px;
}

.section-title {
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.25rem 0;
}

.detail-label {
    font-weight: 500;
    color: #6c757d;
    font-size: 0.875rem;
}

.detail-value {
    font-size: 0.875rem;
    color: #212529;
}

.qr-container {
    max-width: 300px;
    margin: 0 auto;
}

.qr-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.receipt-logo {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.modal-content {
    border-radius: 15px;
    overflow: hidden;
}

.modal-header {
    border-radius: 15px 15px 0 0;
}

@media print {
    .modal-footer {
        display: none !important;
    }
    
    .modal-header {
        background: #0d6efd !important;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
    }
    
    .receipt-header {
        background: #f8f9fa !important;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
    }
}
</style>

<script>
// Global function to show QR receipt
window.showQRReceipt = function(payment) {
    // Update modal title
    document.getElementById('qrReceiptTitle').textContent = `QR Receipt - ${payment.reference_number || 'Payment'}`;
    
    // Update payment details
    document.getElementById('qrAmountPaid').textContent = '₱' + parseFloat(payment.amount_paid).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('qrReferenceNumber').textContent = payment.reference_number || 'N/A';
    document.getElementById('qrPaymentDate').textContent = new Date(payment.payment_date || payment.created_at).toLocaleDateString('en-US');
    document.getElementById('qrPaymentMethod').textContent = payment.payment_method ? payment.payment_method.charAt(0).toUpperCase() + payment.payment_method.slice(1) : 'N/A';
    
    // Update payer details
    document.getElementById('qrPayerName').textContent = payment.payer_name || 'N/A';
    document.getElementById('qrPayerId').textContent = payment.payer_id || 'N/A';
    document.getElementById('qrPayerContact').textContent = payment.contact_number || 'N/A';
    document.getElementById('qrPayerEmail').textContent = payment.email_address || 'N/A';
    
    // Update contribution details
    document.getElementById('qrContributionTitle').textContent = payment.contribution_title || 'N/A';
    
    // Update issued by details
    document.getElementById('qrRecordedBy').textContent = payment.recorded_by_name || 'System Administrator';
    document.getElementById('qrIssueDate').textContent = new Date().toLocaleDateString('en-US');
    
    // Update payment status
    const statusBadge = document.getElementById('qrPaymentStatus');
    const status = payment.payment_status || 'pending';
    const statusText = status === 'fully paid' ? 'Completed' : (status === 'partial' ? 'Partial' : 'Pending');
    statusBadge.innerHTML = `<span class="badge bg-${status === 'fully paid' ? 'success' : (status === 'partial' ? 'warning' : 'secondary')}">${statusText}</span>`;
    
    // Generate QR code
    generateQRCode(payment);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('qrReceiptModal'));
    modal.show();
};

function generateQRCode(payment) {
    const qrContainer = document.getElementById('qrReceiptContent');
    
    // QR code data
    const qrText = `${payment.receipt_number || payment.id}|${payment.payer_name || 'Payer'}|${payment.amount_paid}|${payment.payment_date}|${payment.reference_number}`;
    const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&ecc=H&data=${encodeURIComponent(qrText)}`;
    
    // Show loading state
    qrContainer.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm text-primary mb-2" role="status"><span class="visually-hidden">Loading...</span></div><p class="text-muted mb-0 small">Generating QR code...</p></div>';
    
    // Create QR image
    const qrImage = document.createElement('img');
    qrImage.style.cssText = 'max-width: 200px; max-height: 200px; width: auto; height: auto; border: 2px solid #0d6efd; border-radius: 8px; padding: 5px; background: white;';
    qrImage.alt = 'QR Receipt for Payment #' + payment.id;
    qrImage.crossOrigin = 'anonymous';
    
    qrImage.onload = function() {
        qrContainer.innerHTML = '';
        qrContainer.appendChild(qrImage);
    };
    
    qrImage.onerror = function() {
        // Fallback to Google Charts API
        const fallbackUrl = `https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=${encodeURIComponent(qrText)}&choe=UTF-8`;
        qrImage.src = fallbackUrl;
    };
    
    qrImage.src = qrApiUrl;
}

function printReceipt() {
    const modalContent = document.querySelector('#qrReceiptModal .modal-content');
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Payment Receipt</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                .receipt-content { max-width: 600px; margin: 0 auto; }
                .text-center { text-align: center; }
                .text-primary { color: #0d6efd; }
                .text-success { color: #198754; }
                .fw-bold { font-weight: bold; }
                .mb-3 { margin-bottom: 1rem; }
                .mb-2 { margin-bottom: 0.5rem; }
                .p-4 { padding: 1.5rem; }
                .bg-light { background-color: #f8f9fa; }
                .border-bottom { border-bottom: 1px solid #dee2e6; }
                .receipt-section { border-left: 3px solid #0d6efd; padding-left: 15px; margin-bottom: 1rem; }
                .section-title { font-size: 0.9rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #0d6efd; }
                .detail-item { display: flex; justify-content: space-between; align-items: center; padding: 0.25rem 0; }
                .detail-label { font-weight: 500; color: #6c757d; font-size: 0.875rem; }
                .detail-value { font-size: 0.875rem; color: #212529; }
                .qr-container { max-width: 300px; margin: 0 auto; background: white; padding: 1rem; border-radius: 8px; border: 1px solid #dee2e6; }
                .badge { padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 0.75rem; }
                .bg-success { background-color: #198754; color: white; }
                .bg-warning { background-color: #ffc107; color: #000; }
                .bg-secondary { background-color: #6c757d; color: white; }
            </style>
        </head>
        <body>
            ${modalContent.innerHTML}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
}
</script>
