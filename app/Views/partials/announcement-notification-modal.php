<!-- Announcement Notification Modal -->
<div class="modal fade" id="announcementNotificationModal" tabindex="-1" aria-labelledby="announcementNotificationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white border-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="announcement-icon me-3">
                        <i class="fas fa-bullhorn fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="announcementNotificationModalLabel">
                            <i class="fas fa-bell me-2"></i>New Announcement
                        </h5>
                        <small class="opacity-75">Important Update</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <!-- Announcement Content -->
                <div class="announcement-content">
                    <div class="announcement-header mb-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h4 class="announcement-title text-primary mb-0" id="announcementTitle">
                                <!-- Title will be populated here -->
                            </h4>
                            <span class="badge" id="announcementPriority">
                                <!-- Priority badge will be populated here -->
                            </span>
                        </div>
                        <div class="announcement-meta text-muted small">
                            <i class="fas fa-calendar me-1"></i>
                            <span id="announcementDate">Just now</span>
                            <span class="mx-2">•</span>
                            <i class="fas fa-user me-1"></i>
                            <span id="announcementAuthor">System Administrator</span>
                        </div>
                    </div>

                    <div class="announcement-body">
                        <div class="alert alert-info border-0 mb-3" id="announcementAlert">
                            <i class="fas fa-info-circle me-2"></i>
                            <span id="announcementType">General Announcement</span>
                        </div>
                        
                        <div class="announcement-text" id="announcementText">
                            <!-- Announcement text will be populated here -->
                        </div>
                    </div>
                </div>

                <!-- Visual Elements -->
                <div class="announcement-visual mt-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center py-3">
                                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                                    <h6 class="mb-1">Priority Level</h6>
                                    <span class="badge fs-6" id="announcementPriorityBadge">Normal</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center py-3">
                                    <i class="fas fa-clock fa-2x text-info mb-2"></i>
                                    <h6 class="mb-1">Posted</h6>
                                    <span class="text-muted" id="announcementTime">Just now</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <button type="button" class="btn btn-primary" onclick="viewAllAnnouncements()">
                    <i class="fas fa-list me-1"></i>View All Announcements
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.announcement-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.announcement-title {
    font-weight: 700;
    line-height: 1.3;
}

.announcement-text {
    font-size: 1rem;
    line-height: 1.6;
    color: #333;
}

.announcement-visual .card {
    transition: transform 0.2s ease;
}

.announcement-visual .card:hover {
    transform: translateY(-2px);
}

.modal-content {
    border-radius: 15px;
    overflow: hidden;
}

.modal-header {
    border-radius: 15px 15px 0 0;
}

/* Priority-specific styling */
.priority-critical .modal-header {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}

.priority-high .modal-header {
    background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);
}

.priority-normal .modal-header {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

/* Auto-show animation */
#announcementNotificationModal.show {
    animation: slideInDown 0.5s ease-out;
}

@keyframes slideInDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>

<script>
// Global variable to store current announcement data
let currentAnnouncementData = null;

