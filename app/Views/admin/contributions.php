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
                'text' => '10'
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-hand-holding-usd',
                'iconColor' => 'text-primary',
                'title' => 'Total',
                'text' => '25'
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-times-circle',
                'iconColor' => 'text-danger',
                'title' => 'Inactive',
                'text' => '5'
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-calendar-day',
                'iconColor' => 'text-info',
                'title' => 'Today',
                'text' => '2'
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

<!-- Active Contributions Section -->
<?= view('partials/container-card', [
    'title' => '<i class="fas fa-hand-holding-usd me-2"></i>Active Contributions',
    'cardClass' => 'shadow-sm border-0',
    'bodyClass' => 'p-3',
    'content' => view('partials/contributions_list', [
        'contributions' => $contributions ?? []
    ])
]) ?>

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
    
    // Handle form submission
    const contributionForm = document.getElementById('contributionForm');
    if (contributionForm) {
        contributionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Contribution saved successfully!');
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('contributionModal'));
                    if (modal) {
                        modal.hide();
                    }
                    // Reload page to show updated list
                    window.location.reload();
                } else {
                    alert(data.message || 'An error occurred while saving the contribution.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected error occurred.');
            })
            .finally(() => {
                // Restore button state
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});
</script>

<?= $this->endSection() ?>