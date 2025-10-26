<!-- QR Code Scanner Modal -->
<div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="qrScannerModalLabel">
                    <i class="fas fa-qrcode me-2"></i>Verify Payment via QR Code
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="text-center mb-4">
                    <p class="text-muted">Scan the QR code from the payment receipt to verify the transaction</p>
                </div>

                <!-- Scanner Container -->
                <div class="scanner-container mb-4">
                    <div id="qrReader" style="position: relative;">
                        <!-- Camera preview will be inserted here by jsQR -->
                        <video id="qrVideo" autoplay playsinline style="width: 100%; border: 2px solid #0d6efd; border-radius: 8px;"></video>
                        
                        <!-- Overlay with scanning area -->
                        <div class="scanner-overlay">
                            <div class="scan-line"></div>
                        </div>
                    </div>
                </div>

                <!-- Scanning Status -->
                <div id="scannerStatus" class="text-center">
                    <p class="text-muted">
                        <i class="fas fa-camera me-2"></i>
                        Point camera at QR code
                    </p>
                </div>

                <!-- Manual Input Option -->
                <div class="text-center mt-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleManualInput()">
                        <i class="fas fa-keyboard me-1"></i>Or Enter Receipt Number Manually
                    </button>
                </div>

                <!-- Manual Input Form (Initially Hidden) -->
                <div id="manualInputSection" style="display: none; margin-top: 20px;">
                    <div class="card border-primary">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-keyboard me-2"></i>Enter Receipt Number
                            </h6>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-receipt"></i></span>
                                <input type="text" class="form-control" id="manualReceiptInput" placeholder="Enter receipt number (e.g., RCPT-20250101-12345678)">
                                <button class="btn btn-primary" type="button" onclick="verifyManualReceipt()">
                                    <i class="fas fa-search me-1"></i>Verify
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="stopScanner()" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.scanner-container {
    position: relative;
    max-width: 500px;
    margin: 0 auto;
}

.scanner-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 200px;
    height: 200px;
    border: 2px solid #0d6efd;
    border-radius: 8px;
    pointer-events: none;
}

.scanner-overlay::before,
.scanner-overlay::after {
    content: '';
    position: absolute;
    width: 30px;
    height: 30px;
    border: 3px solid #0d6efd;
}

.scanner-overlay::before {
    top: -3px;
    left: -3px;
    border-right: none;
    border-bottom: none;
    border-top-left-radius: 8px;
}

.scanner-overlay::after {
    bottom: -3px;
    right: -3px;
    border-left: none;
    border-top: none;
    border-bottom-right-radius: 8px;
}

.scan-line {
    position: absolute;
    top: 10%;
    left: 50%;
    transform: translateX(-50%);
    width: 180px;
    height: 2px;
    background: linear-gradient(to bottom, transparent, #0d6efd, transparent);
    animation: scan 2s linear infinite;
}

@keyframes scan {
    0% { top: 10%; opacity: 1; }
    50% { top: 90%; opacity: 1; }
    100% { top: 10%; opacity: 0.3; }
}
</style>

<script>
let scannerStream = null;
let scannerCanvas = null;
let scannerContext = null;

// Function to start the scanner
async function startQRScanner() {
    try {
        // Get user media (camera)
        scannerStream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'environment', // Use back camera on mobile
                width: { ideal: 640 },
                height: { ideal: 480 }
            } 
        });
        
        const video = document.getElementById('qrVideo');
        video.srcObject = scannerStream;
        
        // Wait for video to be ready
        video.onloadedmetadata = () => {
            video.play();
            
            // Create canvas for scanning
            scannerCanvas = document.createElement('canvas');
            scannerCanvas.width = video.videoWidth;
            scannerCanvas.height = video.videoHeight;
            scannerContext = scannerCanvas.getContext('2d');
            
            // Start scanning loop
            scanQRCode();
        };
        
    } catch (error) {
        console.error('Error accessing camera:', error);
        showNotification('Unable to access camera. Please check permissions.', 'error');
        document.getElementById('scannerStatus').innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Camera access denied. Please use manual input instead.
            </div>
        `;
    }
}

// Function to scan for QR codes
function scanQRCode() {
    const video = document.getElementById('qrVideo');
    
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        scannerContext.drawImage(video, 0, 0, scannerCanvas.width, scannerCanvas.height);
        const imageData = scannerContext.getImageData(0, 0, scannerCanvas.width, scannerCanvas.height);
        
        // Use jsQR library to detect QR code
        if (typeof jsQR !== 'undefined') {
            const code = jsQR(imageData.data, imageData.width, imageData.height);
            
            if (code) {
                console.log('QR Code detected:', code.data);
                stopScanner();
                verifyPayment(code.data);
                return;
            }
        }
    }
    
    // Continue scanning
    requestAnimationFrame(scanQRCode);
}

// Function to stop the scanner
function stopScanner() {
    if (scannerStream) {
        scannerStream.getTracks().forEach(track => track.stop());
        scannerStream = null;
    }
}

// Function to verify payment from QR data
function verifyPayment(qrData) {
    console.log('Verifying payment with data:', qrData);
    
    // Parse QR data (format: receipt_number|payer_name|amount_paid|payment_date)
    const parts = qrData.split('|');
    
    if (parts.length < 1) {
        showNotification('Invalid QR code format', 'error');
        return;
    }
    
    const receiptNumber = parts[0];
    
    // Fetch payment details from backend
    const baseUrl = window.APP_BASE_URL || '';
    
    fetch(`${baseUrl}/payments/verify/${encodeURIComponent(receiptNumber)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.payment) {
                showPaymentVerificationModal(data.payment);
            } else {
                showNotification('Payment not found or invalid receipt number', 'warning');
            }
        })
        .catch(error => {
            console.error('Error verifying payment:', error);
            showNotification('Error verifying payment', 'error');
        });
}

