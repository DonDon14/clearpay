<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>


<!-- Stats Cards Row -->
<div class="container-fluid mb-4">
    <div class="row g-3">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-check-circle',
                'iconColor' => 'text-success',
                'title' => 'Active',
                'text' => '10'
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-hand-holding-usd',
                'iconColor' => 'text-primary',
                'title' => 'Total',
                'text' => '25'
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-times-circle',
                'iconColor' => 'text-danger',
                'title' => 'Inactive',
                'text' => '5'
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-calendar-day',
                'iconColor' => 'text-info',
                'title' => 'Today',
                'text' => '2'
            ]) ?>
        </div>
    </div>
</div>

<!-- Quick Actions Section -->
<div class="row mb-4">
        <!-- Quick Actions -->
        <div class="col-12">
            <div class="card h-100 shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                    <small class="text-muted">Frequently used operations</small>
                </div>
                <div class="card-body p-2">
                    <div class="row g-2">
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-plus-circle',
                            'title' => 'Add New Contribution',
                            'subtitle' => 'Add new contribution type',
                            'bgColor' => 'bg-primary',
                            'modalTarget' => '#contributionModal',  // <- triggers modal
                            'colClass' => 'col-lg-4 col-md-4 col-sm-6'
                        ]) ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-plus-square',
                            'title' => 'Record Payments',
                            'subtitle' => 'Add new payment record',
                            'bgColor' => 'bg-success',
                            'link' => '/admin/payments',
                            'colClass' => 'col-lg-4 col-md-4 col-sm-6'
                        ]) ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-history',
                            'title' => 'View History',
                            'subtitle' => 'View contribution history',
                            'bgColor' => 'bg-info',
                            'link' => '/admin/history',
                            'colClass' => 'col-lg-4 col-md-4 col-sm-6'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

<?= view('partials/modal-contribution', [
    'action' => base_url('contributions/save'),
    'title' => 'Add New Contribution'
]) ?>


<!-- Active Contributions Section -->
<?= view('partials/container-card', [
    'title' => '<i class="fas fa-hand-holding-usd me-2"></i>Active Contributions',
    'cardClass' => 'shadow-sm border-0',
    'bodyClass' => 'p-3',
    'content' => view('partials/contributions_list')
]) ?>

<?= $this->endSection() ?>