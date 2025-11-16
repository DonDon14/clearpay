<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Use data from controller
$announcements = $announcements ?? [];
$status_counts = $stats ?? [
    'total' => 0,
    'published' => 0,
    'draft' => 0,
    'archived' => 0
];
?>

<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Total Announcements',
                'text' => number_format($status_counts['total']),
                'icon' => 'bullhorn',
                'iconColor' => 'text-primary'
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Published',
                'text' => number_format($status_counts['published']),
                'icon' => 'eye',
                'iconColor' => 'text-success'
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Drafts',
                'text' => number_format($status_counts['draft']),
                'icon' => 'edit',
                'iconColor' => 'text-warning'
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Archived',
                'text' => number_format($status_counts['archived']),
                'icon' => 'archive',
                'iconColor' => 'text-secondary'
            ]) ?>
        </div>
    </div>

    <!-- Announcements List -->
    <?= view('partials/container-card', [
        'title' => 'All Announcements',
        'subtitle' => 'Manage your announcements',
        'headerAction' => '<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#announcementModal"><i class="fas fa-plus"></i> Add Announcement</button>',
        'bodyClass' => '', // Override default flex-wrap
        'content' => '
            <!-- Search and Filter Controls -->
            <div class="row mb-4 pb-3 border-bottom">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search announcements...">
                    </div>
                </div>
                    <!-- Status filter removed - announcements are always published -->
                <div class="col-md-2 mb-3">
                    <label class="form-label">Priority</label>
                    <select class="form-select" id="priorityFilter">
                        <option value="">All Priority</option>
                        <option value="critical">Critical</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>
                <!-- Audience filter removed - always payers -->
                <div class="col-md-3 mb-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
            
            <!-- Announcements List -->
            <div id="announcementsList" class="w-100">
                ' . implode('', array_map(function($announcement) {
                    $priorityColors = [
                        'critical' => 'danger',
                        'high' => 'warning', 
                        'medium' => 'info',
                        'low' => 'secondary'
                    ];
                    
                    $statusColors = [
                        'published' => 'success',
                        'draft' => 'warning',
                        'archived' => 'secondary'
                    ];
                    
                    $typeIcons = [
                        'general' => 'info-circle',
                        'urgent' => 'exclamation-triangle',
                        'maintenance' => 'tools',
                        'event' => 'calendar',
                        'deadline' => 'clock'
                    ];
                    
                    $priorityColor = $priorityColors[$announcement['priority']] ?? 'secondary';
                    $statusColor = $statusColors[$announcement['status']] ?? 'secondary';
                    $typeIcon = $typeIcons[$announcement['type']] ?? 'bullhorn';
                    
                    return '
                        <div class="card mb-3 announcement-card w-100" data-status="' . $announcement['status'] . '" 
                             data-priority="' . $announcement['priority'] . '" data-audience="' . $announcement['target_audience'] . '" style="max-width: 100%;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-1">
                                        <i class="fas fa-' . $typeIcon . ' me-2"></i>
                                        ' . htmlspecialchars($announcement['title']) . '
                                    </h5>
                                    <div class="text-muted small">
                                        <i class="fas fa-calendar me-1"></i>
                                        Created: ' . date('M j, Y g:i A', strtotime($announcement['created_at'])) . '
                                        ' . ($announcement['published_at'] ? '| Published: ' . date('M j, Y', strtotime($announcement['published_at'])) : '') . '
                                        ' . ($announcement['expires_at'] ? '| Expires: ' . date('M j, Y', strtotime($announcement['expires_at'])) : '') . '
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary btn-sm" onclick="editAnnouncement(' . $announcement['id'] . ')" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    ' . ($announcement['status'] !== 'archived' ? 
                                        '<button class="btn btn-outline-warning btn-sm" onclick="archiveAnnouncement(' . $announcement['id'] . ')" title="Archive">
                                            <i class="fas fa-archive"></i>
                                        </button>' : '') . '
                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteAnnouncement(' . $announcement['id'] . ')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="announcement-content mb-3">
                                    ' . nl2br(htmlspecialchars($announcement['text'] ?? '')) . '
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-' . $priorityColor . '">' . ucfirst($announcement['priority']) . ' Priority</span>
                                    <span class="badge bg-' . $statusColor . '">' . ucfirst($announcement['status']) . '</span>
                                    <!-- Target audience badge removed - always payers -->
                                    <span class="badge bg-info">' . ucfirst($announcement['type']) . '</span>
                                </div>
                            </div>
                        </div>
                    ';
                }, $announcements)) . '
                
                <!-- Empty State (hidden by default, shown when no announcements match filters) -->
                <div id="emptyState" class="text-center py-5" style="display: none;">
                    <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                    <h3>No Announcements Found</h3>
                    <p class="text-muted">No announcements match your current filters.</p>
                    <button class="btn btn-primary" onclick="clearFilters()">Clear Filters</button>
                </div>
            </div>
        '
    ]) ?>

