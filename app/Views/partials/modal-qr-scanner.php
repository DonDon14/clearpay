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

<script></script>
