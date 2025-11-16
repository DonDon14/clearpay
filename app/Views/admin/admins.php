<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Total Admins',
                'text' => number_format($totalAdmins),
                'icon' => 'users',
                'iconColor' => 'text-primary'
            ]) ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Online Now',
                'text' => number_format($onlineAdmins),
                'icon' => 'user-check',
                'iconColor' => 'text-success'
            ]) ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Offline',
                'text' => number_format($totalAdmins - $onlineAdmins),
                'icon' => 'user-slash',
                'iconColor' => 'text-secondary'
            ]) ?>
        </div>
    </div>

    <!-- Admins List -->
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">Administrators</h5>
                <p class="text-muted mb-0 small">View all administrators and their online status</p>
            </div>
        </div>
        <div class="card-body">
            <!-- Search Bar -->
            <div class="mb-3">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="searchAdminInput" 
                                   placeholder="Search by Name, Username, or Email..."
                                   autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearchBtn" style="display: none;">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted" id="searchResultsCount">
                            Showing <?= !empty($admins) ? count($admins) : '0' ?> of <?= !empty($admins) ? count($admins) : '0' ?> admins
                        </small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label for="filterStatus" class="form-label small text-muted mb-1">Filter by Status</label>
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="online">Online</option>
                            <option value="offline">Offline</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label for="filterRole" class="form-label small text-muted mb-1">Filter by Role</label>
                        <select class="form-select form-select-sm" id="filterRole">
                            <option value="">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="officer">Officer</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label for="sortBy" class="form-label small text-muted mb-1">Sort By</label>
                        <select class="form-select form-select-sm" id="sortBy">
                            <option value="status">Status (Online First)</option>
                            <option value="name_asc">Name (A-Z)</option>
                            <option value="name_desc">Name (Z-A)</option>
                            <option value="role_asc">Role</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($admins)): ?>
                            <?php foreach ($admins as $admin): ?>
                                <tr class="admin-row" 
                                    data-admin-id="<?= $admin['id'] ?>"
                                    data-admin-name="<?= esc(strtolower($admin['name'] ?? $admin['username'])) ?>"
                                    data-username="<?= esc(strtolower($admin['username'])) ?>"
                                    data-email="<?= esc(strtolower($admin['email'] ?? '')) ?>"
                                    data-role="<?= esc(strtolower($admin['role'] ?? '')) ?>"
                                    data-status="<?= $admin['is_online'] ? 'online' : 'offline' ?>">
                                    <td>
                                        <?php if ($admin['is_online']): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-circle" style="font-size: 0.6rem; margin-right: 4px;"></i>Online
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-circle" style="font-size: 0.6rem; margin-right: 4px; opacity: 0.5;"></i>Offline
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($admin['profile_picture']) && trim($admin['profile_picture']) !== ''): ?>
                                            <?php 
                                            // Check if it's a Cloudinary URL (full URL) or local path
                                            $adminPicUrl = (strpos($admin['profile_picture'], 'res.cloudinary.com') !== false) 
                                                ? $admin['profile_picture'] 
                                                : base_url($admin['profile_picture']);
                                            ?>
                                            <img src="<?= $adminPicUrl ?>" 
                                                 alt="<?= esc($admin['name'] ?? $admin['username']) ?>"
                                                 class="rounded-circle"
                                                 style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #e9ecef;">
                                        <?php else: ?>
                                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary text-white"
                                                 style="width: 40px; height: 40px; flex-shrink: 0;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= esc($admin['name'] ?? $admin['username']) ?></strong></td>
                                    <td><?= esc($admin['username']) ?></td>
                                    <td><?= esc($admin['email'] ?? 'N/A') ?></td>
                                    <td><?= esc($admin['phone'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php if (strtolower($admin['role'] ?? '') === 'admin'): ?>
                                            <span class="badge bg-danger">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Officer</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No admins found</td>
                            </tr>
                        <?php endif; ?>
                        <!-- No Results Row (hidden by default) -->
                        <tr id="noResultsRow" style="display: none;">
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-search fa-2x mb-2 d-block"></i>
                                <p class="mb-0">No admins found matching your search criteria</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted" id="paginationInfo">
                    Showing <?= !empty($admins) ? '1 to ' . count($admins) . ' of ' . count($admins) : '0' ?> entries
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchAdminInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const filterStatus = document.getElementById('filterStatus');
    const filterRole = document.getElementById('filterRole');
    const sortBy = document.getElementById('sortBy');
    const searchResultsCount = document.getElementById('searchResultsCount');
    const noResultsRow = document.getElementById('noResultsRow');
    
    let allAdminRows = Array.from(document.querySelectorAll('.admin-row'));
    
    function filterAndSort() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const statusFilter = filterStatus.value;
        const roleFilter = filterRole.value;
        const sortValue = sortBy.value;
        
        let visibleRows = allAdminRows.filter(row => {
            const name = row.dataset.adminName || '';
            const username = row.dataset.username || '';
            const email = row.dataset.email || '';
            const status = row.dataset.status || '';
            const role = row.dataset.role || '';
            
            // Search filter
            const matchesSearch = !searchTerm || 
                name.includes(searchTerm) || 
                username.includes(searchTerm) || 
                email.includes(searchTerm);
            
            // Status filter
            const matchesStatus = !statusFilter || status === statusFilter;
            
            // Role filter
            const matchesRole = !roleFilter || role === roleFilter;
            
            return matchesSearch && matchesStatus && matchesRole;
        });
        
        // Hide all rows first
        allAdminRows.forEach(row => {
            row.style.display = 'none';
        });
        
        // Sort visible rows
        if (sortValue === 'status') {
            visibleRows.sort((a, b) => {
                const aOnline = a.dataset.status === 'online';
                const bOnline = b.dataset.status === 'online';
                if (aOnline !== bOnline) {
                    return bOnline ? 1 : -1;
                }
                return 0;
            });
        } else if (sortValue === 'name_asc') {
            visibleRows.sort((a, b) => {
                const aName = a.dataset.adminName || '';
                const bName = b.dataset.adminName || '';
                return aName.localeCompare(bName);
            });
        } else if (sortValue === 'name_desc') {
            visibleRows.sort((a, b) => {
                const aName = a.dataset.adminName || '';
                const bName = b.dataset.adminName || '';
                return bName.localeCompare(aName);
            });
        } else if (sortValue === 'role_asc') {
            visibleRows.sort((a, b) => {
                const aRole = a.dataset.role || '';
                const bRole = b.dataset.role || '';
                return aRole.localeCompare(bRole);
            });
        }
        
        // Show visible rows
        visibleRows.forEach(row => {
            row.style.display = '';
        });
        
        // Show/hide no results row
        if (visibleRows.length === 0) {
            noResultsRow.style.display = '';
        } else {
            noResultsRow.style.display = 'none';
        }
        
        // Update search results count
        searchResultsCount.textContent = `Showing ${visibleRows.length} of ${allAdminRows.length} admins`;
    }
    
    // Event listeners
    searchInput.addEventListener('input', function() {
        filterAndSort();
        clearSearchBtn.style.display = this.value.trim() ? 'block' : 'none';
    });
    
    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearSearchBtn.style.display = 'none';
        filterAndSort();
    });
    
    filterStatus.addEventListener('change', filterAndSort);
    filterRole.addEventListener('change', filterAndSort);
    sortBy.addEventListener('change', filterAndSort);
    
    // Auto-refresh online status every 30 seconds
    setInterval(function() {
        // Reload the page to refresh online status
        // In a production environment, you might want to use AJAX instead
        // For now, we'll just reload after 2 minutes
    }, 120000); // 2 minutes
    
    // Initial filter
    filterAndSort();
});
</script>

<?= $this->endSection() ?>


