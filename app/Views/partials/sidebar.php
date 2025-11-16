    <div class="sidebar-inner">
        <div class="sidebar-header">
            <a href="<?= base_url('dashboard') ?>" class="logo" id="sidebarLogo" data-hover-text="Open sidebar" style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
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
                    <a href="<?= base_url('payment-requests') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Payment Requests Management') ? 'active' : '' ?>" data-tooltip="Payment Requests">
                        <i class="fas fa-paper-plane"></i> 
                        <span class="menu-text">Payment Requests</span>
                        <span class="notification-badge" id="paymentRequestsBadge" style="display: none; background: #ef4444 !important; color: white !important; font-size: 0.75rem !important; font-weight: 600 !important; padding: 2px 6px !important; border-radius: 10px !important; min-width: 18px !important; height: 18px !important; margin-left: auto !important; margin-right: 8px !important; text-decoration: none !important; border: none !important; line-height: 1 !important; flex-shrink: 0 !important;"></span>
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('refunds') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Refunds') ? 'active' : '' ?>" data-tooltip="Refunds">
                        <i class="fas fa-undo"></i> 
                        <span class="menu-text">Refunds</span>
                        <span class="notification-badge" id="refundRequestsBadge" style="display: none; background: #ef4444 !important; color: white !important; font-size: 0.75rem !important; font-weight: 600 !important; padding: 2px 6px !important; border-radius: 10px !important; min-width: 18px !important; height: 18px !important; margin-left: auto !important; margin-right: 8px !important; text-decoration: none !important; border: none !important; line-height: 1 !important; flex-shrink: 0 !important;"></span>
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
                    <a href="<?= base_url('admins') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Admins') ? 'active' : '' ?>" data-tooltip="Admins">
                        <i class="fas fa-user-shield"></i> 
                        <span class="menu-text">Admins</span>
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('announcements') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Announcements') ? 'active' : '' ?>" data-tooltip="Announcements">
                        <i class="fas fa-bullhorn"></i> 
                        <span class="menu-text">Announcements</span>
                    </a>
                </li>
                <li><hr></li>
                <li>
                    <a href="<?= base_url('settings') ?>" class="sidebar-item <?= (($pageTitle ?? '') === 'Settings') ? 'active' : '' ?>" data-tooltip="Settings">
                        <i class="fas fa-cog"></i> 
                        <span class="menu-text">Settings</span>
                    </a>
                </li>
            </ul>
        </div>

        <footer class="sidebar-footer">
            <?= $this->include('partials/help_section') ?>
        </footer>
    </div>