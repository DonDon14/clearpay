<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>" />

<?php
$peso = '&#8369;';

$severityClass = static function (?string $label): string {
    return match (strtolower((string) $label)) {
        'high' => 'bg-danger-subtle text-danger',
        'medium' => 'bg-warning-subtle text-warning-emphasis',
        default => 'bg-secondary-subtle text-secondary-emphasis',
    };
};
?>

<div class="container-fluid">
    <div class="ui-page-intro">
        <div>
            <h6>Admin Review Center</h6>
            <p>One place for approval queues, analytics alerts, payer account problems, and system configuration issues.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary" href="<?= base_url('payment-requests') ?>">Open Payment Requests</a>
            <a class="btn btn-outline-primary" href="<?= base_url('refunds') ?>">Open Refunds</a>
            <a class="btn btn-primary" href="<?= base_url('analytics') ?>">Open Analytics</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-lg-4 col-md-6">
            <?= view('partials/card', [
                'title' => 'Payment Requests',
                'text' => number_format((int) ($summary['pending_payment_requests'] ?? 0)),
                'subtitle' => 'Pending approvals',
                'icon' => 'paper-plane',
                'iconColor' => 'text-primary',
            ]) ?>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <?= view('partials/card', [
                'title' => 'Refund Requests',
                'text' => number_format((int) ($summary['pending_refunds'] ?? 0)),
                'subtitle' => 'Pending refund actions',
                'icon' => 'undo',
                'iconColor' => 'text-warning',
            ]) ?>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <?= view('partials/card', [
                'title' => 'Duplicate Alerts',
                'text' => number_format((int) ($summary['duplicate_alerts'] ?? 0)),
                'subtitle' => 'Flagged by Python',
                'icon' => 'copy',
                'iconColor' => 'text-danger',
            ]) ?>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <?= view('partials/card', [
                'title' => 'Suspicious Alerts',
                'text' => number_format((int) ($summary['suspicious_alerts'] ?? 0)),
                'subtitle' => 'Needs review',
                'icon' => 'shield-alt',
                'iconColor' => 'text-warning',
            ]) ?>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <?= view('partials/card', [
                'title' => 'Payer Issues',
                'text' => number_format((int) ($summary['payer_account_issues'] ?? 0)),
                'subtitle' => 'Missing login passwords',
                'icon' => 'users-slash',
                'iconColor' => 'text-secondary',
            ]) ?>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <?= view('partials/card', [
                'title' => 'Email Issues',
                'text' => number_format((int) ($summary['email_issues'] ?? 0)),
                'subtitle' => 'Configuration warnings',
                'icon' => 'envelope-circle-check',
                'iconColor' => 'text-info',
            ]) ?>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm ui-surface-card">
                <div class="card-header ui-surface-card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">Pending Payment Requests</h5>
                        <small class="text-muted ui-surface-subtitle">Latest payer-submitted payment requests waiting for admin action</small>
                    </div>
                    <a class="btn btn-sm btn-outline-primary" href="<?= base_url('payment-requests') ?>">View Queue</a>
                </div>
                <div class="card-body ui-surface-card-body">
                    <?php if (!empty($paymentRequests)): ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Payer</th>
                                        <th>Contribution</th>
                                        <th>Amount</th>
                                        <th>Priority</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($paymentRequests as $request): ?>
                                        <tr id="payment-request-row-<?= (int) $request['id'] ?>">
                                            <td>
                                                <strong><?= esc($request['payer_name'] ?? 'Unknown Payer') ?></strong>
                                                <div class="ui-list-meta"><?= esc($request['reference_number'] ?? '-') ?></div>
                                                <div class="ui-list-meta"><?= !empty($request['requested_at']) ? esc(date('M d, Y h:i A', strtotime($request['requested_at']))) : '-' ?></div>
                                            </td>
                                            <td><?= esc($request['contribution_title'] ?? 'Unknown Contribution') ?></td>
                                            <td><?= $peso ?><?= number_format((float) ($request['requested_amount'] ?? 0), 2) ?></td>
                                            <td>
                                                <span class="badge rounded-pill <?= $severityClass($request['severity_label'] ?? 'Normal') ?>">
                                                    <?= esc($request['severity_label'] ?? 'Normal') ?>
                                                </span>
                                                <div class="ui-list-meta mt-1">Score <?= (int) ($request['priority_score'] ?? 0) ?></div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <button class="btn btn-sm btn-success" onclick="reviewCenterApprovePaymentRequest(<?= (int) $request['id'] ?>)">
                                                        Approve
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="reviewCenterRejectPaymentRequest(<?= (int) $request['id'] ?>)">
                                                        Reject
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">No pending payment requests.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card border-0 shadow-sm ui-surface-card">
                <div class="card-header ui-surface-card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">Pending Refund Requests</h5>
                        <small class="text-muted ui-surface-subtitle">Refund requests submitted by payers that still need admin review</small>
                    </div>
                    <a class="btn btn-sm btn-outline-primary" href="<?= base_url('refunds') ?>">View Queue</a>
                </div>
                <div class="card-body ui-surface-card-body">
                    <?php if (!empty($refundRequests)): ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Payer</th>
                                        <th>Receipt</th>
                                        <th>Amount</th>
                                        <th>Priority</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($refundRequests as $request): ?>
                                        <tr id="refund-request-row-<?= (int) $request['id'] ?>">
                                            <td>
                                                <strong><?= esc($request['payer_name'] ?? 'Unknown Payer') ?></strong>
                                                <div class="ui-list-meta"><?= esc($request['contribution_title'] ?? '-') ?></div>
                                                <div class="ui-list-meta"><?= !empty($request['requested_at']) ? esc(date('M d, Y h:i A', strtotime($request['requested_at']))) : '-' ?></div>
                                            </td>
                                            <td><?= esc($request['receipt_number'] ?? '-') ?></td>
                                            <td><?= $peso ?><?= number_format((float) ($request['refund_amount'] ?? 0), 2) ?></td>
                                            <td>
                                                <span class="badge rounded-pill <?= $severityClass($request['severity_label'] ?? 'Normal') ?>">
                                                    <?= esc($request['severity_label'] ?? 'Normal') ?>
                                                </span>
                                                <div class="ui-list-meta mt-1">Score <?= (int) ($request['priority_score'] ?? 0) ?></div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <button class="btn btn-sm btn-success" onclick="reviewCenterApproveRefundRequest(<?= (int) $request['id'] ?>)">
                                                        Approve
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="reviewCenterRejectRefundRequest(<?= (int) $request['id'] ?>)">
                                                        Reject
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">No pending refund requests.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <?= view('partials/container-card', [
                'title' => 'Python Analytics Alerts',
                'subtitle' => 'Duplicate and suspicious records surfaced by the analytics worker',
                'bodyClass' => '',
                'headerAction' => '<a class="btn btn-sm btn-outline-primary" href="' . base_url('analytics') . '">Open Analytics</a>',
                'content' => '
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Duplicate Alerts</h6>
                                    <span class="badge bg-danger">' . number_format(count($analyticsAlerts['duplicates'] ?? [])) . '</span>
                                </div>
                                ' .
                                (!empty($analyticsAlerts['duplicates'])
                                    ? implode('', array_map(static function ($item) use ($peso) {
                                        return '<div class="mb-3">
                                            <div class="fw-semibold">' . esc($item['payer_name'] ?? 'Unknown Payer') . '</div>
                                            <div class="ui-list-meta">' . esc($item['receipt_number'] ?? '-') . ' · ' . esc($item['contribution_title'] ?? '-') . '</div>
                                            <div class="ui-list-meta">' . $peso . number_format((float) ($item['amount_paid'] ?? 0), 2) . '</div>
                                        </div>';
                                    }, $analyticsAlerts['duplicates']))
                                    : '<p class="text-muted mb-0">No duplicate alerts.</p>') . '
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Suspicious Alerts</h6>
                                    <span class="badge bg-warning text-dark">' . number_format(count($analyticsAlerts['suspicious'] ?? [])) . '</span>
                                </div>
                                ' .
                                (!empty($analyticsAlerts['suspicious'])
                                    ? implode('', array_map(static function ($item) use ($peso) {
                                        return '<div class="mb-3">
                                            <div class="fw-semibold">' . esc($item['payer_name'] ?? 'Unknown Payer') . '</div>
                                            <div class="ui-list-meta">' . esc($item['reason'] ?? 'Flagged by Python analytics') . '</div>
                                            <div class="ui-list-meta">' . $peso . number_format((float) ($item['amount_paid'] ?? 0), 2) . '</div>
                                        </div>';
                                    }, $analyticsAlerts['suspicious']))
                                    : '<p class="text-muted mb-0">No suspicious alerts.</p>') . '
                            </div>
                        </div>
                    </div>
                    ' . (!empty($analyticsAlerts['error']) ? '<div class="alert alert-warning mt-3 mb-0">' . esc($analyticsAlerts['error']) . '</div>' : '')
            ]) ?>
        </div>

        <div class="col-xl-6">
            <div class="card border-0 shadow-sm ui-surface-card">
                <div class="card-header ui-surface-card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">Payer Account Issues</h5>
                        <small class="text-muted ui-surface-subtitle">Existing payers that cannot log in because they have no stored password</small>
                    </div>
                    <a class="btn btn-sm btn-outline-primary" href="<?= base_url('payers') ?>">Open Payers</a>
                </div>
                <div class="card-body ui-surface-card-body">
                    <?php if (!empty($payerIssues)): ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Payer</th>
                                        <th>Email</th>
                                        <th>Contact</th>
                                        <th>Severity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payerIssues as $payer): ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($payer['payer_name'] ?? 'Unknown Payer') ?></strong>
                                                <div class="ui-list-meta"><?= esc($payer['payer_id'] ?? '-') ?></div>
                                            </td>
                                            <td><?= esc($payer['email_address'] ?? '-') ?></td>
                                            <td><?= esc($payer['contact_number'] ?? '-') ?></td>
                                            <td>
                                                <span class="badge rounded-pill <?= $severityClass($payer['severity_label'] ?? 'Medium') ?>">
                                                    <?= esc($payer['severity_label'] ?? 'Medium') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">No payer credential issues detected.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Email Health',
                'subtitle' => 'Checks whether the current email configuration is complete enough for admin workflows',
                'bodyClass' => '',
                'headerAction' => '<a class="btn btn-sm btn-outline-primary" href="' . base_url('settings') . '">Open Settings</a>',
                'content' => '
                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <div class="ui-metric-label">Status</div>
                                <div class="ui-metric-value">' . ($emailHealth['status'] === 'healthy' ? 'Healthy' : 'Needs Review') . '</div>
                                <div class="ui-metric-subtitle">Issue count: ' . number_format((int) ($emailHealth['issue_count'] ?? 0)) . '</div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <div class="ui-metric-label">SMTP Host</div>
                                <div class="fw-semibold">' . esc($emailHealth['settings']['smtp_host'] ?? 'Not configured') . '</div>
                                <div class="ui-list-meta">Sender: ' . esc($emailHealth['settings']['from_email'] ?? 'Not configured') . '</div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <div class="ui-metric-label">Protocol</div>
                                <div class="fw-semibold">' . esc($emailHealth['settings']['protocol'] ?? 'Not configured') . '</div>
                                <div class="ui-list-meta">Port: ' . esc((string) ($emailHealth['settings']['smtp_port'] ?? 'Not configured')) . '</div>
                            </div>
                        </div>
                        <div class="col-12">
                            ' . (!empty($emailHealth['issues'])
                                ? '<ul class="mb-0">' . implode('', array_map(static fn ($issue) => '<li>' . esc($issue) . '</li>', $emailHealth['issues'])) . '</ul>'
                                : '<p class="mb-0 text-muted">No configuration issues detected in the active email settings.</p>') . '
                        </div>
                    </div>'
            ]) ?>
        </div>
    </div>
