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
          <span class="notification-count">3</span>
          <div class="notification-pulse"></div>
        </button>
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
