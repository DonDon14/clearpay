<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>" />

<?php $peso = '&#8369;'; ?>

<div class="container-fluid">
    <div class="ui-page-intro">
        <div>
            <h6>Operations Overview</h6>
            <p>Track collections, resolve pending requests, and review the latest activity without digging through separate screens.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="ui-stat-pill">
                <i class="fas fa-wallet"></i>
                Collections <?= $peso . number_format((float) ($totalCollections ?? 0), 2) ?>
            </span>
            <?php if (($pendingPaymentRequests ?? 0) > 0): ?>
                <span class="ui-stat-pill is-warning">
                    <i class="fas fa-clock"></i>
                    <?= (int) $pendingPaymentRequests ?> requests need review
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-database',
                'iconColor' => 'text-primary',
                'title' => 'Total Collections',
                'text' => $peso . number_format((float) ($totalCollections ?? 0), 2),
                'subtitle' => 'All recorded collections',
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-check-square',
                'iconColor' => 'text-success',
                'title' => 'Completed Payments',
                'text' => number_format((int) ($verifiedPayments ?? 0)),
                'subtitle' => 'Fully paid records',
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-clock',
                'iconColor' => 'text-warning',
                'title' => 'Partial Payments',
                'text' => number_format((int) ($partialPayments ?? 0)),
                'subtitle' => 'Still has remaining balances',
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-calendar-alt',
                'iconColor' => 'text-info',
                'title' => "Today's Payments",
                'text' => number_format((int) ($todayPayments ?? 0)),
                'subtitle' => 'Payments posted today',
            ]) ?>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card border-0 shadow-sm h-100 ui-metric-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <p class="ui-metric-label">Payment Requests</p>
                            <div class="ui-metric-value"><?= number_format((int) ($pendingPaymentRequests ?? 0)) ?></div>
                            <p class="ui-metric-subtitle mb-0">
                                <?= ($pendingPaymentRequests ?? 0) > 0 ? 'Pending admin review' : 'No pending requests right now' ?>
                            </p>
                        </div>
                        <span class="ui-stat-pill <?= ($pendingPaymentRequests ?? 0) > 0 ? 'is-warning' : '' ?>">
                            <i class="fas fa-clock"></i>
                            <?= ($pendingPaymentRequests ?? 0) > 0 ? 'Action required' : 'Up to date' ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-4">
        <div class="col-xl-4 col-lg-5">
            <div class="card h-100 border-0 shadow-sm ui-surface-card">
                <div class="card-header ui-surface-card-header">
                    <h5 class="card-title mb-1">Quick Actions</h5>
                    <small class="text-muted ui-surface-subtitle">Most-used tasks for daily payment operations</small>
                </div>
                <div class="card-body ui-surface-card-body">
                    <div class="ui-dashboard-grid">
                        <?= view('partials/quick-action-add-payment', [
                            'title' => 'Record Payment',
                            'subtitle' => 'Add new payment record',
                            'icon' => 'fas fa-plus-circle',
                            'bgColor' => 'bg-primary',
                            'colClass' => '',
                            'contributions' => $contributions ?? []
                        ]) ?>
                        <?php unset($modalTarget, $link); ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-qrcode',
                            'title' => 'Verify Payments',
                            'subtitle' => 'Scan QR codes to verify',
                            'bgColor' => 'bg-success',
                            'link' => null,
                            'modalTarget' => 'qrScannerModal',
                            'colClass' => ''
                        ]) ?>
                        <?php unset($modalTarget, $link); ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-hand-holding-usd',
                            'title' => 'Manage Contributions',
                            'subtitle' => 'Add or edit fee types',
                            'bgColor' => 'bg-info',
                            'link' => base_url('/contributions'),
                            'modalTarget' => null,
                            'colClass' => ''
                        ]) ?>
                        <?php unset($modalTarget, $link); ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-chart-bar',
                            'title' => 'View Analytics',
                            'subtitle' => 'System performance reports',
                            'bgColor' => 'bg-secondary',
                            'link' => base_url('/analytics'),
                            'modalTarget' => null,
                            'colClass' => ''
                        ]) ?>
                        <?php unset($modalTarget, $link); ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-bullhorn',
                            'title' => 'Add Announcement',
                            'subtitle' => 'Create system updates',
                            'bgColor' => 'bg-danger',
                            'link' => base_url('announcements') . '?open_modal=true',
                            'modalTarget' => null,
                            'colClass' => ''
                        ]) ?>
                        <?php unset($modalTarget, $link); ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-user-plus',
                            'title' => 'Add New Payer',
                            'subtitle' => 'Register a new payer',
                            'bgColor' => 'bg-warning',
                            'link' => null,
                            'modalTarget' => 'addPayerModal',
                            'colClass' => ''
                        ]) ?>
                        <?php unset($modalTarget, $link); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-7 col-md-6">
            <div class="card h-100 border-0 shadow-sm ui-surface-card">
                <div class="card-header ui-surface-card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">Recent Payments</h5>
                        <small class="text-muted ui-surface-subtitle">Latest posted payments with direct receipt access</small>
                    </div>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#allPaymentsModal">
                        View All
                    </button>
                </div>
                <div class="card-body ui-surface-card-body pt-0" id="recent-payments-body">
                    <?php if (!empty($recentPayments)): ?>
                        <?php foreach ($recentPayments as $payment): ?>
                            <div class="d-flex align-items-center gap-3 p-3 mb-3 ui-list-card recent-payment-item">
                                <div>
                                    <?php if (!empty($payment['profile_picture'])): ?>
                                        <?php
                                        $paymentPicUrl = (strpos($payment['profile_picture'], 'res.cloudinary.com') !== false)
                                            ? $payment['profile_picture']
                                            : base_url($payment['profile_picture']);
                                        ?>
                                        <img src="<?= $paymentPicUrl ?>"
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
                                    <div class="ui-list-meta"><?= esc($payment['contribution_title']) ?></div>
                                    <div class="ui-list-meta"><?= date('M d, Y h:i A', strtotime($payment['payment_date'])) ?></div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold"><?= $peso ?><?= number_format($payment['amount_paid'], 2) ?></div>
                                    <?php
                                    $status = $payment['computed_status'] ?? $payment['payment_status'] ?? 'unpaid';
                                    $badgeClass = match($status) {
                                        'fully paid' => 'bg-primary',
                                        'partial' => 'bg-warning text-dark',
                                        'unpaid' => 'bg-secondary',
                                        default => 'bg-danger'
                                    };
                                    $statusText = match($status) {
                                        'fully paid' => 'Completed',
                                        'partial' => 'Partial',
                                        'unpaid' => 'Unpaid',
                                        default => ucwords(str_replace('_', ' ', $status))
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?> small"><?= esc($statusText) ?></span>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="showPaymentReceipt(<?= htmlspecialchars(json_encode($payment), ENT_QUOTES, 'UTF-8') ?>)">
                                            View Receipt
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-3 text-center text-muted">No recent payments found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-12 col-md-12">
            <div class="card h-100 border-0 shadow-sm ui-surface-card">
                <div class="card-header ui-surface-card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">User Activities</h5>
                        <small class="text-muted ui-surface-subtitle">Recent system events with faster navigation to the related module</small>
                    </div>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#allUserActivitiesModal">
                        View All
                    </button>
                </div>
                <div class="card-body ui-surface-card-body pt-0">
                    <?php if (!empty($userActivities)): ?>
                        <?php foreach ($userActivities as $activity): ?>
                            <?php
                            $clickUrl = null;
                            $entityType = strtolower($activity['entity_type'] ?? '');
                            $activityType = strtolower($activity['activity_type'] ?? '');

                            if ($entityType === 'payment_request') {
                                $clickUrl = base_url('payment-requests');
                            } elseif ($entityType === 'refund') {
                                $clickUrl = in_array($activityType, ['rejected', 'completed', 'processed'], true)
                                    ? base_url('refunds') . '#history'
                                    : base_url('refunds');
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

                            $activityIcon = match($activity['activity_type'] ?? '') {
                                'create' => 'fa-plus-circle',
                                'update' => 'fa-edit',
                                'delete' => 'fa-trash',
                                'login' => 'fa-sign-in-alt',
                                'logout' => 'fa-sign-out-alt',
                                'approved' => 'fa-check-circle',
                                'rejected' => 'fa-times-circle',
                                'processed', 'completed' => 'fa-check-double',
                                'refund_processed' => 'fa-undo',
                                'refund_approved' => 'fa-check-circle',
                                'refund_rejected' => 'fa-times-circle',
                                'refund_completed' => 'fa-check-double',
                                default => 'fa-circle'
                            };
                            $activityColor = match($activity['activity_type'] ?? '') {
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
                            <div class="d-flex align-items-start p-3 mb-3 ui-list-card">
                                <div class="me-3 position-relative">
                                    <?php if (!empty($activity['profile_picture'])): ?>
                                        <?php
                                        $activityPicUrl = (strpos($activity['profile_picture'], 'res.cloudinary.com') !== false ||
                                            strpos($activity['profile_picture'], 'http://') === 0 ||
                                            strpos($activity['profile_picture'], 'https://') === 0)
                                            ? $activity['profile_picture']
                                            : base_url($activity['profile_picture']);
                                        ?>
                                        <img src="<?= $activityPicUrl ?>"
                                             alt="Profile Picture"
                                             class="rounded-circle"
                                             style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #e9ecef;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <?php endif; ?>
                                    <div class="<?= $activityColor ?> text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px; <?= !empty($activity['profile_picture']) ? 'display: none;' : '' ?>">
                                        <i class="fas <?= $activityIcon ?>"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold"><?= esc($activity['user_name']) ?? esc($activity['username']) ?></h6>
                                    <div class="ui-list-meta"><?= esc($activity['description'] ?? 'No description') ?></div>
                                    <?php if (!empty($activity['entity_type'])): ?>
                                        <div class="ui-list-meta mt-1">
                                            <span class="badge bg-secondary"><?= esc(strtoupper($activity['entity_type'])) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="ui-list-meta mt-1">
                                        <?= date('M d, Y h:i A', strtotime($activity['created_at'])) ?>
                                    </div>
                                </div>
                                <?php if ($clickUrl !== null): ?>
                                    <div class="ms-2">
                                        <a class="btn btn-sm btn-outline-secondary" href="<?= esc($clickUrl) ?>">Open</a>
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

<?= view('partials/modal-qr-receipt', [
    'title' => 'Payment Receipt',
]) ?>

<?= view('partials/modal-qr-scanner') ?>
<?= view('partials/modal-add-payer') ?>
<?= view('partials/modal-contribution-payments') ?>
<?= view('partials/modal-edit-payment') ?>
<?= payment_methods_modal($paymentMethods ?? []) ?>

<script>
window.APP_BASE_URL = '<?= base_url() ?>';

function showPaymentReceipt(paymentData) {
    if (typeof showQRReceipt === 'function') {
        showQRReceipt(paymentData);
    } else {
        showNotification('QR Receipt modal not available', 'error');
    }
}
</script>

<?= $this->endSection() ?>
