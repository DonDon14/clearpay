<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Use data from controller
$overview = $overview ?? [];
$charts = $charts ?? [];
$payments = $payments ?? [];
$contributions = $contributions ?? [];
$trends = $trends ?? [];
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Analytics Dashboard</h1>
                    <p class="mb-0 text-muted">Track revenue, payments, and contribution performance</p>
                </div>
                <div>
                    <button class="btn btn-outline-primary" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="exportAnalytics()">
                        <i class="fas fa-download"></i> Export Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Statistics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Total Revenue',
                'text' => '₱' . number_format($overview['total_revenue'] ?? 0, 2),
                'icon' => 'money-bill-wave',
                'iconColor' => 'text-success',
                'subtitle' => isset($overview['monthly_growth']) ? ($overview['monthly_growth'] >= 0 ? '+' : '') . $overview['monthly_growth'] . '% vs last month' : '0% growth'
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Total Profit',
                'text' => '₱' . number_format($overview['total_profit'] ?? 0, 2),
                'icon' => 'chart-line',
                'iconColor' => 'text-primary',
                'subtitle' => isset($overview['avg_profit_margin']) ? ($overview['avg_profit_margin'] ?? 0) . '% avg margin' : 'No profit data'
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Active Contributors',
                'text' => number_format($overview['active_contributors'] ?? 0) . ' payers',
                'icon' => 'users',
                'iconColor' => 'text-info',
                'subtitle' => $overview['total_contributions'] ?? 0 . ' contributions'
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'This Month Revenue',
                'text' => '₱' . number_format($overview['monthly_revenue'] ?? 0, 2),
                'icon' => 'calendar-alt',
                'iconColor' => 'text-warning',
                'subtitle' => date('F Y')
            ]) ?>
        </div>
    </div>

    <!-- Charts Section -->
    <?php if (!empty($charts)): ?>
        <?= view('partials/container-card', [
            'title' => 'Revenue Trends',
            'subtitle' => 'Visual insights into payment patterns and growth',
            'bodyClass' => '',
            'content' => view('partials/analytics_charts_content', ['charts' => $charts])
        ]) ?>
    <?php endif; ?>

    <!-- Payment Analysis -->
    <?php if (!empty($payments)): ?>
        <div class="row mb-4">
            <!-- Payment Method Breakdown -->
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

            <!-- Payment Status Breakdown -->
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

    <!-- Recent Activity & Summary -->
    <?= view('partials/container-card', [
        'title' => 'Recent Activity & Top Performers',
        'subtitle' => 'Latest payments and best performing contributions',
        'bodyClass' => '',
        'content' => view('partials/analytics_summary_content', ['payments' => $payments, 'contributions' => $contributions])
    ]) ?>
</div>

<!-- Chart.js CDN for analytics charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Chart data from PHP
const chartData = <?= json_encode($charts ?? []) ?>;
const paymentData = <?= json_encode($payments ?? []) ?>;

console.log('Chart Data:', chartData);
console.log('Payment Data:', paymentData);

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    initializePaymentCharts();
});

function initializeCharts() {
    // Daily Revenue Chart
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
    
    // Monthly Revenue Chart
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
    
    // Transaction Count Chart
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
    // Payment Method Chart
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

    // Payment Status Chart
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

function exportAnalytics() {
    // Export CSV report
    window.location.href = '<?= base_url('admin/analytics/export/csv') ?>';
}
</script>

<?= $this->endSection() ?>