<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>


<!-- Stats Cards Row -->
<div class="container-fluid mb-4">
    <div class="row g-3">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-check-circle',
                'iconColor' => 'text-success',
                'title' => 'Active',
                'text' => (string)($activeCount ?? 0)
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-hand-holding-usd',
                'iconColor' => 'text-primary',
                'title' => 'Total',
                'text' => (string)($totalCount ?? 0)
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-times-circle',
                'iconColor' => 'text-danger',
                'title' => 'Inactive',
                'text' => (string)($inactiveCount ?? 0)
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-calendar-day',
                'iconColor' => 'text-info',
                'title' => 'Today',
                'text' => (string)($todayCount ?? 0)
            ]) ?>
        </div>
    </div>
</div>

<!-- Quick Actions Section -->
<div class="row mb-4">
        <!-- Quick Actions -->
        <div class="col-12">
            <div class="card h-100 shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                    <small class="text-muted">Frequently used operations</small>
                </div>
                <div class="card-body p-2">
                    <div class="row g-2">
                        <?= view('partials/quick-action-add-contribution', [
                            'title' => 'Add New Contribution',
                            'subtitle' => 'Add new contribution type',
                            'icon' => 'fas fa-plus-square',
                            'bgColor' => 'bg-info',
                            'colClass' => 'col-lg-4 col-md-4 col-sm-6',
                            'action' => base_url('/contributions/save')
                        ]) ?>
                        <?php 
                        // Reset variables for next call
                        $tempModalTarget = null;
                        $tempLink = '/admin/payments';
                        ?>
                        <?= view('partials/quick-action-add-payment', [
                            'title' => 'Record Payment',
                            'subtitle' => 'Add new payment record',
                            'icon' => 'fas fa-plus-circle',
                            'bgColor' => 'bg-primary',
                            'colClass' => 'col-lg-4 col-md-4 col-sm-6',
                            'contributions' => $contributions ?? []
                        ]) ?>
                        <?php 
                        // Reset variables for next call
                        $tempModalTarget = null;
                        $tempLink = '/admin/history';
                        ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-history',
                            'title' => 'View History',
                            'subtitle' => 'View contribution history',
                            'bgColor' => 'bg-warning',
                            'link' => $tempLink,
                            'modalTarget' => $tempModalTarget,
                            'colClass' => 'col-lg-4 col-md-4 col-sm-6'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

<!-- Search and Filter Section -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label for="searchContribution" class="form-label">
                            <i class="fas fa-search me-1"></i>Search
                        </label>
                        <input type="text" id="searchContribution" class="form-control" placeholder="Search by title or description...">
                    </div>
                    <div class="col-md-3">
                        <label for="filterCategory" class="form-label">
                            <i class="fas fa-filter me-1"></i>Category
                        </label>
                        <select id="filterCategory" class="form-select">
                            <option value="">All Categories</option>
                            <option value="tuition">Tuition Fee</option>
                            <option value="library">Library Fee</option>
                            <option value="laboratory">Laboratory Fee</option>
                            <option value="registration">Registration Fee</option>
                            <option value="development">Development Fee</option>
                            <option value="medical">Medical Fee</option>
                            <option value="guidance">Guidance Fee</option>
                            <option value="athletic">Athletic Fee</option>
                            <option value="computer">Computer Fee</option>
                            <option value="damage">Damage Fee</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterStatus" class="form-label">
                            <i class="fas fa-toggle-on me-1"></i>Status
                        </label>
                        <select id="filterStatus" class="form-select">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <button class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                            <i class="fas fa-times me-1"></i>Clear Filters
                        </button>
                        <span class="ms-2 text-muted" id="resultsCount"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Contributions Section -->
<?= view('partials/container-card', [
    'title' => '<i class="fas fa-hand-holding-usd me-2"></i>Active Contributions',
    'cardClass' => 'shadow-sm border-0',
    'bodyClass' => 'p-3',
    'content' => view('partials/contributions_list', [
        'contributions' => $contributions ?? []
    ])
]) ?>

<!-- Contribution Payments Modal -->
<?= view('partials/modal-contribution-payments') ?>

<!-- Additional Payment Modal -->
<?= view('partials/modal-add-payment-to-partial') ?>

<!-- QR Receipt Modal -->
<?= view('partials/modal-qr-receipt', [
    'title' => 'Payment Receipt',
]) ?>

<script>
// Define base URL for API calls
window.APP_BASE_URL = '<?= base_url() ?>';

</script>

<script>
// Edit Contribution Function
function editContribution(contributionId) {
    // Fetch contribution data and populate modal
    fetch(`<?= base_url('contributions/get/') ?>${contributionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const contribution = data.contribution;
                
                // Populate all form fields with existing data
                document.getElementById('contributionId').value = contribution.id;
                document.getElementById('contributionTitle').value = contribution.title || '';
                document.getElementById('contributionDescription').value = contribution.description || '';
                document.getElementById('contributionAmount').value = contribution.amount || '0.00';
                document.getElementById('contributionCostPrice').value = contribution.cost_price || '0.00';
                document.getElementById('contributionCategory').value = contribution.category || '';
                document.getElementById('contributionStatus').value = contribution.status || 'active';
                
                // Update modal title to indicate edit mode
                document.getElementById('contributionModalLabel').textContent = 'Edit Contribution';
                
                // Update form action to edit mode
                document.getElementById('contributionForm').action = `<?= base_url('contributions/update/') ?>${contributionId}`;
                
                // Update submit button text
                const submitBtn = document.querySelector('#contributionForm button[type="submit"]');
                submitBtn.textContent = 'Update Contribution';
                submitBtn.className = 'btn btn-warning';
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('contributionModal'));
                modal.show();
            } else {
                alert('Error loading contribution data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while loading contribution data.');
        });
}

// Delete Contribution Function
function deleteContribution(contributionId) {
    if (confirm('Are you sure you want to delete this contribution? This action cannot be undone.')) {
        fetch(`<?= base_url('contributions/delete/') ?>${contributionId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Contribution deleted successfully!');
                // Reload the page to show updated list
                window.location.reload();
            } else {
                alert('Error deleting contribution: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the contribution.');
        });
    }
}

