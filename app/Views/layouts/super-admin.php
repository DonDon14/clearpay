<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Super Admin Portal') ?> | ClearPay</title>
  <link rel="icon" type="image/png" href="<?= base_url('favicon.png') ?>">
  <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="<?= base_url('css/dashboard.css') ?>" rel="stylesheet">
  <style>
    .super-admin-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 1rem 2rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .super-admin-header .navbar-brand {
      color: white;
      font-weight: 600;
      font-size: 1.25rem;
    }
    .super-admin-header .nav-link {
      color: rgba(255,255,255,0.9);
      transition: color 0.2s;
    }
    .super-admin-header .nav-link:hover {
      color: white;
    }
    body {
      background-color: #f8f9fa;
    }
    .main-content-wrapper {
      padding: 2rem;
      max-width: 1400px;
      margin: 0 auto;
    }
  </style>
</head>
<body>
  <!-- Super Admin Header -->
  <nav class="navbar super-admin-header">
    <div class="container-fluid">
      <a class="navbar-brand" href="<?= base_url('super-admin/portal') ?>">
        <i class="fas fa-shield-alt me-2"></i>
        ClearPay Super Admin Portal
      </a>
      <div class="d-flex align-items-center">
        <span class="me-3">
          <i class="fas fa-user-shield me-1"></i>
          <?= esc(session()->get('super-admin-name') ?? 'Super Admin') ?>
        </span>
        <a href="<?= base_url('super-admin/logout') ?>" class="nav-link">
          <i class="fas fa-sign-out-alt me-1"></i>
          Logout
        </a>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="main-content-wrapper">
    <?= $this->renderSection('content') ?>
  </div>

  <!-- Footer -->
  <footer class="text-center text-muted py-3 mt-5">
    <p class="mb-0">© 2025 ClearPay. All rights reserved.</p>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

