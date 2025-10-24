<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Dashboard') ?></title>
  <link href="<?= base_url('css/global.css') ?>" rel="stylesheet">
  <link href="<?= base_url('css/header.css') ?>" rel="stylesheet">
  <link href="<?= base_url('css/sidebar.css') ?>" rel="stylesheet">
  <link href="<?= base_url('css/footer.css') ?>" rel="stylesheet">
  <link href="<?= base_url('css/dashboard.css') ?>" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
  <div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <?= $this->include('partials/sidebar') ?>
    </aside>
    
    <!-- Main Content Area -->
    <div class="main-content">
      <!-- Header -->
      <header class="header">
        <?= $this->include('partials/header', [
          'pageTitle' => $pageTitle ?? 'Dashboard',
          'pageSubtitle' => $pageSubtitle ?? 'Welcome back to your ClearPay dashboard'
        ]) ?>
      </header>
      
      <!-- Content -->
      <main class="content">
        <?= $this->renderSection('content') ?>
      </main>
      
      <!-- Footer -->
      <footer class="footer">
        <?= $this->include('partials/footer') ?>
      </footer>
    </div>
  </div>
  
  <script>
    // Mobile sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
      sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('mobile-open');
      });
      
      // Close sidebar when clicking outside on mobile
      document.addEventListener('click', function(e) {
        if (window.innerWidth <= 576) {
          if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
            sidebar.classList.remove('mobile-open');
          }
        }
      });
    }
  </script>
</body>
</html>
