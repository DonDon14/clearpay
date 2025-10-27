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

/* Enhanced Notification Styles */
.unread-notification {
    background-color: #f8f9fa;
    border-left: 3px solid #007bff;
}

.unread-indicator {
    width: 8px;
    height: 8px;
    background-color: #dc3545;
    border-radius: 50%;
    position: absolute;
    top: 10px;
    right: 10px;
}

.notification-item {
    position: relative;
    cursor: pointer;
    transition: all 0.2s ease;
}

.notification-item:hover {
    background-color: #e9ecef;
    transform: translateX(2px);
}

.notification-item.show-more {
    border-top: 1px solid #dee2e6;
    background-color: #f8f9fa;
    font-style: italic;
}

.notification-item.show-more:hover {
    background-color: #e9ecef;
}

.notifications-list {
    max-height: 400px;
    overflow-y: auto;
}

.notifications-list .notification-item {
    border-bottom: 1px solid #f1f3f4;
    padding: 12px;
}

.notifications-list .notification-item:last-child {
    border-bottom: none;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .notification-dropdown {
        width: 300px;
        right: -50px;
    }
    
    .notifications-list {
        max-height: 300px;
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

// Global variables for notification management - Facebook-like logic
let currentActivities = [];
let unreadActivityIds = new Set(); // Activities that haven't been individually clicked (read)
let unseenActivityIds = new Set(); // Activities that haven't been seen (bell not clicked)
let lastShownActivityId = 0;

// Function to check for new activities
function checkForNewActivities() {
    console.log('Checking for new activities...');
    
    // Get the last shown activity ID from localStorage
    const storedLastShownId = localStorage.getItem('lastShownActivityId');
    lastShownActivityId = storedLastShownId ? parseInt(storedLastShownId) : 0;
    
    console.log('Last shown activity ID:', lastShownActivityId);
    
    // Build URL with last shown ID
    const url = `${window.APP_BASE_URL}payer/check-new-activities?last_shown_id=${lastShownActivityId}`;
    console.log('Checking URL:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Activity check response:', data);
            
            if (data.success && data.activities) {
                currentActivities = data.activities;
                
                // DON'T clear the unread set - preserve user's read status
                // Only add new activities that are truly unread
                
                if (data.activities && data.activities.length > 0) {
                    // Process activities for Facebook-like logic
                    data.activities.forEach(activity => {
                        const activityIdStr = String(activity.id);
                        
                        // Only add to unseen set if it's a NEW activity (not already processed)
                        // This prevents adding all existing activities as "unseen"
                        if (data.newActivities && data.newActivities.some(newActivity => String(newActivity.id) === activityIdStr)) {
                            if (!unseenActivityIds.has(activityIdStr)) {
                                unseenActivityIds.add(activityIdStr);
                                console.log('Added new unseen activity:', activityIdStr);
                            }
                        }
                        
                        // Add to unread set if not individually read
                        if (!activity.is_read_by_payer && !unreadActivityIds.has(activityIdStr)) {
                            unreadActivityIds.add(activityIdStr);
                            console.log('Added new unread activity:', activityIdStr);
                        }
                    });
                    
                    console.log('Current unseen activities:', unseenActivityIds.size);
                    console.log('Current unread activities:', unreadActivityIds.size);
                }
                
                // Update unread activities set with new activities (if any)
                if (data.newActivities && data.newActivities.length > 0) {
                    console.log('New activities found:', data.newActivities.length);
                    
                    // Show notification badge
                    showNotificationBadge();
                    
                    // DON'T show popup - just update dropdown
                    // The user will see notifications in the dropdown
                } else {
                    console.log('No new activities found');
                    // Still show badge if there are unseen activities (Facebook-like)
                    if (unseenActivityIds.size > 0) {
                        showNotificationBadge();
                    }
                }
                
                // Update dropdown with all activities
                updateNotificationDropdown(data.activities);
                
                // Update last shown ID to the highest activity ID
                if (data.activities.length > 0) {
                    const maxId = Math.max(...data.activities.map(a => a.id));
                    localStorage.setItem('lastShownActivityId', maxId.toString());
                    lastShownActivityId = maxId;
                }
            } else {
                console.log('No activities found:', data.message);
                hideNotificationBadge();
            }
        })
        .catch(error => {
            console.error('Error checking for new activities:', error);
        });
}

