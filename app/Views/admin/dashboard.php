<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
  <div class="container-fluid">
    <div class="row">
        <?= view('partials/card', [
            'icon' => 'fas fa-database',
            'iconColor' => 'text-success',
            'title' => 'Total Collections',
            'text' => '₱150,000.00'
        ]) ?>
        <?= view('partials/card', [
            'icon' => 'fas fa-check-circle',
            'iconColor' => 'text-success',
            'title' => 'Verified Payments',
            'text' => '0'
        ]) ?>
        <?= view('partials/card', [
            'icon' => 'fas fa-clock',
            'iconColor' => 'text-success',
            'title' => 'Pending Payments',
            'text' => '0'
        ]) ?>
        <?= view('partials/card', [
            'icon' => 'fas fa-money-bill-wave',
            'iconColor' => 'text-success',
            'title' => 'Total Payments',
            'text' => '0'
        ]) ?>
    </div>
    
    <div class="row">
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