// Function to show announcement notification
function showAnnouncementNotification(announcementData) {
    console.log('Showing announcement notification:', announcementData);
    currentAnnouncementData = announcementData;
    
    // Show notification badge
    showNotificationBadge();
    
    // Update dropdown content
    updateNotificationDropdown(announcementData);
    
    // Populate modal content
    document.getElementById('announcementTitle').textContent = announcementData.title || 'New Announcement';
    document.getElementById('announcementText').textContent = announcementData.text || 'No content available.';
    // Use backend-formatted date if available, otherwise format on frontend
    const dateElement = document.getElementById('announcementDate');
    if (announcementData.created_at_date) {
        dateElement.textContent = announcementData.created_at_date;
    } else {
        dateElement.textContent = formatAnnouncementDate(announcementData.created_at);
    }
    document.getElementById('announcementAuthor').textContent = announcementData.author || announcementData.created_by_name || 'Administrator';
    // Use backend-formatted time if available, otherwise format on frontend
    const timeElement = document.getElementById('announcementTime');
    if (announcementData.created_at_time) {
        timeElement.textContent = announcementData.created_at_time;
    } else {
        timeElement.textContent = formatAnnouncementTime(announcementData.created_at);
    }
    
    // Set priority and type
    const priority = announcementData.priority || 'normal';
    const type = announcementData.type || 'general';
    
    // Update priority badge
    const priorityBadge = document.getElementById('announcementPriority');
    const priorityBadgeFooter = document.getElementById('announcementPriorityBadge');
    
    let priorityClass, priorityText;
    switch(priority.toLowerCase()) {
        case 'critical':
            priorityClass = 'bg-danger text-white';
            priorityText = 'CRITICAL';
            break;
        case 'high':
            priorityClass = 'bg-warning text-dark';
            priorityText = 'HIGH';
            break;
        default:
            priorityClass = 'bg-info text-white';
            priorityText = 'NORMAL';
    }
    
    priorityBadge.className = `badge ${priorityClass}`;
    priorityBadge.textContent = priorityText;
    priorityBadgeFooter.className = `badge ${priorityClass}`;
    priorityBadgeFooter.textContent = priorityText;
    
    // Update type alert
    const typeAlert = document.getElementById('announcementAlert');
    const typeText = document.getElementById('announcementType');
    
    let alertClass, typeDisplay;
    switch(type.toLowerCase()) {
        case 'urgent':
            alertClass = 'alert-danger';
            typeDisplay = 'Urgent Announcement';
            break;
        case 'important':
            alertClass = 'alert-warning';
            typeDisplay = 'Important Update';
            break;
        default:
            alertClass = 'alert-info';
            typeDisplay = 'General Announcement';
    }
    
    typeAlert.className = `alert ${alertClass} border-0 mb-3`;
    typeText.textContent = typeDisplay;
    
    // Add priority class to modal for styling
    const modalContent = document.querySelector('#announcementNotificationModal .modal-content');
    modalContent.className = `modal-content border-0 shadow-lg priority-${priority}`;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('announcementNotificationModal'));
    modal.show();
    
    // Add event listener for modal close
    document.getElementById('announcementNotificationModal').addEventListener('hidden.bs.modal', function() {
        // Check if user clicked "View All Announcements" button
        const clickedViewAll = localStorage.getItem('announcementViewed');
        if (clickedViewAll === 'true') {
            // User read the announcement, hide the badge
            hideNotificationBadge();
            localStorage.removeItem('announcementViewed');
        }
        // If user just closed without reading, badge stays visible
    });
    
    // Play notification sound (optional)
    playNotificationSound();
}

// Function to show notification badge
function showNotificationBadge() {
    const badge = document.getElementById('notificationBadge');
    if (badge) {
        badge.textContent = '1';
        badge.style.display = 'inline-block';
    }
}

// Function to hide notification badge
function hideNotificationBadge() {
    const badge = document.getElementById('notificationBadge');
    if (badge) {
        badge.style.display = 'none';
    }
}

// Function to update notification dropdown
function updateNotificationDropdown(announcementData) {
    const latestAnnouncement = document.getElementById('latestAnnouncement');
    const noNotifications = document.getElementById('noNotifications');
    const dropdownTitle = document.getElementById('dropdownAnnouncementTitle');
    const dropdownText = document.getElementById('dropdownAnnouncementText');
    const dropdownTime = document.getElementById('dropdownAnnouncementTime');
    
    if (latestAnnouncement && dropdownTitle && dropdownText && dropdownTime) {
        dropdownTitle.textContent = announcementData.title || 'New Announcement';
        dropdownText.textContent = announcementData.text || 'Click to view details';
        // Use backend-formatted date if available, otherwise format on frontend
        if (announcementData.created_at_date) {
            dropdownTime.textContent = announcementData.created_at_date;
        } else {
            dropdownTime.textContent = formatAnnouncementDate(announcementData.created_at);
        }
        
        latestAnnouncement.style.display = 'flex';
        if (noNotifications) {
            noNotifications.style.display = 'none';
        }
    }
}

// Function to format announcement date
function formatAnnouncementDate(dateString) {
    if (!dateString) return 'Just now';
    
    // Parse the date string and handle timezone properly
    const date = new Date(dateString);
    const now = new Date();
    
    // Debug logging
    console.log('Original date string:', dateString);
    console.log('Parsed date:', date);
    console.log('Current time:', now);
    
    // Calculate difference in milliseconds
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    
    console.log('Time difference in minutes:', diffMins);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    
    const diffDays = Math.floor(diffHours / 24);
    if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    
    const diffWeeks = Math.floor(diffDays / 7);
    if (diffWeeks < 4) return `${diffWeeks} week${diffWeeks > 1 ? 's' : ''} ago`;
    
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone
    });
}

