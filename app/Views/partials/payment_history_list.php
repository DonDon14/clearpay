<!-- Payment History List -->
<div class="mb-3">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="searchPayments" class="form-control" placeholder="Search by student name, ID, or payment type...">
            </div>
        </div>
        <div class="col-md-4">
            <div class="d-flex gap-2">
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="verified">Verified</option>
                    <option value="pending">Pending</option>
                    <option value="rejected">Rejected</option>
                </select>
                <button class="btn btn-outline-secondary" onclick="refreshHistory()">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="paymentRecords">
    <?php if (!empty($payments)): ?>
        <div class="row g-3">
            <?php foreach ($payments as $payment): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm payment-item" 
                         data-status="<?= esc($payment['payment_status']) ?>"
                         style="transition: transform 0.2s, box-shadow 0.2s;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="payment-avatar me-3">
                                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6, #0ea5e9); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1rem; flex-shrink: 0;">
                                        <?= strtoupper(substr($payment['student_name'], 0, 2)) ?>
                                    </div>
                                </div>
                                
                                <div class="payment-details flex-grow-1">
                                    <h5 class="mb-1 fw-semibold"><?= esc($payment['student_name']) ?></h5>
                                    <p class="text-muted mb-1 payment-type"><?= esc($payment['payment_type'] ?? 'General Payment') ?></p>
                                    <p class="text-muted mb-0 small payment-time">
                                        ID: <?= esc($payment['student_id']) ?> • <?= date('M j, Y g:i A', strtotime($payment['created_at'])) ?>
                                    </p>
                                </div>
                                
                                <div class="payment-amount-status text-end me-3">
                                    <div class="amount h5 mb-1">₱<?= number_format($payment['amount_paid'], 2) ?></div>
                                    <span class="badge bg-<?= $payment['payment_status'] === 'verified' ? 'success' : ($payment['payment_status'] === 'pending' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst(esc($payment['payment_status'])) ?>
                                    </span>
                                </div>
                                
                                <div class="payment-actions">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-outline-primary btn-sm" onclick="viewPaymentDetails(<?= htmlspecialchars(json_encode($payment)) ?>)" title="View receipt">
                                            <i class="fas fa-receipt"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="downloadReceipt(<?= $payment['id'] ?? 0 ?>)" title="Download QR">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <?php if ($payment['payment_status'] === 'pending'): ?>
                                        <button class="btn btn-outline-warning btn-sm" onclick="verifyPayment(<?= $payment['id'] ?? 0 ?>)" title="Verify payment">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-receipt text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
            </div>
            <h4 class="text-muted">No Payment Records</h4>
            <p class="text-muted">No payment transactions have been recorded yet.</p>
            <button class="btn btn-primary" onclick="window.location.href='<?= base_url('payments') ?>'">
                <i class="fas fa-plus me-2"></i>
                Record First Payment
            </button>
        </div>
    <?php endif; ?>
</div>

<style>
.payment-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.payment-item.hidden {
    display: none !important;
}

.btn-group .btn {
    border-radius: 0.375rem !important;
    margin-right: 0.25rem;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

@media (max-width: 768px) {
    .payment-details h5 {
        font-size: 1rem;
    }
    
    .payment-amount-status {
        text-align: left !important;
        margin-right: 1rem !important;
    }
    
    .btn-group {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .btn-group .btn {
        margin-right: 0;
        font-size: 0.8rem;
        padding: 0.375rem 0.5rem;
    }
}
</style>