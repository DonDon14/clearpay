<div class="header-left">
  <h1 class="page-title"><?= esc($pageTitle ?? 'Dashboard') ?></h1>
  <p class="page-subtitle"><?= esc($pageSubtitle ?? 'Welcome back to your ClearPay dashboard') ?></p>
</div>

<div class="header-right">
  <!-- Search Bar -->
  <div class="search-container">
    <i class="fas fa-search"></i>
    <input type="text" class="search-input" placeholder="Search...">
  </div>
  
  <!-- Notifications -->
  <div class="notification-center">
    <button class="notification-btn" id="notificationBtn">
      <i class="fas fa-bell"></i>
      <span class="notification-count">3</span>
    </button>
  </div>
  
  <!-- User Menu -->
  <div class="user-menu">
    <button class="user-menu-btn" id="userMenuBtn">
      <div class="user-avatar">
        <i class="fas fa-user"></i>
      </div>
      <div class="user-info-preview">
        <span class="user-name"><?= session('username') ?? 'User' ?></span>
        <span class="user-role">Administrator</span>
      </div>
      <i class="fas fa-chevron-down"></i>
    </button>
    
    <!-- Dropdown Menu -->
    <div class="user-dropdown" id="userDropdown">
      <div class="dropdown-header">
        <div class="user-info">
          <h4><?= session('username') ?? 'User' ?></h4>
          <p>administrator@clearpay.com</p>
        </div>
      </div>
      <div class="dropdown-menu">
        <a href="#" class="dropdown-item">
          <i class="fas fa-user"></i>
          Profile
        </a>
        <a href="#" class="dropdown-item">
          <i class="fas fa-cog"></i>
          Settings
        </a>
        <a href="#" class="dropdown-item">
          <i class="fas fa-question-circle"></i>
          Help & Support
        </a>
        <div class="dropdown-divider"></div>
        <a href="<?= base_url('logout') ?>" class="dropdown-item logout">
          <i class="fas fa-sign-out-alt"></i>
          Logout
        </a>
      </div>
    </div>
  </div>
</div>

<script>
// User menu toggle
document.getElementById('userMenuBtn').addEventListener('click', function(e) {
  e.stopPropagation();
  const dropdown = document.getElementById('userDropdown');
  dropdown.classList.toggle('active');
});

// Close dropdown when clicking outside
document.addEventListener('click', function() {
  const dropdown = document.getElementById('userDropdown');
  dropdown.classList.remove('active');
});

// Notification toggle (if needed)
document.getElementById('notificationBtn')?.addEventListener('click', function(e) {
  e.stopPropagation();
  // Add notification dropdown logic here
});
</script>