<!-- Add/Edit Announcement Modal -->
<div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="announcementModalLabel">Add New Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="announcementForm">
                <div class="modal-body">
                    <input type="hidden" id="announcementId">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required maxlength="255">
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="general">General</option>
                                <option value="urgent">Urgent</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="event">Event</option>
                                <option value="deadline">Deadline</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="">Select Priority</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Status field removed - announcements are always published immediately -->
                    
                    <div class="mb-3">
                        <label for="expires_at" class="form-label">Expiration Date (Optional)</label>
                        <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
                        <div class="form-text">Leave empty if the announcement should not expire</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Filter functionality
function filterAnnouncements() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = ''; // Status filter removed - all announcements are published
    const priorityFilter = document.getElementById('priorityFilter').value;
    const audienceFilter = ''; // Always empty since audience is always payers
    
    const cards = document.querySelectorAll('.announcement-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const title = card.querySelector('.card-title').textContent.toLowerCase();
        const content = card.querySelector('.announcement-content').textContent.toLowerCase();
        const status = card.dataset.status;
        const priority = card.dataset.priority;
        const audience = card.dataset.audience;
        
        const matchesSearch = !searchTerm || title.includes(searchTerm) || content.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;
        const matchesPriority = !priorityFilter || priority === priorityFilter;
        const matchesAudience = !audienceFilter || audience === audienceFilter;
        
        if (matchesSearch && matchesStatus && matchesPriority && matchesAudience) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Show/hide empty state
    const emptyState = document.getElementById('emptyState');
    if (visibleCount === 0) {
        emptyState.style.display = 'block';
    } else {
        emptyState.style.display = 'none';
    }
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    // Status filter removed
    document.getElementById('priorityFilter').value = '';
    // audienceFilter is always empty (payers only)
    filterAnnouncements();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('searchInput').addEventListener('input', filterAnnouncements);
    // Status filter removed - no event listener needed
    document.getElementById('priorityFilter').addEventListener('change', filterAnnouncements);
    // audienceFilter removed - always payers
    
    // Form submission
    document.getElementById('announcementForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveAnnouncement();
    });
    
    // Check if we should open the modal on page load
    const urlParams = new URLSearchParams(window.location.search);
    const openModal = urlParams.get('open_modal');
    
    if (openModal === 'true') {
        // Wait for Bootstrap to be fully loaded
        const waitForBootstrap = setInterval(function() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                clearInterval(waitForBootstrap);
                const modalElement = document.getElementById('announcementModal');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            }
        }, 100);
        
        // Timeout after 5 seconds
        setTimeout(function() {
            clearInterval(waitForBootstrap);
        }, 5000);
    }
});

// Save announcement (create or update)
function saveAnnouncement() {
    const formData = new FormData(document.getElementById('announcementForm'));
    const announcementId = document.getElementById('announcementId').value;
    
    if (announcementId) {
        formData.append('announcement_id', announcementId);
    }
    
    fetch('<?= base_url('announcements/save') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('announcementModal'));
            modal.hide();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Error saving announcement', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving announcement', 'error');
    });
}

// Edit announcement
function editAnnouncement(id) {
    fetch(`<?= base_url('announcements/get/') ?>${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.announcement) {
                const announcement = data.announcement;
                
                // Populate form fields
                document.getElementById('announcementId').value = announcement.id;
                document.getElementById('title').value = announcement.title;
                document.getElementById('content').value = announcement.text; // Note: DB uses 'text', form uses 'content'
                document.getElementById('type').value = announcement.type;
                document.getElementById('priority').value = announcement.priority;
                // target_audience is always 'payers', no need to set it
                // Status is always 'published' - no need to set it
                document.getElementById('expires_at').value = announcement.expires_at ? announcement.expires_at.substring(0, 16) : '';
                
                // Update modal title
                document.getElementById('announcementModalLabel').textContent = 'Edit Announcement';
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('announcementModal'));
                modal.show();
            } else {
                showNotification(data.message || 'Error loading announcement', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading announcement', 'error');
        });
}

// Archive announcement
function archiveAnnouncement(id) {
    if (confirm('Archive this announcement?')) {
        fetch(`<?= base_url('announcements/update-status/') ?>${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'status=archived'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(data.message || 'Error archiving announcement', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error archiving announcement', 'error');
        });
    }
}

// Delete announcement
function deleteAnnouncement(id) {
    if (confirm('Delete this announcement? This cannot be undone.')) {
        fetch(`<?= base_url('announcements/delete/') ?>${id}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(data.message || 'Error deleting announcement', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error deleting announcement', 'error');
        });
    }
}

// Placeholder functions for future implementation
function publishDrafts() {
    showNotification('This feature is not yet implemented', 'info');
}

function exportAnnouncements() {
    showNotification('This feature is not yet implemented', 'info');
}

function cleanupExpired() {
    if (confirm('Remove all expired announcements?')) {
        showNotification('This feature is not yet implemented', 'info');
    }
}

// Helper function for notifications
function showNotification(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Reset form when modal is closed
document.getElementById('announcementModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('announcementForm').reset();
    document.getElementById('announcementId').value = '';
    document.getElementById('announcementModalLabel').textContent = 'Add New Announcement';
    
    // Clean up URL if it has the open_modal parameter
    if (window.location.search.includes('open_modal=true')) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});
</script>
<?= $this->endSection() ?>