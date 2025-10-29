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
<body class="sidebar-loading">
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
    // Define base URL globally for all pages
    window.APP_BASE_URL = '<?= base_url() ?>';
    
    // Function to update payment requests notification badge
    window.updatePaymentRequestsBadge = function() {
      fetch(`${window.APP_BASE_URL}/admin/dashboard/pending-payment-requests-count`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        const badge = document.getElementById('paymentRequestsBadge');
        if (badge) {
          if (data.success) {
            const count = data.count || 0;
            if (count > 0) {
              badge.textContent = count;
              badge.style.setProperty('display', 'flex', 'important');
              badge.style.setProperty('opacity', '1', 'important');
              badge.style.setProperty('visibility', 'visible', 'important');
              badge.style.background = '#ef4444';
              badge.style.color = 'white';
            } else {
              badge.textContent = '';
              badge.style.setProperty('display', 'none', 'important');
              badge.style.setProperty('opacity', '0', 'important');
              badge.style.setProperty('visibility', 'hidden', 'important');
            }
          } else {
            // Hide badge if request failed
            badge.textContent = '';
            badge.style.setProperty('display', 'none', 'important');
            badge.style.setProperty('opacity', '0', 'important');
            badge.style.setProperty('visibility', 'hidden', 'important');
          }
        }
      })
      .catch(error => {
        console.error('Error fetching payment requests count:', error);
        // Hide badge on error
        const badge = document.getElementById('paymentRequestsBadge');
        if (badge) {
          badge.textContent = '';
          badge.style.setProperty('display', 'none', 'important');
          badge.style.setProperty('opacity', '0', 'important');
          badge.style.setProperty('visibility', 'hidden', 'important');
        }
      });
    };
    
    // Global function to refresh badge (can be called from other pages)
    window.refreshPaymentRequestsBadge = function() {
      updatePaymentRequestsBadge();
    };
    
    // Update badge on page load and set up auto-refresh
    document.addEventListener('DOMContentLoaded', function() {
      updatePaymentRequestsBadge();
      
      // Auto-refresh badge every 30 seconds to keep it in sync
      setInterval(updatePaymentRequestsBadge, 30000);
    });
    
    // Sidebar Toggle Script with State Persistence
    document.addEventListener('DOMContentLoaded', function() {
      const toggleBtn = document.getElementById('sidebarToggleBtn');
      const sidebarLogo = document.getElementById('sidebarLogo');
      const sidebar = document.querySelector('.sidebar');
      const mainContent = document.querySelector('.main-content');

      // Function to save sidebar state
      function saveSidebarState(isCollapsed) {
        localStorage.setItem('sidebarCollapsed', isCollapsed);
      }

      // Function to update main content margin
      function updateMainContentMargin(isCollapsed) {
        if (mainContent) {
          if (window.innerWidth > 768) { // Only adjust margin on desktop
            mainContent.style.marginLeft = isCollapsed ? '72px' : '260px';
          }
        }
      }

      // Function to expand sidebar
      function expandSidebar() {
        if (sidebar) {
          sidebar.classList.remove('collapsed');
          saveSidebarState(false);
          updateMainContentMargin(false);
        }
      }

      // Function to collapse sidebar
      function collapseSidebar() {
        if (sidebar) {
          sidebar.classList.add('collapsed');
          saveSidebarState(true);
          updateMainContentMargin(true);
        }
      }

      // Function to restore sidebar state
      function restoreSidebarState() {
        // Check if server requested forced expansion (e.g., after login)
        const forceExpand = <?= session()->get('forceSidebarExpanded') ? 'true' : 'false' ?>;
        
        if (forceExpand) {
          // Force expand sidebar and clear the flag
          localStorage.removeItem('sidebarCollapsed');
          if (sidebar) {
            sidebar.classList.remove('collapsed');
            updateMainContentMargin(false);
          }
          
          // Clear the session flag after using it
          fetch('<?= base_url('clearSidebarFlag') ?>', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
          });
        } else {
          // Normal state restoration
          const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
          if (isCollapsed && sidebar) {
            sidebar.classList.add('collapsed');
            updateMainContentMargin(true);
          } else {
            updateMainContentMargin(false);
          }
        }
        
        // Remove loading class after state is restored to enable transitions
        document.body.classList.remove('sidebar-loading');
      }

      // Restore sidebar state on page load
      restoreSidebarState();

      // Toggle button (collapse/expand)
      if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function(e) {
          e.stopPropagation();
          sidebar.classList.toggle('collapsed');
          const isCollapsed = sidebar.classList.contains('collapsed');
          saveSidebarState(isCollapsed);
          updateMainContentMargin(isCollapsed);
        });
      }

      // Logo click handler - expand if collapsed
      if (sidebarLogo && sidebar) {
        sidebarLogo.addEventListener('click', function(e) {
          // Check if sidebar is collapsed
          if (sidebar.classList.contains('collapsed')) {
            // Expand sidebar and prevent navigation
            e.preventDefault();
            e.stopPropagation();
            expandSidebar();
          }
          // If not collapsed, let the default link behavior proceed (navigate to dashboard)
        });
      }

      // Handle window resize
      window.addEventListener('resize', function() {
        const isCollapsed = sidebar && sidebar.classList.contains('collapsed');
        updateMainContentMargin(isCollapsed);
      });
    });
      </script>
  
  <!-- jQuery (required for DataTables and other functionality) -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  
  <!-- DataTables CSS and JS -->
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
  <script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
  
  <!-- jsQR Library for QR Code Scanning -->
  <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
  
  <!-- Phone Number Validation Helper -->
  <script src="<?= base_url('js/phone-helper.js') ?>"></script>
  
  <!-- Bootstrap JavaScript Bundle (required for modals) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Profile Modal (included at body level to avoid overflow issues) -->
  <?= view('partials/modal-profile') ?>
  
  <script>
    // Global notification function
    function showNotification(message, type = 'info') {
      // Create notification element
      const notification = document.createElement('div');
      notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
      notification.style.cssText = 'top: 20px; right: 20px; z-index: 10000; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
      notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `;
      
      // Append to body
      document.body.appendChild(notification);
      
      // Auto-remove after 5 seconds
      setTimeout(() => {
        notification.remove();
      }, 5000);
    }
  </script>
  
  <!-- Render page-specific scripts -->
  <?= $this->renderSection('scripts') ?>
</body>
</html>
