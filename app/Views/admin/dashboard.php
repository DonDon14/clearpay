<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Dashboard Content -->
<div class="container-fluid mb-4">
    <div class="row g-3">
        <div class="col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-users',
                'iconColor' => 'text-primary',
                'title' => 'Total Students',
                'text' => '1,234'
            ]) ?>
        </div>
        <div class="col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-graduation-cap',
                'iconColor' => 'text-success',
                'title' => 'Active Enrollments',
                'text' => '987'
            ]) ?>
        </div>
    </div>
</div>
<!-- Stats Cards -->
<div class="container-fluid">
    <div class="row g-3"> <!-- g-3 adds some gap between columns -->

        <div class="col-lg-4 col-md-6">
            <?= view('partials/container-card', [
                'title' => 'Recent Payments',
                'cardClass' => 'h-100',
                'cards' => [
                    [
                        'icon' => 'fas fa-database',
                        'iconColor' => 'text-success',
                        'title' => 'Total Collections',
                        'text' => '₱150,000.00'
                    ],
                    [
                        'icon' => 'fas fa-check-circle',
                        'iconColor' => 'text-success',
                        'title' => 'Verified Payments',
                        'text' => '0'
                    ]
                ]
            ]) ?>
        </div>

        <div class="col-lg-4 col-md-6">
            <?= view('partials/container-card', [
                'title' => 'Payments Summary',
                'cardClass' => 'h-100',
                'cards' => [
                    [
                        'icon' => 'fas fa-clock',
                        'iconColor' => 'text-warning',
                        'title' => 'Pending Payments',
                        'text' => '0'
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
