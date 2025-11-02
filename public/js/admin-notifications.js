/**
 * Admin Notification System
 * Similar to payer notification system but for admin users
 * Handles notification bell, dropdown, and real-time updates
 */

// Global variables for admin notification management - Facebook-like logic
let currentAdminActivities = [];
let unreadAdminActivityIds = new Set(); // Activities that haven't been individually clicked (read)
let unseenAdminActivityIds = new Set(); // Activities that haven't been seen (bell not clicked)
let lastShownAdminActivityId = 0;
let adminNotificationDataLoaded = false; // Track if initial data is loaded
let lastAdminDataFetch = 0; // Track last fetch time for caching

// Get admin user ID for user-specific localStorage keys
const getAdminUserId = () => {
    return window.ADMIN_USER_ID || 0;
};

// Get user-specific localStorage key
const getLastShownAdminActivityIdKey = () => {
    const userId = getAdminUserId();
    return `lastShownAdminActivityId_${userId}`;
};

// Function to close admin notification dropdown
function closeAdminNotificationDropdown() {
    const dropdown = document.getElementById('notificationDropdown');
    if (dropdown) {
        dropdown.classList.remove('active');
    }
}

// Function to check for new admin activities
function checkForNewAdminActivities() {
    console.log('Checking for new admin activities...');
    
    // Check if we should skip this request due to caching
    const now = Date.now();
    const timeSinceLastFetch = now - lastAdminDataFetch;
    
    // If data was fetched less than 10 seconds ago and we have data, skip
    if (timeSinceLastFetch < 10000 && adminNotificationDataLoaded && currentAdminActivities.length > 0) {
        console.log('Skipping fetch - data is fresh (cached)');
        return;
    }
    
    // Get the last shown activity ID from localStorage (user-specific)
    const lastShownKey = getLastShownAdminActivityIdKey();
    const storedLastShownId = localStorage.getItem(lastShownKey);
    lastShownAdminActivityId = storedLastShownId ? parseInt(storedLastShownId) : 0;
    
    console.log('Last shown admin activity ID:', lastShownAdminActivityId);
    
    // Build URL with last shown ID
    const url = `${window.APP_BASE_URL}admin/check-new-activities?last_shown_id=${lastShownAdminActivityId}`;
    console.log('Checking URL:', url);
    
    // Update last fetch time
    lastAdminDataFetch = now;
    
    // Add timeout to prevent long loading
    const controller = new AbortController();
    const timeoutId = setTimeout(() => {
        console.log('Request timeout - aborting');
        controller.abort();
    }, 10000); // 10 second timeout
    
    fetch(url, { signal: controller.signal })
        .then(response => {
            clearTimeout(timeoutId);
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Admin activity check response:', data);
            
            if (data.success && data.activities) {
                currentAdminActivities = data.activities;
                
                // Process activities for Facebook-like logic (same as payer side)
                if (data.activities && data.activities.length > 0) {
                    data.activities.forEach(activity => {
                        const activityIdStr = String(activity.id);
                        
                        // Add to unseen set if it's a NEW activity (in newActivities array)
                        // OR if it's unread and this is the first load (lastShownAdminActivityId is 0)
                        // This determines if it should show in badge (unseen notifications)
                        const isNewActivity = data.newActivities && data.newActivities.some(newActivity => String(newActivity.id) === activityIdStr);
                        const isUnreadOnFirstLoad = (lastShownAdminActivityId === 0) && (parseInt(activity.is_read_by_admin) || 0) === 0;
                        
                        if ((isNewActivity || isUnreadOnFirstLoad) && !unseenAdminActivityIds.has(activityIdStr)) {
                            unseenAdminActivityIds.add(activityIdStr);
                            console.log('Added new unseen admin activity:', activityIdStr, 'isNew:', isNewActivity, 'isUnreadOnFirstLoad:', isUnreadOnFirstLoad);
                        }
                        
                        // Add to unread set if not individually read (for dots/indicators)
                        // This is separate from unseen - unread controls the dots on items
                        const isReadByAdmin = parseInt(activity.is_read_by_admin) || 0;
                        
                        if (isReadByAdmin === 0 && !unreadAdminActivityIds.has(activityIdStr)) {
                            unreadAdminActivityIds.add(activityIdStr);
                            console.log('Added new unread admin activity:', activityIdStr);
                        } else if (isReadByAdmin === 1 && unreadAdminActivityIds.has(activityIdStr)) {
                            // Remove from unread if it's been read
                            unreadAdminActivityIds.delete(activityIdStr);
                            console.log('Removed read admin activity from unread set:', activityIdStr);
                        }
                    });
                    
                    // Always update badge based on unseen count (even if no new activities)
                    // Badge shows unseen notifications, not unread notifications
                    updateAdminUnreadCount();
                }
                
                // Mark data as loaded
                adminNotificationDataLoaded = true;
                
                // Update dropdown with all activities
                updateAdminNotificationDropdown(data.activities);
                
                // Update badge based on unseen count (not server unread count)
                // Badge shows unseen notifications, not unread notifications
                updateAdminUnreadCount();
                
                // Update last shown ID to the highest activity ID (user-specific)
                if (data.activities.length > 0) {
                    const maxId = Math.max(...data.activities.map(a => a.id));
                    const lastShownKey = getLastShownAdminActivityIdKey();
                    localStorage.setItem(lastShownKey, maxId.toString());
                    lastShownAdminActivityId = maxId;
                }
            } else {
                console.log('No admin activities found:', data.message);
                hideAdminNotificationBadge();
                adminNotificationDataLoaded = true;
                updateAdminNotificationDropdown([]);
            }
        })
        .catch(error => {
            clearTimeout(timeoutId);
            console.error('Error checking for new admin activities:', error);
            
            adminNotificationDataLoaded = true;
            
            // Show cached data if available
            if (currentAdminActivities && currentAdminActivities.length > 0) {
                updateAdminNotificationDropdown(currentAdminActivities);
            } else {
                updateAdminNotificationDropdown([]);
            }
        });
}

