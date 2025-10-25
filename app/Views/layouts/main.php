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
  <link href="<?= base_url('css/header.css') ?>" rel="stylesheet">
  <link href="<?= base_url('css/footer.css') ?>" rel="stylesheet">
  <link href="<?= base_url('css/dashboard.css') ?>" rel="stylesheet">
  
  <!-- Sidebar Component - Complete consolidated styles -->
  <link href="<?= base_url('css/sidebar-complete.css') ?>" rel="stylesheet">
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
    // Sidebar Toggle Script with State Persistence
    document.addEventListener('DOMContentLoaded', function() {
      const toggleBtn = document.getElementById('sidebarToggleBtn');
      const sidebar = document.querySelector('.sidebar');
      const mainContent = document.querySelector('.main-content');
      const expandBtn = document.querySelector('.sidebar-expand-btn');

      // Function to save sidebar state
      function saveSidebarState(isCollapsed) {
        localStorage.setItem('sidebarCollapsed', isCollapsed);
      }

      // Function to update main content margin
      function updateMainContentMargin(isCollapsed) {
        if (mainContent) {
          if (window.innerWidth > 768) { // Only adjust margin on desktop
            mainContent.style.marginLeft = isCollapsed ? '82px' : '280px';
          }
        }
      }

      // Function to restore sidebar state
      function restoreSidebarState() {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed && sidebar) {
          sidebar.classList.add('collapsed');
          updateMainContentMargin(true);
        } else {
          updateMainContentMargin(false);
        }
      }

      // Restore sidebar state on page load
      restoreSidebarState();

      if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
          sidebar.classList.toggle('collapsed');
          const isCollapsed = sidebar.classList.contains('collapsed');
          saveSidebarState(isCollapsed);
          updateMainContentMargin(isCollapsed);
        });
      }

      if (expandBtn && sidebar) {
        expandBtn.addEventListener('click', function() {
          console.log('Expand button clicked');
          sidebar.classList.remove('collapsed');
          saveSidebarState(false);
          updateMainContentMargin(false);
        });
      }

      // Handle window resize
      window.addEventListener('resize', function() {
        const isCollapsed = sidebar && sidebar.classList.contains('collapsed');
        updateMainContentMargin(isCollapsed);
      });
    });
  </script>
</body>
</html>
