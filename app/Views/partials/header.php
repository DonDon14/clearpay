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
      <header class="dropdown-header">
          <h4><?= session('username') ?? 'User' ?></h4>
          <p>administrator@clearpay.com</p>
      </header>
      <main class="dropdown-menu">
        <a href="<?= base_url('profile') ?>" class="dropdown-item">
          <i class="fas fa-user"></i>
          Profile
        </a>
        <a href="<?= base_url('settings') ?>" class="dropdown-item">
          <i class="fas fa-cog"></i>
          Settings
        </a>
        <a href="<?= base_url('help/index.html') ?>" class="dropdown-item" target="_blank">
          <i class="fas fa-question-circle"></i>
          Help & Support
        </a>
        <div class="dropdown-divider"></div>
        <a href="<?= base_url('logout') ?>" class="dropdown-item logout">
          <i class="fas fa-sign-out-alt"></i>
          Logout
        </a>
      </main>
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
