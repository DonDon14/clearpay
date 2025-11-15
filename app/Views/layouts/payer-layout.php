<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Payer Dashboard') ?></title>
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="<?= base_url('favicon.png') ?>">
  <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
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
      <?= $this->include('partials/payer-sidebar') ?>
    </aside>
    
    <!-- Main Content Area -->
    <div class="main-content">
      <!-- Header -->
      <header class="header">
        <?php 
        // Get payer data for header from session
        $payerData = [
            'profile_picture' => session('payer_profile_picture'),
            'student_id' => session('payer_student_id')
        ];
        
        // Debug: Check session data
        $payerId = session('payer_id');
        $profilePicture = session('payer_profile_picture');
        
        // Always sync profile picture from database to ensure we have the latest value
        // This is especially important after Flutter app uploads
        if ($payerId) {
            $payerModel = new \App\Models\PayerModel();
            $payer = $payerModel->find($payerId);
            
            // Update student_id from database if needed
            if ($payer && !empty($payer['payer_id'])) {
                $payerData['student_id'] = $payer['payer_id'];
                // Ensure session has public-facing ID for easy access
                session()->set('payer_student_id', $payer['payer_id']);
            }
            
            // Sync profile picture from database (always check to ensure latest value)
            if ($payer && !empty($payer['profile_picture'])) {
                $path = $payer['profile_picture'];
                
                // Check if it's a Cloudinary URL first
                $cloudinaryService = new \App\Services\CloudinaryService();
                if ($cloudinaryService->isCloudinaryUrl($path)) {
                    // It's a Cloudinary URL - use it directly
                    $payerData['profile_picture'] = $path;
                    // Update session for future requests (only if different from current session)
                    if ($profilePicture !== $path) {
                        session()->set('payer_profile_picture', $path);
                        log_message('info', 'Synced Cloudinary profile picture URL from database for payer ID: ' . $payerId);
                    }
                } else {
                    // It's a local file path - normalize and verify it exists
                    // Remove any base_url or http prefixes
                    $normalizedPath = preg_replace('#^https?://[^/]+/#', '', $path);
                    $normalizedPath = preg_replace('#^uploads/profile/#', '', $normalizedPath);
                    $normalizedPath = preg_replace('#^profile/#', '', $normalizedPath);
                    $filename = basename($normalizedPath);
                    
                    // Verify file exists
                    $filePath = FCPATH . 'uploads/profile/' . $filename;
                    if (file_exists($filePath)) {
                        $finalPath = 'uploads/profile/' . $filename;
                        $payerData['profile_picture'] = $finalPath;
                        // Update session for future requests (only if different from current session)
                        if ($profilePicture !== $finalPath) {
                            session()->set('payer_profile_picture', $finalPath);
                        }
                    } else {
                        log_message('warning', 'Profile picture not found in layout: ' . $filePath);
                        
                        // Try to find a similar file for this payer (fallback)
                        $uploadDir = FCPATH . 'uploads/profile/';
                        $pattern = 'payer_' . $payerId . '_*';
                        $files = glob($uploadDir . $pattern);
                        if (!empty($files)) {
                            // Use the most recent file for this payer
                            usort($files, function($a, $b) {
                                return filemtime($b) - filemtime($a);
                            });
                            $foundFile = basename($files[0]);
                            log_message('info', 'Found fallback profile picture in layout: ' . $foundFile . ' for payer ID: ' . $payerId);
                            $finalPath = 'uploads/profile/' . $foundFile;
                            $payerData['profile_picture'] = $finalPath;
                            // Update session for future requests
                            session()->set('payer_profile_picture', $finalPath);
                            
                            // Update database with correct path
                            try {
                                $payerModel->update($payerId, ['profile_picture' => $finalPath]);
                                log_message('info', 'Updated database with correct profile picture path in layout for payer ID: ' . $payerId);
                            } catch (\Exception $e) {
                                log_message('error', 'Failed to update database with correct profile picture path in layout: ' . $e->getMessage());
                            }
                        } else {
                            $payerData['profile_picture'] = null;
                        }
                    }
                }
            } else if ($payer && empty($payer['profile_picture'])) {
                // No profile picture in database - clear session if it exists
                if ($profilePicture !== null) {
                    $payerData['profile_picture'] = null;
                    session()->remove('payer_profile_picture');
                }
            }
        }
        
        // Debug logging
        log_message('info', 'Layout Debug - Payer ID: ' . $payerId);
        log_message('info', 'Layout Debug - Profile Picture from session: ' . ($profilePicture ?? 'null'));
        log_message('info', 'Layout Debug - Final Profile Picture: ' . ($payerData['profile_picture'] ?? 'null'));
        ?>
        <?= $this->include('partials/payer-header', [
          'pageTitle' => $pageTitle ?? 'Dashboard',
          'pageSubtitle' => $pageSubtitle ?? 'Welcome back to ClearPay',
          'payerData' => $payerData,
          'debug_profile_picture' => $payerData['profile_picture'] ?? 'null'
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
      fetch(`${window.APP_BASE_URL}payer/payment-requests/count`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        const badge = document.getElementById('paymentRequestsBadge');
        if (badge) {
          // Check if sidebar is collapsed
          const sidebar = document.querySelector('.sidebar');
          const isCollapsed = sidebar && sidebar.classList.contains('collapsed');
          
          if (data.success) {
            const count = data.count || 0;
            if (count > 0) {
              // If collapsed, show as dot (empty text), otherwise show number
              if (isCollapsed) {
                badge.textContent = '';
                badge.setAttribute('data-count', count); // Store count for when expanded
                // Override inline styles for collapsed state - force small dot
                badge.style.setProperty('width', '10px', 'important');
                badge.style.setProperty('height', '10px', 'important');
                badge.style.setProperty('min-width', '10px', 'important');
                badge.style.setProperty('max-width', '10px', 'important');
                badge.style.setProperty('padding', '0', 'important');
                badge.style.setProperty('margin', '0', 'important');
                badge.style.setProperty('margin-left', '0', 'important');
                badge.style.setProperty('margin-right', '0', 'important');
                badge.style.setProperty('border-radius', '50%', 'important');
                badge.style.setProperty('font-size', '0', 'important');
                badge.style.setProperty('line-height', '0', 'important');
                badge.style.setProperty('display', 'block', 'important');
              } else {
                badge.textContent = count;
                badge.removeAttribute('data-count');
                // Restore expanded styles - remove forced styles
                badge.style.removeProperty('width');
                badge.style.removeProperty('height');
                badge.style.removeProperty('max-width');
              }
              badge.style.setProperty('display', isCollapsed ? 'block' : 'flex', 'important');
              badge.style.setProperty('opacity', '1', 'important');
              badge.style.setProperty('visibility', 'visible', 'important');
            } else {
              badge.textContent = '';
              badge.removeAttribute('data-count');
              badge.style.setProperty('display', 'none', 'important');
              badge.style.setProperty('opacity', '0', 'important');
              badge.style.setProperty('visibility', 'hidden', 'important');
            }
          } else {
            badge.textContent = '';
            badge.removeAttribute('data-count');
            badge.style.setProperty('display', 'none', 'important');
            badge.style.setProperty('opacity', '0', 'important');
            badge.style.setProperty('visibility', 'hidden', 'important');
          }
        }
      })
      .catch(error => {
        console.error('Error fetching payment requests count:', error);
        const badge = document.getElementById('paymentRequestsBadge');
        if (badge) {
          badge.textContent = '';
          badge.removeAttribute('data-count');
          badge.style.setProperty('display', 'none', 'important');
          badge.style.setProperty('opacity', '0', 'important');
          badge.style.setProperty('visibility', 'hidden', 'important');
        }
      });
    };
    
    // Function to update refund requests notification badge
    window.updateRefundRequestsBadge = function() {
      fetch(`${window.APP_BASE_URL}payer/refund-requests/count`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        const badge = document.getElementById('refundRequestsBadge');
        if (badge) {
          // Check if sidebar is collapsed
          const sidebar = document.querySelector('.sidebar');
          const isCollapsed = sidebar && sidebar.classList.contains('collapsed');
          
          if (data.success) {
            const count = data.count || 0;
            if (count > 0) {
              // If collapsed, show as dot (empty text), otherwise show number
              if (isCollapsed) {
                badge.textContent = '';
                badge.setAttribute('data-count', count); // Store count for when expanded
                // Override inline styles for collapsed state - force small dot
                badge.style.setProperty('width', '10px', 'important');
                badge.style.setProperty('height', '10px', 'important');
                badge.style.setProperty('min-width', '10px', 'important');
                badge.style.setProperty('max-width', '10px', 'important');
                badge.style.setProperty('padding', '0', 'important');
                badge.style.setProperty('margin', '0', 'important');
                badge.style.setProperty('margin-left', '0', 'important');
                badge.style.setProperty('margin-right', '0', 'important');
                badge.style.setProperty('border-radius', '50%', 'important');
                badge.style.setProperty('font-size', '0', 'important');
                badge.style.setProperty('line-height', '0', 'important');
                badge.style.setProperty('display', 'block', 'important');
              } else {
                badge.textContent = count;
                badge.removeAttribute('data-count');
                // Restore expanded styles - remove forced styles
                badge.style.removeProperty('width');
                badge.style.removeProperty('height');
                badge.style.removeProperty('max-width');
              }
              badge.style.setProperty('display', isCollapsed ? 'block' : 'flex', 'important');
              badge.style.setProperty('opacity', '1', 'important');
              badge.style.setProperty('visibility', 'visible', 'important');
            } else {
              badge.textContent = '';
              badge.removeAttribute('data-count');
              badge.style.setProperty('display', 'none', 'important');
              badge.style.setProperty('opacity', '0', 'important');
              badge.style.setProperty('visibility', 'hidden', 'important');
            }
          } else {
            badge.textContent = '';
            badge.removeAttribute('data-count');
            badge.style.setProperty('display', 'none', 'important');
            badge.style.setProperty('opacity', '0', 'important');
            badge.style.setProperty('visibility', 'hidden', 'important');
          }
        }
      })
      .catch(error => {
        console.error('Error fetching refund requests count:', error);
        const badge = document.getElementById('refundRequestsBadge');
        if (badge) {
          badge.textContent = '';
          badge.removeAttribute('data-count');
          badge.style.setProperty('display', 'none', 'important');
          badge.style.setProperty('opacity', '0', 'important');
          badge.style.setProperty('visibility', 'hidden', 'important');
        }
      });
    };
    
    // Global function to refresh badges
    window.refreshPaymentRequestsBadge = function() {
      updatePaymentRequestsBadge();
    };
    
    window.refreshRefundRequestsBadge = function() {
      updateRefundRequestsBadge();
    };
    
    // Update badges on page load and set up auto-refresh
    document.addEventListener('DOMContentLoaded', function() {
      updatePaymentRequestsBadge();
      updateRefundRequestsBadge();
      
      // Auto-refresh badges every 30 seconds
      setInterval(updatePaymentRequestsBadge, 30000);
      setInterval(updateRefundRequestsBadge, 30000);
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
          
          // Update badges to show numbers when expanded
          if (typeof updatePaymentRequestsBadge === 'function') {
            updatePaymentRequestsBadge();
          }
          if (typeof updateRefundRequestsBadge === 'function') {
            updateRefundRequestsBadge();
          }
        }
      }

      // Function to collapse sidebar
      function collapseSidebar() {
        if (sidebar) {
          sidebar.classList.add('collapsed');
          saveSidebarState(true);
          updateMainContentMargin(true);
          
          // Update badges to show dots when collapsed
          if (typeof updatePaymentRequestsBadge === 'function') {
            updatePaymentRequestsBadge();
          }
          if (typeof updateRefundRequestsBadge === 'function') {
            updateRefundRequestsBadge();
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
        
        // Remove loading class after state is restored to enable transitions
        document.body.classList.remove('sidebar-loading');
      }

      // Restore sidebar state on page load
      restoreSidebarState();
      
      // Update badges after restoring state
      setTimeout(() => {
        if (typeof updatePaymentRequestsBadge === 'function') {
          updatePaymentRequestsBadge();
        }
        if (typeof updateRefundRequestsBadge === 'function') {
          updateRefundRequestsBadge();
        }
      }, 100);

      // Toggle button (collapse/expand)
      if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function(e) {
          e.stopPropagation();
          sidebar.classList.toggle('collapsed');
          const isCollapsed = sidebar.classList.contains('collapsed');
          saveSidebarState(isCollapsed);
          updateMainContentMargin(isCollapsed);
          
          // Update badges to show dots when collapsed, numbers when expanded
          setTimeout(() => {
            if (typeof updatePaymentRequestsBadge === 'function') {
              updatePaymentRequestsBadge();
            }
            if (typeof updateRefundRequestsBadge === 'function') {
              updateRefundRequestsBadge();
            }
          }, 50);
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
      
      // Enhanced tooltip positioning for collapsed sidebar items
      function setupTooltips() {
        if (!sidebar) return;
        
        // Remove existing tooltip listeners by using a single delegated listener
        const sidebarMenu = sidebar.querySelector('.sidebar-menu');
        if (!sidebarMenu) return;
        
        // Use event delegation - handle mouseenter on menu items
        sidebarMenu.addEventListener('mouseenter', function(e) {
          if (!sidebar.classList.contains('collapsed')) {
            // Remove tooltip if sidebar is expanded
            const existingTooltip = document.querySelector('.collapsed-tooltip');
            if (existingTooltip) {
              existingTooltip.remove();
            }
            return;
          }
          
          const item = e.target.closest('.sidebar-item');
          if (!item) return;
          
          const tooltipText = item.getAttribute('data-tooltip') || '';
          if (!tooltipText) return;
          
          // Remove any existing tooltip
          const existingTooltip = document.querySelector('.collapsed-tooltip');
          if (existingTooltip) {
            existingTooltip.remove();
          }
          
          // Create tooltip element
          const tooltipElement = document.createElement('div');
          tooltipElement.className = 'collapsed-tooltip';
          tooltipElement.textContent = tooltipText;
          document.body.appendChild(tooltipElement);
          
          // Position tooltip to the right of the icon
          const rect = item.getBoundingClientRect();
          requestAnimationFrame(() => {
            const tooltipRect = tooltipElement.getBoundingClientRect();
            tooltipElement.style.left = (rect.right + 12) + 'px';
            tooltipElement.style.top = (rect.top + rect.height / 2 - tooltipRect.height / 2) + 'px';
            tooltipElement.style.opacity = '1';
          });
        }, true);
        
        sidebarMenu.addEventListener('mouseleave', function(e) {
          if (!sidebar.classList.contains('collapsed')) return;
          
          const item = e.target.closest('.sidebar-item');
          if (!item) return;
          
          const tooltipElement = document.querySelector('.collapsed-tooltip');
          if (tooltipElement) {
            tooltipElement.style.opacity = '0';
            setTimeout(() => {
              if (tooltipElement && tooltipElement.parentNode) {
                tooltipElement.parentNode.removeChild(tooltipElement);
              }
            }, 200);
          }
        }, true);
      }
      
      // Setup tooltips
      setupTooltips();
      
      // Re-setup tooltips when sidebar state changes
      if (sidebar) {
        const observer = new MutationObserver(function(mutations) {
          mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
              // Remove any existing tooltip when state changes
              const existingTooltip = document.querySelector('.collapsed-tooltip');
              if (existingTooltip) {
                existingTooltip.remove();
              }
            }
          });
        });
        
        observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
      }
    });
  </script>
  
         <!-- Payer Session Management (must load early) -->
         <script src="<?= base_url('js/payer-session.js') ?>"></script>
         
         <!-- jQuery (required for payment group interactions) -->
         <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
         
         <!-- Bootstrap JavaScript Bundle (required for modals) -->
         <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
         
         <!-- Announcement Notification Modal -->
         <?= $this->include('partials/announcement-notification-modal') ?>
  
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
</body>
</html>
