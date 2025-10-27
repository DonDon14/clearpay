<div class="header-content">
  <div class="header-left">
    <div class="page-header-info">
      <h1 class="page-title"><?= esc($pageTitle ?? 'Dashboard') ?></h1>
      <p class="page-subtitle"><?= esc($pageSubtitle ?? 'Welcome back to ClearPay') ?></p>
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
                   <button class="btn-close btn-close-sm" onclick="closeNotificationDropdown()"></button>
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
                   <button class="btn btn-sm btn-primary w-100" onclick="showAllNotificationsModal()">
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
            <?php if (isset($payerData) && !empty($payerData['profile_picture'])): ?>
              <img src="<?= base_url($payerData['profile_picture']) ?>" 
                   alt="Profile Picture" 
                   class="avatar-image">
            <?php else: ?>
              <i class="fas fa-user"></i>
            <?php endif; ?>
          </div>
          <div class="status-indicator"></div>
        </div>
        <div class="user-info-preview">
          <span class="user-name"><?= session('payer_name') ?? 'Payer' ?></span>
          <span class="user-role">Payer</span>
        </div>
        <i class="fas fa-chevron-down dropdown-arrow"></i>
      </button>
      
      <!-- Enhanced Dropdown Menu - Unified -->
      <div class="user-dropdown" id="userDropdown">
        <div class="user-profile-info">
          <div class="user-avatar-large">
            <?php if (isset($payerData) && !empty($payerData['profile_picture'])): ?>
              <img src="<?= base_url($payerData['profile_picture']) ?>" 
                   alt="Profile Picture" 
                   class="avatar-image-large">
            <?php else: ?>
              <i class="fas fa-user"></i>
            <?php endif; ?>
          </div>
          <div class="user-details">
            <h4 class="user-full-name"><?= session('payer_name') ?? 'Payer' ?></h4>
            <p class="user-email"><?= session('payer_email') ?? 'payer@clearpay.com' ?></p>
            <span class="user-status">
              <span class="status-dot"></span>
              Online
            </span>
          </div>
        </div>
        
        <div class="dropdown-divider-top"></div>
        
                 <div class="dropdown-section">
           <a href="<?= base_url('payer/my-data') ?>" class="dropdown-item">
             <div class="item-icon">
               <i class="fas fa-user"></i>
             </div>
             <div class="item-content">
               <span class="item-title">My Data</span>
               <span class="item-desc">View your information</span>
             </div>
           </a>
           <a href="<?= base_url('payer/contributions') ?>" class="dropdown-item">
             <div class="item-icon">
               <i class="fas fa-hand-holding-usd"></i>
             </div>
             <div class="item-content">
               <span class="item-title">Contributions</span>
               <span class="item-desc">View active contributions</span>
             </div>
           </a>
           <a href="<?= base_url('payer/payment-history') ?>" class="dropdown-item">
             <div class="item-icon">
               <i class="fas fa-history"></i>
             </div>
             <div class="item-content">
               <span class="item-title">Payment History</span>
               <span class="item-desc">View all transactions</span>
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
          <a href="<?= base_url('payer/logout') ?>" class="dropdown-item logout">
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
             if (typeof markAllAsSeen === 'function') {
               markAllAsSeen();
             }
             
             // Ensure data is loaded before opening dropdown
             if (typeof notificationDataLoaded !== 'undefined' && !notificationDataLoaded) {
               console.log('Data not loaded yet, loading...');
               if (typeof checkForNewActivities === 'function') {
                 checkForNewActivities();
               }
               
               // If data still not loaded after 1 second, open dropdown anyway
               setTimeout(() => {
                 if (typeof notificationDataLoaded !== 'undefined' && !notificationDataLoaded) {
                   console.log('Data still loading, opening dropdown anyway');
                   notificationDataLoaded = true; // Force open
                   if (typeof updateNotificationDropdown === 'function') {
                     updateNotificationDropdown([]);
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
         
         // Close notification dropdown when clicking outside
         document.addEventListener('click', function(e) {
           if (notificationDropdown && !notificationDropdown.contains(e.target) && !notificationBtn.contains(e.target)) {
             notificationDropdown.classList.remove('active');
           }
         });
         
         // Prevent notification dropdown from closing when clicking inside it
         if (notificationDropdown) {
           notificationDropdown.addEventListener('click', function(e) {
             e.stopPropagation();
           });
         }
       });
       </script>
       
       <style>
/* Avatar Image Styles */
.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.avatar-image-large {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

/* Ensure proper sizing for avatar circles */
.avatar-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
}

.user-avatar-large {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
  margin-right: 1rem;
  flex-shrink: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .avatar-circle {
    width: 35px;
    height: 35px;
  }
  
  .user-avatar-large {
    width: 45px;
    height: 45px;
  }
}
</style>

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
         hyphens: auto;
         max-height: 2.8em;
         overflow: hidden;
         display: -webkit-box;
         -webkit-line-clamp: 2;
         -webkit-box-orient: vertical;
       }
       
       .notification-time {
         font-size: 0.75rem;
         color: #9ca3af;
       }
       
       .notification-unread-badge {
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
       
       .notification-action {
         margin-left: 0.5rem;
         flex-shrink: 0;
       }
       
       .dropdown-footer {
         padding: 1rem;
         border-top: 1px solid #e5e7eb;
         background: #f8f9fa;
         border-radius: 0 0 12px 12px;
       }
       
       .no-notifications {
         padding: 1rem;
       }
       
       /* Notification Badge Animation */
       .notification-count {
         animation: pulse-badge 2s infinite;
       }
       
       @keyframes pulse-badge {
         0% { transform: scale(1); }
         50% { transform: scale(1.1); }
         100% { transform: scale(1); }
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
           max-height: 2.4em;
         }
       }
       </style>