// Function to show payment verification details
function showPaymentVerificationModal(payment) {
    // Debug: Log the payment data
    console.log('Payment data received:', payment);
    
    // Ensure amount_paid is properly displayed
    const amountPaid = payment.amount_paid || 0;
    console.log('Amount to display:', amountPaid);
    
    const modal = `
        <div class="modal fade" id="paymentVerificationModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-check-circle me-2"></i>Payment Verified
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            This payment has been successfully verified!
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="text-muted mb-2">Receipt Number</h6>
                                        <p class="h5 mb-0">${payment.receipt_number}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="text-muted mb-2">Payment Date</h6>
                                        <p class="h6 mb-0">${formatDate(payment.payment_date)}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="text-muted mb-2">Payer Name</h6>
                                        <p class="h6 mb-0">${payment.payer_name}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="text-muted mb-2">Total Amount Paid</h6>
                                        <p class="h5 text-success mb-0">₱${parseFloat(amountPaid).toFixed(2)}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="text-muted mb-2">Contribution</h6>
                                        <p class="h6 mb-0">${payment.contribution_title || 'N/A'}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="text-muted mb-2">Status</h6>
                                        ${(() => {
                                            const status = payment.computed_status || payment.payment_status || 'fully paid';
                                            const statusText = status === 'fully paid' ? 'COMPLETED' : (status === 'partial' ? 'PARTIAL' : status.toUpperCase());
                                            const badgeClass = status === 'fully paid' ? 'bg-primary text-white' : (status === 'partial' ? 'bg-warning text-dark' : 'bg-danger text-white');
                                            return `<span class="badge ${badgeClass}">${statusText}</span>`;
                                        })()}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Close
                        </button>
                        <button type="button" class="btn btn-primary" onclick="showPaymentHistoryFromVerification()">
                            <i class="fas fa-history me-1"></i>View Payment History
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to body if not exists
    if (!document.getElementById('paymentVerificationModal')) {
        document.body.insertAdjacentHTML('beforeend', modal);
    }
    
    // Show modal
    const verificationModal = new bootstrap.Modal(document.getElementById('paymentVerificationModal'));
    verificationModal.show();
    
    // Clean up on close
    document.getElementById('paymentVerificationModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
    
    // Store payment data for receipt viewing
    window.paymentForReceipt = payment;
}

// Function to show payment history from verification modal
function showPaymentHistoryFromVerification() {
    if (!window.paymentForReceipt) {
        showNotification('Payment data not available', 'error');
        return;
    }
    
    const payment = window.paymentForReceipt;
    
    // Close the verification modal
    const verificationModalEl = document.getElementById('paymentVerificationModal');
    if (verificationModalEl) {
        const verificationModal = bootstrap.Modal.getInstance(verificationModalEl);
        if (verificationModal) {
            verificationModal.hide();
        }
    }
    
    // Close the scanner modal if it's still open
    const scannerModalEl = document.getElementById('qrScannerModal');
    if (scannerModalEl && scannerModalEl.classList.contains('show')) {
        const scannerModal = bootstrap.Modal.getInstance(scannerModalEl);
        if (scannerModal) {
            scannerModal.hide();
        }
    }
    
    // Wait a bit for modals to close
    setTimeout(() => {
        // Fetch all payments for this payer's contribution
        const baseUrl = window.APP_BASE_URL || '';
        
        // First, we need to find all payments for this specific payer and contribution
        // We'll use the payer_id from the payment and fetch by contribution
        fetch(`${baseUrl}/payments/by-contribution/${payment.contribution_id || payment.contributionId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.payments && data.payments.length > 0) {
                    // Filter payments for this specific payer
                    const payerPayments = data.payments.filter(p => 
                        p.payer_id === payment.payer_id || p.payer_id === payment.payerId
                    );
                    
                    if (payerPayments.length > 0) {
                        // Create payer data with all their payments
                        const payerData = {
                            payer_name: payment.payer_name,
                            payer_id: payment.payer_id,
                            payer_student_id: payment.payer_id_number || payment.payer_id, // Use payer_id_number (student ID) if available
                            contact_number: payment.contact_number || 'N/A',
                            email_address: payment.email_address || 'N/A',
                            contribution_title: payment.contribution_title || 'N/A', // Add contribution title here
                            payments: payerPayments.map(p => ({
                                id: p.id,
                                amount_paid: parseFloat(p.amount_paid || 0),
                                payment_date: p.payment_date,
                                payment_method: p.payment_method || 'cash',
                                payment_status: p.payment_status || 'fully paid',
                                remaining_balance: parseFloat(p.remaining_balance || 0),
                                receipt_number: p.receipt_number,
                                contribution_title: p.contribution_title || payment.contribution_title || 'N/A',
                                contact_number: p.contact_number || payment.contact_number || 'N/A',
                                email_address: p.email_address || payment.email_address || 'N/A',
                                payer_id: p.payer_student_id || p.payer_id || payment.payer_id_number || 'N/A'
                            }))
                        };
                        
                        console.log('Payer data for payment history:', payerData);
                        
                        // Show the payment history modal
                        if (typeof showPayerPaymentHistory === 'function') {
                            showPayerPaymentHistory(payerData);
                        } else {
                            showNotification('Payment history modal not available', 'error');
                        }
                    } else {
                        // No other payments found, just show the current one
                        const payerData = {
                            payer_name: payment.payer_name,
                            payer_id: payment.payer_id,
                            payer_student_id: payment.payer_id_number || payment.payer_id, // Use payer_id_number (student ID) if available
                            contact_number: payment.contact_number || 'N/A',
                            email_address: payment.email_address || 'N/A',
                            contribution_title: payment.contribution_title || 'N/A', // Add contribution title here
                            payments: [{
                                amount_paid: parseFloat(payment.amount_paid || 0),
                                payment_date: payment.payment_date,
                                payment_method: payment.payment_method || 'cash',
                                payment_status: payment.payment_status || 'fully paid',
                                remaining_balance: 0,
                                receipt_number: payment.receipt_number,
                                contribution_title: payment.contribution_title || 'N/A',
                                contact_number: payment.contact_number || 'N/A',
                                email_address: payment.email_address || 'N/A',
                                payer_id: payment.payer_id_number || payment.payer_id || 'N/A'
                            }]
                        };
                        
                        console.log('Payer data for single payment:', payerData);
                        
                        if (typeof showPayerPaymentHistory === 'function') {
                            showPayerPaymentHistory(payerData);
                        } else {
                            showNotification('Payment history modal not available', 'error');
                        }
                    }
                } else {
                    showNotification('No payment history found', 'warning');
                }
            })
            .catch(error => {
                console.error('Error fetching payment history:', error);
                showNotification('Error loading payment history', 'error');
            });
    }, 100);
}

// Toggle manual input
function toggleManualInput() {
    const manualSection = document.getElementById('manualInputSection');
    manualSection.style.display = manualSection.style.display === 'none' ? 'block' : 'none';
}

// Verify manual receipt
function verifyManualReceipt() {
    const receiptNumber = document.getElementById('manualReceiptInput').value.trim();
    
    if (!receiptNumber) {
        showNotification('Please enter a receipt number', 'warning');
        return;
    }
    
    verifyPayment(receiptNumber);
}

// Event listener for modal close
document.addEventListener('DOMContentLoaded', function() {
    const scannerModal = document.getElementById('qrScannerModal');
    
    if (scannerModal) {
        // Start scanner when modal opens
        scannerModal.addEventListener('shown.bs.modal', function() {
            startQRScanner();
        });
        
        // Stop scanner when modal closes
        scannerModal.addEventListener('hidden.bs.modal', function() {
            stopScanner();
            document.getElementById('manualInputSection').style.display = 'none';
            document.getElementById('manualReceiptInput').value = '';
        });
    }
});
</script>
