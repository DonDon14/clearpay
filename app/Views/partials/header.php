<div class="header-content">
  <div class="header-left">
    <div class="page-header-info">
      <h1 class="page-title"><?= esc($pageTitle ?? 'Dashboard') ?></h1>
      <p class="page-subtitle"><?= esc($pageSubtitle ?? 'Welcome back to your ClearPay dashboard') ?></p>
    </div>
  </div>

  <div class="header-right">
    <!-- Quick Actions -->
    <div class="header-actions">
      <!-- Notifications -->
      <div class="notification-center">
        <button class="notification-btn modern-btn" id="notificationBtn" title="Notifications">
          <i class="fas fa-bell"></i>
          <span class="notification-count" id="notificationBadge" style="display: none;">0</span>
          <div class="notification-pulse"></div>
        </button>
        
        <!-- Notification Dropdown -->
        <div class="notification-dropdown" id="notificationDropdown">
          <div class="dropdown-header">
            <h6 class="mb-0">
              <i class="fas fa-bell me-2"></i>Notifications
            </h6>
            <button class="btn-close btn-close-sm" onclick="closeAdminNotificationDropdown()"></button>
          </div>
          
          <div class="dropdown-content" id="notificationContent">
            <!-- Dynamic notification items will be inserted here -->
            <div class="no-notifications" id="noNotifications">
              <div class="text-center py-3">
                <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                <p class="text-muted mb-0">No new notifications</p>
              </div>
            </div>
          </div>
          
          <div class="dropdown-footer">
            <button class="btn btn-sm btn-primary w-100" onclick="showAllAdminNotificationsModal()">
              <i class="fas fa-list me-1"></i>View All Notifications
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- User Menu -->
    <div class="user-menu">
      <button class="user-menu-btn" id="userMenuBtn">
        <div class="user-avatar">
          <div class="avatar-circle">
            <?php if (session('profile_picture')): ?>
              <img src="<?= base_url(session('profile_picture')) ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            <?php else: ?>
              <i class="fas fa-user"></i>
            <?php endif; ?>
          </div>
          <div class="status-indicator"></div>
        </div>
        <div class="user-info-preview">
          <span class="user-name"><?= session('username') ?? 'admin' ?></span>
          <span class="user-role">Administrator</span>
        </div>
        <i class="fas fa-chevron-down dropdown-arrow"></i>
      </button>
      
      <!-- Enhanced Dropdown Menu - Unified -->
      <div class="user-dropdown" id="userDropdown">
        <div class="user-profile-info">
          <div class="user-avatar-large">
            <?php if (session('profile_picture')): ?>
              <img src="<?= base_url(session('profile_picture')) ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            <?php else: ?>
              <i class="fas fa-user"></i>
            <?php endif; ?>
          </div>
          <div class="user-details">
            <h4 class="user-full-name"><?= session('name') ?? session('username') ?? 'Administrator' ?></h4>
            <p class="user-email"><?= session('email') ?? 'administrator@clearpay.com' ?></p>
            <small class="text-muted" style="display:block;margin-top:-4px;">ID: <?= esc(session('user-id')) ?></small>
            <script>
              // Make admin user ID available to JavaScript
              window.ADMIN_USER_ID = <?= session('user-id') ?? 0 ?>;
            </script>
            <span class="user-status">
              <span class="status-dot"></span>
              Online
            </span>
          </div>
        </div>
        
        <div class="dropdown-divider-top"></div>
        
        <div class="dropdown-section">
          <button type="button" class="dropdown-item" onclick="openProfileModal()">
            <div class="item-icon">
              <i class="fas fa-user"></i>
            </div>
            <div class="item-content">
              <span class="item-title">My Profile</span>
              <span class="item-desc">View and edit profile</span>
            </div>
          </button>
          <a href="<?= base_url('settings') ?>" class="dropdown-item">
            <div class="item-icon">
              <i class="fas fa-cog"></i>
            </div>
            <div class="item-content">
              <span class="item-title">Account Settings</span>
              <span class="item-desc">Manage preferences</span>
            </div>
          </a>
          <a href="<?= base_url('help/index.html') ?>" class="dropdown-item" target="_blank">
            <div class="item-icon">
              <i class="fas fa-question-circle"></i>
            </div>
            <div class="item-content">
              <span class="item-title">Help & Support</span>
              <span class="item-desc">Get assistance</span>
            </div>
          </a>
        </div>
        
        <div class="dropdown-divider"></div>
        
        <div class="dropdown-section">
          <a href="<?= base_url('logout') ?>" class="dropdown-item logout">
            <div class="item-icon">
              <i class="fas fa-sign-out-alt"></i>
            </div>
            <div class="item-content">
              <span class="item-title">Sign Out</span>
              <span class="item-desc">Logout from account</span>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // User menu toggle
  // User menu toggle
  const userMenuBtn = document.getElementById('userMenuBtn');
  const userDropdown = document.getElementById('userDropdown');
  
  if (userMenuBtn && userDropdown) {
    userMenuBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      // Calculate position
      const rect = userMenuBtn.getBoundingClientRect();
      const isOpen = userDropdown.classList.contains('active');
      
      if (!isOpen) {
        // Position dropdown
        userDropdown.style.top = (rect.bottom + 12) + 'px';
        userDropdown.style.right = '20px';
      }
      
      userDropdown.classList.toggle('active');
    });
  }

  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    if (userDropdown && !userDropdown.contains(e.target) && !userMenuBtn.contains(e.target)) {
      userDropdown.classList.remove('active');
    }
  });

  // Prevent dropdown from closing when clicking inside it
  if (userDropdown) {
    userDropdown.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  }

  // Notification dropdown toggle
  const notificationBtn = document.getElementById('notificationBtn');
  const notificationDropdown = document.getElementById('notificationDropdown');
  
  if (notificationBtn && notificationDropdown) {
    notificationBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      // Mark all notifications as seen (Facebook-like behavior)
      // This clears the badge but keeps unread dots on individual items
      if (typeof markAllAdminAsSeen === 'function') {
        markAllAdminAsSeen();
      }
      
      // Ensure data is loaded before opening dropdown
      if (typeof adminNotificationDataLoaded !== 'undefined' && !adminNotificationDataLoaded) {
        console.log('Data not loaded yet, loading...');
        if (typeof checkForNewAdminActivities === 'function') {
          checkForNewAdminActivities();
        }
        
        // If data still not loaded after 1 second, open dropdown anyway
        setTimeout(() => {
          if (typeof adminNotificationDataLoaded !== 'undefined' && !adminNotificationDataLoaded) {
            console.log('Data still loading, opening dropdown anyway');
            adminNotificationDataLoaded = true; // Force open
            if (typeof updateAdminNotificationDropdown === 'function') {
              updateAdminNotificationDropdown([]);
            }
          }
        }, 1000);
      }
      
      // Calculate position
      const rect = notificationBtn.getBoundingClientRect();
      const isOpen = notificationDropdown.classList.contains('active');
      
      if (!isOpen) {
        // Position dropdown
        notificationDropdown.style.top = (rect.bottom + 12) + 'px';
        notificationDropdown.style.right = '20px';
      }
      
      notificationDropdown.classList.toggle('active');
    });
  }

  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    if (notificationDropdown && !notificationDropdown.contains(e.target) && notificationBtn && !notificationBtn.contains(e.target)) {
      notificationDropdown.classList.remove('active');
    }
  });

  // Prevent dropdown from closing when clicking outside, but allow clicks inside
  // Don't stop propagation for notification items - they need to handle their own clicks
  if (notificationDropdown) {
    notificationDropdown.addEventListener('click', function(e) {
      // Only stop propagation if clicking on the dropdown header/footer (not on notification items)
      if (!e.target.closest('.notification-item') && !e.target.closest('.dropdown-content')) {
        e.stopPropagation();
      }
    });
    
    // Set up event delegation for notification item clicks ONCE when page loads
    // This works because we're attaching to the dropdown-content element which persists
    const notificationContent = notificationDropdown.querySelector('.dropdown-content');
    if (notificationContent) {
      notificationContent.addEventListener('click', function(e) {
        const notificationItem = e.target.closest('.notification-item[data-activity-id]');
        if (notificationItem) {
          e.preventDefault();
          e.stopPropagation();
          
          const activityId = parseInt(notificationItem.getAttribute('data-activity-id'));
          let activityType = notificationItem.getAttribute('data-activity-type');
          const entityId = notificationItem.getAttribute('data-entity-id');
          
          console.log('Notification item clicked via delegation in header:', {
            activityId: activityId,
            activityType: activityType,
            entityId: entityId,
            rawActivityId: notificationItem.getAttribute('data-activity-id'),
            rawActivityType: notificationItem.getAttribute('data-activity-type'),
            rawEntityId: notificationItem.getAttribute('data-entity-id'),
            allDataAttributes: Array.from(notificationItem.attributes).map(attr => ({ name: attr.name, value: attr.value }))
          });
          
          // If activityType is missing or invalid, try to get from click-url or current activities
          if (!activityType || activityType === 'null' || activityType === 'unknown' || activityType === 'undefined') {
            console.warn('Activity type missing or invalid, attempting to recover');
            
            // Try to get from current activities if available
            if (typeof currentAdminActivities !== 'undefined' && currentAdminActivities && currentAdminActivities.length > 0) {
              const activity = currentAdminActivities.find(a => a.id == activityId || a.id === activityId);
              if (activity) {
                activityType = activity.activity_type || activity.entity_type || activity.type || null;
                console.log('Recovered activity type from current activities:', activityType);
              }
            }
            
            // Try to infer from click URL if available
            const clickUrl = notificationItem.getAttribute('data-click-url');
            if (!activityType && clickUrl) {
              const url = new URL(clickUrl, window.location.origin);
              const path = url.pathname;
              
              if (path.includes('payment-requests')) {
                activityType = 'payment_request';
              } else if (path.includes('refunds')) {
                activityType = 'refund';
              } else if (path.includes('payments')) {
                activityType = 'payment';
              } else if (path.includes('contributions')) {
                activityType = 'contribution';
              } else if (path.includes('announcements')) {
                activityType = 'announcement';
              } else if (path.includes('payers')) {
                activityType = 'payer';
              } else if (path.includes('users')) {
                activityType = 'user';
              }
              
              console.log('Inferred activity type from click URL:', activityType);
            }
          }
          
          if (activityId) {
            if (typeof handleAdminNotificationClick === 'function') {
              handleAdminNotificationClick(activityId, activityType, entityId && entityId !== 'null' && entityId !== 'undefined' ? parseInt(entityId) : null);
            } else {
              console.error('handleAdminNotificationClick function not found');
              // Fallback: redirect based on activity type or click URL
              const clickUrl = notificationItem.getAttribute('data-click-url');
              if (clickUrl && clickUrl !== 'null' && clickUrl !== 'undefined') {
                console.log('Fallback redirect using click URL:', clickUrl);
                window.location.href = clickUrl;
              } else if (activityType && activityType !== 'null' && activityType !== 'unknown') {
                const normalizedType = activityType.toLowerCase().trim();
                let redirectUrl = `${window.APP_BASE_URL || ''}dashboard`;
                
                if (normalizedType === 'payment_request' || normalizedType === 'paymentrequest' || normalizedType === 'payment-request') {
                  redirectUrl = `${window.APP_BASE_URL || ''}payment-requests`;
                } else if (normalizedType === 'refund') {
                  redirectUrl = `${window.APP_BASE_URL || ''}refunds#history`;
                }
                
                console.log('Fallback redirect to:', redirectUrl);
                window.location.href = redirectUrl;
              } else {
                console.error('Cannot determine redirect URL - missing both activity type and click URL');
              }
            }
          } else {
            console.error('Invalid notification click - missing activityId:', activityId, 'activityType:', activityType);
          }
        }
      });
    }
  }
});
</script>