// Function to update admin notification dropdown with multiple activities
function updateAdminNotificationDropdown(activities) {
    const noNotifications = document.getElementById('noNotifications');
    const dropdownContent = document.getElementById('notificationContent');
    
    if (!dropdownContent) return;
    
    // Show loading state if data isn't loaded yet
    if (!adminNotificationDataLoaded) {
        dropdownContent.innerHTML = `
            <div class="notification-item loading-state" style="text-align: center; padding: 1.5rem;">
                <div class="d-flex align-items-center justify-content-center">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="text-muted small">Loading...</span>
                </div>
            </div>
        `;
        return;
    }
    
    if (!activities || activities.length === 0) {
        if (noNotifications) noNotifications.style.display = 'block';
        return;
    }
    
    // Hide no notifications message
    if (noNotifications) noNotifications.style.display = 'none';
    
    // Show 5+ most recent notifications
    const recentActivities = activities.slice(0, 5);
    
    // Create notification items for recent activities
    let html = '';
    recentActivities.forEach(activity => {
        const activityIdStr = String(activity.id);
        const isUnread = unreadAdminActivityIds.has(activityIdStr);
        
        // Debug: Log activity object to see what fields are available
        console.log('Processing activity:', {
            id: activity.id,
            activity_type: activity.activity_type,
            entity_type: activity.entity_type,
            action: activity.action,
            allKeys: Object.keys(activity)
        });
        
        // Handle null/undefined entity_id safely
        const entityId = activity.entity_id || activity.entity_id === 0 ? activity.entity_id : 'null';
        const activityId = activity.id || 0;
        // Try multiple ways to get activity_type - it might be stored differently
        const activityType = activity.activity_type || activity.entity_type || activity.type || 'unknown';
        
        console.log('Extracted activity type:', activityType, 'from activity:', activity);
        
        // Build click handler URL based on activity type
        let clickUrl = `${window.APP_BASE_URL || ''}dashboard`;
        const normalizedType = String(activityType).toLowerCase().trim();
        const action = activity.action || '';
        
        switch(normalizedType) {
            case 'announcement':
                clickUrl = `${window.APP_BASE_URL || ''}announcements`;
                break;
            case 'contribution':
                clickUrl = `${window.APP_BASE_URL || ''}contributions`;
                break;
            case 'payment':
                clickUrl = `${window.APP_BASE_URL || ''}payments`;
                break;
            case 'payer':
                clickUrl = `${window.APP_BASE_URL || ''}payers`;
                break;
            case 'payment_request':
            case 'paymentrequest':
            case 'payment-request':
                clickUrl = `${window.APP_BASE_URL || ''}payment-requests`;
                break;
            case 'refund':
                if (action === 'completed' || action === 'rejected') {
                    clickUrl = `${window.APP_BASE_URL || ''}refunds#history`;
                } else {
                    clickUrl = `${window.APP_BASE_URL || ''}refunds`;
                }
                break;
            case 'user':
                clickUrl = `${window.APP_BASE_URL || ''}settings/users`;
                break;
        }
        
        html += `
            <div class="notification-item ${isUnread ? 'unread-notification' : ''}" 
                 data-activity-id="${activityId}"
                 data-activity-type="${activityType}"
                 data-entity-id="${entityId}"
                 data-click-url="${clickUrl}"
                 onclick="handleAdminNotificationClick(${activityId}, '${activityType}', ${entityId !== 'null' ? entityId : 'null'}); return false;"
                 style="cursor: pointer;">
                <div class="notification-icon">
                    <i class="${activity.activity_icon} text-${activity.activity_color}"></i>
                </div>
                <div class="notification-body">
                    <h6 class="notification-title">${escapeHtml(activity.title)}</h6>
                    <p class="notification-text">${escapeHtml(activity.description)}</p>
                    <small class="notification-time">${activity.created_at_date || activity.created_at_formatted || 'Just now'}</small>
                </div>
                ${isUnread ? '<div class="unread-indicator"></div>' : ''}
            </div>
        `;
    });
    
    // Add "show more" if there are more than 5 activities
    if (activities.length > 5) {
        html += `
            <div class="notification-item show-more" onclick="showAllAdminNotificationsModal()" style="cursor: pointer;">
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
}

// Function to handle admin notification click
function handleAdminNotificationClick(activityId, activityType, entityId) {
    console.log('Admin notification clicked:', { activityId, activityType, entityId });
    console.log('Activity Type:', activityType, 'Type:', typeof activityType);
    console.log('Current activities array:', currentAdminActivities);
    
    // Validate inputs
    if (!activityId) {
        console.error('Invalid notification click - missing activityId');
        return;
    }
    
    // Try multiple ways to get activity_type if not provided
    if (!activityType || activityType === 'unknown' || activityType === 'null' || activityType === 'undefined') {
        console.warn('Activity type not provided or invalid:', activityType, '- attempting to find from current activities');
        
        // Try to get activity type from current activities
        if (currentAdminActivities && currentAdminActivities.length > 0) {
            const activity = currentAdminActivities.find(a => a.id == activityId || a.id === activityId);
            console.log('Found activity in current activities:', activity);
            
            if (activity) {
                // Try multiple field names
                activityType = activity.activity_type || activity.entity_type || activity.type || null;
                console.log('Extracted activity type from activity object:', activityType);
                
                if (!activityType && activity.entity_type) {
                    // Fallback: use entity_type if activity_type is not available
                    activityType = activity.entity_type;
                    console.log('Using entity_type as fallback:', activityType);
                }
            }
        }
        
        // Try to get from data attribute if clicked from DOM
        if (!activityType || activityType === 'unknown') {
            const clickedElement = document.querySelector(`[data-activity-id="${activityId}"]`);
            if (clickedElement) {
                const attrType = clickedElement.getAttribute('data-activity-type');
                if (attrType && attrType !== 'null' && attrType !== 'unknown') {
                    activityType = attrType;
                    console.log('Found activity type from data attribute:', activityType);
                }
            }
        }
        
        if (!activityType || activityType === 'unknown' || activityType === 'null') {
            console.error('Could not determine activity type from any source, defaulting to dashboard');
            console.error('Available activities:', currentAdminActivities);
            activityType = 'unknown';
        }
    }
    
    const activityIdStr = String(activityId);
    
    // Remove from both unseen and unread sets (like payer side)
    // When clicked, it's both "seen" and "read"
    unseenAdminActivityIds.delete(activityIdStr);
    unreadAdminActivityIds.delete(activityIdStr);
    
    // Update badge (should update if unseen count changed)
    updateAdminUnreadCount();
    
    // Mark as read on server (don't wait for it to complete - do it async)
    markAdminActivityAsRead(activityId).catch(error => {
        console.error('Failed to mark activity as read (non-blocking):', error);
        // Don't block redirect if this fails
    });
    
    // Close the dropdown
    closeAdminNotificationDropdown();
    
    // Navigate immediately - don't wait for mark as read to complete
    // Get the action from the activity data to determine the correct page/tab
    let action = '';
    if (currentAdminActivities && currentAdminActivities.length > 0) {
        const activity = currentAdminActivities.find(a => a.id === activityId);
        if (activity) {
            action = activity.action || '';
            // If activityType wasn't provided, try to get it from activity
            if (!activityType || activityType === 'unknown') {
                activityType = activity.activity_type || 'unknown';
            }
        }
    }
    
    // Normalize activity type (remove underscores, handle variations)
    const normalizedActivityType = String(activityType).toLowerCase().trim();
    console.log('Normalized activity type:', normalizedActivityType, 'Original:', activityType);
    
    // Ensure base URL has trailing slash for proper URL construction
    const baseUrl = (window.APP_BASE_URL || '').replace(/\/$/, '') + '/';
    
    let redirectUrl = `${baseUrl}dashboard`;
    
    switch(normalizedActivityType) {
        case 'announcement':
            redirectUrl = `${baseUrl}announcements`;
            break;
        case 'contribution':
            redirectUrl = `${baseUrl}contributions`;
            break;
        case 'payment':
            redirectUrl = `${baseUrl}payments`;
            break;
        case 'payer':
            redirectUrl = `${baseUrl}payers`;
            break;
        case 'payment_request':
        case 'paymentrequest':
        case 'payment-request':
            // Always redirect to payment requests page
            redirectUrl = `${baseUrl}payment-requests`;
            break;
        case 'refund':
            // Redirect to refunds page, scroll to history tab if action is completed/rejected
            if (action === 'completed' || action === 'rejected' || action === 'processed') {
                redirectUrl = `${baseUrl}refunds#history`;
            } else {
                redirectUrl = `${baseUrl}refunds`;
            }
            break;
        case 'user':
            redirectUrl = `${baseUrl}settings/users`;
            break;
        default:
            console.warn('Unknown activity type, defaulting to dashboard:', normalizedActivityType);
            redirectUrl = `${baseUrl}dashboard`;
    }
    
    console.log('Redirecting to:', redirectUrl, 'for activity type:', normalizedActivityType, 'action:', action);
    
    // Redirect immediately
    window.location.href = redirectUrl;
}

