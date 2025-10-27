<?php
// Reusable QR Receipt Modal Component
// This component displays payment receipt with QR code

// Ensure modal is only included once
if (!isset($GLOBALS['qr_receipt_modal_included'])) {
    $GLOBALS['qr_receipt_modal_included'] = true;
    
// Default values
$title = $title ?? 'Payment Receipt';
$paymentData = $paymentData ?? [];
$showPrintButton = $showPrintButton ?? true;
$showDownloadButton = $showDownloadButton ?? true;
?>

<!-- QR Receipt Modal -->
<style>
/* Ensure QR Receipt modal has proper z-index above all other modals */
#qrReceiptModal {
    z-index: 2000 !important;
}

#qrReceiptModal .modal-dialog {
    z-index: 2001 !important;
    margin: 3rem auto 1rem auto !important;
}

#qrReceiptModal .modal-content {
    position: relative;
    z-index: 2002 !important;
}

/* QR modal backdrop should fully cover everything behind it */
body:has(#qrReceiptModal.show) .modal-backdrop.show:last-of-type {
    z-index: 1999 !important;
    background-color: rgba(0, 0, 0, 0.75) !important;
}

/* All Payments modal backdrop should fade when QR receipt is shown */
body:has(#qrReceiptModal.show) #allPaymentsModal ~ .modal-backdrop.show {
    background-color: rgba(0, 0, 0, 0.35) !important;
    z-index: 1998 !important;
}

/* Compress QR receipt modal content to fit on screen */
#qrReceiptModal .modal-body {
    padding: 1rem !important;
    max-height: 70vh;
    overflow-y: auto;
}

#qrReceiptModal .card {
    margin-bottom: 0.75rem !important;
}

#qrReceiptModal .card-body {
    padding: 0.75rem !important;
}

/* Ensure All Payments modal is visible behind QR but faded */
body:has(#qrReceiptModal.show) #allPaymentsModal {
    opacity: 0.3 !important;
    pointer-events: none !important;
}
</style>

<div class="modal fade" id="qrReceiptModal" tabindex="-1" aria-labelledby="qrReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="qrReceiptModalLabel">
                    <i class="fas fa-receipt me-2"></i><?= esc($title) ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body" id="receiptContent">
                <!-- Receipt Header -->
                <div class="text-center mb-4">
                    <h4 class="text-primary mb-1">ClearPay</h4>
                    <p class="text-muted mb-0">Payment Receipt</p>
                    <hr class="my-3">
                </div>

                <!-- Payment Details -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body p-3">
                                <h6 class="card-title text-muted mb-2">Receipt Number</h6>
                                <p class="h5 mb-0" id="qrReceiptNumber">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body p-3">
                                <h6 class="card-title text-muted mb-2">Payment Date</h6>
                                <p class="h6 mb-0" id="qrPaymentDate">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payer Information -->
                <div class="card border-0 bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title text-primary mb-3">
                            <i class="fas fa-user me-2"></i>Payer Information
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <small class="text-muted">Name:</small>
                                <p class="mb-1 fw-semibold" id="qrPayerName">-</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">ID:</small>
                                <p class="mb-1 fw-semibold" id="qrPayerId">-</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Contact:</small>
                                <p class="mb-1" id="qrPayerContact">-</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Email:</small>
                                <p class="mb-1" id="qrPayerEmail">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="card border-0 bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title text-primary mb-3">
                            <i class="fas fa-credit-card me-2"></i>Payment Details
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <small class="text-muted">Contribution:</small>
                                <p class="mb-1 fw-semibold" id="qrContributionTitle">-</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Amount Paid:</small>
                                <p class="mb-1 fw-semibold text-success" id="qrAmountPaid">-</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Payment Method:</small>
                                <p class="mb-1" id="qrPaymentMethod">-</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Status:</small>
                                <p class="mb-1">
                                    <span class="badge" id="qrPaymentStatus">-</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- QR Code Section -->
                <div class="text-center">
                    <div class="card border-2 border-primary">
                        <div class="card-body p-2">
                            <h6 class="card-title text-primary mb-2">
                                <i class="fas fa-qrcode me-2"></i>QR Receipt Code
                            </h6>
                            <div id="qrCodeContainer" class="mb-2" style="min-height: 120px; display: flex; align-items: center; justify-content: center;">
                                <div class="text-muted">
                                    <i class="fas fa-qrcode fs-1 mb-2"></i>
                                    <p class="mb-0">QR Code will appear here</p>
                                </div>
                            </div>
                            <small class="text-muted">Scan this QR code to verify payment</small>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-4">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        This receipt is digitally signed and verified
                    </small>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <?php if ($showDownloadButton): ?>
                <button type="button" class="btn btn-info" onclick="downloadQRReceipt()">
                    <i class="fas fa-download me-1"></i>Download QR
                </button>
                <?php endif; ?>
                <?php if ($showPrintButton): ?>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">
                    <i class="fas fa-print me-1"></i>Print Receipt
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Global variable to store current payment data
let currentReceiptData = null;

