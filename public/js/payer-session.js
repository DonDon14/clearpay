/**
 * Payer Session Management
 * Handles session timeout, tab close/reopen detection, and AJAX error handling
 */

(function() {
    'use strict';

    // Session timeout in milliseconds (30 minutes)
    const SESSION_TIMEOUT = 30 * 60 * 1000; // 30 minutes
    
    // Check session interval (every 5 minutes)
    const CHECK_INTERVAL = 5 * 60 * 1000; // 5 minutes
    
    let sessionCheckInterval = null;
    let lastActivityTime = Date.now();

    /**
     * Check if session is still valid
     */
    function checkSession() {
        // Simple check - make a lightweight AJAX request to a protected endpoint
        fetch(window.APP_BASE_URL + 'payer/check-new-activities', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.status === 401 || response.status === 403) {
                handleSessionExpired();
            } else if (response.ok) {
                lastActivityTime = Date.now();
            }
        })
        .catch(error => {
            console.error('Session check error:', error);
        });
    }

    /**
     * Handle session expired
     */
    function handleSessionExpired() {
        // Stop checking
        if (sessionCheckInterval) {
            clearInterval(sessionCheckInterval);
        }

        // Show message
        alert('Your session has expired due to inactivity. You will be redirected to the login page.');

        // Redirect to login
        window.location.href = window.APP_BASE_URL + 'payer/login';
    }

    /**
     * Update last activity time
     */
    function updateLastActivity() {
        lastActivityTime = Date.now();
    }

    /**
     * Check if session has expired based on last activity
     */
    function checkSessionTimeout() {
        const timeSinceLastActivity = Date.now() - lastActivityTime;
        
        if (timeSinceLastActivity > SESSION_TIMEOUT) {
            handleSessionExpired();
            return true;
        }
        
        return false;
    }

    /**
     * Initialize session management
     */
    function initSessionManagement() {
        // Track user activity
        const activityEvents = ['mousedown', 'keydown', 'scroll', 'touchstart', 'click'];
        activityEvents.forEach(event => {
            document.addEventListener(event, updateLastActivity, true);
        });

        // Check session periodically
        sessionCheckInterval = setInterval(function() {
            if (!checkSessionTimeout()) {
                checkSession();
            }
        }, CHECK_INTERVAL);

        // Check session when page becomes visible again (tab reopened)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                // Page became visible - check if session expired
                if (checkSessionTimeout()) {
                    return;
                }
                // If not expired, verify with server
                checkSession();
            }
        });

        // Check session when window gains focus
        window.addEventListener('focus', function() {
            if (checkSessionTimeout()) {
                return;
            }
            checkSession();
        });

        // Handle beforeunload (tab close)
        window.addEventListener('beforeunload', function(e) {
            // Note: Most modern browsers ignore custom messages in beforeunload
            // This is just a fallback
        });
    }

    /**
     * Handle AJAX response for session errors
     */
    function handleAJAXSessionError(xhr) {
        if (xhr.status === 401 || xhr.status === 403) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.session_expired || response.message) {
                    handleSessionExpired();
                    return true;
                }
            } catch (e) {
                // If response is not JSON, still treat as session expired
                handleSessionExpired();
                return true;
            }
        }
        return false;
    }

    /**
     * Override fetch to handle session errors
     */
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        return originalFetch.apply(this, args)
            .then(response => {
                // Check if response indicates session expired
                if (response.status === 401 || response.status === 403) {
                    // Try to parse JSON response
                    const clonedResponse = response.clone();
                    clonedResponse.json()
                        .then(data => {
                            if (data.session_expired || data.success === false) {
                                handleSessionExpired();
                            }
                        })
                        .catch(() => {
                            // If not JSON, still treat as session expired for 401/403
                            handleSessionExpired();
                        });
                }
                return response;
            })
            .catch(error => {
                console.error('Fetch error:', error);
                throw error;
            });
    };

    /**
     * Initialize when DOM is ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSessionManagement);
    } else {
        initSessionManagement();
    }

    // Export functions for global use
    window.payerSession = {
        checkSession: checkSession,
        updateLastActivity: updateLastActivity,
        handleSessionExpired: handleSessionExpired
    };

})();