// Function to show activity notification (popup)
function showActivityNotification(activityData) {
    console.log('Showing activity notification:', activityData);
    
    // Populate modal content
    document.getElementById('announcementTitle').textContent = activityData.title || 'New Activity';
    document.getElementById('announcementText').textContent = activityData.description || 'No details available.';
    
    // Use backend-formatted date if available, otherwise format on frontend
    const dateElement = document.getElementById('announcementDate');
    if (activityData.created_at_date) {
        dateElement.textContent = activityData.created_at_date;
    } else {
        dateElement.textContent = formatAnnouncementDate(activityData.created_at);
    }
    
    document.getElementById('announcementAuthor').textContent = 'System';
    
    // Use backend-formatted time if available, otherwise format on frontend
    const timeElement = document.getElementById('announcementTime');
    if (activityData.created_at_time) {
        timeElement.textContent = activityData.created_at_time;
    } else {
        timeElement.textContent = formatAnnouncementTime(activityData.created_at);
    }
    
    // Set activity type and action
    const activityType = activityData.activity_type || 'general';
    const action = activityData.action || 'created';
    
    // Update type badge
    const typeBadge = document.getElementById('announcementType');
    const typeText = document.getElementById('announcementTypeText');
    
    let typeDisplay, alertClass;
    switch(activityType) {
        case 'announcement':
            typeDisplay = 'Announcement Update';
            alertClass = 'alert-info';
            break;
        case 'contribution':
            typeDisplay = 'Contribution Update';
            alertClass = 'alert-success';
            break;
        case 'payment':
            typeDisplay = 'Payment Update';
            alertClass = 'alert-warning';
            break;
        case 'payer':
            typeDisplay = 'Account Update';
            alertClass = 'alert-primary';
            break;
        default:
            typeDisplay = 'System Update';
            alertClass = 'alert-secondary';
    }
    
    typeBadge.className = `alert ${alertClass} border-0 mb-3`;
    typeText.textContent = typeDisplay;
    
    // Update priority badge based on action
    const priorityBadge = document.getElementById('announcementPriority');
    const priorityBadgeFooter = document.getElementById('announcementPriorityBadge');
    
    let priorityClass, priorityText;
    switch(action) {
        case 'created':
            priorityClass = 'bg-success';
            priorityText = 'NEW';
            break;
        case 'updated':
            priorityClass = 'bg-warning';
            priorityText = 'UPDATED';
            break;
        case 'deleted':
            priorityClass = 'bg-danger';
            priorityText = 'REMOVED';
            break;
        case 'published':
            priorityClass = 'bg-primary';
            priorityText = 'PUBLISHED';
            break;
        default:
            priorityClass = 'bg-info';
            priorityText = 'CHANGE';
    }
    
    priorityBadge.className = `badge ${priorityClass}`;
    priorityBadgeFooter.className = `badge ${priorityClass}`;
    priorityBadge.textContent = priorityText;
    priorityBadgeFooter.textContent = priorityText;
    
    // Add activity class to modal for styling
    const modalContent = document.querySelector('#announcementNotificationModal .modal-content');
    modalContent.className = `modal-content border-0 shadow-lg activity-${activityType}`;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('announcementNotificationModal'));
    modal.show();
    
    // Add event listener for modal close
    document.getElementById('announcementNotificationModal').addEventListener('hidden.bs.modal', function() {
        // Check if user clicked "View All Notifications" button
        const clickedViewAll = localStorage.getItem('announcementViewed');
        if (clickedViewAll === 'true') {
            // User read the activity, hide the badge
            hideNotificationBadge();
            localStorage.removeItem('announcementViewed');
        }
        // If user just closed without reading, badge stays visible
    });
    
    // Play notification sound (optional)
    playNotificationSound();
}

