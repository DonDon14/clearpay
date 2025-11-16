<div class="header-content">
  <div class="header-left">
    <div class="page-header-info">
      <h1 class="page-title"><?= esc($pageTitle ?? 'Super Admin Portal') ?></h1>
      <p class="page-subtitle"><?= esc($pageSubtitle ?? 'Manage officer approvals and system administration') ?></p>
    </div>
  </div>

  <div class="header-right">
    <!-- User Menu -->
    <div class="user-menu">
      <button class="user-menu-btn" id="userMenuBtn">
        <div class="user-avatar">
          <div class="avatar-circle">
            <?php if (session()->get('super-admin-profile-picture')): ?>
              <?php 
              $headerProfilePic = session()->get('super-admin-profile-picture');
              $headerPicUrl = (strpos($headerProfilePic, 'res.cloudinary.com') !== false) 
                  ? $headerProfilePic 
                  : base_url($headerProfilePic);
              ?>
              <img src="<?= $headerPicUrl ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            <?php else: ?>
              <i class="fas fa-user-shield"></i>
            <?php endif; ?>
          </div>
          <div class="status-indicator"></div>
        </div>
        <div class="user-info-preview">
          <span class="user-name"><?= esc(session()->get('super-admin-name') ?? session()->get('super-admin-username') ?? 'Super Admin') ?></span>
          <span class="user-role">Super Administrator</span>
        </div>
        <i class="fas fa-chevron-down dropdown-arrow"></i>
      </button>
      
      <!-- Enhanced Dropdown Menu -->
      <div class="user-dropdown" id="userDropdown">
        <div class="user-profile-info">
          <div class="user-avatar-large">
            <?php if (session()->get('super-admin-profile-picture')): ?>
              <?php 
              $headerProfilePic = session()->get('super-admin-profile-picture');
              $headerPicUrl = (strpos($headerProfilePic, 'res.cloudinary.com') !== false) 
                  ? $headerProfilePic 
                  : base_url($headerProfilePic);
              ?>
              <img src="<?= $headerPicUrl ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            <?php else: ?>
              <i class="fas fa-user-shield"></i>
            <?php endif; ?>
          </div>
          <div class="user-details">
            <h4 class="user-full-name"><?= esc(session()->get('super-admin-name') ?? session()->get('super-admin-username') ?? 'Super Admin') ?></h4>
            <p class="user-email"><?= esc(session()->get('super-admin-email') ?? 'superadmin@clearpay.com') ?></p>
            <small class="text-muted" style="display:block;margin-top:-4px;">ID: <?= esc(session()->get('super-admin-id')) ?></small>
            <span class="user-status">
              <span class="status-dot"></span>
              Online
            </span>
          </div>
        </div>
        
        <div class="dropdown-divider-top"></div>
        
        <div class="dropdown-section">
          <a href="<?= base_url('super-admin/logout') ?>" class="dropdown-item logout">
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
    if (userDropdown && !userDropdown.contains(e.target) && userMenuBtn && !userMenuBtn.contains(e.target)) {
      userDropdown.classList.remove('active');
    }
  });

  // Prevent dropdown from closing when clicking inside it
  if (userDropdown) {
    userDropdown.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  }
});
</script>


