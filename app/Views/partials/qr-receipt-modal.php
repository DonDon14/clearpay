<!-- QR Receipt Partial Modal -->
<div class="modal fade" id="qrReceiptModal" tabindex="-1" aria-labelledby="qrReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white border-0 py-2">
                <div class="d-flex align-items-center">
                    <div class="qr-icon me-2">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <div>
                        <h6 class="modal-title mb-0" id="qrReceiptModalLabel">
                            <span id="qrReceiptTitle">QR Receipt</span>
                        </h6>
                        <small class="opacity-75" style="font-size: 0.7rem;">Payment Verification</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-0">
                <!-- Receipt Header -->
                <div class="receipt-header bg-light p-2 text-center border-bottom">
                    <div class="receipt-logo mb-1">
                        <i class="fas fa-credit-card fa-2x text-primary"></i>
                    </div>
                    <h6 class="text-primary mb-0">ClearPay</h6>
                    <small class="text-muted">Payment Receipt</small>
                </div>

                <!-- Payment Details -->
                <div class="receipt-content p-3">
                    <!-- Payment Info -->
                    <div class="receipt-section mb-3">
                        <h6 class="section-title text-primary mb-2">
                            <i class="fas fa-receipt me-1"></i>Payment Info
                        </h6>
                        <div class="receipt-details">
                            <div class="detail-item mb-1">
                                <span class="detail-label">Amount:</span>
                                <span class="detail-value fw-bold text-success" id="qrAmountPaid">₱0.00</span>
                            </div>
                            <div class="detail-item mb-1">
                                <span class="detail-label">Reference:</span>
                                <span class="detail-value" id="qrReferenceNumber">N/A</span>
                            </div>
                            <div class="detail-item mb-1">
                                <span class="detail-label">Date:</span>
                                <span class="detail-value" id="qrPaymentDate">N/A</span>
                            </div>
                            <div class="detail-item mb-1">
                                <span class="detail-label">Method:</span>
                                <span class="detail-value" id="qrPaymentMethod">N/A</span>
                            </div>
                            <div class="detail-item mb-1">
                                <span class="detail-label">Status:</span>
                                <span class="detail-value" id="qrPaymentStatus">
                                    <span class="badge bg-success">Completed</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Payer Info -->
                    <div class="receipt-section mb-3">
                        <h6 class="section-title text-primary mb-2">
                            <i class="fas fa-user me-1"></i>Payer Info
                        </h6>
                        <div class="receipt-details">
                            <div class="detail-item mb-1">
                                <span class="detail-label">Name:</span>
                                <span class="detail-value fw-bold" id="qrPayerName">N/A</span>
                            </div>
                            <div class="detail-item mb-1">
                                <span class="detail-label">ID:</span>
                                <span class="detail-value" id="qrPayerId">N/A</span>
                            </div>
                            <div class="detail-item mb-1">
                                <span class="detail-label">Contact:</span>
                                <span class="detail-value" id="qrPayerContact">N/A</span>
                            </div>
                            <div class="detail-item mb-1">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value" id="qrPayerEmail">N/A</span>
                            </div>
                        </div>
                    </div>

                    <!-- Contribution -->
                    <div class="receipt-section mb-3">
                        <h6 class="section-title text-primary mb-2">
                            <i class="fas fa-hand-holding-usd me-1"></i>Contribution
                        </h6>
                        <div class="receipt-details">
                            <div class="detail-item mb-1">
                                <span class="detail-label">Title:</span>
                                <span class="detail-value fw-bold" id="qrContributionTitle">N/A</span>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Section -->
                    <div class="qr-section text-center py-2">
                        <div class="qr-container bg-white p-3 rounded shadow-sm border">
                            <h6 class="text-primary mb-2" id="qrReferenceDisplay" style="font-size: 0.9rem;">
                                <!-- Reference number will be displayed here -->
                            </h6>
                            <div id="qrReceiptContent" class="mb-2">
                                <!-- QR code will be generated here -->
                            </div>
                            <button type="button" class="btn btn-success btn-sm" onclick="downloadQRCode()">
                                <i class="fas fa-download me-1"></i>Download QR
                            </button>
                        </div>
                    </div>

                    <!-- Issued By -->
                    <div class="receipt-section">
                        <h6 class="section-title text-primary mb-2">
                            <i class="fas fa-user-tie me-1"></i>Issued By
                        </h6>
                        <div class="receipt-details">
                            <div class="detail-item mb-1">
                                <span class="detail-label">Recorded By:</span>
                                <span class="detail-value" id="qrRecordedBy">System Admin</span>
                            </div>
                            <div class="detail-item mb-1">
                                <span class="detail-label">Issue Date:</span>
                                <span class="detail-value" id="qrIssueDate">N/A</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light border-0 py-2 px-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal" onclick="cleanupModal()">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="printReceipt()">
                    <i class="fas fa-print me-1"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.receipt-section {
    border-left: 2px solid #0d6efd;
    padding-left: 10px;
}

