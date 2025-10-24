<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Dashboard Content -->
<!-- Stats Cards -->
<div class="container-fluid mb-4">
    <div class="row g-3">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-database',
                'iconColor' => 'text-primary',
                'title' => 'Total Collections',
                'text' => '₱150,000.00'
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-check-square',
                'iconColor' => 'text-success',
                'title' => 'Verified Payments',
                'text' => '0'
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-clock',
                'iconColor' => 'text-warning',
                'title' => 'Pending Payments',
                'text' => '0'
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-calendar-alt',
                'iconColor' => 'text-info',
                'title' => 'Today\'s Payments',
                'text' => '0'
            ]) ?>
        </div>
    </div>
</div>
<!--End Stats Cards -->

<!-- Detailed Stats and Welcome Message -->
<div class="container-fluid">
    <div class="row g-3"> <!-- g-3 adds some gap between columns -->

    <!-- Recent Payments -->
        <div class="col-lg-4 col-md-6">
            <?= view('partials/container-card', [
                'title' => 'Quick Actions',
                'subtitle' => 'Manage your tasks efficiently',
                'cardClass' => 'h-100',
                'bodyClass' => 'p-2', // Custom body styling
                'content' => '
                    <div class="row g-2 w-100">
                        ' . view('partials/quick-action', [
                            'icon' => 'fas fa-plus-circle',
                            'title' => 'Add Payment',
                            'subtitle' => 'Record new payment',
                            'bgColor' => 'bg-primary',
                            'link' => '/payments/add'
                        ]) . '
                        ' . view('partials/quick-action', [
                            'icon' => 'fas fa-eye',
                            'title' => 'View Reports',
                            'subtitle' => 'Check analytics',
                            'bgColor' => 'bg-success',
                            'link' => '/analytics'
                        ]) . '
                        ' . view('partials/quick-action', [
                            'icon' => 'fas fa-users',
                            'title' => 'Manage Students',
                            'subtitle' => 'Student records',
                            'bgColor' => 'bg-info',
                            'link' => '/students'
                        ]) . '
                        ' . view('partials/quick-action', [
                            'icon' => 'fas fa-cog',
                            'title' => 'Settings',
                            'subtitle' => 'System config',
                            'bgColor' => 'bg-secondary',
                            'link' => '/settings'
                        ]) . '
                    </div>
                '
            ]) ?>
        </div>
      <!-- End Recent Payments -->

        <div class="col-lg-4 col-md-6">
            <?= view('partials/container-card', [
                'title' => 'Payments Summary',
                'subtitle' => 'Overview of all payments',
                'cardClass' => 'h-100',
                'cards' => [
                    [
                        'icon' => 'fas fa-clock',
                        'iconColor' => 'text-warning',
                        'title' => 'Pending Payments',
                        'text' => '0',
                        'bgColor' => 'bg-warning'
                    ],
                    [
                        'icon' => 'fas fa-money-bill-wave',
                        'iconColor' => 'text-info',
                        'title' => 'Total Payments',
                        'text' => '₱0'
                    ]
                ]
            ]) ?>
        </div>

        <div class="col-lg-4 col-md-12">
            <?= view('partials/container-card', [
                'title' => 'Other Stats',
                'subtitle' => 'Additional information',
                'cardClass' => 'h-100',
                'cards' => [
                    [
                        'icon' => 'fas fa-users',
                        'iconColor' => 'text-primary',
                        'title' => 'Total Students',
                        'text' => '1,234'
                    ]
                ]
            ]) ?>
        </div>

    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Welcome, <?= esc($username) ?>!</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">You are successfully logged in to the ClearPay admin dashboard.</p>
                    <p class="text-muted">Use the sidebar navigation to access different sections of the application.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
