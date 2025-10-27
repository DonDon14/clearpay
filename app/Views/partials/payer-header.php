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
          <span class="notification-count">0</span>
          <div class="notification-pulse"></div>
        </button>
      </div>
    </div>
    
    <!-- User Menu -->
    <div class="user-menu">
      <button class="user-menu-btn" id="userMenuBtn">
        <div class="user-avatar">
          <div class="avatar-circle">
            <i class="fas fa-user"></i>
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
            <i class="fas fa-user"></i>
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

  // Notification toggle (if needed)
  const notificationBtn = document.getElementById('notificationBtn');
  if (notificationBtn) {
    notificationBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      // Add notification dropdown logic here
    });
  }
});
</script>
