<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Force dummy data for UI development - override any controller data
$overview = [
    'total_revenue' => 125000,
    'total_profit' => 45000,
    'avg_profit_margin' => 36.5,
    'active_contributors' => 245,
    'monthly_revenue' => 18500,
    'monthly_growth' => 12.3
];

$charts = [
    'daily_revenue' => [
        'labels' => ['Oct 20', 'Oct 21', 'Oct 22', 'Oct 23', 'Oct 24', 'Oct 25'],
        'data' => [1200, 1500, 980, 2100, 1800, 2200]
    ],
    'monthly_revenue' => [
        'labels' => ['Aug 2024', 'Sep 2024', 'Oct 2024'],
        'data' => [15000, 17500, 18500]
    ],
    'daily_transactions' => [
        'labels' => ['Oct 20', 'Oct 21', 'Oct 22', 'Oct 23', 'Oct 24', 'Oct 25'],
        'data' => [12, 18, 9, 25, 21, 28]
    ]
];

$payments = [
    'recent_payments' => [
        [
            'student_name' => 'John Doe',
            'contribution_title' => 'Class T-Shirt Fund',
            'amount' => 500,
            'payment_method' => 'gcash',
            'created_at' => '2024-10-25 10:30:00'
        ],
        [
            'student_name' => 'Jane Smith',
            'contribution_title' => 'Graduation Fund',
            'amount' => 1200,
            'payment_method' => 'bank_transfer',
            'created_at' => '2024-10-24 14:15:00'
        ],
        [
            'student_name' => 'Mike Johnson',
            'contribution_title' => 'Class Party Fund',
            'amount' => 350,
            'payment_method' => 'cash',
            'created_at' => '2024-10-24 09:45:00'
        ],
        [
            'student_name' => 'Sarah Wilson',
            'contribution_title' => 'Field Trip Fund',
            'amount' => 800,
            'payment_method' => 'gcash',
            'created_at' => '2024-10-23 16:20:00'
        ],
        [
            'student_name' => 'David Brown',
            'contribution_title' => 'Sports Equipment',
            'amount' => 450,
            'payment_method' => 'bank_transfer',
            'created_at' => '2024-10-23 11:10:00'
        ]
    ]
];

$contributions = [
    'top_profitable' => [
        [
            'title' => 'Graduation T-Shirts',
            'category' => 'Apparel',
            'profit_amount' => 15000,
            'profit_margin' => 45.2
        ],
        [
            'title' => 'Class Yearbook',
            'category' => 'Memorabilia',
            'profit_amount' => 12500,
            'profit_margin' => 38.7
        ],
        [
            'title' => 'School Event Fund',
            'category' => 'Events',
            'profit_amount' => 8200,
            'profit_margin' => 42.1
        ],
        [
            'title' => 'Study Materials',
            'category' => 'Academic',
            'profit_amount' => 6800,
            'profit_margin' => 31.5
        ],
        [
            'title' => 'Sports Equipment',
            'category' => 'Athletics',
            'profit_amount' => 5200,
            'profit_margin' => 28.9
        ]
    ]
];
?>
<div class="container-fluid">
    <!-- Page Header with Stats -->
    <div class="row g-4 mb-4">
        <!-- Revenue Card -->
        <div class="col-lg-3 col-md-6">
            <?= view('partials/card', [
                'title' => 'Total Revenue',
                'text' => '₱' . number_format($overview['total_revenue'], 2) . 
                        ' (' . ($overview['monthly_growth'] >= 0 ? '+' : '') . 
                        $overview['monthly_growth'] . '% vs last month)',
                'icon' => 'fas fa-money-bill-wave',
                'iconColor' => 'text-success'
                ]) ?>
            </div>

        
        <!-- Profit Card -->
        <div class="col-lg-3 col-md-6">
            <?= view('partials/card', [
                'title' => 'Total Profit',
                'text' => '₱' . number_format($overview['total_profit'], 2) . ' (' . $overview['avg_profit_margin'] . '% avg margin)',
                'icon' => 'fas fa-chart-line',
                'iconColor' => 'text-primary'
            ]) ?>
        </div>
        
        <!-- Contributors Card -->
        <div class="col-lg-3 col-md-6">
            <?= view('partials/card', [
                'title' => 'Active Contributors',
                'text' => number_format($overview['active_contributors']) . ' paying students (Growing)',
                'icon' => 'fas fa-users',
                'iconColor' => 'text-info'
            ]) ?>
        </div>
        
        <!-- Monthly Revenue Card -->
        <div class="col-lg-3 col-md-6">
            <?= view('partials/card', [
                'title' => 'Monthly Revenue',
                'text' => '₱' . number_format($overview['monthly_revenue'], 2) . ' (Current month)',
                'icon' => 'fas fa-calendar-alt',
                'iconColor' => 'text-warning'
            ]) ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <?= view('partials/analytics_quick_actions') ?>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <?= view('partials/analytics_charts', ['charts' => $charts]) ?>
        </div>
    </div>

    <!-- Recent Activity & Summary -->
    <div class="row g-4">
        <div class="col-12">
            <?= view('partials/analytics_summary', ['payments' => $payments, 'contributions' => $contributions]) ?>
        </div>
    </div>
</div>

<script>
// Analytics page functionality
function refreshAnalytics() {
    const refreshBtn = event.target.closest('.card');
    if (refreshBtn) {
        const original = refreshBtn.innerHTML;
        refreshBtn.innerHTML = '<div class="card-body d-flex align-items-center gap-3 h-100"><div class="icon-circle"><i class="fas fa-spinner fa-spin fs-4"></i></div><div class="flex-grow-1"><h6 class="mb-1 fw-semibold">Refreshing...</h6><small class="text-white-75">Please wait</small></div></div>';
        
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }
}

function exportAnalytics() {
    const exportBtn = event.target.closest('.card');
    if (exportBtn) {
        const original = exportBtn.innerHTML;
        exportBtn.innerHTML = '<div class="card-body d-flex align-items-center gap-3 h-100"><div class="icon-circle"><i class="fas fa-spinner fa-spin fs-4"></i></div><div class="flex-grow-1"><h6 class="mb-1 fw-semibold">Generating...</h6><small class="text-white-75">Please wait</small></div></div>';
        
        setTimeout(() => {
            exportBtn.innerHTML = original;
        }, 3000);
    }
    
    // Export CSV report
    window.location.href = '<?= base_url('admin/analytics/export/csv') ?>';
}

function viewDetailedReports() {
    alert('Detailed analytics reports feature coming soon!');
}
</script>

<!-- Chart.js CDN for analytics charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?= $this->endSection() ?>