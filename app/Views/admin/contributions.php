<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>" />

<div class="container-fluid ui-page-shell">
    <div class="ui-page-intro">
        <div>
            <h6>Contributions</h6>
            <p>Manage contribution types, review active and inactive items, and open the most common contribution workflows quickly.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="ui-stat-pill"><i class="fas fa-hand-holding-usd"></i>Total <?= number_format((int)($totalCount ?? 0)) ?></span>
            <span class="ui-stat-pill"><i class="fas fa-file-invoice-dollar"></i>Contributions <?= number_format((int)($contributionCount ?? $totalCount ?? 0)) ?></span>
        </div>
    </div>

<!-- Stats Cards Row -->
<div class="ui-stats-row">
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
            <div class="card h-100 shadow-sm border-0 ui-surface-card">
                <div class="card-header ui-surface-card-header">
                    <h5 class="ui-section-title">Quick Actions</h5>
                    <small class="ui-section-subtitle">Frequently used contribution operations</small>
                </div>
                <div class="card-body ui-surface-card-body pt-2">
                    <div class="row g-3">
                        <?= view('partials/quick-action-add-contribution', [
                            'title' => 'Add Contribution',
                            'subtitle' => 'Add a section-wide payable item',
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
                            'modalTarget' => 'contributionHistoryModal',
                            'colClass' => 'col-lg-4 col-md-4 col-sm-6'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
</div>

<!-- Active Contributions Section -->
<div class="card border-0 shadow-sm mb-3 ui-surface-card">
    <div class="card-header ui-surface-card-header">
        <h5 class="card-title mb-1"><i class="fas fa-hand-holding-usd me-2"></i>Active Contributions</h5>
        <small class="text-muted d-block ui-surface-subtitle">Search, filter, and open contribution history.</small>
    </div>
    <div class="card-body p-3 ui-surface-card-body">
        <?= view('partials/list-controls', [
            'controlId' => 'contributionControls',
            'searchId' => 'searchContribution',
            'searchLabel' => 'Search contributions',
            'placeholder' => 'Title, description, code...',
            'resultId' => 'resultsCount',
            'chipsId' => 'activeFilterChips',
            'clearId' => 'clearContributionFilters',
            'filters' => [
                [
                    'id' => 'filterCategory',
                    'label' => 'Category',
                    'options' => ['' => 'All categories'] + array_column($categories ?? [], 'name', 'code'),
                ],
                [
                    'id' => 'filterType',
                    'label' => 'Type',
                    'options' => ['' => 'All types', 'contribution' => 'Contribution'],
                ],
                [
                    'id' => 'filterStatus',
                    'label' => 'Status',
                    'options' => ['' => 'All statuses', 'active' => 'Active', 'inactive' => 'Inactive'],
                ],
            ],
        ]) ?>

        <?= view('partials/contributions_list', [
            'contributions' => $contributions ?? []
        ]) ?>
    </div>
</div>

</div>

<div class="modal fade" id="contributionImagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="contributionImagePreviewTitle">Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" alt="" id="contributionImagePreviewModalImage" class="img-fluid rounded-4">
            </div>
        </div>
    </div>
</div>

<!-- Contribution Payments Modal -->
<?= view('partials/modal-contribution-payments') ?>

<!-- Additional Payment Modal -->
<?= view('partials/modal-add-payment-to-partial') ?>

<!-- QR Receipt Modal -->
<?= view('partials/modal-qr-receipt', [
    'title' => 'Payment Receipt',
]) ?>

<script src="<?= base_url('js/list-controls.js') ?>"></script>
<script>
// Define base URL for API calls
window.APP_BASE_URL = '<?= base_url() ?>';

</script>

<script>
// Calculate Amount Per Payer
function calculateAmountPerPayer() {
    const grandTotal = parseFloat(document.getElementById('contributionGrandTotal').value) || 0;
    const numPayers = parseFloat(document.getElementById('contributionNumPayers').value) || 0;
    const amountField = document.getElementById('contributionAmount');
    
    if (grandTotal > 0 && numPayers > 0) {
        const amountPerPayer = grandTotal / numPayers;
        amountField.value = amountPerPayer.toFixed(2);
    } else {
        amountField.value = '0.00';
    }
}

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
                document.getElementById('contributionType').value = contribution.contribution_type || 'contribution';
                document.getElementById('contributionCode').value = contribution.contribution_code || '';
                document.getElementById('contributionDescription').value = contribution.description || '';
                document.getElementById('contributionRemoveImage').value = '0';
                if (contribution.image_path) {
                    const imagePath = String(contribution.image_path);
                    const imageUrl = /^https?:\/\//i.test(imagePath)
                        ? imagePath
                        : `<?= rtrim(base_url(), '/') ?>/` + imagePath.replace(/^\/+/, '');
                    document.getElementById('contributionImagePreview').src = imageUrl;
                    document.getElementById('contributionImagePreviewWrap').classList.remove('d-none');
                } else {
                    document.getElementById('contributionImagePreview').src = '';
                    document.getElementById('contributionImagePreviewWrap').classList.add('d-none');
                }
                document.getElementById('contributionGrandTotal').value = contribution.grand_total || '';
                document.getElementById('contributionCostPrice').value = contribution.cost_price || '0.00';
                document.getElementById('contributionCategory').value = contribution.category || '';
                document.getElementById('contributionStatus').value = contribution.status || 'active';
                
                // Calculate number of payers from existing data (grand_total / amount)
                const grandTotal = parseFloat(contribution.grand_total) || 0;
                const amount = parseFloat(contribution.amount) || 0;
                let numPayers = '';
                if (grandTotal > 0 && amount > 0) {
                    numPayers = Math.round(grandTotal / amount);
                }
                document.getElementById('contributionNumPayers').value = numPayers;
                
                // Calculate and set amount per payer
                calculateAmountPerPayer();
                
                // Update modal title to indicate edit mode
                document.getElementById('contributionModalLabel').textContent = 'Edit Item';
                
                // Update form action to edit mode
                document.getElementById('contributionForm').action = `<?= base_url('contributions/update/') ?>${contributionId}`;
                
                // Update submit button text
                const submitBtn = document.querySelector('#contributionForm button[type="submit"]');
                submitBtn.textContent = 'Update Item';
                submitBtn.className = 'btn btn-warning';
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('contributionModal'));
                modal.show();
            } else {
                alert('Error loading contribution data: ' + data.message);
            }
        })
        .catch(error => {
            alert('An error occurred while loading contribution data.');
        });
}