</div>

<script>
function removeReviewRow(rowId) {
    const row = document.getElementById(rowId);
    if (row) {
        row.remove();
    }
    if (typeof refreshPaymentRequestsBadge === 'function') {
        refreshPaymentRequestsBadge();
    }
    if (typeof refreshRefundRequestsBadge === 'function') {
        refreshRefundRequestsBadge();
    }
    if (typeof refreshReviewCenterBadge === 'function') {
        refreshReviewCenterBadge();
    }
}

function reviewCenterApprovePaymentRequest(requestId) {
    fetch(`${window.APP_BASE_URL}/admin/approve-payment-request`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({ request_id: requestId, admin_notes: 'Approved from Review Center' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Payment request approved', 'success');
            removeReviewRow(`payment-request-row-${requestId}`);
        } else {
            showNotification(data.message || 'Failed to approve payment request', 'error');
        }
    })
    .catch(() => showNotification('Failed to approve payment request', 'error'));
}

function reviewCenterRejectPaymentRequest(requestId) {
    fetch(`${window.APP_BASE_URL}/admin/reject-payment-request`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({ request_id: requestId, admin_notes: 'Rejected from Review Center' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Payment request rejected', 'success');
            removeReviewRow(`payment-request-row-${requestId}`);
        } else {
            showNotification(data.message || 'Failed to reject payment request', 'error');
        }
    })
    .catch(() => showNotification('Failed to reject payment request', 'error'));
}

function reviewCenterApproveRefundRequest(refundId) {
    fetch(`${window.APP_BASE_URL}/admin/refunds/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({ refund_id: refundId, admin_notes: 'Approved from Review Center' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Refund request approved', 'success');
            removeReviewRow(`refund-request-row-${refundId}`);
        } else {
            showNotification(data.message || 'Failed to approve refund request', 'error');
        }
    })
    .catch(() => showNotification('Failed to approve refund request', 'error'));
}

function reviewCenterRejectRefundRequest(refundId) {
    fetch(`${window.APP_BASE_URL}/admin/refunds/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({ refund_id: refundId, admin_notes: 'Rejected from Review Center' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Refund request rejected', 'success');
            removeReviewRow(`refund-request-row-${refundId}`);
        } else {
            showNotification(data.message || 'Failed to reject refund request', 'error');
        }
    })
    .catch(() => showNotification('Failed to reject refund request', 'error'));
}
</script>

<?= $this->endSection() ?>
