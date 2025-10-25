<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Dummy data for UI development - replace with actual controller data later
$announcements = [
    [
        'id' => 1,
        'title' => 'System Maintenance Notice',
        'content' => 'ClearPay will undergo scheduled maintenance this Saturday from 2:00 AM to 6:00 AM. During this time, the system will be temporarily unavailable. We apologize for any inconvenience.',
        'type' => 'maintenance',
        'priority' => 'high',
        'target_audience' => 'all',
        'status' => 'published',
        'created_at' => '2024-10-20 10:00:00',
        'published_at' => '2024-10-20 10:00:00',
        'expires_at' => '2024-10-27 00:00:00'
    ],
    [
        'id' => 2,
        'title' => 'New Payment Methods Available',
        'content' => 'We are excited to announce that GCash and PayMaya are now available as payment methods. Students can now use these convenient options to pay their contributions.',
        'type' => 'general',
        'priority' => 'medium',
        'target_audience' => 'students',
        'status' => 'published',
        'created_at' => '2024-10-18 14:30:00',
        'published_at' => '2024-10-18 14:30:00',
        'expires_at' => null
    ],
    [
        'id' => 3,
        'title' => 'Semester End Payment Deadline',
        'content' => 'Reminder: All semester contributions must be paid by November 30, 2024. Late payments will incur additional charges. Please settle your accounts promptly.',
        'type' => 'deadline',
        'priority' => 'critical',
        'target_audience' => 'students',
        'status' => 'published',
        'created_at' => '2024-10-15 09:15:00',
        'published_at' => '2024-10-15 09:15:00',
        'expires_at' => '2024-11-30 23:59:59'
    ],
    [
        'id' => 4,
        'title' => 'Welcome New Students',
        'content' => 'Welcome to all new students! Please visit the admin office to complete your registration and setup your ClearPay account. Don\'t forget to bring your student ID and enrollment certificate.',
        'type' => 'general',
        'priority' => 'low',
        'target_audience' => 'students',
        'status' => 'draft',
        'created_at' => '2024-10-22 16:45:00',
        'published_at' => null,
        'expires_at' => null
    ],
    [
        'id' => 5,
        'title' => 'Staff Training Session',
        'content' => 'All administrative staff are required to attend the ClearPay training session on October 28, 2024, at 2:00 PM in Conference Room A.',
        'type' => 'event',
        'priority' => 'high',
        'target_audience' => 'staff',
        'status' => 'published',
        'created_at' => '2024-10-21 11:20:00',
        'published_at' => '2024-10-21 11:20:00',
        'expires_at' => '2024-10-28 17:00:00'
    ],
    [
        'id' => 6,
        'title' => 'Holiday Schedule Update',
        'content' => 'Please note the updated holiday schedule for November. The office will be closed on November 1-2 for All Saints\' Day and All Souls\' Day.',
        'type' => 'general',
        'priority' => 'medium',
        'target_audience' => 'all',
        'status' => 'archived',
        'created_at' => '2024-10-10 13:00:00',
        'published_at' => '2024-10-10 13:00:00',
        'expires_at' => null
    ]
];

