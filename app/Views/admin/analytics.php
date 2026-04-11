<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$overview = $overview ?? [];
$charts = $charts ?? [];
$payments = $payments ?? [];
$contributions = $contributions ?? [];
$trends = $trends ?? [];
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

    <?= view('partials/container-card', [
        'title' => 'Top Performers',
        'subtitle' => 'Top payers and best performing contributions',
        'bodyClass' => '',
        'content' => view('partials/analytics_summary_content', ['payments' => $payments, 'contributions' => $contributions])
    ]) ?>

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
}

function exportAnalytics(type = 'csv') {
    window.location.href = '<?= base_url('admin/analytics/export/') ?>' + type;
}
</script>

<?= $this->endSection() ?>
