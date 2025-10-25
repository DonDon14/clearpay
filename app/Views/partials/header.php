<div class="header-content">
  <div class="header-left">
    <div class="page-header-info">
      <h1 class="page-title"><?= esc($pageTitle ?? 'Dashboard') ?></h1>
      <p class="page-subtitle"><?= esc($pageSubtitle ?? 'Welcome back to your ClearPay dashboard') ?></p>
    </div>
  </div>

  <div class="header-right">
    <!-- Search Bar -->
    <div class="search-container">
      <div class="search-wrapper">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-input" placeholder="Search anything...">
        <div class="search-shortcut">
          <span>Ctrl+K</span>
        </div>
      </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="header-actions">
      <!-- Notifications -->
      <div class="notification-center">
        <button class="notification-btn modern-btn" id="notificationBtn" title="Notifications">
          <i class="fas fa-bell"></i>
          <span class="notification-count">3</span>
          <div class="notification-pulse"></div>
        </button>
      </div>
      
      <!-- Quick Settings -->
      <div class="quick-settings">
        <button class="settings-btn modern-btn" title="Quick Settings">
          <i class="fas fa-cog"></i>
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
          <span class="user-name"><?= session('username') ?? 'admin' ?></span>
          <span class="user-role">Administrator</span>
        </div>
        <i class="fas fa-chevron-down dropdown-arrow"></i>
      </button>
      
      <!-- Enhanced Dropdown Menu -->
      <div class="user-dropdown" id="userDropdown">
        <div class="dropdown-header">
          <div class="user-profile-info">
            <div class="user-avatar-large">
              <i class="fas fa-user"></i>
            </div>
            <div class="user-details">
              <h4 class="user-full-name"><?= session('username') ?? 'Administrator' ?></h4>
              <p class="user-email">administrator@clearpay.com</p>
              <span class="user-status">
                <span class="status-dot"></span>
                Online
              </span>
            </div>
          </div>
        </div>
        
        <div class="dropdown-menu">
          <div class="dropdown-section">
            <a href="<?= base_url('profile') ?>" class="dropdown-item">
              <div class="item-icon">
                <i class="fas fa-user"></i>
              </div>
              <div class="item-content">
                <span class="item-title">My Profile</span>
                <span class="item-desc">View and edit profile</span>
              </div>
            </a>
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
      console.log('User menu clicked'); // Debug log
      console.log('Dropdown element:', userDropdown); // Debug log
      console.log('Dropdown classes before toggle:', userDropdown.className); // Debug log
      userDropdown.classList.toggle('active');
      console.log('Dropdown classes after toggle:', userDropdown.className); // Debug log
      console.log('Dropdown computed styles:', window.getComputedStyle(userDropdown)); // Debug log
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