// Calculate status counts
$status_counts = [
    'total' => count($announcements),
    'published' => count(array_filter($announcements, fn($a) => $a['status'] === 'published')),
    'draft' => count(array_filter($announcements, fn($a) => $a['status'] === 'draft')),
    'archived' => count(array_filter($announcements, fn($a) => $a['status'] === 'archived'))
];
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Announcements Management</h1>
                    <p class="mb-0 text-muted">Create and manage system announcements for students and staff</p>
                </div>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#announcementModal">
                        <i class="fas fa-plus"></i> Add Announcement
                    </button>
                </div>
            </div>
        </div>
    </div>

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

    <!-- Filters and Search -->
    <?= view('partials/container-card', [
        'title' => 'Filter Announcements',
        'subtitle' => 'Search and filter announcements by status, priority, or content',
        'content' => '
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search announcements...">
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
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
                <div class="col-md-2 mb-3">
                    <label class="form-label">Audience</label>
                    <select class="form-select" id="audienceFilter">
                        <option value="">All Audience</option>
                        <option value="all">All Users</option>
                        <option value="students">Students</option>
                        <option value="staff">Staff</option>
                        <option value="admins">Admins</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
        '
    ]) ?>

    <!-- Announcements List -->
    <?= view('partials/container-card', [
        'title' => 'All Announcements',
        'subtitle' => 'Manage your announcements',
        'content' => '
            <div id="announcementsList">
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
                        <div class="card mb-3 announcement-card" data-status="' . $announcement['status'] . '" 
                             data-priority="' . $announcement['priority'] . '" data-audience="' . $announcement['target_audience'] . '">
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
                                    ' . nl2br(htmlspecialchars($announcement['content'])) . '
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-' . $priorityColor . '">' . ucfirst($announcement['priority']) . ' Priority</span>
                                    <span class="badge bg-' . $statusColor . '">' . ucfirst($announcement['status']) . '</span>
                                    <span class="badge bg-primary">' . ucfirst($announcement['target_audience']) . '</span>
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

    <!-- Quick Actions -->
    <?= view('partials/container-card', [
        'title' => 'Quick Actions',
        'subtitle' => 'Common announcement management tasks',
        'content' => '
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    ' . view('partials/quick-action', [
                        'icon' => 'plus',
                        'title' => 'Create Announcement',
                        'color' => 'primary',
                        'action' => 'data-bs-toggle="modal" data-bs-target="#announcementModal"'
                    ]) . '
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    ' . view('partials/quick-action', [
                        'icon' => 'eye',
                        'title' => 'Publish Drafts',
                        'color' => 'success',
                        'action' => 'onclick="publishDrafts()"'
                    ]) . '
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    ' . view('partials/quick-action', [
                        'icon' => 'file-export',
                        'title' => 'Export All',
                        'color' => 'info',
                        'action' => 'onclick="exportAnnouncements()"'
                    ]) . '
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    ' . view('partials/quick-action', [
                        'icon' => 'broom',
                        'title' => 'Cleanup Expired',
                        'color' => 'warning',
                        'action' => 'onclick="cleanupExpired()"'
                    ]) . '
                </div>
            </div>
        '
    ]) ?>
</div>

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
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="target_audience" class="form-label">Target Audience <span class="text-danger">*</span></label>
                            <select class="form-select" id="target_audience" name="target_audience" required>
                                <option value="">Select Audience</option>
                                <option value="all">All Users</option>
                                <option value="students">Students Only</option>
                                <option value="admins">Admins Only</option>
                                <option value="staff">Staff Only</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>
                    
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
    const statusFilter = document.getElementById('statusFilter').value;
    const priorityFilter = document.getElementById('priorityFilter').value;
    const audienceFilter = document.getElementById('audienceFilter').value;
    
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
    document.getElementById('statusFilter').value = '';
    document.getElementById('priorityFilter').value = '';
    document.getElementById('audienceFilter').value = '';
    filterAnnouncements();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('searchInput').addEventListener('input', filterAnnouncements);
    document.getElementById('statusFilter').addEventListener('change', filterAnnouncements);
    document.getElementById('priorityFilter').addEventListener('change', filterAnnouncements);
    document.getElementById('audienceFilter').addEventListener('change', filterAnnouncements);
    
    // Form submission
    document.getElementById('announcementForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // Add your form submission logic here
        alert('Form submission would happen here - connect to your controller');
    });
});

// Placeholder functions for actions
function editAnnouncement(id) {
    alert('Edit announcement ' + id + ' - connect to your controller');
}

function archiveAnnouncement(id) {
    if (confirm('Archive this announcement?')) {
        alert('Archive announcement ' + id + ' - connect to your controller');
    }
}

function deleteAnnouncement(id) {
    if (confirm('Delete this announcement? This cannot be undone.')) {
        alert('Delete announcement ' + id + ' - connect to your controller');
    }
}

function publishDrafts() {
    alert('Publish all drafts - connect to your controller');
}

function exportAnnouncements() {
    alert('Export announcements - connect to your controller');
}

function cleanupExpired() {
    if (confirm('Remove all expired announcements?')) {
        alert('Cleanup expired - connect to your controller');
    }
}
</script>
<?= $this->endSection() ?>