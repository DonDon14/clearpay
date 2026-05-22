<?= $this->extend('layouts/payer-layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid ui-page-shell payer-page-shell">
    <?= view('partials/payer-page-intro', [
        'title' => 'Contributions',
        'subtitle' => 'Track your running balance per contribution and submit payments quickly.',
        'actionsHtml' => '
            <div class="input-group payer-search">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="contributionSearch" placeholder="Search contributions...">
            </div>
        ',
    ]) ?>

    <?php if (empty($contributions)): ?>
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Contributions Available</h5>
                <p class="text-muted mb-0">Active section contributions will appear here.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4" id="contributionsGrid">
            <?php foreach ($contributions as $contribution): ?>
                <div class="col-xl-6 contribution-item" data-title="<?= strtolower(esc($contribution['title'])) ?>">
                    <div class="card border-0 shadow-sm h-100 payer-grid-card">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex gap-3 align-items-start flex-wrap">
                                <div class="contribution-visual">
                                    <?php if (!empty($contribution['image_path'])): ?>
                                        <?php
                                            $contributionImagePath = (string) $contribution['image_path'];
                                            $contributionImageUrl = preg_match('#^https?://#i', $contributionImagePath)
                                                ? $contributionImagePath
                                                : base_url($contributionImagePath);
                                        ?>
                                        <img
                                            src="<?= esc($contributionImageUrl) ?>"
                                            alt="<?= esc($contribution['title']) ?>"
                                            class="payer-item-image"
                                            onerror="this.onerror=null; this.style.display='none'; this.parentElement.insertAdjacentHTML('beforeend', '<div class=&quot;payer-item-image payer-item-image--placeholder&quot;><i class=&quot;fas fa-file-invoice-dollar&quot;></i></div>');"
                                            onclick="openPayerImagePreview('<?= esc($contributionImageUrl) ?>', '<?= esc($contribution['title']) ?>')">
                                    <?php else: ?>
                                        <div class="payer-item-image payer-item-image--placeholder">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between gap-3 flex-wrap">
                                        <div>
                                            <h5 class="mb-1"><?= esc($contribution['title']) ?></h5>
                                            <div class="d-flex gap-2 flex-wrap mb-2">
                                                <span class="badge bg-info">Contribution</span>
                                                <?php if (!empty($contribution['category'])): ?>
                                                    <span class="badge bg-light text-dark border"><?= esc(ucfirst($contribution['category'])) ?></span>
                                                <?php endif; ?>
                                                <span class="badge <?= ($contribution['status'] ?? 'active') === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= ucfirst($contribution['status'] ?? 'active') ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="payer-metric-label">Per Payer</div>
                                            <div class="payer-metric-value">PHP <?= number_format((float)($contribution['amount'] ?? 0), 2) ?></div>
                                        </div>
                                    </div>
                                    <p class="text-muted mb-3"><?= esc($contribution['description'] ?: 'No description provided.') ?></p>
                                </div>
                            </div>

                            <?php
                                $paid = (float)($contribution['total_paid'] ?? 0);
                                $amount = (float)($contribution['amount'] ?? 0);
                                $remaining = (float)($contribution['remaining_balance'] ?? max(0, $amount - $paid));
                                $progress = $amount > 0 ? min(100, round(($paid / $amount) * 100, 1)) : 0;
                            ?>

                            <div class="payer-stats-grid mt-3">
                                <div class="payer-stat-card">
                                    <span class="payer-metric-label">Paid</span>
                                    <strong>PHP <?= number_format($paid, 2) ?></strong>
                                </div>
                                <div class="payer-stat-card">
                                    <span class="payer-metric-label">Remaining</span>
                                    <strong>PHP <?= number_format($remaining, 2) ?></strong>
                                </div>
                                <div class="payer-stat-card">
                                    <span class="payer-metric-label">Progress</span>
                                    <strong><?= number_format($progress, 1) ?>%</strong>
                                </div>
                            </div>

                            <div class="progress mt-3 payer-progress" role="progressbar" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar <?= $progress >= 100 ? 'bg-success' : 'bg-primary' ?>" style="width: <?= $progress ?>%"></div>
                            </div>

                            <div class="mt-4 d-flex gap-2 flex-wrap">
                                <?php if (($contribution['status'] ?? 'active') === 'active' && $remaining > 0): ?>
                                    <button
                                        class="btn btn-primary"
                                        onclick='openPaymentRequestModal(<?= json_encode([
                                            'id' => (int)$contribution['id'],
                                            'title' => $contribution['title'],
                                            'description' => $contribution['description'],
                                            'amount' => (float)$contribution['amount'],
                                            'remaining_balance' => $remaining,
                                            'item_type' => 'contribution',
                                            'image_path' => !empty($contribution['image_path']) ? $contributionImageUrl : null,
                                        ]) ?>)'>
                                        <i class="fas fa-paper-plane me-2"></i>Submit Payment
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-outline-secondary" disabled>
                                        <i class="fas fa-check-circle me-2"></i><?= $remaining <= 0 ? 'Fully Paid' : 'Unavailable' ?>
                                    </button>
                                <?php endif; ?>
                                <button
                                    type="button"
                                    class="btn btn-outline-dark"
                                    onclick='openContributionPaymentHistory(<?= json_encode([
                                        'id' => (int)$contribution['id'],
                                        'title' => $contribution['title'],
                                        'amount' => (float)$contribution['amount'],
                                    ]) ?>)'>
                                    <i class="fas fa-history me-2"></i>Payment History
                                </button>
                                <a class="btn btn-outline-danger" href="<?= base_url('payer/refund-requests') ?>">
                                    <i class="fas fa-undo me-2"></i>Refunds
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?= $this->include('partials/modal-payment-request') ?>
<?= $this->include('partials/modal-qr-receipt') ?>