// Function to update notification dropdown with multiple activities - Facebook-like logic
function updateNotificationDropdown(activities) {
    const noNotifications = document.getElementById('noNotifications');
    const dropdownContent = document.getElementById('notificationContent');
    
    if (!activities || activities.length === 0) {
        if (noNotifications) noNotifications.style.display = 'block';
        return;
    }
    
    // Hide no notifications message
    if (noNotifications) noNotifications.style.display = 'none';
    
    // Show 5+ most recent notifications (Facebook-like behavior)
    const recentActivities = activities.slice(0, 5); // Show 5 most recent
    
    // Create notification items for recent activities
    let html = '';
    recentActivities.forEach(activity => {
        const activityIdStr = String(activity.id);
        const isUnread = unreadActivityIds.has(activityIdStr);
        
        html += `
            <div class="notification-item ${isUnread ? 'unread-notification' : ''}" 
                 data-activity-id="${activity.id}"
                 data-activity-type="${activity.activity_type}"
                 data-entity-id="${activity.entity_id}"
                 onclick="console.log('Click on activity ${activity.id}'); handleNotificationClick(${activity.id}, '${activity.activity_type}', ${activity.entity_id});"
                 style="cursor: pointer;">
                <div class="notification-icon">
                    <i class="${activity.activity_icon} text-${activity.activity_color}"></i>
                </div>
                <div class="notification-body">
                    <h6 class="notification-title">${activity.title}</h6>
                    <p class="notification-text">${activity.description}</p>
                    <small class="notification-time">${activity.created_at_date}</small>
                </div>
                ${isUnread ? '<div class="unread-indicator"></div>' : ''}
            </div>
        `;
    });
    
    // Add "show more" if there are more than 5 activities
    if (activities.length > 5) {
        html += `
            <div class="notification-item show-more" onclick="showAllNotificationsModal()" style="cursor: pointer;">
                <div class="notification-icon">
                    <i class="fas fa-ellipsis-h text-muted"></i>
                </div>
                <div class="notification-body">
                    <h6 class="notification-title">Show ${activities.length - 5} more notifications</h6>
                </div>
            </div>
        `;
    }
    
    dropdownContent.innerHTML = html;
    
    console.log('Dropdown updated with', recentActivities.length, 'recent activities');
    console.log('Unread count:', unreadActivityIds.size);
}

// Function to add event listeners to notification items
function addNotificationEventListeners() {
    const notificationItems = document.querySelectorAll('.notification-item[data-activity-id]');
    console.log('Adding event listeners to', notificationItems.length, 'notification items');
    
    notificationItems.forEach(item => {
        const activityId = parseInt(item.getAttribute('data-activity-id'));
        const activityType = item.getAttribute('data-activity-type');
        const entityId = parseInt(item.getAttribute('data-entity-id'));
        
        console.log('Setting up events for activity:', activityId);
        
        // Add hover event listener
        item.addEventListener('mouseenter', function() {
            console.log('Hover detected on activity:', activityId);
            markActivityAsRead(activityId);
        });
        
        // Add click event listener
        item.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Click detected on activity:', activityId);
            handleNotificationClick(activityId, activityType, entityId);
        });
        
        // Add a visual test - change background on hover to confirm events work
        item.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#e3f2fd';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
}

// Function to handle notification click
function handleNotificationClick(activityId, activityType, entityId) {
    console.log('Notification clicked:', activityId, activityType, entityId);
    
    // Mark as read immediately and wait for UI update
    markActivityAsRead(activityId);
    
    // Close the dropdown
    closeNotificationDropdown();
    
    // Small delay to ensure UI updates before redirect
    setTimeout(() => {
        // Navigate based on activity type
        switch(activityType) {
            case 'announcement':
                window.location.href = `${window.APP_BASE_URL}payer/announcements`;
                break;
            case 'contribution':
                window.location.href = `${window.APP_BASE_URL}payer/contributions`;
                break;
            case 'payment':
                window.location.href = `${window.APP_BASE_URL}payer/payment-history`;
                break;
            case 'payer':
                window.location.href = `${window.APP_BASE_URL}payer/my-data`;
                break;
            default:
                window.location.href = `${window.APP_BASE_URL}payer/dashboard`;
        }
    }, 100);
}