// Toggle Status Function
function toggleContributionStatus(contributionId, currentStatus) {
    fetch(`<?= base_url('contributions/toggle-status/') ?>${contributionId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show notification
            showNotification(`Contribution status changed to ${data.newStatus}`, 'success');
            // Reload the page to show updated list with proper sorting
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            alert('Error updating contribution status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the contribution status.');
    });
}

// Reset form when modal is closed
document.addEventListener('DOMContentLoaded', function() {
    const contributionModal = document.getElementById('contributionModal');
    if (contributionModal) {
        contributionModal.addEventListener('hidden.bs.modal', function() {
            // Reset form to add mode
            document.getElementById('contributionForm').reset();
            document.getElementById('contributionForm').action = '<?= base_url('contributions/save') ?>';
            
            // Reset modal title
            document.getElementById('contributionModalLabel').textContent = 'Add New Contribution';
            
            // Reset submit button
            const submitBtn = document.querySelector('#contributionForm button[type="submit"]');
            submitBtn.textContent = 'Save';
            submitBtn.className = 'btn btn-primary';
            
            // Clear hidden ID field
            document.getElementById('contributionId').value = '';
        });
    }
    // Form submission is handled by contribution.js
    
    // Search and Filter Functionality
    const searchInput = document.getElementById('searchContribution');
    const categoryFilter = document.getElementById('filterCategory');
    const statusFilter = document.getElementById('filterStatus');
    const resultsCount = document.getElementById('resultsCount');
    
    function filterContributions() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const selectedCategory = categoryFilter.value;
        const selectedStatus = statusFilter.value;
        
        const items = document.querySelectorAll('.contribution-item');
        let visibleCount = 0;
        
        items.forEach(item => {
            const title = item.getAttribute('data-title') || '';
            const category = item.getAttribute('data-category') || '';
            const status = item.getAttribute('data-status') || '';
            
            // Check if item matches search term
            const matchesSearch = searchTerm === '' || title.includes(searchTerm);
            
            // Check if item matches category filter
            const matchesCategory = selectedCategory === '' || category === selectedCategory;
            
            // Check if item matches status filter
            const matchesStatus = selectedStatus === '' || status === selectedStatus;
            
            // Show or hide item based on all filters
            if (matchesSearch && matchesCategory && matchesStatus) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        
        // Update results count
        const totalCount = items.length;
        resultsCount.textContent = `Showing ${visibleCount} of ${totalCount} contributions`;
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', filterContributions);
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterContributions);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterContributions);
    }
    
    // Initialize results count
    if (resultsCount) {
        const items = document.querySelectorAll('.contribution-item');
        resultsCount.textContent = `Showing ${items.length} of ${items.length} contributions`;
    }
    
    // Handle hash from URL (for search result navigation)
    const hash = window.location.hash;
    if (hash && hash.startsWith('#contribution-')) {
        const contributionId = hash.substring(15); // Remove '#contribution-'
        
        // Find the contribution card
        const contributionCard = document.getElementById('contribution-' + contributionId);
        
        if (contributionCard) {
            // Scroll to the card
            contributionCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Highlight the card temporarily
            const card = contributionCard.querySelector('.card');
            if (card) {
                card.style.backgroundColor = '#fff3cd';
                setTimeout(() => {
                    card.style.backgroundColor = '';
                }, 2000);
            }
            
            // Get title and amount from the card content
            const titleElement = contributionCard.querySelector('.fw-semibold');
            const amountElement = contributionCard.querySelector('.text-primary');
            
            if (titleElement && amountElement) {
                const contributionTitle = titleElement.textContent.trim();
                const amountText = amountElement.textContent.trim();
                // Remove the ₱ symbol and parse the number
                const contributionAmount = parseFloat(amountText.replace('₱', '').replace(/,/g, ''));
                
                // Open the payments modal after a short delay
                setTimeout(() => {
                    showContributionPayments(contributionId, contributionTitle, contributionAmount);
                }, 500);
            }
        }
    }
});

// Clear filters function
function clearFilters() {
    document.getElementById('searchContribution').value = '';
    document.getElementById('filterCategory').value = '';
    document.getElementById('filterStatus').value = '';
    
    // Show all items
    document.querySelectorAll('.contribution-item').forEach(item => {
        item.style.display = '';
    });
    
    // Update results count
    const items = document.querySelectorAll('.contribution-item');
    document.getElementById('resultsCount').textContent = `Showing ${items.length} of ${items.length} contributions`;
}
</script>

<?= $this->endSection() ?>