<?= $this->extend('layouts/payer-layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Payment History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($contributions)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Payment Records</h5>
                            <p class="text-muted">Your payment history will appear here</p>
                        </div>
                    <?php else: ?>
                        <div class="accordion" id="contributionsAccordion">
                            <?php foreach ($contributions as $index => $contribution): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading<?= $contribution['id'] ?>">
                                        <button class="accordion-button <?= $index === 0 ? '' : 'collapsed' ?>" 
                                                type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#collapse<?= $contribution['id'] ?>" 
                                                aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" 
                                                aria-controls="collapse<?= $contribution['id'] ?>">
                                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                <div class="text-start">
                                                    <h6 class="mb-1 fw-bold"><?= esc($contribution['title']) ?></h6>
                                                    <small class="text-muted"><?= esc($contribution['description']) ?></small>
                                                </div>
                                                <div class="text-end">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="text-center">
                                                            <small class="text-muted d-block">Total Amount</small>
                                                            <strong class="text-primary">₱<?= number_format($contribution['amount'], 2) ?></strong>
                                                        </div>
                                                        <div class="text-center">
                                                            <small class="text-muted d-block">Paid</small>
                                                            <strong class="text-success">₱<?= number_format($contribution['total_paid'], 2) ?></strong>
                                                        </div>
                                                        <div class="text-center">
                                                            <small class="text-muted d-block">Status</small>
                                                            <?php if ($contribution['is_fully_paid']): ?>
                                                                <span class="badge bg-success">COMPLETED</span>
                                                            <?php elseif ($contribution['is_partially_paid']): ?>
                                                                <span class="badge bg-warning text-dark">PARTIAL</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">UNPAID</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse<?= $contribution['id'] ?>" 
                                         class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" 
                                         aria-labelledby="heading<?= $contribution['id'] ?>" 
                                         data-bs-parent="#contributionsAccordion">
                                        <div class="accordion-body">
                                            <?php if (empty($contribution['payments'])): ?>
                                                <div class="text-center py-3">
                                                    <i class="fas fa-receipt fa-2x text-muted mb-2"></i>
                                                    <p class="text-muted mb-0">No payments made for this contribution yet</p>
                                                </div>
                                            <?php else: ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Date</th>
                                                                <th>Amount</th>
                                                                <th>Reference Number</th>
                                                                <th>Payment Method</th>
                                                                <th>Payment Status</th>
                                                                <th>Refund Status</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($contribution['payments'] as $payment): ?>
                                                                <tr class="payment-row"
                                                                    style="cursor: pointer;"
                                                                    data-payment='<?= json_encode($payment) ?>'
                                                                    onmouseover="this.style.backgroundColor='#f8f9fa'" 
                                                                    onmouseout="this.style.backgroundColor=''">
                                                                    <td><?= date('M d, Y', strtotime($payment['payment_date'] ?? $payment['created_at'])) ?></td>
                                                                    <td><strong>₱<?= number_format($payment['amount_paid'], 2) ?></strong></td>
                                                                    <td><code><?= esc($payment['reference_number'] ?? 'N/A') ?></code></td>
                                                                    <td><?= esc(ucfirst($payment['payment_method'] ?? 'N/A')) ?></td>
                                                                    <td>
                                                                        <?php 
                                                                        $paymentStatus = $payment['payment_status'] ?? 'completed';
                                                                        $statusClass = 'bg-primary text-white';
                                                                        $statusText = 'COMPLETED';
                                                                        
                                                                        if ($paymentStatus === 'pending') {
                                                                            $statusClass = 'bg-warning text-dark';
                                                                            $statusText = 'PENDING';
                                                                        } elseif ($paymentStatus === 'failed') {
                                                                            $statusClass = 'bg-danger text-white';
                                                                            $statusText = 'FAILED';
                                                                        }
                                                                        ?>
                                                                        <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                                                    </td>
                                                                    <td>
                                                                        <?php
                                                                        $refundStatus = $payment['refund_status'] ?? 'no_refund';
                                                                        $refundStatusClass = 'bg-secondary';
                                                                        $refundStatusText = 'NO REFUND';
                                                                        
                                                                        switch($refundStatus) {
                                                                            case 'fully_refunded':
                                                                                $refundStatusClass = 'bg-danger';
                                                                                $refundStatusText = 'FULLY REFUNDED';
                                                                                break;
                                                                            case 'partially_refunded':
                                                                                $refundStatusClass = 'bg-warning text-dark';
                                                                                $refundStatusText = 'PARTIALLY REFUNDED';
                                                                                break;
                                                                            case 'no_refund':
                                                                                $refundStatusClass = 'bg-secondary';
                                                                                $refundStatusText = 'NO REFUND';
                                                                                break;
                                                                        }
                                                                        ?>
                                                                        <span class="badge <?= $refundStatusClass ?>"><?= $refundStatusText ?></span>
                                                                        <?php if ($refundStatus !== 'no_refund' && isset($payment['total_refunded'])): ?>
                                                                            <br><small class="text-muted">₱<?= number_format($payment['total_refunded'], 2) ?> refunded</small>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <button class="btn btn-sm btn-outline-primary" 
                                                                                onclick="event.stopPropagation(); showPaymentQRReceipt(<?= htmlspecialchars(json_encode($payment)) ?>)">
                                                                            <i class="fas fa-qrcode me-1"></i>View QR
                                                                        </button>
                                                                        <?php if (($payment['available_refund'] ?? 0) > 0): ?>
                                                                            <button type="button" class="btn btn-sm btn-outline-warning mt-1" onclick="event.stopPropagation(); openRequestRefundModal(<?= htmlspecialchars(json_encode($payment)) ?>)">
                                                                                <i class="fas fa-undo me-1"></i>Request Refund
                                                                            </button>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Receipt Modal -->
<?= $this->include('partials/modal-qr-receipt') ?>
<?= $this->include('partials/modal-request-refund') ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers to payment rows
    const paymentRows = document.querySelectorAll('.payment-row');
    paymentRows.forEach(row => {
        row.addEventListener('click', function() {
            const paymentData = JSON.parse(this.getAttribute('data-payment'));
            console.log('Payment clicked:', paymentData);
            
            // Show QR receipt modal
            if (typeof window.showQRReceipt === 'function') {
                window.showQRReceipt(paymentData);
            } else {
                console.error('showQRReceipt function not available');
                alert('QR receipt functionality not available. Please refresh the page.');
            }
        });
    });
});

// Function to show payment QR receipt (called from button click)
function showPaymentQRReceipt(paymentData) {
    console.log('showPaymentQRReceipt called with data:', paymentData);
    
    if (typeof window.showQRReceipt === 'function') {
        window.showQRReceipt(paymentData);
    } else {
        console.error('showQRReceipt function not available');
        alert('QR receipt functionality not available. Please refresh the page.');
    }
}

function openRequestRefundModal(payment) {
    window._refundModalPayment = payment;
    const modalEl = document.getElementById('requestRefundModal');
    if (modalEl) {
        try { modalEl.dataset.payment = JSON.stringify(payment); } catch(e){}
    }
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}
</script>

<?= $this->endSection() ?>