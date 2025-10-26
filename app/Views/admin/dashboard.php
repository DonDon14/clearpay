<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>" />

<!-- Dashboard Content -->
<div class="container-fluid mb-4">
    <div class="row g-3">
        <!-- Stats Cards -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-database',
                'iconColor' => 'text-primary',
                'title' => 'Total Collections',
                'text' => '₱' . ($totalCollections ?? '0.00')
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-check-square',
                'iconColor' => 'text-success',
                'title' => 'Completed Payments',
                'text' => ($verifiedPayments ?? '0')
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-clock',
                'iconColor' => 'text-warning',
                'title' => 'Partial Payments',
                'text' => ($partialPayments ?? '0')
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-calendar-alt',
                'iconColor' => 'text-info',
                'title' => "Today's Payments",
                'text' => ($todayPayments ?? '0')
            ]) ?>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row mb-4">

        <!-- Quick Actions -->
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                    <small class="text-muted">Frequently used operations</small>
                </div>
                <div class="card-body p-2">
                    <div class="row g-2">
                        <?= view('partials/quick-action-add-payment', [
                            'title' => 'Record Payment',
                            'subtitle' => 'Add new payment record',
                            'icon' => 'fas fa-plus-circle',
                            'bgColor' => 'bg-primary',
                            'colClass' => 'col-6',
                            'contributions' => $contributions ?? []
                        ]) ?>
                        <?php 
                        // Explicitly set variables to prevent leakage from previous view() call
                        $tempModalTarget = 'qrScannerModal';
                        $tempLink = null;
                        ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-qrcode',
                            'title' => 'Verify Payments',
                            'subtitle' => 'Scan QR codes to verify',
                            'bgColor' => 'bg-success',
                            'link' => $tempLink,
                            'modalTarget' => $tempModalTarget,
                            'colClass' => 'col-6'
                        ]) ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-hand-holding-usd',
                            'title' => 'Manage Contributions',
                            'subtitle' => 'Add or edit fee types',
                            'bgColor' => 'bg-info',
                            'link' => base_url('/contributions'),
                            'colClass' => 'col-6'
                        ]) ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-chart-bar',
                            'title' => 'View Analytics',
                            'subtitle' => 'System performance reports',
                            'bgColor' => 'bg-secondary',
                            'link' => base_url('/analytics'),
                            'colClass' => 'col-6'
                        ]) ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-bullhorn',
                            'title' => 'Add Announcements',
                            'subtitle' => 'Create system announcements',
                            'bgColor' => 'bg-danger',
                            'link' => base_url('/announcements'),
                            'colClass' => 'col-6'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Recent Payments</h5>
                        <small class="text-muted">Last 30 days</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#allPaymentsModal">
                            View All
                        </button>
                    </div>
                </div>
                <div class="card-body p-0" id="recent-payments-body">
                    <?php if (!empty($recentPayments)): ?>
                        <?php foreach ($recentPayments as $payment): ?>
                            <div class="d-flex align-items-center p-3 border-bottom recent-payment-item" 
                                 style="cursor: pointer; transition: background-color 0.2s;" 
                                 onmouseover="this.style.backgroundColor='#f8f9fa'" 
                                 onmouseout="this.style.backgroundColor=''" 
                                 onclick="showPaymentReceipt(<?= htmlspecialchars(json_encode($payment), ENT_QUOTES, 'UTF-8') ?>)">
                                <div class="me-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold"><?= esc($payment['payer_name']) ?></h6>
                                    <small class="text-muted"><?= esc($payment['contribution_title']) ?></small>
                                    <div class="text-muted small">
                                        <?= date('M d, Y h:i A', strtotime($payment['payment_date'])) ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold">₱<?= number_format($payment['amount_paid'], 2) ?></div>
                                    <span class="badge <?= $payment['payment_status'] === 'fully paid' ? 'bg-success' : ($payment['payment_status'] === 'partial' ? 'bg-warning' : 'bg-danger') ?> small">
                                        <?= strtoupper($payment['payment_status']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-3 text-center text-muted">No recent payments found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="col-lg-4 col-md-12">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">System Status</h5>
                        <small class="text-muted">Last checked: 2 minutes ago</small>
                    </div>
                    <div class="badge bg-success">
                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                        ONLINE
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3 border-bottom pb-3">
                        <div class="me-3">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-database"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Database</h6>
                            <small class="text-muted">Connected and operational</small>
                        </div>
                        <span class="badge bg-success">HEALTHY</span>
                    </div>
                    <div class="d-flex align-items-center mb-3 border-bottom pb-3">
                        <div class="me-3">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-qrcode"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">QR Generation</h6>
                            <small class="text-muted">QR extension active</small>
                        </div>
                        <span class="badge bg-success">ACTIVE</span>
                    </div>
                    <div class="d-flex align-items-center mb-3 border-bottom pb-3">
                        <div class="me-3">
                            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Backup System</h6>
                            <small class="text-muted">Last backup: 2 hours ago</small>
                        </div>
                        <span class="badge bg-warning">SCHEDULED</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?= view('partials/all-payments-modal', [
    'allPayments' => $allPayments ?? [],
]) ?>

<!-- QR Receipt Modal -->
<?= view('partials/modal-qr-receipt', [
    'title' => 'Payment Receipt',
]) ?>

<!-- QR Scanner Modal -->
<?= view('partials/modal-qr-scanner') ?>

<!-- Contribution Payments Modal (for payment history) -->
<?= view('partials/modal-contribution-payments') ?>

<script>
// Define base URL for payment.js
window.APP_BASE_URL = '<?= base_url() ?>';

// Function to show payment receipt from recent payments
function showPaymentReceipt(paymentData) {
    if (typeof showQRReceipt === 'function') {
        showQRReceipt(paymentData);
    } else {
        showNotification('QR Receipt modal not available', 'error');
    }
}
</script>

<?= $this->endSection() ?>
