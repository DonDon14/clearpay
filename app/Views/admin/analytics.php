<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$overview = $overview ?? [];
$charts = $charts ?? [];
$payments = $payments ?? [];
$refunds = $refunds ?? [];
$contributions = $contributions ?? [];
$trends = $trends ?? [];
$predictions = $predictions ?? [];
$typeBreakdown = $contributions['by_type'] ?? [];
$peso = '&#8369;';
?>

<div class="container-fluid">
    <div class="ui-page-intro">
        <div>
            <h6>Financial Analytics</h6>
            <p>
                Python-driven summaries, trends, and anomaly flags for admin review
                <?php if (!empty($generatedAt)): ?>
                    · Generated <?= esc($generatedAt) ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-outline-primary" onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <div class="btn-group">
                <button class="btn btn-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-download"></i> Export Report
                </button>
                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                    <li><a class="dropdown-item" href="#" onclick="exportAnalytics('pdf'); return false;"><i class="fas fa-file-pdf text-danger"></i> Export as PDF</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportAnalytics('csv'); return false;"><i class="fas fa-file-excel text-success"></i> Export as CSV/Excel</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="ui-analytics-alerts">
        <div class="ui-analytics-alert">
            <span class="ui-metric-label">Immediate Attention</span>
            <strong><?= number_format((int) ($overview['duplicate_records'] ?? 0)) ?> duplicate alerts</strong>
            <span class="ui-list-meta">Review repeated receipts and near-identical payment patterns first.</span>
        </div>
        <div class="ui-analytics-alert">
            <span class="ui-metric-label">Outstanding Balance</span>
            <strong><?= $peso . number_format((float) ($overview['total_outstanding_balance'] ?? 0), 2) ?></strong>
            <span class="ui-list-meta">Remaining unpaid balances across active contributions and payers.</span>
        </div>
        <div class="ui-analytics-alert">
            <span class="ui-metric-label">Suspicious Patterns</span>
            <strong><?= number_format((int) ($overview['suspicious_records'] ?? 0)) ?> flagged records</strong>
            <span class="ui-list-meta">Potential anomalies identified by the Python analytics worker.</span>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Total Revenue',
                'text' => $peso . number_format($overview['total_revenue'] ?? 0, 2),
                'icon' => 'money-bill-wave',
                'iconColor' => 'text-success',
                'subtitle' => isset($overview['monthly_growth']) ? ($overview['monthly_growth'] >= 0 ? '+' : '') . $overview['monthly_growth'] . '% vs last month' : '0% growth'
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Total Profit',
                'text' => $peso . number_format($overview['total_profit'] ?? 0, 2),
                'icon' => 'chart-line',
                'iconColor' => 'text-primary',
                'subtitle' => isset($overview['avg_profit_margin']) ? ($overview['avg_profit_margin'] ?? 0) . '% average margin' : 'No profit data'
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Active Contributors',
                'text' => number_format($overview['active_contributors'] ?? 0) . ' payers',
                'icon' => 'users',
                'iconColor' => 'text-info',
                'subtitle' => number_format($overview['total_contributions'] ?? 0) . ' active contributions'
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'This Month Revenue',
                'text' => $peso . number_format($overview['monthly_revenue'] ?? 0, 2),
                'icon' => 'calendar-alt',
                'iconColor' => 'text-warning',
                'subtitle' => date('F Y')
            ]) ?>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Outstanding Balance',
                'text' => $peso . number_format($overview['total_outstanding_balance'] ?? 0, 2),
                'icon' => 'wallet',
                'iconColor' => 'text-danger',
                'subtitle' => 'Remaining unpaid balances'
            ]) ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Duplicate Alerts',
                'text' => number_format($overview['duplicate_records'] ?? 0),
                'icon' => 'copy',
                'iconColor' => 'text-danger',
                'subtitle' => 'Potential duplicate transactions'
            ]) ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Suspicious Alerts',
                'text' => number_format($overview['suspicious_records'] ?? 0),
                'icon' => 'shield-alt',
                'iconColor' => 'text-warning',
                'subtitle' => 'Python anomaly-detection flags'
            ]) ?>
        </div>
    </div>

    <?php if (!empty($typeBreakdown)): ?>
        <div class="row mb-4">
            <?php foreach ($typeBreakdown as $type): ?>
                <div class="col-lg-6 col-md-6 mb-4">
                    <?= view('partials/card', [
                        'title' => ucfirst($type['contribution_type'] ?? 'contribution') . ' Items',
                        'text' => number_format((int) ($type['count'] ?? 0)),
                        'icon' => ($type['contribution_type'] ?? 'contribution') === 'product' ? 'box-open' : 'file-invoice-dollar',
                        'iconColor' => ($type['contribution_type'] ?? 'contribution') === 'product' ? 'text-primary' : 'text-success',
                        'subtitle' => $peso . number_format((float) ($type['total_amount'] ?? 0), 2) . ' total configured amount'
                    ]) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($charts)): ?>
        <?= view('partials/container-card', [
            'title' => 'Revenue Trends',
            'subtitle' => 'Visual insights into payment patterns and growth',
            'bodyClass' => '',
            'content' => view('partials/analytics_charts_content', ['charts' => $charts])
        ]) ?>
    <?php endif; ?>

    <?php if (!empty($predictions)): ?>
        <div class="row mb-4">
            <div class="col-lg-4 mb-4">
                <?= view('partials/card', [
                    'title' => 'Forecast (30 Days)',
                    'text' => $peso . number_format((float)($predictions['next_30_days_total'] ?? 0), 2),
                    'icon' => 'chart-area',
                    'iconColor' => 'text-primary',
                    'subtitle' => $predictions['confidence_note'] ?? 'Estimate only'
                ]) ?>
            </div>
            <div class="col-lg-8 mb-4">
                <?= view('partials/container-card', [
                    'title' => 'Projected Revenue (Next 7 Days)',
                    'subtitle' => 'Simple forecast based on recent payment momentum',
                    'bodyClass' => '',
                    'content' => '
                        <div style="position: relative; height: 250px;">
                            <canvas id="forecastRevenueChart"></canvas>
                        </div>
                    '
                ]) ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($payments)): ?>
        <div class="row mb-4">
            <?php if (!empty($payments['by_method'])): ?>
                <div class="col-lg-6 mb-4">
                    <?= view('partials/container-card', [
                        'title' => 'Payment Methods',
                        'subtitle' => 'Revenue by payment method',
                        'bodyClass' => '',
                        'content' => '
                            <div style="position: relative; height: 250px;">
                                <canvas id="paymentMethodChart"></canvas>
                            </div>
                        '
                    ]) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($payments['by_status'])): ?>
                <div class="col-lg-6 mb-4">
                    <?= view('partials/container-card', [
                        'title' => 'Payment Status',
                        'subtitle' => 'Breakdown of payment statuses',
                        'bodyClass' => '',
                        'content' => '
                            <div style="position: relative; height: 250px;">
                                <canvas id="paymentStatusChart"></canvas>
                            </div>
                        '
                    ]) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($refunds)): ?>
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-4">
                <?= view('partials/card', [
                    'title' => 'Refund Total',
                    'text' => $peso . number_format((float)($refunds['total_refunds'] ?? 0), 2),
                    'icon' => 'undo',
                    'iconColor' => 'text-danger',
                    'subtitle' => number_format((int)($refunds['total_count'] ?? 0)) . ' refund records'
                ]) ?>
            </div>
            <div class="col-lg-9 mb-4">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?= view('partials/container-card', [
                            'title' => 'Refund Status',
                            'subtitle' => 'Count by status',
                            'bodyClass' => '',
                            'content' => '<div style="position: relative; height: 220px;"><canvas id="refundStatusChart"></canvas></div>'
                        ]) ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <?= view('partials/container-card', [
                            'title' => 'Refund Methods',
                            'subtitle' => 'Amount by refund method',
                            'bodyClass' => '',
                            'content' => '<div style="position: relative; height: 220px;"><canvas id="refundMethodChart"></canvas></div>'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($charts['daily_refunds'])): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <?= view('partials/container-card', [
                        'title' => 'Refund Trend Over Time',
                        'subtitle' => 'Daily refund amount for the last 30 days',
                        'bodyClass' => '',
                        'content' => '
                            <div class="d-flex gap-2 mb-3">
                                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="setRefundTrendRange(7)">7D</button>
                                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="setRefundTrendRange(30)">30D</button>
                                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="setRefundTrendRange(0)">All</button>
                            </div>
                            <div style="position: relative; height: 260px;"><canvas id="dailyRefundTrendChart"></canvas></div>
                        '
                    ]) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($refunds['top_refunded_items'])): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <?= view('partials/container-card', [
                        'title' => 'Top Refunded Items',
                        'subtitle' => 'Items with highest refunded amount',
                        'bodyClass' => '',
                        'content' => '
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Type</th>
                                            <th class="text-end">Refund Count</th>
                                            <th class="text-end">Total Refunded</th>
                                        </tr>
                                    </thead>
                                    <tbody>' .
                                        implode('', array_map(static function ($item) {
                                            $title = esc((string) ($item['item_title'] ?? 'Unknown'));
                                            $type = esc(ucfirst((string) ($item['item_type'] ?? 'contribution')));
                                            $count = number_format((int) ($item['refund_count'] ?? 0));
                                            $total = number_format((float) ($item['total_refunded'] ?? 0), 2);
                                            return "<tr><td>{$title}</td><td>{$type}</td><td class=\"text-end\">{$count}</td><td class=\"text-end\">PHP {$total}</td></tr>";
                                        }, array_slice($refunds['top_refunded_items'], 0, 10)))
                                    . '</tbody>
                                </table>
                            </div>
                        '
                    ]) ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?= view('partials/container-card', [
        'title' => 'Top Performers',
        'subtitle' => 'Top payers and best performing contributions',
        'bodyClass' => '',
        'content' => view('partials/analytics_summary_content', ['payments' => $payments, 'contributions' => $contributions])
    ]) ?>

    <?php if (!empty($payments['recent_payments'])): ?>
        <?= view('partials/container-card', [
            'title' => 'Payment History Snapshot',
            'subtitle' => 'Latest payments first (most recent at top)',
            'bodyClass' => '',
            'content' => '
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Payer</th>
                                <th>Item</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            ' . implode('', array_map(static function($row) {
                                $date = !empty($row['created_at']) ? date('M d, Y h:i A', strtotime((string)$row['created_at'])) : 'N/A';
                                $payer = esc((string)($row['student_name'] ?? 'Unknown'));
                                $item = esc((string)($row['contribution_title'] ?? 'N/A'));
                                $method = esc(strtoupper((string)($row['payment_method'] ?? 'N/A')));
                                $status = esc(ucwords(str_replace('_', ' ', (string)($row['status'] ?? 'N/A'))));
                                $amount = number_format((float)($row['amount'] ?? 0), 2);
                                return "<tr><td>{$date}</td><td>{$payer}</td><td>{$item}</td><td>{$method}</td><td>{$status}</td><td class=\"text-end\">PHP {$amount}</td></tr>";
                            }, array_slice($payments['recent_payments'], 0, 10))) . '
                        </tbody>
                    </table>
                </div>
            '
        ]) ?>
    <?php endif; ?>

    <?= view('partials/container-card', [
        'title' => 'Audit Findings',
        'subtitle' => 'Duplicate and suspicious records detected by the Python analytics worker',
        'bodyClass' => '',
        'content' => view('partials/analytics_audit_content', ['payments' => $payments])
    ]) ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const chartData = <?= json_encode($charts ?? []) ?>;
