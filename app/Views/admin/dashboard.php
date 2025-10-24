<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            <div class="text-primary mb-2">
              <i class="fas fa-users fa-2x"></i>
            </div>
            <h5 class="card-title">1,234</h5>
            <p class="card-text text-muted">Total Students</p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            <div class="text-success mb-2">
              <i class="fas fa-dollar-sign fa-2x"></i>
            </div>
            <h5 class="card-title">₱45,678</h5>
            <p class="card-text text-muted">Total Payments</p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            <div class="text-warning mb-2">
              <i class="fas fa-clock fa-2x"></i>
            </div>
            <h5 class="card-title">23</h5>
            <p class="card-text text-muted">Pending Payments</p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            <div class="text-info mb-2">
              <i class="fas fa-chart-line fa-2x"></i>
            </div>
            <h5 class="card-title">89%</h5>
            <p class="card-text text-muted">Collection Rate</p>
          </div>
        </div>
      </div>
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