// Function to mark activity as read
function markActivityAsRead(activityId) {
    console.log('=== MARKING ACTIVITY AS READ ===');
    console.log('Activity ID:', activityId);
    console.log('Type of activityId:', typeof activityId);
    console.log('Current unread activities:', Array.from(unreadActivityIds));
    
    // Convert activityId to string to match the set's data type
    const activityIdStr = String(activityId);
    console.log('Activity ID as string:', activityIdStr);
    console.log('Is activity in unread set?', unreadActivityIds.has(activityIdStr));
    
    if (unreadActivityIds.has(activityIdStr)) {
        unreadActivityIds.delete(activityIdStr);
        console.log('Activity marked as read. Remaining unread:', unreadActivityIds.size);
        
        // DON'T update badge here - badge shows unseen count, not unread count
        // updateNotificationBadge();
        
        // Update UI immediately - remove unread styling and red dot
        const notificationItem = document.querySelector(`[data-activity-id="${activityId}"]`);
        console.log('Found notification item:', notificationItem);
        
        if (notificationItem) {
            notificationItem.classList.remove('unread-notification');
            const unreadIndicator = notificationItem.querySelector('.unread-indicator');
            console.log('Found unread indicator:', unreadIndicator);
            
            if (unreadIndicator) {
                unreadIndicator.remove();
                console.log('Unread indicator removed');
            }
            console.log('UI updated for activity:', activityId);
            
            // Add a visual feedback that it was read
            notificationItem.style.opacity = '0.6';
            notificationItem.style.transition = 'opacity 0.3s ease';
        } else {
            console.log('Notification item not found for activity:', activityId);
        }
        
        // Refresh the dropdown to hide the read notification
        updateNotificationDropdown(currentActivities);
        
        // Send to server
        fetch(`${window.APP_BASE_URL}payer/mark-activity-read/${activityId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        }).then(response => {
            console.log('Server response for marking as read:', response.status);
        }).catch(error => {
            console.error('Error marking activity as read:', error);
        });
    } else {
        console.log('Activity not found in unread set:', activityId);
        console.log('Available unread activities:', Array.from(unreadActivityIds));
    }
    console.log('=== END MARKING ACTIVITY AS READ ===');
}

// Function to update notification badge
function updateNotificationBadge() {
    const badge = document.getElementById('notificationBadge');
    const count = unseenActivityIds.size; // Show unseen count (Facebook-like)
    
    console.log('Updating notification badge. Unseen count:', count);
    console.log('Unseen activity IDs:', Array.from(unseenActivityIds));
    
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'inline-block';
        console.log('Badge shown with unseen count:', count);
    } else {
        badge.style.display = 'none';
        console.log('Badge hidden - no unseen activities');
    }
}

// Function to show notification badge
function showNotificationBadge() {
    updateNotificationBadge();
}

// Function to mark all notifications as seen (Facebook-like bell click behavior)
function markAllAsSeen() {
    console.log('=== MARKING ALL AS SEEN ===');
    console.log('Unseen activities before:', Array.from(unseenActivityIds));
    
    // Clear unseen set (Facebook-like behavior)
    unseenActivityIds.clear();
    
    // Update badge (should disappear)
    updateNotificationBadge();
    
    console.log('All notifications marked as seen');
    console.log('=== END MARKING ALL AS SEEN ===');
}

// Function to hide notification badge
function hideNotificationBadge() {
    unreadActivityIds.clear();
    updateNotificationBadge();
}

// Function to show all notifications modal
function showAllNotificationsModal() {
    fetch(`${window.APP_BASE_URL}payer/get-all-activities`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.activities) {
                // Create modal content
                let html = `
                    <div class="modal fade" id="allNotificationsModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-bell me-2"></i>All Notifications
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="notifications-list">
                `;
                
                data.activities.forEach(activity => {
                    const isUnread = unreadActivityIds.has(activity.id);
                    const unreadClass = isUnread ? 'unread-notification' : '';
                    
                    html += `
                        <div class="notification-item ${unreadClass}" 
                             data-activity-id="${activity.id}"
                             onclick="handleNotificationClick(${activity.id}, '${activity.activity_type}', ${activity.entity_id})"
                             onmouseover="markActivityAsRead(${activity.id})">
                            <div class="notification-icon">
                                <i class="${activity.activity_icon} text-${activity.activity_color}"></i>
                            </div>
                            <div class="notification-body">
                                <h6 class="notification-title">${activity.title}</h6>
                                <p class="notification-text">${activity.description}</p>
                                <small class="notification-time">${activity.created_at_date} at ${activity.created_at_time}</small>
                            </div>
                            ${isUnread ? '<div class="unread-indicator"></div>' : ''}
                        </div>
                    `;
                });
                
                html += `
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" onclick="markAllAsRead()">Mark All as Read</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Remove existing modal if any
                const existingModal = document.getElementById('allNotificationsModal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                // Add modal to body
                document.body.insertAdjacentHTML('beforeend', html);
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('allNotificationsModal'));
                modal.show();
            }
        })
        .catch(error => {
            console.error('Error fetching all activities:', error);
        });
}