<style>
/* Notification Dropdown Styles */
.notification-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    width: 380px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    max-height: 500px;
    overflow: visible;
    margin-top: 12px;
}

.notification-dropdown.active {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-header {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
    border-radius: 12px 12px 0 0;
}

.dropdown-header h6 {
    color: #374151;
    font-weight: 600;
    margin: 0;
}

.dropdown-content {
    max-height: 250px;
    overflow-y: auto;
    padding: 0.5rem;
}

.notification-item {
    display: flex;
    align-items: flex-start;
    padding: 0.75rem;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    transition: background-color 0.2s;
    cursor: pointer;
    position: relative;
    min-height: 60px;
}

.notification-item:hover {
    background: #f8f9fa;
}

.notification-item.unread-notification {
    background-color: #f0f9ff;
    border-left: 3px solid #3b82f6;
}

.notification-icon {
    width: 40px;
    height: 40px;
    background: #e3f2fd;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.notification-body {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.25rem;
    line-height: 1.3;
}

.notification-text {
    font-size: 0.8rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
    line-height: 1.4;
    word-wrap: break-word;
    word-break: break-word;
}

.notification-time {
    font-size: 0.75rem;
    color: #9ca3af;
}

.unread-indicator {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 8px;
    height: 8px;
    background-color: #dc3545;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 1px #e5e7eb;
}

.dropdown-footer {
    padding: 1rem;
    border-top: 1px solid #e5e7eb;
    background: #f8f9fa;
    border-radius: 0 0 12px 12px;
}

.no-notifications {
    padding: 1rem;
    text-align: center;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .notification-dropdown {
        width: 320px;
        right: -40px;
    }
    
    .notification-item {
        padding: 0.5rem;
        min-height: 50px;
    }
    
    .notification-text {
        font-size: 0.75rem;
    }
}
</style>
