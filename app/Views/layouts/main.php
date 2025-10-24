<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Dashboard') ?></title>
  <!-- Load Bootstrap and external CSS first -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  
  <!-- Custom CSS (loads after Bootstrap to override styles) -->
  <link href="<?= base_url('css/global.css') ?>" rel="stylesheet">
  <link href="<?= base_url('css/header.css') ?>" rel="stylesheet">
  <link href="<?= base_url('css/footer.css') ?>" rel="stylesheet">
  <link href="<?= base_url('css/dashboard.css') ?>" rel="stylesheet">
  
  <!-- Sidebar Component - Complete consolidated styles -->
  <link href="<?= base_url('css/components/sidebar-complete.css') ?>" rel="stylesheet">
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
    // Sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('aside.sidebar');
    const appLayout = document.querySelector('.app-layout');
    
    function updateLayoutClass() {
      if (sidebar.classList.contains('collapsed')) {
        appLayout.classList.add('sidebar-collapsed');
      } else {
        appLayout.classList.remove('sidebar-collapsed');
      }
    }
    
    if (sidebarToggle && sidebar) {
      sidebarToggle.addEventListener('click', function() {
        if (window.innerWidth > 576) {
          // Desktop: Toggle collapsed state
          sidebar.classList.toggle('collapsed');
          updateLayoutClass();
          
          // Save state to localStorage
          if (sidebar.classList.contains('collapsed')) {
            localStorage.setItem('sidebarCollapsed', 'true');
          } else {
            localStorage.setItem('sidebarCollapsed', 'false');
          }
        } else {
          // Mobile: Toggle mobile-open state
          sidebar.classList.toggle('mobile-open');
        }
      });
      
      // Load saved collapsed state on page load (desktop only)
      if (window.innerWidth > 576) {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
          sidebar.classList.add('collapsed');
          updateLayoutClass();
        }
      }
    }

    // Expand button functionality
    const sidebarExpand = document.getElementById('sidebarExpand');
    if (sidebarExpand && sidebar) {
      sidebarExpand.addEventListener('click', function() {
        if (window.innerWidth > 576 && sidebar.classList.contains('collapsed')) {
          sidebar.classList.remove('collapsed');
          updateLayoutClass();
          localStorage.setItem('sidebarCollapsed', 'false');
        }
      });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
      if (window.innerWidth <= 576) {
        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
          sidebar.classList.remove('mobile-open');
        }
      }
    });
    
    // Handle window resize - reset states appropriately
    window.addEventListener('resize', function() {
      if (window.innerWidth > 576) {
        sidebar.classList.remove('mobile-open');
        // Restore collapsed state on desktop
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
          sidebar.classList.add('collapsed');
        } else {
          sidebar.classList.remove('collapsed');
        }
        updateLayoutClass();
      } else {
        sidebar.classList.remove('collapsed');
        appLayout.classList.remove('sidebar-collapsed');
      }
    });
  </script>
</body>
</html>