.section-title {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.1rem 0;
}

.detail-label {
    font-weight: 500;
    color: #6c757d;
    font-size: 0.75rem;
}

.detail-value {
    font-size: 0.75rem;
    color: #212529;
}

.qr-container {
    max-width: 200px;
    margin: 0 auto;
}

.qr-icon {
    width: 25px;
    height: 25px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 4px;
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
    border-radius: 10px;
    overflow: hidden;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

.modal-body {
    flex: 1;
    overflow-y: auto;
}

.modal-footer {
    flex-shrink: 0;
}

.modal-header {
    border-radius: 10px 10px 0 0;
}

.modal-lg {
    max-width: 600px;
}

.modal-footer {
    padding: 0.5rem 1rem;
    border-top: 1px solid #dee2e6;
    background-color: #f8f9fa;
}

.modal-footer .btn {
    margin: 0 0.25rem;
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
    
    /* Print layout for half bond paper */
    body {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .modal-content {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
    }
    
    .modal-body {
        padding: 10px !important;
    }
    
    .receipt-content {
        padding: 5px !important;
    }
    
    .receipt-section {
        margin-bottom: 8px !important;
        padding-left: 8px !important;
        border-left: 2px solid #0d6efd !important;
    }
    
    .section-title {
        font-size: 0.7rem !important;
        margin-bottom: 4px !important;
    }
    
    .detail-item {
        padding: 1px 0 !important;
        font-size: 0.7rem !important;
    }
    
    .detail-label {
        font-size: 0.7rem !important;
    }
    
    .detail-value {
        font-size: 0.7rem !important;
    }
    
    .qr-container {
        max-width: 100px !important;
        margin: 5px auto !important;
        padding: 5px !important;
    }
    
    .qr-container img {
        max-width: 100px !important;
        max-height: 100px !important;
    }
    
    .receipt-header {
        padding: 5px !important;
    }
    
    .receipt-logo i {
        font-size: 1.5rem !important;
    }
    
    .receipt-header h6 {
        font-size: 0.8rem !important;
        margin-bottom: 2px !important;
    }
    
    .receipt-header small {
        font-size: 0.6rem !important;
    }
    
    .modal-header {
        padding: 5px 10px !important;
    }
    
    .modal-header h6 {
        font-size: 0.8rem !important;
    }
    
    .modal-header small {
        font-size: 0.6rem !important;
    }
    
    .qr-icon {
        width: 20px !important;
        height: 20px !important;
    }
    
    /* Compact layout for print */
    .row {
        margin: 0 !important;
    }
    
    .col-md-6 {
        padding: 0 5px !important;
    }
    
    /* Hide unnecessary elements for print */
    .btn-close {
        display: none !important;
    }
}
</style>

<script>
// Global function to show QR receipt - moved to contributions page for better timing
window.showQRReceipt = function(payment) {
    console.log('showQRReceipt called with payment:', payment);
    // This function is now defined in the contributions page for better timing
};

function printReceipt() {
    const modalContent = document.querySelector('#qrReceiptModal .modal-content');
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Payment Receipt - ClearPay</title>
            <style>
                @page {
                    size: A4;
                    margin: 0.5in;
                }
                
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 0; 
                    padding: 0; 
                    font-size: 12px;
                    line-height: 1.2;
                }
                
                .receipt-content { 
                    max-width: 100%; 
                    margin: 0; 
                }
                
                .text-center { text-align: center; }
                .text-primary { color: #0d6efd; }
                .text-success { color: #198754; }
                .fw-bold { font-weight: bold; }
                
                .receipt-header {
                    background: #f8f9fa;
                    padding: 8px;
                    text-align: center;
                    border-bottom: 2px solid #0d6efd;
                    margin-bottom: 8px;
                }
                
                .receipt-header h6 {
                    font-size: 14px;
                    margin: 2px 0;
                    color: #0d6efd;
                }
                
                .receipt-header small {
                    font-size: 10px;
                    color: #6c757d;
                }
                
                .receipt-section { 
                    border-left: 2px solid #0d6efd; 
                    padding-left: 8px; 
                    margin-bottom: 6px; 
                }
                
                .section-title { 
                    font-size: 10px; 
                    font-weight: 600; 
                    text-transform: uppercase; 
                    letter-spacing: 0.3px; 
                    color: #0d6efd; 
                    margin-bottom: 3px;
                }
                
                .detail-item { 
                    display: flex; 
                    justify-content: space-between; 
                    align-items: center; 
                    padding: 1px 0; 
                    font-size: 10px;
                }
                
                .detail-label { 
                    font-weight: 500; 
                    color: #6c757d; 
                    font-size: 10px; 
                }
                
                .detail-value { 
                    font-size: 10px; 
                    color: #212529; 
                }
                
                .qr-container { 
                    max-width: 100px; 
                    margin: 5px auto; 
                    background: white; 
                    padding: 5px; 
                    border-radius: 4px; 
                    border: 1px solid #dee2e6; 
                    text-align: center;
                }
                
                .qr-container img {
                    max-width: 100px;
                    max-height: 100px;
                }
                
                .qr-container h6 {
                    font-size: 9px;
                    margin: 2px 0;
                }
                
                .badge { 
                    padding: 2px 4px; 
                    border-radius: 3px; 
                    font-size: 8px; 
                }
                
                .bg-success { background-color: #198754; color: white; }
                .bg-warning { background-color: #ffc107; color: #000; }
                .bg-secondary { background-color: #6c757d; color: white; }
                
                .row {
                    display: flex;
                    margin: 0;
                }
                
                .col-md-6 {
                    flex: 1;
                    padding: 0 5px;
                }
                
                .modal-header {
                    background: #0d6efd;
                    color: white;
                    padding: 5px 8px;
                    margin-bottom: 5px;
                }
                
                .modal-header h6 {
                    font-size: 12px;
                    margin: 0;
                }
                
                .modal-header small {
                    font-size: 9px;
                    opacity: 0.8;
                }
                
                /* Hide buttons and localhost link in print */
                .modal-footer,
                .btn,
                .btn-close,
                [href*="localhost"] {
                    display: none !important;
                }
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

function cleanupModal() {
    // Remove any lingering backdrop
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => backdrop.remove());
    
    // Remove modal-open class from body
    document.body.classList.remove('modal-open');
    
    // Reset body padding if needed
    document.body.style.paddingRight = '';
    
    // Reset overflow
    document.body.style.overflow = '';
}

function downloadQRCode() {
    const qrImage = document.querySelector('#qrReceiptContent img');
    const referenceNumber = document.getElementById('qrReferenceDisplay').textContent;
    
    if (!qrImage) {
        alert('QR code not found');
        return;
    }
    
    // Create a canvas with proper spacing
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    
    // Set canvas dimensions
    const qrSize = qrImage.naturalWidth || qrImage.width;
    const headerHeight = 40; // Space for ClearPay title
    const footerHeight = 30; // Space for reference number
    const padding = 20; // Padding around the entire image
    
    // Calculate text width to ensure proper fit
    ctx.font = '12px Arial';
    const textWidth = ctx.measureText(referenceNumber).width;
    const minWidth = Math.max(qrSize, textWidth + (padding * 2));
    
    canvas.width = minWidth;
    canvas.height = qrSize + headerHeight + footerHeight + (padding * 2);
    
    // Fill background with white
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Add border
    ctx.strokeStyle = '#0d6efd';
    ctx.lineWidth = 2;
    ctx.strokeRect(1, 1, canvas.width - 2, canvas.height - 2);
    
    // Add ClearPay title at the top
    ctx.fillStyle = '#0d6efd';
    ctx.font = 'bold 18px Arial';
    ctx.textAlign = 'center';
    ctx.fillText('ClearPay', canvas.width / 2, padding + 25);
    
    // Draw QR code in the middle (centered)
    const qrX = (canvas.width - qrSize) / 2;
    ctx.drawImage(qrImage, qrX, padding + headerHeight);
    
    // Add reference number at the bottom (centered)
    ctx.fillStyle = '#212529';
    ctx.font = '12px Arial';
    ctx.textAlign = 'center';
    ctx.fillText(referenceNumber, canvas.width / 2, padding + headerHeight + qrSize + 20);
    
    canvas.toBlob(function(blob) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `QR_Receipt_${referenceNumber.replace(/[^a-zA-Z0-9]/g, '_')}.png`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });
}
</script>