const paymentData = <?= json_encode($payments ?? []) ?>;
const refundData = <?= json_encode($refunds ?? []) ?>;

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    initializePaymentCharts();
});

function initializeCharts() {
    if (chartData.daily_revenue && document.getElementById('dailyRevenueChart')) {
        new Chart(document.getElementById('dailyRevenueChart'), {
            type: 'line',
            data: {
                labels: chartData.daily_revenue.labels || [],
                datasets: [{
                    label: 'Daily Revenue',
                    data: chartData.daily_revenue.data || [],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    if (chartData.monthly_revenue && document.getElementById('monthlyRevenueChart')) {
        new Chart(document.getElementById('monthlyRevenueChart'), {
            type: 'bar',
            data: {
                labels: chartData.monthly_revenue.labels || [],
                datasets: [{
                    label: 'Monthly Revenue',
                    data: chartData.monthly_revenue.data || [],
                    backgroundColor: '#10b981',
                    borderColor: '#059669',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    if (chartData.daily_transactions && document.getElementById('transactionChart')) {
        new Chart(document.getElementById('transactionChart'), {
            type: 'line',
            data: {
                labels: chartData.daily_transactions.labels || [],
                datasets: [{
                    label: 'Transactions',
                    data: chartData.daily_transactions.data || [],
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
}

function initializePaymentCharts() {
    window.dailyRefundTrendChartInstance = null;
    window.dailyRefundTrendSource = null;
    if (paymentData.by_method && document.getElementById('paymentMethodChart')) {
        const methodData = paymentData.by_method;
        new Chart(document.getElementById('paymentMethodChart'), {
            type: 'doughnut',
            data: {
                labels: methodData.map(item => item.payment_method.toUpperCase()),
                datasets: [{
                    data: methodData.map(item => parseFloat(item.total_amount)),
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ₱' + context.parsed.toLocaleString('en-US', {minimumFractionDigits: 2});
                            }
                        }
                    }
                }
            }
        });
    }

    if (paymentData.by_status && document.getElementById('paymentStatusChart')) {
        const statusData = paymentData.by_status;
        new Chart(document.getElementById('paymentStatusChart'), {
            type: 'pie',
            data: {
                labels: statusData.map(item => item.status.replace('_', ' ').toUpperCase()),
                datasets: [{
                    data: statusData.map(item => parseInt(item.count)),
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#6b7280'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    if (chartData.forecast_revenue && document.getElementById('forecastRevenueChart')) {
        new Chart(document.getElementById('forecastRevenueChart'), {
            type: 'line',
            data: {
                labels: chartData.forecast_revenue.labels || [],
                datasets: [{
                    label: 'Projected Revenue',
                    data: chartData.forecast_revenue.data || [],
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.12)',
                    borderWidth: 2,
                    borderDash: [8, 4],
                    fill: true,
                    tension: 0.25
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'PHP ' + Number(value).toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    if (refundData.by_status && document.getElementById('refundStatusChart')) {
        new Chart(document.getElementById('refundStatusChart'), {
            type: 'bar',
            data: {
                labels: refundData.by_status.map(item => (item.status || 'unknown').toUpperCase()),
                datasets: [{
                    label: 'Refund Count',
                    data: refundData.by_status.map(item => Number(item.count || 0)),
                    backgroundColor: '#f97316'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    if (refundData.by_method && document.getElementById('refundMethodChart')) {
        new Chart(document.getElementById('refundMethodChart'), {
            type: 'doughnut',
            data: {
                labels: refundData.by_method.map(item => (item.refund_method || 'unknown').toUpperCase()),
                datasets: [{
                    data: refundData.by_method.map(item => Number(item.total_amount || 0)),
                    backgroundColor: ['#ef4444', '#f97316', '#22c55e', '#3b82f6', '#a855f7'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': PHP ' + Number(context.parsed).toLocaleString('en-US', {minimumFractionDigits: 2});
                            }
                        }
                    }
                }
            }
        });
    }

    if (chartData.daily_refunds && document.getElementById('dailyRefundTrendChart')) {
        const labels = chartData.daily_refunds.labels || [];
        const data = chartData.daily_refunds.data || [];
        window.dailyRefundTrendSource = { labels, data };
        window.dailyRefundTrendChartInstance = new Chart(document.getElementById('dailyRefundTrendChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Daily Refund Amount',
                    data: data,
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.12)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.25
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'PHP ' + Number(value).toLocaleString('en-US', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                }
            }
        });
    }
}

function setRefundTrendRange(days) {
    if (!window.dailyRefundTrendChartInstance || !window.dailyRefundTrendSource) return;
    const sourceLabels = window.dailyRefundTrendSource.labels || [];
    const sourceData = window.dailyRefundTrendSource.data || [];
    let labels = sourceLabels;
    let data = sourceData;

    if (days > 0 && sourceLabels.length > days) {
        labels = sourceLabels.slice(-days);
        data = sourceData.slice(-days);
    }

    window.dailyRefundTrendChartInstance.data.labels = labels;
    window.dailyRefundTrendChartInstance.data.datasets[0].data = data;
    window.dailyRefundTrendChartInstance.update();
}

function exportAnalytics(type = 'csv') {
    window.location.href = '<?= base_url('admin/analytics/export/') ?>' + type;
}

function markAnalyticsFindingReviewed(findingType, paymentId, buttonEl) {
    if (!findingType || !paymentId) return;
    if (!window.confirm('Mark this finding as reviewed?')) return;

    const formData = new URLSearchParams();
    formData.set('finding_type', findingType);
    formData.set('payment_id', String(paymentId));

    if (buttonEl) {
        buttonEl.disabled = true;
        buttonEl.textContent = 'Saving...';
    }

    fetch('<?= base_url('admin/analytics/mark-finding-reviewed') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData.toString()
    })
    .then(resp => resp.json())
    .then(data => {
        if (data.success) {
            const card = buttonEl ? buttonEl.closest('.activity-item') : null;
            if (card) {
                card.remove();
            } else {
                window.location.reload();
            }
            return;
        }

        alert(data.message || 'Failed to mark reviewed.');
        if (buttonEl) {
            buttonEl.disabled = false;
            buttonEl.textContent = 'Mark Reviewed';
        }
    })
    .catch(() => {
        alert('Failed to mark reviewed.');
        if (buttonEl) {
            buttonEl.disabled = false;
            buttonEl.textContent = 'Mark Reviewed';
        }
    });
}
</script>

<?= $this->endSection() ?>