// Delete Contribution Function
function deleteContribution(contributionId, buttonElement) {
    if (confirm('Are you sure you want to delete this contribution? This action cannot be undone.')) {
        // Find the delete button and show loading state
        const deleteBtn = buttonElement || (typeof event !== 'undefined' ? event.target.closest('button') : null);
        const originalText = deleteBtn ? deleteBtn.innerHTML : '';
        if (deleteBtn) {
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Deleting...';
        }
        
        fetch(`<?= base_url('contributions/delete/') ?>${contributionId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Restore button state
            if (deleteBtn) {
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalText;
            }
            
            if (data.success) {
                alert('Contribution deleted successfully!');
                // Reload the page to show updated list
                window.location.reload();
            } else {
                alert('Error deleting contribution: ' + data.message);
            }
        })
        .catch(error => {
            // Restore button state on error
            if (deleteBtn) {
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalText;
            }
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
        alert('An error occurred while updating the contribution status.');
    });
}

// Reset form when modal is closed
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for auto-calculation
    const grandTotalField = document.getElementById('contributionGrandTotal');
    const numPayersField = document.getElementById('contributionNumPayers');
    const contributionImageInput = document.getElementById('contributionImage');
    const contributionImagePreview = document.getElementById('contributionImagePreview');
    const contributionImagePreviewWrap = document.getElementById('contributionImagePreviewWrap');
    
    if (grandTotalField) {
        grandTotalField.addEventListener('input', calculateAmountPerPayer);
        grandTotalField.addEventListener('change', calculateAmountPerPayer);
    }
    
    if (numPayersField) {
        numPayersField.addEventListener('input', calculateAmountPerPayer);
        numPayersField.addEventListener('change', calculateAmountPerPayer);
    }

    if (contributionImageInput) {
        contributionImageInput.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (!file) {
                return;
            }

            document.getElementById('contributionRemoveImage').value = '0';
            contributionImagePreview.src = URL.createObjectURL(file);
            contributionImagePreviewWrap.classList.remove('d-none');
        });
    }

    const contributionRemoveImageBtn = document.getElementById('contributionRemoveImageBtn');
    if (contributionRemoveImageBtn) {
        contributionRemoveImageBtn.addEventListener('click', function() {
            if (contributionImageInput) {
                contributionImageInput.value = '';
            }
            if (contributionImagePreview) {
                contributionImagePreview.src = '';
            }
            if (contributionImagePreviewWrap) {
                contributionImagePreviewWrap.classList.add('d-none');
            }
            document.getElementById('contributionRemoveImage').value = '1';
        });
    }

    const contributionModal = document.getElementById('contributionModal');
    if (contributionModal) {
        contributionModal.addEventListener('hidden.bs.modal', function() {
            // Reset form to add mode
            document.getElementById('contributionForm').reset();
            document.getElementById('contributionForm').action = '<?= base_url('contributions/save') ?>';
            
            // Reset modal title
            document.getElementById('contributionModalLabel').textContent = 'Add Contribution';
            
            // Reset submit button
            const submitBtn = document.querySelector('#contributionForm button[type="submit"]');
            submitBtn.textContent = 'Save';
            submitBtn.className = 'btn btn-primary';
            
            // Clear hidden ID field
            document.getElementById('contributionId').value = '';
            document.getElementById('contributionRemoveImage').value = '0';
            if (contributionImagePreviewWrap) {
                contributionImagePreviewWrap.classList.add('d-none');
            }
            if (contributionImagePreview) {
                contributionImagePreview.src = '';
            }
            
            // Reset amount field
            document.getElementById('contributionAmount').value = '0.00';
        });
    }
    // Form submission is handled by contribution.js
    
    window.contributionListControls = createListControls({
        searchId: 'searchContribution',
        resultId: 'resultsCount',
        chipsId: 'activeFilterChips',
        clearId: 'clearContributionFilters',
        itemSelector: '.contribution-item',
        containerSelector: '#contributionsContainer',
        emptySelector: '#noContributionResults',
        label: 'contributions',
        filters: [
            { id: 'filterCategory', key: 'category', label: 'Category', attribute: 'data-category' },
            { id: 'filterType', key: 'type', label: 'Type', attribute: 'data-type' },
            { id: 'filterStatus', key: 'status', label: 'Status', attribute: 'data-status' },
        ],
    });
    
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

function openContributionImagePreview(src, title) {
    document.getElementById('contributionImagePreviewTitle').textContent = title || 'Contribution Image';
    document.getElementById('contributionImagePreviewModalImage').src = src;
    new bootstrap.Modal(document.getElementById('contributionImagePreviewModal')).show();
}
</script>

<!-- Contribution History Modal -->
<?= $this->include('partials/modal-contribution-history') ?>

<?= $this->endSection() ?>