// Make function globally available and log for debugging
window.showQRReceipt = function(paymentData) {
    console.log('showQRReceipt called with data:', paymentData);
    currentReceiptData = paymentData;
    
    // Populate receipt data
    document.getElementById('qrReceiptNumber').textContent = paymentData.receipt_number || 'N/A';
    document.getElementById('qrPaymentDate').textContent = formatDate(paymentData.payment_date);
    document.getElementById('qrPayerName').textContent = paymentData.payer_name || 'N/A';
    document.getElementById('qrPayerId').textContent = paymentData.payer_id || 'N/A';
    document.getElementById('qrPayerContact').textContent = paymentData.contact_number || 'N/A';
    document.getElementById('qrPayerEmail').textContent = paymentData.email_address || 'N/A';
    document.getElementById('qrContributionTitle').textContent = paymentData.contribution_title || 'N/A';
    document.getElementById('qrAmountPaid').textContent = '₱' + parseFloat(paymentData.amount_paid || 0).toFixed(2);
    document.getElementById('qrPaymentMethod').textContent = formatPaymentMethod(paymentData.payment_method);
    
    // Set status badge (use computed_status if available, fall back to payment_status)
    const statusBadge = document.getElementById('qrPaymentStatus');
    const status = paymentData.computed_status || paymentData.payment_status || 'pending';
    const statusText = status === 'fully paid' ? 'COMPLETED' : (status === 'partial' ? 'PARTIAL' : status.toUpperCase());
    statusBadge.textContent = statusText;
    statusBadge.className = 'badge ' + getStatusBadgeClass(status);
    
    // Generate QR code
    generateQRCode(paymentData);
    
    // Show modal using Bootstrap's modal API
    const modalEl = document.getElementById('qrReceiptModal');
    const modal = new bootstrap.Modal(modalEl);
    
    // Find any open parent modal (All Payments, Payment History, etc.)
    const allPaymentsModal = document.getElementById('allPaymentsModal');
    const paymentHistoryModal = document.getElementById('paymentHistoryModal');
    const parentModal = allPaymentsModal || paymentHistoryModal;
    const parentModalDialog = parentModal ? parentModal.querySelector('.modal-dialog') : null;
    
    // Fade parent modal itself (not backdrop) when QR modal is shown
    if (parentModalDialog) {
        parentModalDialog.style.transition = 'opacity 0.3s ease';
        parentModalDialog.style.opacity = '0.4'; // Fade the modal content
    }
    
    // Clean up extra backdrops when QR modal is hidden
    modalEl.addEventListener('hidden.bs.modal', function () {
        // Restore parent modal opacity
        if (parentModalDialog) {
            parentModalDialog.style.opacity = '1';
        }
        
        // Keep only the first backdrop (for parent modal)
        const backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length > 1) {
            // Remove all backdrops except the first one
            for (let i = 1; i < backdrops.length; i++) {
                backdrops[i].remove();
            }
        }
        
        // Ensure body doesn't have duplicate modal-open classes
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        
        // Re-add modal-open if parent modal is still open
        if (parentModal && parentModal.classList.contains('show')) {
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        }
    });
    
    modal.show();
};

