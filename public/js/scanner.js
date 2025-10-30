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
    const parts = qrData.split('|');
    if (parts.length < 1) return showNotification('Invalid QR code format', 'error');
    const receiptNumber = parts[0];
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
    // (Truncated: implementation same as before; should be retained from modal-qr-scanner)
    // ...
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
