// ================= Global QR Scanners ===================
// SCHOOL ID (Add Payment - new payer) and PAYMENT RECEIPT (Verify Payment) scanners
// Provides: openSchoolIDScanner(), startSchoolIDScanner(), stopSchoolIDScanner(),
//           startQRScanner(), stopScanner(), and all modal event setup for both modals
// Requires: jsQR (should be globally loaded), Bootstrap for modals, and modals present in DOM

// Defensive loader guard
if (window.__GLOBAL_SCANNER_LOGIC_LOADED__) throw new Error("scanner.js loaded more than once!");
window.__GLOBAL_SCANNER_LOGIC_LOADED__ = true;

// --------- SCHOOL ID SCANNER LOGIC ---------
let schoolIDScannerStream = null;
let schoolIDScannerCanvas = null;
let schoolIDScannerContext = null;

window.openSchoolIDScanner = function() {
    const elem = document.getElementById('schoolIDScannerModal');
    if (!elem) {
        alert('School ID scanner modal missing from page!');
        return;
    }
    let scannerModal;
    // If already initialized, use getInstance to avoid double init
    scannerModal = bootstrap.Modal.getOrCreateInstance(elem);
    if (elem.classList.contains('show')) {
        // Already shown; do nothing
        return;
    }
    scannerModal.show();
};