<div class="modal fade" id="payerItemImagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="payerItemImagePreviewTitle">Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" alt="" id="payerItemImagePreviewImage" class="img-fluid rounded-4">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="contributionPaymentHistoryModal" tabindex="-1" aria-labelledby="contributionPaymentHistoryTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="contributionPaymentHistoryTitle">
                        <i class="fas fa-history me-2"></i>Payment History
                    </h5>
                    <p class="text-muted mb-0 small" id="contributionPaymentHistorySubtitle">Contribution payment records</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="contributionPaymentHistoryBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('contributionSearch');
    if (!searchInput) {
        return;
    }

    searchInput.addEventListener('input', function() {
        const term = this.value.toLowerCase().trim();
        document.querySelectorAll('.contribution-item').forEach(item => {
            const title = item.getAttribute('data-title') || '';
            item.style.display = term === '' || title.includes(term) ? '' : 'none';
        });
    });
});

function openPayerImagePreview(src, title) {
    document.getElementById('payerItemImagePreviewTitle').textContent = title || 'Item Image';
    document.getElementById('payerItemImagePreviewImage').src = src;
    new bootstrap.Modal(document.getElementById('payerItemImagePreviewModal')).show();
}

function openContributionPaymentHistory(contribution) {
    const modalEl = document.getElementById('contributionPaymentHistoryModal');
    const titleEl = document.getElementById('contributionPaymentHistoryTitle');
    const subtitleEl = document.getElementById('contributionPaymentHistorySubtitle');
    const bodyEl = document.getElementById('contributionPaymentHistoryBody');

    if (!modalEl || !bodyEl || !contribution || !contribution.id) {
        return;
    }

    titleEl.innerHTML = '<i class="fas fa-history me-2"></i>' + escapeHtml(contribution.title || 'Payment History');
    subtitleEl.textContent = 'Per payer amount: PHP ' + formatAmount(contribution.amount || 0);
    bodyEl.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

    new bootstrap.Modal(modalEl).show();

    fetch('<?= base_url('payer/get-contribution-payments') ?>/' + encodeURIComponent(contribution.id), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Unable to load payment history.');
        }
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Unable to load payment history.');
        }

        renderContributionPaymentHistory(bodyEl, data.payments || []);
    })
    .catch(error => {
        bodyEl.innerHTML = `
            <div class="alert alert-danger mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>${escapeHtml(error.message || 'Unable to load payment history.')}
            </div>
        `;
    });
}

function renderContributionPaymentHistory(container, payments) {
    if (!payments.length) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Payments Yet</h5>
                <p class="text-muted mb-0">Payments for this contribution will appear here once recorded.</p>
            </div>
        `;
        return;
    }

    const totalPaid = payments.reduce((sum, payment) => sum + Number(payment.amount_paid || 0), 0);
    const rows = payments.map(payment => {
        const status = String(payment.payment_status || 'pending');
        const statusClass = status === 'fully paid' ? 'bg-success' : (status === 'partial' ? 'bg-warning text-dark' : 'bg-secondary');
        const dateText = formatDate(payment.payment_date || payment.created_at);

        return `
            <tr class="payer-click-row contribution-history-payment-row" data-payment='${escapeHtml(JSON.stringify(payment))}' onclick="showContributionHistoryReceipt(this)">
                <td>
                    <div class="fw-semibold">${escapeHtml(dateText.date)}</div>
                    <small class="text-muted">${escapeHtml(dateText.time)}</small>
                </td>
                <td><code>${escapeHtml(payment.receipt_number || 'N/A')}</code></td>
                <td>${escapeHtml(payment.payment_method || 'N/A')}</td>
                <td><span class="badge ${statusClass}">${escapeHtml(status.toUpperCase())}</span></td>
                <td class="text-end fw-semibold">PHP ${formatAmount(payment.amount_paid || 0)}</td>
            </tr>
        `;
    }).join('');

    container.innerHTML = `
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                <div class="payer-metric-label">Total Paid</div>
                <div class="h4 mb-0">PHP ${formatAmount(totalPaid)}</div>
            </div>
            <span class="badge bg-light text-dark border">${payments.length} payment${payments.length === 1 ? '' : 's'}</span>
        </div>
        <div class="table-responsive ui-table-wrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Receipt</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;
}

function showContributionHistoryReceipt(row) {
    if (!row) {
        return;
    }

    try {
        const paymentData = JSON.parse(row.getAttribute('data-payment') || '{}');
        if (typeof window.showQRReceipt === 'function') {
            window.showQRReceipt(paymentData);
            return;
        }
    } catch (error) {
        // Fall through to the user-facing message below.
    }

    alert('Receipt view is not available. Please refresh the page.');
}

function formatAmount(value) {
    return Number(value || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatDate(value) {
    if (!value) {
        return { date: 'N/A', time: '' };
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return { date: String(value), time: '' };
    }

    return {
        date: date.toLocaleDateString('en-PH', { month: 'short', day: '2-digit', year: 'numeric' }),
        time: date.toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit' })
    };
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, function(char) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char];
    });
}
</script>

<?= $this->endSection() ?>
