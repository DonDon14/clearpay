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
<?= view('partials/container-card', [
    'title' => '<i class="fas fa-plus-circle me-2"></i>Quick Actions',
    'cardClass' => 'shadow-sm border-0',
    'bodyClass' => 'p-0',
    'content' => view('partials/quick_actions_content')
]) ?>

<!-- Active Contributions Section -->
<?= view('partials/container-card', [
    'title' => '<i class="fas fa-hand-holding-usd me-2"></i>Active Contributions',
    'cardClass' => 'shadow-sm border-0',
    'bodyClass' => 'p-3',
    'content' => view('partials/contributions_list')
]) ?>

<?= $this->endSection() ?>