// Function to mark admin activity as read (server-side)
// Note: UI updates are handled by handleAdminNotificationClick
// Returns a promise so it can be awaited if needed
function markAdminActivityAsRead(activityId) {
    console.log('Marking admin activity as read on server:', activityId);
    
    // Normalize base URL - ensure it has a trailing slash
    const baseUrl = (window.APP_BASE_URL || '').replace(/\/$/, '') + '/';
    const url = `${baseUrl}admin/mark-activity-read/${activityId}`;
    
    console.log('Mark as read URL:', url);
    
    // Send to server to mark as read
    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin' // Include cookies for authentication
    }).then(response => {
        console.log('Mark as read response status:', response.status, response.statusText);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            console.log('Activity marked as read on server:', activityId);
            
            // Update the unread set if server confirms it's read
            const activityIdStr = String(activityId);
            if (data.unreadCount !== undefined && data.unreadCount === 0) {
                // If unread count is 0, this was the last one
                unreadAdminActivityIds.clear();
            } else {
                // Remove this specific activity from unread set
                unreadAdminActivityIds.delete(activityIdStr);
            }
            
            // Refresh dropdown to update UI (only if dropdown is still visible)
            if (document.getElementById('notificationDropdown') && document.getElementById('notificationDropdown').classList.contains('active')) {
                updateAdminNotificationDropdown(currentAdminActivities);
            }
        }
        return data;
    }).catch(error => {
        console.error('Error marking admin activity as read (non-fatal):', error);
        // Return a resolved promise so the caller doesn't fail
        return { success: false, error: error.message };
    });
}