// Function to mark all notifications as read
function markAllAsRead() {
    console.log('Marking all notifications as read');
    
    // Get all current unread activity IDs
    const unreadIds = Array.from(unreadActivityIds);
    
    if (unreadIds.length === 0) {
        console.log('No unread notifications to mark');
        return;
    }
    
    // Clear the unread set
    unreadActivityIds.clear();
    
    // Update badge
    updateNotificationBadge();
    
    // Refresh dropdown to show "no notifications"
    updateNotificationDropdown(currentActivities);
    
    // Send to server for each notification
    const promises = unreadIds.map(activityId => {
        return fetch(`${window.APP_BASE_URL}payer/mark-activity-read/${activityId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        });
    });
    
    Promise.all(promises)
        .then(responses => {
            console.log('All notifications marked as read on server');
        })
        .catch(error => {
            console.error('Error marking all notifications as read:', error);
        });
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('allNotificationsModal'));
    if (modal) {
        modal.hide();
    }
}

// Function to view announcement (for backward compatibility)
function viewAnnouncement() {
    localStorage.setItem('announcementViewed', 'true');
    window.location.href = `${window.APP_BASE_URL}payer/announcements`;
}

// Function to close notification dropdown
function closeNotificationDropdown() {
    const dropdown = document.getElementById('notificationDropdown');
    if (dropdown) {
        dropdown.classList.remove('active');
    }
}

// Start checking for new activities every 30 seconds
document.addEventListener('DOMContentLoaded', function() {
    console.log('Notification system initialized');
    
    // Initialize notification system
    initializeNotificationSystem();
    
    // Check immediately on page load
    setTimeout(checkForNewActivities, 2000);
    
    // Then check every 30 seconds
    setInterval(checkForNewActivities, 30000);
    
});

// Function to initialize notification system
function initializeNotificationSystem() {
    console.log('Initializing notification system...');
    
    // Clear both sets to start fresh
    unseenActivityIds.clear();
    unreadActivityIds.clear();
    
    console.log('Notification system initialized with empty sets');
}

// Function to show activity notification (replaces announcement notification)
function showActivityNotification(activityData) {
    console.log('Showing activity notification:', activityData);
    
    // Show notification badge
    showNotificationBadge();
    
    // Update dropdown content
    updateActivityDropdown(activityData);
    
    // Populate modal content
    document.getElementById('announcementTitle').textContent = activityData.title || 'New Activity';
    document.getElementById('announcementText').textContent = activityData.description || 'No details available.';
    
    // Use backend-formatted date if available, otherwise format on frontend
    const dateElement = document.getElementById('announcementDate');
    if (activityData.created_at_date) {
        dateElement.textContent = activityData.created_at_date;
    } else {
        dateElement.textContent = formatAnnouncementDate(activityData.created_at);
    }
    
    document.getElementById('announcementAuthor').textContent = 'System';
    
    // Use backend-formatted time if available, otherwise format on frontend
    const timeElement = document.getElementById('announcementTime');
    if (activityData.created_at_time) {
        timeElement.textContent = activityData.created_at_time;
    } else {
        timeElement.textContent = formatAnnouncementTime(activityData.created_at);
    }
    
    // Set activity type and action
    const activityType = activityData.activity_type || 'general';
    const action = activityData.action || 'created';
    
    // Update type badge
    const typeBadge = document.getElementById('announcementType');
    const typeText = document.getElementById('announcementTypeText');
    
    let typeDisplay, alertClass;
    switch(activityType) {
        case 'announcement':
            typeDisplay = 'Announcement Update';
            alertClass = 'alert-info';
            break;
        case 'contribution':
            typeDisplay = 'Contribution Update';
            alertClass = 'alert-success';
            break;
        case 'payment':
            typeDisplay = 'Payment Update';
            alertClass = 'alert-warning';
            break;
        case 'payer':
            typeDisplay = 'Account Update';
            alertClass = 'alert-primary';
            break;
        default:
            typeDisplay = 'System Update';
            alertClass = 'alert-secondary';
    }
    
    typeBadge.className = `alert ${alertClass} border-0 mb-3`;
    typeText.textContent = typeDisplay;
    
    // Update priority badge based on action
    const priorityBadge = document.getElementById('announcementPriority');
    const priorityBadgeFooter = document.getElementById('announcementPriorityBadge');
    
    let priorityClass, priorityText;
    switch(action) {
        case 'created':
            priorityClass = 'bg-success';
            priorityText = 'NEW';
            break;
        case 'updated':
            priorityClass = 'bg-warning';
            priorityText = 'UPDATED';
            break;
        case 'deleted':
            priorityClass = 'bg-danger';
            priorityText = 'REMOVED';
            break;
        case 'published':
            priorityClass = 'bg-primary';
            priorityText = 'PUBLISHED';
            break;
        default:
            priorityClass = 'bg-info';
            priorityText = 'CHANGE';
    }
    
    priorityBadge.className = `badge ${priorityClass}`;
    priorityBadgeFooter.className = `badge ${priorityClass}`;
    priorityBadge.textContent = priorityText;
    priorityBadgeFooter.textContent = priorityText;
    
    // Add activity class to modal for styling
    const modalContent = document.querySelector('#announcementNotificationModal .modal-content');
    modalContent.className = `modal-content border-0 shadow-lg activity-${activityType}`;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('announcementNotificationModal'));
    modal.show();
    
    // Add event listener for modal close
    document.getElementById('announcementNotificationModal').addEventListener('hidden.bs.modal', function() {
        // Check if user clicked "View All Announcements" button
        const clickedViewAll = localStorage.getItem('announcementViewed');
        if (clickedViewAll === 'true') {
            // User read the activity, hide the badge
            hideNotificationBadge();
            localStorage.removeItem('announcementViewed');
        }
        // If user just closed without reading, badge stays visible
    });
    
    // Play notification sound (optional)
    playNotificationSound();
}

// Function to update activity dropdown
function updateActivityDropdown(activityData) {
    const latestAnnouncement = document.getElementById('latestAnnouncement');
    const noNotifications = document.getElementById('noNotifications');
    const dropdownTitle = document.getElementById('dropdownAnnouncementTitle');
    const dropdownText = document.getElementById('dropdownAnnouncementText');
    const dropdownTime = document.getElementById('dropdownAnnouncementTime');
    
    if (latestAnnouncement && dropdownTitle && dropdownText && dropdownTime) {
        dropdownTitle.textContent = activityData.title || 'New Activity';
        dropdownText.textContent = activityData.description || 'Click to view details';
        
        // Use backend-formatted date if available, otherwise format on frontend
        if (activityData.created_at_date) {
            dropdownTime.textContent = activityData.created_at_date;
        } else {
            dropdownTime.textContent = formatAnnouncementDate(activityData.created_at);
        }
        
        latestAnnouncement.style.display = 'flex';
        if (noNotifications) {
            noNotifications.style.display = 'none';
        }
    }
}

// Make functions globally available
window.showActivityNotification = showActivityNotification;
window.showAnnouncementNotification = showAnnouncementNotification;
window.checkForNewActivities = checkForNewActivities;
window.showNotificationBadge = showNotificationBadge;
window.markAllAsSeen = markAllAsSeen;
window.hideNotificationBadge = hideNotificationBadge;
window.updateNotificationDropdown = updateNotificationDropdown;
window.viewAnnouncement = viewAnnouncement;
window.closeNotificationDropdown = closeNotificationDropdown;
</script>
