<link rel="stylesheet" href="<?= base_url('css/sidebar-complete.css') ?>">

<div class="sidebar-inner">
    <div class="sidebar-header">
        <a href="<?= base_url('payer/dashboard') ?>" class="logo" id="sidebarLogo" data-hover-text="Open sidebar" style="text-decoration: none;">
            <i class="fas fa-credit-card"></i>
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
                <a href="<?= base_url('payer/dashboard') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Dashboard') ? 'active' : '' ?>" data-tooltip="Dashboard">
                    <i class="fas fa-home"></i> 
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('payer/my-data') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'My Data') ? 'active' : '' ?>" data-tooltip="My Data">
                    <i class="fas fa-user-circle"></i> 
                    <span class="menu-text">My Data</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('payer/announcements') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Announcements') ? 'active' : '' ?>" data-tooltip="Announcements">
                    <i class="fas fa-bullhorn"></i> 
                    <span class="menu-text">Announcements</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('payer/contributions') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Contributions') ? 'active' : '' ?>" data-tooltip="Contributions">
                    <i class="fas fa-hand-holding-usd"></i> 
                    <span class="menu-text">Contributions</span>
                </a>
            </li>
            <li><hr></li>
            <li>
                <a href="<?= base_url('payer/payment-history') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Payment History') ? 'active' : '' ?>" data-tooltip="Payment History">
                    <i class="fas fa-history"></i> 
                    <span class="menu-text">Payment History</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('payer/payment-requests') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Payment Requests') ? 'active' : '' ?>" data-tooltip="Payment Requests">
                    <i class="fas fa-paper-plane"></i> 
                    <span class="menu-text">Payment Requests</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('payer/refund-requests') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Refund Requests') ? 'active' : '' ?>" data-tooltip="Refund Requests">
                    <i class="fas fa-undo"></i> 
                    <span class="menu-text">Refund Requests</span>
                </a>
            </li>
        </ul>
    </div>

    <footer class="sidebar-footer">
        <?= $this->include('partials/help_section') ?>
    </footer>
</div>
