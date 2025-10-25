<!-- Partial Payments List -->
<div id="partialPaymentsList">
    <?php if (!empty($partialPayments)): ?>
        <div class="row g-3">
            <?php foreach ($partialPayments as $payment): ?>
                <?php 
                    $paidAmount = $payment['total_amount_due'] - $payment['remaining_balance'];
                    $progressPercentage = ($paidAmount / $payment['total_amount_due']) * 100;
                ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm partial-payment-card" 
                         onclick="openPaymentModal(<?= $payment['contribution_id'] ?>, '<?= esc($payment['student_id']) ?>', '<?= esc($payment['student_name']) ?>', '<?= esc($payment['contribution_title']) ?>', <?= $payment['total_amount_due'] ?>, <?= $payment['remaining_balance'] ?>)"
                         data-contribution="<?= $payment['contribution_id'] ?>" 
                         data-student="<?= esc($payment['student_id']) ?>"
                         style="transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;">
                        <div class="card-body">
                            <!-- Payment Card Header -->
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div class="student-info flex-grow-1">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="student-avatar" style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6, #0ea5e9); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1rem; flex-shrink: 0;">
                                            <?= strtoupper(substr($payment['student_name'], 0, 2)) ?>
                                        </div>
                                        <div>
                                            <h5 class="mb-1 fw-semibold"><?= esc($payment['student_name']) ?></h5>
                                            <p class="text-muted mb-1 small">ID: <?= esc($payment['student_id']) ?></p>
                                            <p class="text-muted mb-0" style="font-size: 0.8rem;">Last payment: <?= date('M j, Y', strtotime($payment['created_at'])) ?></p>
                                        </div>
                                    </div>
                                    <div class="contribution-info p-2 bg-light rounded border-start border-info border-3">
                                        <p class="mb-0 fw-medium text-info small"><?= esc($payment['contribution_title']) ?></p>
                                    </div>
                                </div>
                                
                                <div class="payment-status text-end">
                                    <span class="badge bg-warning text-dark mb-2">PARTIAL</span>
                                    <div class="small text-muted">
                                        <?= number_format($progressPercentage, 1) ?>% Complete
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment Progress -->
                            <div class="payment-progress mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="h5 text-success fw-bold mb-0">₱<?= number_format($paidAmount, 2) ?></span>
                                    <span class="text-muted small">of</span>
                                    <span class="h6 fw-semibold mb-0">₱<?= number_format($payment['total_amount_due'], 2) ?></span>
                                </div>
                                
                                <div class="progress mb-3" style="height: 8px;">
                                    <div class="progress-bar bg-gradient" style="background: linear-gradient(90deg, #22c55e, #0ea5e9); width: <?= $progressPercentage ?>%;" role="progressbar"></div>
                                </div>
                                
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-exclamation-circle text-warning"></i>
                                        <span class="text-muted small">Remaining:</span>
                                        <span class="fw-semibold text-warning">₱<?= number_format($payment['remaining_balance'], 2) ?></span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 text-primary small fw-medium">
                                        <i class="fas fa-plus-circle"></i>
                                        <span>Add Payment</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 pt-3 border-top">
                                <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="event.stopPropagation(); viewPaymentHistory(<?= $payment['contribution_id'] ?>, '<?= esc($payment['student_id']) ?>')">
                                    <i class="fas fa-history me-1"></i>
                                    History
                                </button>
                                <button class="btn btn-primary btn-sm flex-fill" onclick="event.stopPropagation(); openPaymentModal(<?= $payment['contribution_id'] ?>, '<?= esc($payment['student_id']) ?>', '<?= esc($payment['student_name']) ?>', '<?= esc($payment['contribution_title']) ?>', <?= $payment['total_amount_due'] ?>, <?= $payment['remaining_balance'] ?>)">
                                    <i class="fas fa-plus me-1"></i>
                                    Add Installment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-check-circle text-success" style="font-size: 4rem; opacity: 0.3;"></i>
            </div>
            <h4 class="text-muted">No Partial Payments</h4>
            <p class="text-muted">All students have either fully paid or haven't started payments yet.</p>
            <button class="btn btn-primary" onclick="window.location.href='<?= base_url('payments') ?>'">
                <i class="fas fa-plus me-2"></i>
                Record New Payment
            </button>
        </div>
    <?php endif; ?>
</div>

<style>
.partial-payment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.progress-bar.bg-gradient {
    transition: width 0.3s ease;
}

.partial-payment-card:hover .progress-bar.bg-gradient {
    animation: pulse-progress 2s ease-in-out infinite;
}

@keyframes pulse-progress {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
</style>