async function startSchoolIDScanner() {
    try {
        schoolIDScannerStream = await navigator.mediaDevices.getUserMedia({ 
            video: { facingMode: 'environment', width: { ideal: 640 }, height: { ideal: 480 } }
        });
        const video = document.getElementById('schoolIDVideo');
        video.srcObject = schoolIDScannerStream;
        video.onloadedmetadata = () => {
            video.play();
            schoolIDScannerCanvas = document.createElement('canvas');
            schoolIDScannerCanvas.width = video.videoWidth;
            schoolIDScannerCanvas.height = video.videoHeight;
            schoolIDScannerContext = schoolIDScannerCanvas.getContext('2d');
            scanSchoolIDQRCode();
        };
    } catch (error) {
        console.error('Error accessing camera:', error);
        showNotification('Unable to access camera. Please check permissions.', 'error');
        document.getElementById('schoolIDScannerStatus').innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Camera access denied. Please use manual input instead.
            </div>
        `;
    }
}

function scanSchoolIDQRCode() {
    const video = document.getElementById('schoolIDVideo');
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        schoolIDScannerContext.drawImage(video, 0, 0, schoolIDScannerCanvas.width, schoolIDScannerCanvas.height);
        const imageData = schoolIDScannerContext.getImageData(0, 0, schoolIDScannerCanvas.width, schoolIDScannerCanvas.height);
        if (typeof jsQR !== 'undefined') {
            const code = jsQR(imageData.data, imageData.width, imageData.height);
            if (code) {
                stopSchoolIDScanner();
                processSchoolIDQRCode(code.data);
                return;
            }
        }
    }
    requestAnimationFrame(scanSchoolIDQRCode);
}

function processSchoolIDQRCode(qrData) {
    let studentData;
    try {
        studentData = JSON.parse(qrData);
    } catch (e) {
        studentData = parseSchoolIDString(qrData);
    }
    let studentID = studentData.student_id || studentData.id || studentData.payer_id || null;
    let studentName = studentData.name || studentData.student_name || studentData.payer_name || null;
    let courseCode = studentData.course || studentData.course_code || null;

    if (studentID) {
        document.getElementById('payerId').value = studentID;
        if (studentName) {
            document.getElementById('payerName').value = studentName;
            // Generate email
            const timestamp = Date.now().toString().slice(-6);
            const emailBase = studentName.toLowerCase().replace(/\s+/g, '');
            const uniqueEmail = emailBase + timestamp + '@example.com';
            document.getElementById('payerEmail').value = uniqueEmail;
        }
        if (courseCode) document.getElementById('payerCourse').value = courseCode;
        showNotification(`School ID scanned successfully! ID: ${studentID}${studentName ? `\nName: ${studentName}` : ''}${courseCode ? `\nCourse: ${courseCode}` : ''}`, 'success');
        const scannerModal = bootstrap.Modal.getInstance(document.getElementById('schoolIDScannerModal'));
        if (scannerModal) scannerModal.hide();
        // NEW: If scan was requested from Add New Payer, reopen that modal
        if (window._openSchoolIDFromNewPayer) {
          setTimeout(() => {
            const addModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('addPaymentModal'));
            addModal.show();
            window._openSchoolIDFromNewPayer = false;
          }, 500); // Wait a bit so modals don't stack
        }
    } else {
        showNotification('Could not extract student ID from QR code', 'warning');
    }
}

function parseSchoolIDString(qrData) {
    const idMatch = qrData.match(/^(\d+)/);
    if (!idMatch) return { student_id: qrData };
    const idNumber = idMatch[1];
    let remainingString = qrData.substring(idNumber.length), courseCode = null, nameString = remainingString;
    const courseCodeMatch = remainingString.match(/([A-Z]{2,6}\d?)$/);
    if (courseCodeMatch) {
        courseCode = courseCodeMatch[1];
        nameString = remainingString.substring(0, remainingString.length - courseCode.length);
    }
    let studentName = nameString.replace(/\s+/g, ' ').trim();
    if (studentName.includes('.')) studentName = studentName.split('.').map(part => part.trim()).join('. ').trim();
    return { student_id: idNumber, name: studentName, course: courseCode };
}

function stopSchoolIDScanner() {
    if (schoolIDScannerStream) {
        schoolIDScannerStream.getTracks().forEach(track => track.stop());
        schoolIDScannerStream = null;
    }
}

window.toggleSchoolIDManualInput = function() {
    const manualSection = document.getElementById('schoolIDManualInputSection');
    manualSection.style.display = manualSection.style.display === 'none' ? 'block' : 'none';
};

window.useManualStudentID = function() {
    const studentID = document.getElementById('manualStudentIDInput').value.trim();
    if (!studentID) return showNotification('Please enter a student ID', 'warning');
    document.getElementById('payerId').value = studentID;
    showNotification(`Student ID entered: ${studentID}`, 'success');
    const scannerModal = bootstrap.Modal.getInstance(document.getElementById('schoolIDScannerModal'));
    if (scannerModal) scannerModal.hide();
};

// Wire up modal events (school ID)
document.addEventListener('DOMContentLoaded', function() {
    const schoolIDScannerModal = document.getElementById('schoolIDScannerModal');
    if (schoolIDScannerModal) {
        schoolIDScannerModal.addEventListener('shown.bs.modal', startSchoolIDScanner);
        schoolIDScannerModal.addEventListener('hidden.bs.modal', function() {
            stopSchoolIDScanner();
            document.getElementById('schoolIDManualInputSection').style.display = 'none';
            document.getElementById('manualStudentIDInput').value = '';
        });
    }
});

// --------- PAYMENT RECEIPT SCANNER LOGIC ---------
let paymentScannerStream = null;
let paymentScannerCanvas = null;
let paymentScannerContext = null;

async function startQRScanner() {
    if (typeof jsQR === 'undefined') {
        document.getElementById('scannerStatus').innerHTML =
            `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>QR scanning library (jsQR) failed to load.<br>Please reload the page or contact your administrator.</div>`;
        return;
    }
    try {
        paymentScannerStream = await navigator.mediaDevices.getUserMedia({ 
            video: { facingMode: 'environment', width: { ideal: 640 }, height: { ideal: 480 } }
        });
        const video = document.getElementById('qrVideo');
        video.srcObject = paymentScannerStream;
        video.onloadedmetadata = () => {
            video.play();
            paymentScannerCanvas = document.createElement('canvas');
            paymentScannerCanvas.width = video.videoWidth;
            paymentScannerCanvas.height = video.videoHeight;
            paymentScannerContext = paymentScannerCanvas.getContext('2d');
            scanQRCode();
        };
    } catch (error) {
        console.error('Error accessing camera:', error);
        showNotification('Unable to access camera. Please check permissions.', 'error');
        document.getElementById('scannerStatus').innerHTML =
            `<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Camera access denied. Please use manual input instead.</div>`;
    }
}

function scanQRCode() {
    const video = document.getElementById('qrVideo');
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        paymentScannerContext.drawImage(video, 0, 0, paymentScannerCanvas.width, paymentScannerCanvas.height);
        const imageData = paymentScannerContext.getImageData(0, 0, paymentScannerCanvas.width, paymentScannerCanvas.height);
        if (typeof jsQR !== 'undefined') {
            const code = jsQR(imageData.data, imageData.width, imageData.height);
            if (code) {
                stopScanner();
                verifyPayment(code.data);
                return;
            }
        }
    }
    requestAnimationFrame(scanQRCode);
}

function stopScanner() {
    if (paymentScannerStream) {
        paymentScannerStream.getTracks().forEach(track => track.stop());
        paymentScannerStream = null;
    }
}

window.toggleManualInput = function() {
    const manualSection = document.getElementById('manualInputSection');
    manualSection.style.display = manualSection.style.display === 'none' ? 'block' : 'none';
};

window.verifyManualReceipt = function() {
    const receiptNumber = document.getElementById('manualReceiptInput').value.trim();
    if (!receiptNumber) return showNotification('Please enter a receipt number', 'warning');
    verifyPayment(receiptNumber);
};

// PAYMENT VERIFICATION LOGIC
function verifyPayment(qrData) {
    // Defensive guards to avoid firing on modal open or empty scans
    if (!qrData || typeof qrData !== 'string') {
        return; // ignore invalid input silently to prevent noisy toasts
    }

    let receiptNumber = '';

    // Try JSON format first
    try {
        const parsed = JSON.parse(qrData);
        if (parsed && (parsed.receipt_number || parsed.receiptNumber)) {
            receiptNumber = String(parsed.receipt_number || parsed.receiptNumber).trim();
        }
    } catch (_) {
        // Not JSON; fall through to delimited parsing
    }

    if (!receiptNumber) {
        // Common format: receipt_number|...
        const parts = qrData.split('|');
        if (parts.length >= 1) {
            receiptNumber = String(parts[0] || '').trim();
        }
    }

    // Final validation: must be non-empty and reasonably shaped
    if (!receiptNumber) return; // do nothing if no receipt detected

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

function showPaymentVerificationModal(payment) {
    // Ensure amount_paid is properly displayed
    const amountPaid = payment.amount_paid || 0;

    // Local date formatter fallback
    function formatDateLocal(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Check if payment is refunded
    const isRefunded = payment.is_refunded || payment.refund_status === 'fully_refunded' || payment.refund_status === 'partially_refunded';
    const refundStatus = payment.refund_status || 'no_refund';
    const totalRefunded = payment.total_refunded || 0;
    const refundedAt = payment.refunded_at ? formatDateLocal(payment.refunded_at) : null;
    
    // Determine header and alert styling based on refund status
    const headerClass = isRefunded ? 'bg-danger' : 'bg-success';
    const headerIcon = isRefunded ? 'fa-exclamation-triangle' : 'fa-check-circle';
    const headerTitle = isRefunded ? 'Payment Refunded' : 'Payment Verified';
    
    const modal = `
        <div class="modal fade" id="paymentVerificationModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header ${headerClass} text-white">
                        <h5 class="modal-title">
                            <i class="fas ${headerIcon} me-2"></i>${headerTitle}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${isRefunded ? `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>WARNING:</strong> This payment has already been refunded!
                            ${refundStatus === 'fully_refunded' ? 
                                '<br>The full amount of ₱' + parseFloat(amountPaid).toFixed(2) + ' has been refunded.' :
                                '<br>Partially refunded: ₱' + parseFloat(totalRefunded).toFixed(2) + ' of ₱' + parseFloat(amountPaid).toFixed(2) + ' has been refunded.'
                            }
                            ${refundedAt ? '<br>Refund processed on: ' + refundedAt : ''}
                        </div>
                        ` : `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            This payment has been successfully verified!
                        </div>
                        `}
                        
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
                                        <p class="h6 mb-0">${typeof formatDate === 'function' ? formatDate(payment.payment_date) : formatDateLocal(payment.payment_date)}</p>
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
                                            const statusText = status === 'fully paid' ? 'COMPLETED' : (status === 'partial' ? 'PARTIAL' : String(status).toUpperCase());
                                            const badgeClass = status === 'fully paid' ? 'bg-primary text-white' : (status === 'partial' ? 'bg-warning text-dark' : 'bg-danger text-white');
                                            return `<span class="badge ${badgeClass}">${statusText}</span>`;
                                        })()}
                                    </div>
                                </div>
                            </div>
                            ${isRefunded ? `
                            <div class="col-md-6">
                                <div class="card border-0 bg-danger bg-opacity-10 border-danger">
                                    <div class="card-body">
                                        <h6 class="text-danger mb-2">
                                            <i class="fas fa-undo me-1"></i>Refund Status
                                        </h6>
                                        ${(() => {
                                            if (refundStatus === 'fully_refunded') {
                                                return '<span class="badge bg-danger">FULLY REFUNDED</span><br><small class="text-muted mt-1 d-block">₱' + parseFloat(totalRefunded).toFixed(2) + ' refunded</small>';
                                            } else if (refundStatus === 'partially_refunded') {
                                                return '<span class="badge bg-warning text-dark">PARTIALLY REFUNDED</span><br><small class="text-muted mt-1 d-block">₱' + parseFloat(totalRefunded).toFixed(2) + ' of ₱' + parseFloat(amountPaid).toFixed(2) + ' refunded</small>';
                                            }
                                            return '<span class="badge bg-secondary">NO REFUND</span>';
                                        })()}
                                        ${refundedAt ? '<br><small class="text-muted mt-2 d-block">Refunded: ' + refundedAt + '</small>' : ''}
                                    </div>
                                </div>
                            </div>
                            ` : ''}
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

    // Store payment data for receipt viewing/handoff
    window.paymentForReceipt = payment;
}

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
        const baseUrl = window.APP_BASE_URL || '';
        fetch(`${baseUrl}/payments/by-contribution/${payment.contribution_id || payment.contributionId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.payments && data.payments.length > 0) {
                    const payerPayments = data.payments.filter(p => 
                        p.payer_id === payment.payer_id || p.payer_id === payment.payerId
                    );

                    if (payerPayments.length > 0) {
                        const payerData = {
                            payer_name: payment.payer_name,
                            payer_id: payment.payer_id,
                            payer_student_id: payment.payer_id_number || payment.payer_id,
                            contact_number: payment.contact_number || 'N/A',
                            email_address: payment.email_address || 'N/A',
                            contribution_title: payment.contribution_title || 'N/A',
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

                        if (typeof showPayerPaymentHistory === 'function') {
                            showPayerPaymentHistory(payerData);
                        } else {
                            showNotification('Payment history modal not available', 'error');
                        }
                    } else {
                        const payerData = {
                            payer_name: payment.payer_name,
                            payer_id: payment.payer_id,
                            payer_student_id: payment.payer_id_number || payment.payer_id,
                            contact_number: payment.contact_number || 'N/A',
                            email_address: payment.email_address || 'N/A',
                            contribution_title: payment.contribution_title || 'N/A',
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

// Attach scanner modal events globally
document.addEventListener('DOMContentLoaded', function() {
    const scannerModal = document.getElementById('qrScannerModal');
    if (scannerModal) {
        scannerModal.addEventListener('shown.bs.modal', startQRScanner);
        scannerModal.addEventListener('hidden.bs.modal', function() {
            stopScanner();
            document.getElementById('manualInputSection').style.display = 'none';
            document.getElementById('manualReceiptInput').value = '';
        });
    }
});
// =============== END OF scanner.js ===================
