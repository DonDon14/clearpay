<div class="row g-4">
    <!-- Daily Revenue Chart -->
    <div class="col-lg-6">
        <div class="chart-container">
            <h6 class="mb-3 fw-semibold">Daily Revenue (Last 30 Days)</h6>
            <canvas id="dailyRevenueChart" style="max-height: 300px;"></canvas>
        </div>
    </div>
    
    <!-- Monthly Revenue Chart -->
    <div class="col-lg-6">
        <div class="chart-container">
            <h6 class="mb-3 fw-semibold">Monthly Revenue Trends</h6>
            <canvas id="monthlyRevenueChart" style="max-height: 300px;"></canvas>
        </div>
    </div>
    
    <!-- Transaction Count Chart -->
    <div class="col-12">
        <div class="chart-container">
            <h6 class="mb-3 fw-semibold">Daily Transactions</h6>
            <canvas id="transactionChart" style="max-height: 250px;"></canvas>
        </div>
    </div>
</div>

<script>
// Chart data from PHP
const chartData = <?= json_encode($charts ?? []) ?>;

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
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
            type: 'area',
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
</script>