// Function to format announcement time
function formatAnnouncementTime(dateString) {
    if (!dateString) return 'Just now';
    
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Function to play notification sound
function playNotificationSound() {
    try {
        // Create a simple notification sound using Web Audio API
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
        oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);
        
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.2);
    } catch (error) {
        console.log('Could not play notification sound:', error);
    }
}

// Function to view all announcements
function viewAllAnnouncements() {
    // Mark announcement as viewed
    localStorage.setItem('announcementViewed', 'true');
    
    // Close the notification modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('announcementNotificationModal'));
    if (modal) {
        modal.hide();
    }
    
    // Navigate to announcements page
    window.location.href = window.APP_BASE_URL + 'payer/announcements';
}

// Function to view announcement from dropdown
function viewAnnouncement() {
    // Mark announcement as viewed
    localStorage.setItem('announcementViewed', 'true');
    
    // Close notification dropdown
    closeNotificationDropdown();
    
    // Navigate to announcements page
    window.location.href = window.APP_BASE_URL + 'payer/announcements';
}

// Function to close notification dropdown
function closeNotificationDropdown() {
    const dropdown = document.getElementById('notificationDropdown');
    if (dropdown) {
        dropdown.classList.remove('active');
    }
}

// Function to check for new announcements
function checkForNewAnnouncements() {
    console.log('Checking for new announcements...');
    
    // Get the last shown announcement ID from localStorage
    const lastShownId = localStorage.getItem('lastShownAnnouncementId');
    console.log('Last shown announcement ID:', lastShownId);
    
    // Build URL with last shown ID
    const url = `${window.APP_BASE_URL}payer/check-new-announcements?last_shown_id=${lastShownId || 0}`;
    console.log('Checking URL:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Announcement check response:', data);
            
            if (data.success && data.announcement) {
                console.log('New announcement found:', data.announcement);
                console.log('Last shown ID:', lastShownId, 'Current ID:', data.announcement.id);
                
                if (lastShownId !== data.announcement.id.toString()) {
                    console.log('Showing new announcement notification');
                    showAnnouncementNotification(data.announcement);
                    localStorage.setItem('lastShownAnnouncementId', data.announcement.id.toString());
                } else {
                    console.log('Announcement already shown (IDs match)');
                }
            } else {
                console.log('No new announcements found:', data.message);
            }
        })
        .catch(error => {
            console.error('Error checking for new announcements:', error);
        });
}

// Start checking for new announcements every 30 seconds
document.addEventListener('DOMContentLoaded', function() {
    // Check immediately on page load
    setTimeout(checkForNewAnnouncements, 2000);
    
    // Then check every 30 seconds
    setInterval(checkForNewAnnouncements, 30000);
    
    // Add manual test buttons for debugging (remove in production)
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        // Test Announcement Button
        const testButton = document.createElement('button');
        testButton.textContent = 'Test Announcement';
        testButton.style.cssText = 'position: fixed; top: 10px; right: 10px; z-index: 9999; background: red; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;';
        testButton.onclick = function() {
            console.log('Manual test triggered');
            checkForNewAnnouncements();
        };
        document.body.appendChild(testButton);
        
        // Clear localStorage Button
        const clearButton = document.createElement('button');
        clearButton.textContent = 'Clear Cache';
        clearButton.style.cssText = 'position: fixed; top: 50px; right: 10px; z-index: 9999; background: orange; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;';
        clearButton.onclick = function() {
            console.log('Clearing localStorage...');
            localStorage.removeItem('lastShownAnnouncementId');
            console.log('localStorage cleared. Next check should show new announcements.');
        };
        document.body.appendChild(clearButton);
    }
});

// Make functions globally available
window.showAnnouncementNotification = showAnnouncementNotification;
window.checkForNewAnnouncements = checkForNewAnnouncements;
window.showNotificationBadge = showNotificationBadge;
window.hideNotificationBadge = hideNotificationBadge;
window.updateNotificationDropdown = updateNotificationDropdown;
window.viewAnnouncement = viewAnnouncement;
window.closeNotificationDropdown = closeNotificationDropdown;
</script>
