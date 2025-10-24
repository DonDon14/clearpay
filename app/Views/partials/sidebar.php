
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
            <li class="sidebar-item <?= ($pageTitle === 'Dashboard') ? 'active' : '' ?>">
                <i class="fas fa-home"></i> 
                <span>Dashboard</span>
            </li>
            <li class="sidebar-item <?= ($pageTitle === 'Payments') ? 'active' : '' ?>">
                <i class="fas fa-money-bill-wave"></i> 
                <span>Payments</span>
            </li>
            <li class="sidebar-item <?= ($pageTitle === 'Contributions') ? 'active' : '' ?>">
                <i class="fas fa-hand-holding-usd"></i> 
                <span>Contributions</span>
            </li>
            <li class="sidebar-item <?= ($pageTitle === 'Partial Payments') ? 'active' : '' ?>">
                <i class="fas fa-wallet"></i> 
                <span>Partial Payments</span>
            </li>
            <li class="sidebar-item <?= ($pageTitle === 'Payment History') ? 'active' : '' ?>">
                <i class="fas fa-history"></i> 
                <span>Payment History</span>
            </li>
            <hr>
            <li class="sidebar-item <?= ($pageTitle === 'Analytics') ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i> 
                <span>Analytics</span>
            </li>
            <li class="sidebar-item <?= ($pageTitle === 'Students') ? 'active' : '' ?>">
                <i class="fas fa-users"></i> 
                <span>Students</span>
            </li>
            <li class="sidebar-item <?= ($pageTitle === 'Announcements') ? 'active' : '' ?>">
                <i class="fas fa-bullhorn"></i> 
                <span>Announcements</span>
            </li>
            <li class="sidebar-item <?= ($pageTitle === 'Profile') ? 'active' : '' ?>">
                <i class="fas fa-user"></i> 
                <span>Profile</span>
            </li>
            <li class="sidebar-item <?= ($pageTitle === 'Settings') ? 'active' : '' ?>">
                <i class="fas fa-cog"></i> 
                <span>Settings</span>
            </li>
        </ul>
    </div>
    
    <footer class="sidebar-footer">
        <?= $this->include('partials/help_section') ?>
    </footer>
</div>