// Function to generate QR code
function generateQRCode(paymentData) {
    const qrContainer = document.getElementById('qrCodeContainer');
    
    // QR code data - simplified for better encoding
    const qrText = `${paymentData.receipt_number || paymentData.id}|${paymentData.payer_name}|${paymentData.amount_paid}|${paymentData.payment_date}`;
    
    // Generate QR code using external API with error correction
    const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&ecc=H&data=${encodeURIComponent(qrText)}`;
    
    console.log('Generating QR code for:', paymentData);
    console.log('QR API URL:', qrApiUrl);
    
    // Show loading state
    qrContainer.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm text-primary mb-2" role="status"><span class="visually-hidden">Loading...</span></div><p class="text-muted mb-0 small">Generating QR code...</p></div>';
    
    // Create and configure QR image - smaller size
    const qrImage = document.createElement('img');
    qrImage.style.cssText = 'max-width: 120px; max-height: 120px; width: auto; height: auto; border: 2px solid #0d6efd; border-radius: 6px; padding: 3px; background: white;';
    qrImage.alt = 'QR Receipt for Payment #' + paymentData.id;
    qrImage.crossOrigin = 'anonymous';
    
    // Track if we've tried the fallback
    let triedFallback = false;
    
    qrImage.onload = function() {
        console.log('QR code loaded successfully from:', qrImage.src);
        qrContainer.innerHTML = '';
        qrContainer.appendChild(qrImage);
    };
    
    qrImage.onerror = function() {
        console.error('Primary QR API failed');
        
        if (!triedFallback) {
            triedFallback = true;
            console.log('Trying Google Charts API fallback...');
            
            // Fallback: use Google Charts API with smaller size
            const fallbackUrl = `https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=${encodeURIComponent(qrText)}&choe=UTF-8`;
            
            qrImage.src = '';
            qrImage.src = fallbackUrl;
        } else {
            console.error('Both QR APIs failed');
            // Both failed - show receipt number
            qrContainer.innerHTML = `
                <div class="text-center">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle mb-2"></i>
                        <p class="mb-1">QR code unavailable</p>
                        <small>Receipt: ${paymentData.receipt_number || 'N/A'}</small>
                    </div>
                </div>
            `;
        }
    };
    
    // Start loading the QR code
    qrImage.src = qrApiUrl;
}

// Function to format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Function to format payment method
function formatPaymentMethod(method) {
    if (!method) return 'N/A';
    const methods = {
        'cash': 'Cash',
        'online': 'Online',
        'check': 'Check',
        'bank': 'Bank Transfer'
    };
    return methods[method] || method;
}

// Function to get status badge class
function getStatusBadgeClass(status) {
    const classes = {
        'fully paid': 'bg-primary text-white',
        'paid': 'bg-primary text-white',
        'partial': 'bg-warning text-dark',
        'pending': 'bg-warning text-dark',
        'unpaid': 'bg-secondary text-white',
        'failed': 'bg-danger text-white'
    };
    return classes[status] || 'bg-secondary text-white';
}

// Function to download QR receipt with custom format
function downloadQRReceipt() {
    if (!currentReceiptData) {
        alert('Unable to download QR code. Payment data not available.');
        return;
    }
    
    // Find the QR code image in the modal
    const qrImage = document.querySelector('#qrCodeContainer img');
    
    if (!qrImage || !qrImage.src) {
        alert('QR code is still generating. Please wait a moment and try again.');
        return;
    }
    
    try {
        // Create a canvas with proper dimensions for the formatted receipt
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        // Set canvas size - wider to accommodate text
        const qrSize = 200;
        const padding = 40;
        const textHeight = 60;
        canvas.width = qrSize + (padding * 2);
        canvas.height = qrSize + (textHeight * 3) + (padding * 2);
        
        // Set background to white
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        // Draw the QR code image
        const img = new Image();
        img.crossOrigin = 'anonymous';
        
        img.onload = function() {
            // Clear canvas and set white background
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            // Draw QR code in the center
            const qrX = padding;
            const qrY = textHeight + padding;
            ctx.drawImage(img, qrX, qrY, qrSize, qrSize);
            
            // Set text properties
            ctx.fillStyle = '#000000';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            
            // Draw "ClearPay" title at the top
            ctx.font = 'bold 24px Arial, sans-serif';
            ctx.fillStyle = '#0d6efd';
            ctx.fillText('ClearPay', canvas.width / 2, 30);
            
            // Draw payer name below QR code
            ctx.font = 'bold 16px Arial, sans-serif';
            ctx.fillStyle = '#000000';
            const payerName = currentReceiptData.payer_name || 'N/A';
            ctx.fillText(payerName, canvas.width / 2, qrY + qrSize + 30);
            
            // Draw reference number at the bottom
            ctx.font = '14px Arial, sans-serif';
            ctx.fillStyle = '#666666';
            const referenceNumber = currentReceiptData.reference_number || currentReceiptData.receipt_number || 'N/A';
            ctx.fillText(`Ref: ${referenceNumber}`, canvas.width / 2, qrY + qrSize + 60);
            
            // Convert canvas to blob and download
            canvas.toBlob(function(blob) {
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = 'clearpay_qr_receipt_' + (currentReceiptData.reference_number || currentReceiptData.receipt_number || currentReceiptData.id) + '.png';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            }, 'image/png');
        };
        
        img.onerror = function() {
            alert('Unable to download QR code. Please try again.');
        };
        
        img.src = qrImage.src;
        
    } catch (error) {
        console.error('Download error:', error);
        alert('An error occurred while downloading the QR code. Please try again.');
    }
}

// Function to print receipt
function printReceipt() {
    if (!currentReceiptData) {
        alert('No receipt data available');
        return;
    }
    
    // Build the receipt HTML with proper styling
    const receiptHTML = buildReceiptHTML(currentReceiptData);
    
    // Open print window
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    printWindow.document.write(receiptHTML);
    printWindow.document.close();
    
    // Wait for all content including images to load before printing
    printWindow.addEventListener('load', function() {
        const qrImage = printWindow.document.querySelector('.qr-code img');
        
        if (qrImage) {
            // Check if image is already loaded
            if (qrImage.complete && qrImage.naturalHeight !== 0) {
                // Image already loaded, print immediately
                setTimeout(() => {
                    printWindow.print();
                }, 200);
            } else {
                // Wait for image to load
                const checkImageLoad = setInterval(() => {
                    if (qrImage.complete && qrImage.naturalHeight !== 0) {
                        clearInterval(checkImageLoad);
                        setTimeout(() => {
                            printWindow.print();
                        }, 200);
                    }
                }, 100);
                
                // Timeout after 3 seconds if image doesn't load
                setTimeout(() => {
                    clearInterval(checkImageLoad);
                    printWindow.print();
                }, 3000);
            }
        } else {
            // No QR image found, print anyway
            setTimeout(() => {
                printWindow.print();
            }, 500);
        }
    });
    
    // Fallback: If page loads before event listener is attached
    if (printWindow.document.readyState === 'complete') {
        setTimeout(() => {
            const qrImage = printWindow.document.querySelector('.qr-code img');
            if (qrImage && qrImage.complete) {
                printWindow.print();
            }
        }, 1000);
    }
}

// Function to build receipt HTML
function buildReceiptHTML(paymentData) {
    const formattedDate = formatDate(paymentData.payment_date);
    const formattedMethod = formatPaymentMethod(paymentData.payment_method);
    
    // Generate QR code URL with simplified data
    const qrText = `${paymentData.receipt_number || paymentData.id}|${paymentData.payer_name}|${paymentData.amount_paid}|${paymentData.payment_date}`;
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&ecc=H&data=${encodeURIComponent(qrText)}`;
    
    return `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClearPay Payment Receipt - ${paymentData.receipt_number || paymentData.id}</title>
    <style>
        @page { size: A4; margin: 20mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: white; color: #333; line-height: 1.6; padding: 40px; }
        .receipt-wrapper { max-width: 700px; margin: 0 auto; background: white; border: 2px solid #e0e0e0; border-radius: 10px; overflow: hidden; }
        .receipt-header { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: white; text-align: center; padding: 30px 20px; }
        .receipt-header h1 { font-size: 32px; font-weight: 700; margin-bottom: 5px; letter-spacing: 1px; }
        .receipt-header p { font-size: 16px; opacity: 0.95; font-weight: 300; }
        .receipt-body { padding: 30px; }
        .section { margin-bottom: 25px; background: #f8f9fa; border-radius: 8px; padding: 20px; border-left: 5px solid #0d6efd; }
        .section-title { font-size: 16px; font-weight: 700; color: #0d6efd; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px 30px; }
        .info-item { margin-bottom: 12px; }
        .info-label { font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; font-weight: 600; }
        .info-value { font-size: 15px; font-weight: 600; color: #333; }
        .info-value.highlight { color: #28a745; font-size: 18px; }
        .receipt-number-box { background: white; border: 2px solid #0d6efd; border-radius: 8px; padding: 15px; text-align: center; margin-bottom: 25px; }
        .receipt-number-label { font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .receipt-number-value { font-size: 18px; font-weight: 700; color: #0d6efd; letter-spacing: 1px; }
        .qr-section { text-align: center; background: white; border: 3px solid #0d6efd; border-radius: 10px; padding: 25px; margin: 30px 0; }
        .qr-section h3 { font-size: 16px; color: #0d6efd; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .qr-code { margin: 20px 0; }
        .qr-code img { width: 200px; height: 200px; border: 5px solid #f0f0f0; border-radius: 10px; background: white; }
        .qr-note { font-size: 12px; color: #666; margin-top: 10px; font-style: italic; }
        .receipt-footer { text-align: center; padding: 20px; background: #f8f9fa; border-top: 2px solid #e0e0e0; margin-top: 30px; }
        .footer-text { font-size: 12px; color: #666; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .badge { display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-success { background: #28a745; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-danger { background: #dc3545; color: white; }
        @media print { body { background: white; padding: 0; } .receipt-wrapper { border: none; box-shadow: none; } }
    </style>
</head>
<body>
    <div class="receipt-wrapper">
        <div class="receipt-header">
            <h1>ClearPay</h1>
            <p>Payment Receipt</p>
        </div>
        <div class="receipt-body">
            <div class="receipt-number-box">
                <div class="receipt-number-label">Receipt Number</div>
                <div class="receipt-number-value">${paymentData.receipt_number || 'N/A'}</div>
            </div>
            <div class="section">
                <h2 class="section-title">📋 Receipt Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Receipt Number</div>
                        <div class="info-value">${paymentData.receipt_number || 'N/A'}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Payment Date</div>
                        <div class="info-value">${formattedDate}</div>
                    </div>
                </div>
            </div>
            <div class="section">
                <h2 class="section-title">👤 Payer Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value">${paymentData.payer_name || 'N/A'}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ID Number</div>
                        <div class="info-value">${paymentData.payer_id || 'N/A'}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Contact Number</div>
                        <div class="info-value">${paymentData.contact_number || 'N/A'}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value">${paymentData.email_address || 'N/A'}</div>
                    </div>
                </div>
            </div>
            <div class="section">
                <h2 class="section-title">💳 Payment Details</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Contribution Type</div>
                        <div class="info-value">${paymentData.contribution_title || 'N/A'}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Payment Method</div>
                        <div class="info-value">${formattedMethod}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Amount Paid</div>
                        <div class="info-value highlight">₱${parseFloat(paymentData.amount_paid || 0).toFixed(2)}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            ${(() => {
                                const status = paymentData.computed_status || paymentData.payment_status || 'pending';
                                const statusText = status === 'fully paid' ? 'COMPLETED' : (status === 'partial' ? 'PARTIAL' : status.toUpperCase());
                                const badgeClass = status === 'fully paid' ? 'badge-primary' : (status === 'partial' ? 'badge-warning' : 'badge-danger');
                                return `<span class="badge ${badgeClass}">${statusText}</span>`;
                            })()}
                        </div>
                    </div>
                </div>
            </div>
            <div class="qr-section">
                <h3>🔲 QR Receipt Code</h3>
                <div class="qr-code">
                    <img src="${qrUrl}" alt="QR Code for Receipt ${paymentData.receipt_number || paymentData.id}" 
                         onerror="console.error('QR image failed to load:', this.src); 
                                  this.onerror=null; 
                                  this.src='https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=${encodeURIComponent(qrText)}&choe=UTF-8';"
                         crossorigin="anonymous" />
                </div>
                <div class="qr-note">Scan this QR code to verify payment authenticity</div>
            </div>
            <div class="receipt-footer">
                <p class="footer-text">
                    <span style="font-size: 14px;">✓</span>
                    This receipt is digitally signed and verified by ClearPay Payment System
                </p>
            </div>
        </div>
    </div>
</body>
</html>`;
}

// Reset modal when closed and verify function is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Verify showQRReceipt function is available
    if (typeof window.showQRReceipt === 'function') {
        console.log('✓ showQRReceipt function is loaded and available');
    } else {
        console.error('✗ showQRReceipt function is NOT available');
    }
    
    const qrReceiptModal = document.getElementById('qrReceiptModal');
    if (qrReceiptModal) {
        qrReceiptModal.addEventListener('hidden.bs.modal', function() {
            currentReceiptData = null;
        });
    }
});

// Also verify immediately (in case DOM is already loaded)
if (typeof window.showQRReceipt === 'function') {
    console.log('✓ showQRReceipt function is available (immediate check)');
} else {
    console.error('✗ showQRReceipt function is NOT available (immediate check)');
}
</script>

<style>
@media print {
    .modal-footer {
        display: none !important;
    }
}

#qrReceiptModal .modal-content {
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

#qrReceiptModal .card {
    transition: transform 0.2s ease;
}

#qrReceiptModal .card:hover {
    transform: translateY(-2px);
}
</style>

<?php } ?> 