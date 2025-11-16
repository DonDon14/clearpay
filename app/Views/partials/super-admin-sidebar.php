    <div class="sidebar-inner">
        <div class="sidebar-header">
            <a href="<?= base_url('super-admin/portal') ?>" class="logo" id="sidebarLogo" data-hover-text="Open sidebar" style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
                <img src="<?= base_url('uploads/logo.png') ?>" alt="ClearPay Logo" style="width: 32px; height: 32px; object-fit: contain;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                <i class="fas fa-credit-card" style="display: none;"></i>
                <span class="logo-text">ClearPay</span>
            </a>
            <!-- Toggle button for normal state -->
            <button class="sidebar-toggle-btn" id="sidebarToggleBtn">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <div class="sidebar-content">
            <ul class="sidebar-menu">
                <li>
                    <a href="<?= base_url('super-admin/portal') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Super Admin Portal') ? 'active' : '' ?>" data-tooltip="Super Admin Portal">
                        <i class="fas fa-shield-alt"></i> 
                        <span class="menu-text">Super Admin Portal</span>
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('super-admin/user-activity-history') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'User Activity History') ? 'active' : '' ?>" data-tooltip="User Activity History">
                        <i class="fas fa-history"></i> 
                        <span class="menu-text">Activity History</span>
                    </a>
                </li>
                <li><hr></li>
                <li>
                    <a href="<?= base_url('super-admin/logout') ?>" class="sidebar-item" data-tooltip="Logout">
                        <i class="fas fa-sign-out-alt"></i> 
                        <span class="menu-text">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>


