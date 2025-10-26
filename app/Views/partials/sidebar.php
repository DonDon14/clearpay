<link rel="stylesheet" href="<?= base_url('css/sidebar-complete.css') ?>">

    <div class="sidebar-inner">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-credit-card"></i>
                <span class="logo-text">ClearPay</span>
            </div>
            <!-- Expand button for collapsed state -->
            <button class="sidebar-toggle-btn" id="sidebarToggleBtn">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <div class="sidebar-content">
            <ul class="sidebar-menu">
                <li>
                    <a href="<?= base_url('dashboard') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Dashboard') ? 'active' : '' ?>" data-tooltip="Dashboard">
                        <i class="fas fa-home"></i> 
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('payments') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Payments') ? 'active' : '' ?>" data-tooltip="Payments">
                        <i class="fas fa-money-bill-wave"></i> 
                        <span class="menu-text">Payments</span>
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('contributions') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Contributions') ? 'active' : '' ?>" data-tooltip="Contributions">
                        <i class="fas fa-hand-holding-usd"></i> 
                        <span class="menu-text">Contributions</span>
                    </a>
                </li>
                <li><hr></li>
                <li>
                    <a href="<?= base_url('analytics') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Analytics') ? 'active' : '' ?>" data-tooltip="Analytics">
                        <i class="fas fa-chart-bar"></i> 
                        <span class="menu-text">Analytics</span>
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('payers') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Payers') ? 'active' : '' ?>" data-tooltip="Payers">
                        <i class="fas fa-users"></i> 
                        <span class="menu-text">Payers</span>
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('announcements') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Announcements') ? 'active' : '' ?>" data-tooltip="Announcements">
                        <i class="fas fa-bullhorn"></i> 
                        <span class="menu-text">Announcements</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Expanding button section -->
         <button class="sidebar-expand-btn" id="sidebarExpandBtn">
            <i class="fas fa-arrow-right"></i>
        </button>
        
        <footer class="sidebar-footer">
            <?= $this->include('partials/help_section') ?>
        </footer>
    </div>