// Function to update admin unread count (badge)
// Badge should show "unseen" count (Facebook-like behavior)
// When bell is clicked, unseen count clears but unread dots remain
function updateAdminUnreadCount(count = null) {
    const badge = document.getElementById('notificationBadge');
    
    if (!badge) return;
    
    // Badge shows "unseen" count, not "unread" count (like payer side)
    // When bell is clicked, unseen is cleared but unread remains (for dots)
    const unseenCount = unseenAdminActivityIds.size;
    
    if (unseenCount > 0) {
        badge.textContent = unseenCount > 99 ? '99+' : unseenCount;
        badge.style.display = 'inline-block';
    } else {
        badge.style.display = 'none';
    }
}

// Function to show admin notification badge
function showAdminNotificationBadge() {
    updateAdminUnreadCount();
}

// Function to hide admin notification badge
function hideAdminNotificationBadge() {
    const badge = document.getElementById('notificationBadge');
    if (badge) {
        badge.style.display = 'none';
    }
}

// Function to mark all admin notifications as seen (Facebook-like bell click behavior)
// This clears the badge but keeps the unread dots on individual items
function markAllAdminAsSeen() {
    console.log('=== MARKING ALL ADMIN AS SEEN ===');
    console.log('Unseen activities before:', Array.from(unseenAdminActivityIds));
    
    // Clear unseen set (this will hide the badge)
    unseenAdminActivityIds.clear();
    
    // DON'T clear unreadAdminActivityIds - those control the dots/indicators
    
    // Update badge (should disappear now)
    updateAdminUnreadCount();
    
    console.log('All notifications marked as seen (badge cleared, dots remain)');
    console.log('=== END MARKING ALL AS SEEN ===');
}

