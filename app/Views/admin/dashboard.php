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
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-2 position-relative">
                        <i class="fas fa-clock fa-2x"></i>
                        <?php if (($pendingPaymentRequests ?? 0) > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                                <?= $pendingPaymentRequests ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <h5 class="card-title">Payment Requests</h5>
                    <?php if (($pendingPaymentRequests ?? 0) > 0): ?>
                        <p class="card-text text-muted"><?= $pendingPaymentRequests ?> pending</p>
                        <small class="text-warning fw-bold">Action Required</small>
                    <?php else: ?>
                        <p class="card-text text-muted">No pending requests</p>
                    <?php endif; ?>
                </div>
            </div>
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
                        unset($modalTarget, $link); // Clear variables
                        ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-qrcode',
                            'title' => 'Verify Payments',
                            'subtitle' => 'Scan QR codes to verify',
                            'bgColor' => 'bg-success',
                            'link' => null,
                            'modalTarget' => 'qrScannerModal',
                            'colClass' => 'col-6'
                        ]) ?>
                        <?php 
                        unset($modalTarget, $link); // Clear variables
                        ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-hand-holding-usd',
                            'title' => 'Manage Contributions',
                            'subtitle' => 'Add or edit fee types',
                            'bgColor' => 'bg-info',
                            'link' => base_url('/contributions'),
                            'modalTarget' => null,
                            'colClass' => 'col-6'
                        ]) ?>
                        <?php 
                        unset($modalTarget, $link); // Clear variables
                        ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-chart-bar',
                            'title' => 'View Analytics',
                            'subtitle' => 'System performance reports',
                            'bgColor' => 'bg-secondary',
                            'link' => base_url('/analytics'),
                            'modalTarget' => null,
                            'colClass' => 'col-6'
                        ]) ?>
                        <?php 
                        unset($modalTarget, $link); // Clear variables
                        ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-bullhorn',
                            'title' => 'Add Announcements',
                            'subtitle' => 'Create system announcements',
                            'bgColor' => 'bg-danger',
                            'link' => base_url('announcements') . '?open_modal=true',
                            'modalTarget' => null,
                            'colClass' => 'col-6'
                        ]) ?>
                        <script>
                        console.log('DEBUG: Announcements URL:', <?= json_encode(base_url('announcements') . '?open_modal=true') ?>);
                        </script>
                        <?php 
                        unset($modalTarget, $link); // Clear variables
                        ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-user-plus',
                            'title' => 'Add New Payer',
                            'subtitle' => 'Register a new payer',
                            'bgColor' => 'bg-warning',
                            'link' => null,
                            'modalTarget' => 'addPayerModal',
                            'colClass' => 'col-6'
                        ]) ?>
                        <?php 
                        unset($modalTarget, $link); // Clear variables after last call
                        ?>
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
                                    <?php if (!empty($payment['profile_picture'])): ?>
                                        <img src="<?= base_url($payment['profile_picture']) ?>" 
                                             alt="Profile Picture" 
                                             class="rounded-circle" 
                                             style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #e9ecef;">
                                    <?php else: ?>
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
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
                                    <?php 
                                        $status = $payment['computed_status'] ?? $payment['payment_status'] ?? 'unpaid';
                                        $badgeClass = match($status) {
                                            'fully paid' => 'bg-primary',
                                            'partial' => 'bg-warning text-dark',
                                            'unpaid' => 'bg-secondary',
                                            default => 'bg-danger'
                                        };
                                        $statusText = match($status) {
                                            'fully paid' => 'COMPLETED',
                                            'partial' => 'PARTIAL',
                                            'unpaid' => 'UNPAID',
                                            default => strtoupper($status)
                                        };
                                    ?>
                                    <span class="badge <?= $badgeClass ?> small">
                                        <?= $statusText ?>
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

        <!-- User Activities -->
        <div class="col-lg-4 col-md-12">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">User Activities</h5>
                        <small class="text-muted">Recent user actions</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#allUserActivitiesModal">
                            View All
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($userActivities)): ?>
                        <?php foreach ($userActivities as $activity): ?>
                            <?php
                                // Determine click URL based on entity_type and activity_type
                                $clickUrl = null;
                                $entityType = strtolower($activity['entity_type'] ?? '');
                                $activityType = strtolower($activity['activity_type'] ?? ''); // This is the action: create, update, delete, etc.
                                
                                // For user_activities table, entity_type determines the redirect
                                if ($entityType === 'payment_request') {
                                    $clickUrl = base_url('payment-requests');
                                } elseif ($entityType === 'refund') {
                                    // For refunds, redirect to history if rejected/processed
                                    if (in_array($activityType, ['rejected', 'completed', 'processed'])) {
                                        $clickUrl = base_url('refunds') . '#history';
                                    } else {
                                        $clickUrl = base_url('refunds');
                                    }
                                } elseif ($entityType === 'payment') {
                                    $clickUrl = base_url('payments');
                                } elseif ($entityType === 'contribution') {
                                    $clickUrl = base_url('contributions');
                                } elseif ($entityType === 'announcement') {
                                    $clickUrl = base_url('announcements');
                                } elseif ($entityType === 'payer') {
                                    $clickUrl = base_url('payers');
                                } elseif ($entityType === 'user') {
                                    $clickUrl = base_url('settings/users');
                                }
                                
                                $isClickable = $clickUrl !== null;
                                $clickStyle = $isClickable ? 'cursor: pointer; transition: background-color 0.2s;' : '';
                                $clickClass = $isClickable ? 'user-activity-clickable' : '';
                            ?>
                            <div class="d-flex align-items-start p-3 border-bottom <?= $clickClass ?>" 
                                 onclick="<?= $isClickable ? "window.location.href='{$clickUrl}'" : '' ?>"
                                 style="<?= $clickStyle ?>"
                                 onmouseover="<?= $isClickable ? "this.style.backgroundColor='#f8f9fa'" : '' ?>"
                                 onmouseout="<?= $isClickable ? "this.style.backgroundColor=''" : '' ?>">
                                <div class="me-3">
                                    <?php if (!empty($activity['profile_picture'])): ?>
                                        <img src="<?= base_url($activity['profile_picture']) ?>" 
                                             alt="Profile Picture" 
                                             class="rounded-circle" 
                                             style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #e9ecef;">
                                    <?php else: ?>
                                        <?php 
                                            $activityIcon = match($activity['activity_type']) {
                                                'create' => 'fa-plus-circle',
                                                'update' => 'fa-edit',
                                                'delete' => 'fa-trash',
                                                'login' => 'fa-sign-in-alt',
                                                'logout' => 'fa-sign-out-alt',
                                                'approved' => 'fa-check-circle',
                                                'rejected' => 'fa-times-circle',
                                                'processed' => 'fa-check-double',
                                                'completed' => 'fa-check-double',
                                                'refund_processed' => 'fa-undo',
                                                'refund_approved' => 'fa-check-circle',
                                                'refund_rejected' => 'fa-times-circle',
                                                'refund_completed' => 'fa-check-double',
                                                default => 'fa-circle'
                                            };
                                            $activityColor = match($activity['activity_type']) {
                                                'create' => 'bg-success',
                                                'update' => 'bg-info',
                                                'delete' => 'bg-danger',
                                                'login' => 'bg-primary',
                                                'logout' => 'bg-secondary',
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                'processed', 'completed' => 'bg-primary',
                                                'refund_processed', 'refund_approved', 'refund_completed' => 'bg-warning',
                                                'refund_rejected' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                        <div class="<?= $activityColor ?> text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas <?= $activityIcon ?>"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold"><?= esc($activity['user_name']) ?? esc($activity['username']) ?></h6>
                                    <small class="text-muted"><?= esc($activity['description'] ?? 'No description') ?></small>
                                    <?php if (!empty($activity['entity_type'])): ?>
                                        <div class="text-muted small">
                                            <span class="badge bg-secondary"><?= esc(strtoupper($activity['entity_type'])) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="text-muted small mt-1">
                                        <?= date('M d, Y h:i A', strtotime($activity['created_at'])) ?>
                                    </div>
                                </div>
                                <?php if ($isClickable): ?>
                                    <div class="ms-2">
                                        <i class="fas fa-chevron-right text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-3 text-center text-muted">No user activities found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?= view('partials/all-payments-modal', [
    'allPayments' => $allPayments ?? [],
]) ?>

<?= view('partials/all-user-activities-modal') ?>

<!-- QR Receipt Modal -->
<?= view('partials/modal-qr-receipt', [
    'title' => 'Payment Receipt',
]) ?>

<!-- QR Scanner Modal -->
<?= view('partials/modal-qr-scanner') ?>

<!-- Add Payer Modal -->
<?= view('partials/modal-add-payer') ?>

<!-- Contribution Payments Modal (for payment history) -->
<?= view('partials/modal-contribution-payments') ?>

<!-- Edit Payment Modal -->
<?= view('partials/modal-edit-payment') ?>

<!-- Payment Methods Modal -->
<?= payment_methods_modal($paymentMethods ?? []) ?>

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
