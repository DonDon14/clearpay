<?= $this->extend('layouts/payer-layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Announcements</h5>
                    <div class="search-container">
                        <div class="input-group" style="width: 300px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" 
                                   class="form-control border-start-0" 
                                   id="announcementSearch" 
                                   placeholder="Search announcements..."
                                   style="box-shadow: none;">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($announcements)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Announcements</h5>
                            <p class="text-muted">Check back later for updates</p>
                        </div>
                    <?php else: ?>
                        <div class="announcements-list" id="announcementsList">
                            <?php foreach ($announcements as $announcement): ?>
                                <div class="announcement-item" 
                                     data-title="<?= esc(strtolower($announcement['title'])) ?>"
                                     data-content="<?= esc(strtolower($announcement['text'])) ?>"
                                     onclick="showAnnouncementModal(<?= htmlspecialchars(json_encode($announcement)) ?>)">
                                    <div class="announcement-icon">
                                        <i class="fas fa-<?= $announcement['type'] === 'urgent' ? 'exclamation-triangle text-warning' : 'info-circle text-info' ?>"></i>
                                    </div>
                                    <div class="announcement-content">
                                        <div class="announcement-header">
                                            <h6 class="announcement-title"><?= esc($announcement['title']) ?></h6>
                                            <span class="badge bg-<?= $announcement['priority'] === 'critical' ? 'danger' : ($announcement['priority'] === 'high' ? 'warning' : 'info') ?>">
                                                <?= esc(ucfirst($announcement['priority'])) ?>
                                            </span>
                                        </div>
                                        <div class="announcement-date">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('M d, Y g:i A', strtotime($announcement['created_at'])) ?>
                                        </div>
                                    </div>
                                    <div class="announcement-arrow">
                                        <i class="fas fa-chevron-right text-muted"></i>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="no-results text-center py-4" id="noResults" style="display: none;">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No announcements found</h5>
                            <p class="text-muted">Try adjusting your search terms</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Announcement Detail Modal -->
<div class="modal fade" id="announcementDetailModal" tabindex="-1" aria-labelledby="announcementDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" id="modalHeader">
                <h5 class="modal-title" id="announcementDetailModalLabel">
                    <i class="fas fa-bullhorn me-2"></i>Announcement Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.announcements-list {
    max-height: 600px;
    overflow-y: auto;
}

.announcement-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #ffffff;
}

.announcement-item:hover {
    background: #f8f9fa;
    border-color: #3b82f6;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.announcement-icon {
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.announcement-content {
    flex: 1;
    min-width: 0;
}

.announcement-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.25rem;
}

.announcement-title {
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    margin: 0;
    line-height: 1.3;
}

.announcement-date {
    font-size: 0.875rem;
    color: #6b7280;
    display: flex;
    align-items: center;
}

.announcement-arrow {
    margin-left: 1rem;
    flex-shrink: 0;
}

.search-container .input-group-text {
    border-color: #d1d5db;
}

.search-container .form-control {
    border-color: #d1d5db;
}

.search-container .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
}

/* Priority-specific modal header colors */
.priority-critical .modal-header {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
}

.priority-high .modal-header {
    background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);
    color: white;
}

.priority-normal .modal-header {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    color: white;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .search-container .input-group {
        width: 100% !important;
    }
    
    .announcement-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .announcement-header .badge {
        margin-top: 0.25rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('announcementSearch');
    const announcementsList = document.getElementById('announcementsList');
    const noResults = document.getElementById('noResults');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const announcementItems = announcementsList.querySelectorAll('.announcement-item');
            let visibleCount = 0;
            
            announcementItems.forEach(item => {
                const title = item.dataset.title || '';
                const content = item.dataset.content || '';
                
                if (title.includes(searchTerm) || content.includes(searchTerm)) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            if (visibleCount === 0 && searchTerm !== '') {
                noResults.style.display = 'block';
                announcementsList.style.display = 'none';
            } else {
                noResults.style.display = 'none';
                announcementsList.style.display = 'block';
            }
        });
    }
});

function showAnnouncementModal(announcement) {
    const modal = new bootstrap.Modal(document.getElementById('announcementDetailModal'));
    const modalLabel = document.getElementById('announcementDetailModalLabel');
    const modalBody = document.getElementById('modalBody');
    const modalHeader = document.getElementById('modalHeader');
    
    // Set modal title
    modalLabel.innerHTML = `<i class="fas fa-bullhorn me-2"></i>${announcement.title}`;
    
    // Set modal header class based on priority
    modalHeader.className = `modal-header priority-${announcement.priority}`;
    
    // Populate modal body
    modalBody.innerHTML = `
        <div class="announcement-detail">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong><i class="fas fa-tag me-2"></i>Type:</strong>
                    <span class="badge bg-${announcement.type === 'urgent' ? 'warning' : 'info'} ms-2">
                        ${announcement.type === 'urgent' ? 'Urgent' : 'Normal'}
                    </span>
                </div>
                <div class="col-md-6">
                    <strong><i class="fas fa-exclamation-circle me-2"></i>Priority:</strong>
                    <span class="badge bg-${announcement.priority === 'critical' ? 'danger' : (announcement.priority === 'high' ? 'warning' : 'info')} ms-2">
                        ${announcement.priority.charAt(0).toUpperCase() + announcement.priority.slice(1)}
                    </span>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong><i class="fas fa-calendar me-2"></i>Created:</strong>
                    ${new Date(announcement.created_at).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    })}
                </div>
                <div class="col-md-6">
                    <strong><i class="fas fa-users me-2"></i>Target:</strong>
                    ${announcement.target_audience.charAt(0).toUpperCase() + announcement.target_audience.slice(1)}
                </div>
            </div>
            
            <hr>
            
            <div class="announcement-content">
                <h6 class="mb-3"><i class="fas fa-file-text me-2"></i>Content:</h6>
                <div class="announcement-text p-3 bg-light rounded">
                    ${announcement.text.replace(/\n/g, '<br>')}
                </div>
            </div>
        </div>
    `;
    
    modal.show();
}
</script>
<?= $this->endSection() ?>