// Function to show all admin notifications modal
function showAllAdminNotificationsModal() {
    fetch(`${window.APP_BASE_URL}admin/get-all-activities`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.activities) {
                // Create modal content
                let html = `
                    <div class="modal fade" id="allAdminNotificationsModal" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-bell me-2"></i>All Notifications
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <button class="btn btn-sm btn-primary" onclick="markAllAdminActivitiesAsRead()">
                                            <i class="fas fa-check-double me-1"></i>Mark All as Read
                                        </button>
                                    </div>
                                    <div class="list-group">
                `;
                
                data.activities.forEach(activity => {
                    const activityIdStr = String(activity.id);
                    const isUnread = !activity.is_read;
                    const entityId = activity.entity_id || activity.entity_id === 0 ? activity.entity_id : 'null';
                    const activityId = activity.id || 0;
                    const activityType = activity.activity_type || 'unknown';
                    
                    // Build click handler URL based on activity type
                    let clickUrl = `${window.APP_BASE_URL || ''}dashboard`;
                    const normalizedType = String(activityType).toLowerCase().trim();
                    const action = activity.action || '';
                    
                    switch(normalizedType) {
                        case 'announcement':
                            clickUrl = `${window.APP_BASE_URL || ''}announcements`;
                            break;
                        case 'contribution':
                            clickUrl = `${window.APP_BASE_URL || ''}contributions`;
                            break;
                        case 'payment':
                            clickUrl = `${window.APP_BASE_URL || ''}payments`;
                            break;
                        case 'payer':
                            clickUrl = `${window.APP_BASE_URL || ''}payers`;
                            break;
                        case 'payment_request':
                        case 'paymentrequest':
                        case 'payment-request':
                            clickUrl = `${window.APP_BASE_URL || ''}payment-requests`;
                            break;
                        case 'refund':
                            if (action === 'completed' || action === 'rejected' || action === 'processed') {
                                clickUrl = `${window.APP_BASE_URL || ''}refunds#history`;
                            } else {
                                clickUrl = `${window.APP_BASE_URL || ''}refunds`;
                            }
                            break;
                        case 'user':
                            clickUrl = `${window.APP_BASE_URL || ''}settings/users`;
                            break;
                    }
                    
                    html += `
                        <div class="list-group-item notification-item-modal ${isUnread ? 'bg-light' : ''}" 
                             data-activity-id="${activityId}"
                             data-activity-type="${activityType}"
                             data-entity-id="${entityId}"
                             data-click-url="${clickUrl}"
                             onclick="handleAdminNotificationClick(${activityId}, '${activityType}', ${entityId !== 'null' ? entityId : 'null'}); return false;"
                             style="cursor: pointer;">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <i class="${activity.activity_icon} fa-2x text-${activity.activity_color}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${escapeHtml(activity.title)}</h6>
                                    <p class="mb-1">${escapeHtml(activity.description)}</p>
                                    <small class="text-muted">${activity.created_at_date || activity.created_at_formatted || 'Just now'}</small>
                                </div>
                                ${isUnread ? '<span class="badge bg-primary">New</span>' : ''}
                            </div>
                        </div>
                    `;
                });
                
                html += `
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Remove existing modal if any
                const existingModal = document.getElementById('allAdminNotificationsModal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                // Add modal to body
                document.body.insertAdjacentHTML('beforeend', html);
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('allAdminNotificationsModal'));
                modal.show();
                
                // Add event listeners to modal notification items using event delegation
                // Use setTimeout to ensure modal is fully rendered
                setTimeout(() => {
                    const modalBody = document.querySelector('#allAdminNotificationsModal .modal-body');
                    if (modalBody) {
                        // Remove any existing event listeners to avoid duplicates
                        const newModalBody = modalBody.cloneNode(true);
                        modalBody.parentNode.replaceChild(newModalBody, modalBody);
                        
                        newModalBody.addEventListener('click', function(e) {
                            const notificationItem = e.target.closest('.notification-item-modal[data-activity-id]');
                            if (notificationItem) {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                const activityId = parseInt(notificationItem.getAttribute('data-activity-id'));
                                const activityType = notificationItem.getAttribute('data-activity-type');
                                const entityId = notificationItem.getAttribute('data-entity-id');
                                
                                console.log('Modal notification item clicked:', activityId, activityType, entityId);
                                
                                // Close modal first
                                const bsModal = bootstrap.Modal.getInstance(document.getElementById('allAdminNotificationsModal'));
                                if (bsModal) {
                                    bsModal.hide();
                                }
                                
                                handleAdminNotificationClick(activityId, activityType, entityId && entityId !== 'null' ? parseInt(entityId) : null);
                            }
                        });
                    }
                }, 100);
                
                // Clean up on close
                document.getElementById('allAdminNotificationsModal').addEventListener('hidden.bs.modal', function() {
                    this.remove();
                });
            }
        })
        .catch(error => {
            console.error('Error fetching all admin activities:', error);
            alert('Failed to load notifications. Please try again.');
        });
}

// Function to mark all admin activities as read
function markAllAdminActivitiesAsRead() {
    fetch(`${window.APP_BASE_URL}admin/mark-all-activities-read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear unread sets
            unreadAdminActivityIds.clear();
            unseenAdminActivityIds.clear();
            
            // Update badge (should be 0 since both sets are cleared)
            updateAdminUnreadCount();
            
            // Reload notification dropdown
            checkForNewAdminActivities();
            
            // Close modal and reload page to show updated state
            const modal = bootstrap.Modal.getInstance(document.getElementById('allAdminNotificationsModal'));
            if (modal) {
                modal.hide();
            }
            
            // Reload page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }
    })
    .catch(error => {
        console.error('Error marking all admin activities as read:', error);
        alert('Failed to mark all as read. Please try again.');
    });
}

// Utility function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize admin notification system on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check for new activities on page load
    checkForNewAdminActivities();
    
    // Set up periodic checks (every 30 seconds)
    setInterval(checkForNewAdminActivities, 30000);
    
    // Note: Badge is managed by unseen count, not server unread count
    // Server unread count is only used for initial loading, badge is client-side
});

// Export functions for global access
window.checkForNewAdminActivities = checkForNewAdminActivities;
window.updateAdminNotificationDropdown = updateAdminNotificationDropdown;
window.handleAdminNotificationClick = handleAdminNotificationClick;
window.markAdminActivityAsRead = markAdminActivityAsRead;
window.showAllAdminNotificationsModal = showAllAdminNotificationsModal;
window.markAllAdminActivitiesAsRead = markAllAdminActivitiesAsRead;
window.markAllAdminAsSeen = markAllAdminAsSeen;
window.closeAdminNotificationDropdown = closeAdminNotificationDropdown;

