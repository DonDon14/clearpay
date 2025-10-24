
<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-credit-card"></i>
            <span>ClearPay</span>
        </div>
        <button class="toggle-btn" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <div class="sidebar-content">
        <ul class="sidebar-menu">
            <li>
                <a href="<?= base_url('dashboard') ?>" class="sidebar-item <?= ($pageTitle === 'Dashboard') ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> 
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('payments') ?>" class="sidebar-item <?= ($pageTitle === 'Payments') ? 'active' : '' ?>">
                    <i class="fas fa-money-bill-wave"></i> 
                    <span>Payments</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('contributions') ?>" class="sidebar-item <?= ($pageTitle === 'Contributions') ? 'active' : '' ?>">
                    <i class="fas fa-hand-holding-usd"></i> 
                    <span>Contributions</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('partial_payments/index.html') ?>" class="sidebar-item <?= ($pageTitle === 'Partial Payments') ? 'active' : '' ?>">
                    <i class="fas fa-wallet"></i> 
                    <span>Partial Payments</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('payment_history/index.html') ?>" class="sidebar-item <?= ($pageTitle === 'Payment History') ? 'active' : '' ?>">
                    <i class="fas fa-history"></i> 
                    <span>Payment History</span>
                </a>
            </li>
            <li><hr></li>
            <li>
                <a href="<?= base_url('analytics/index.html') ?>" class="sidebar-item <?= ($pageTitle === 'Analytics') ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i> 
                    <span>Analytics</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('students') ?>" class="sidebar-item <?= ($pageTitle === 'Students') ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> 
                    <span>Students</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('announcements') ?>" class="sidebar-item <?= ($pageTitle === 'Announcements') ? 'active' : '' ?>">
                    <i class="fas fa-bullhorn"></i> 
                    <span>Announcements</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('profile/index.html') ?>" class="sidebar-item <?= ($pageTitle === 'Profile') ? 'active' : '' ?>">
                    <i class="fas fa-user"></i> 
                    <span>Profile</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('settings/index.html') ?>" class="sidebar-item <?= ($pageTitle === 'Settings') ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i> 
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>
    
    <footer class="sidebar-footer">
        <?= $this->include('partials/help_section') ?>
    </footer>
</div>