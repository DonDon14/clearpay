<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
  <div class="container mt-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4 class="card-title">Dashboard</h4>
        <p class="card-text">You are now logged in as <strong><?= esc($username) ?></strong>.</p>
        <hr>
        <p class="text-muted">This is your dashboard — we’ll add your app content here next.</p>
      </div>
    </div>
  </div>
<?= $this->endSection() ?>
