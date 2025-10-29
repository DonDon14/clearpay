/**
 * Refund Methods Management JavaScript
 * Handles all functionality for the refund methods modal
 */

// Define base URL - this should be set globally, but we'll use a fallback
if (typeof window.APP_BASE_URL === 'undefined') {
    window.APP_BASE_URL = window.location.origin + '/';
}

// Load refund methods function - must be defined globally
window.loadRefundMethods = function() {
    console.log('loadRefundMethods() called');
    
    if (typeof jQuery === 'undefined') {
        console.error('jQuery not available for loadRefundMethods!');
        return;
    }
    
    const tbody = $('#refundMethodsTableBody');
    
    if (!tbody.length) {
        console.error('Table body not found!');
        return;
    }
    
    // Show loading state
    tbody.html('<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin fa-2x mb-2"></i><br>Loading refund methods...</td></tr>');
    
    const url = window.APP_BASE_URL + 'admin/settings/refund-methods/data';
    console.log('Fetching from URL:', url);
    
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            console.log('Refund methods response:', response);
            tbody.empty();
            
            // Check if we got valid data
            if (!response || !response.success) {
                console.error('Invalid response:', response);
                tbody.html('<tr><td colspan="6" class="text-center text-warning py-4"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>Invalid response from server. Please refresh the page.</td></tr>');
                return;
            }
            
            if (response.data && response.data.length > 0) {
                console.log('Found', response.data.length, 'refund methods');
                response.data.forEach(function(method) {
                    console.log('Processing refund method:', method);
                    const statusBadge = method.status === 'active' ? 
                        '<span class="badge bg-success">Active</span>' : 
                        '<span class="badge bg-secondary">Inactive</span>';
                    const statusIcon = method.status === 'active' ? 'pause' : 'play';
                    const statusClass = method.status === 'active' ? 'warning' : 'success';
                    
                    function escapeHtml(text) {
                        if (!text) return '';
                        const map = {
                            '&': '&amp;',
                            '<': '&lt;',
                            '>': '&gt;',
                            '"': '&quot;',
                            "'": '&#039;'
                        };
                        return text.replace(/[&<>"']/g, m => map[m]);
                    }
                    
                    const row = `
                        <tr data-id="${method.id}">
                            <td><strong>${escapeHtml(method.name)}</strong></td>
                            <td><code>${escapeHtml(method.code)}</code></td>
                            <td>${escapeHtml(method.description || 'N/A')}</td>
                            <td>${statusBadge}</td>
                            <td>${method.sort_order || 0}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary" onclick="window.editRefundMethod(${method.id})" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-${statusClass}" onclick="window.toggleRefundMethodStatus(${method.id})" title="${method.status === 'active' ? 'Deactivate' : 'Activate'}">
                                        <i class="fas fa-${statusIcon}"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="window.deleteRefundMethod(${method.id})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            } else {
                console.log('No refund methods found');
                tbody.html('<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-undo fa-2x mb-2"></i><br>No refund methods found. Add your first refund method to get started.</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Failed to load refund methods:', {
                xhr: xhr,
                status: status,
                error: error,
                responseText: xhr.responseText,
                statusCode: xhr.status
            });
            tbody.html('<tr><td colspan="6" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>Failed to load refund methods. Please refresh and try again.<br><small>' + error + ' (Status: ' + xhr.status + ')</small></td></tr>');
        }
    });
};

// Edit refund method - make it globally available
window.editRefundMethod = window.editRefundMethod || function(id) {
    console.log('editRefundMethod called with id:', id);
    $.ajax({
        url: window.APP_BASE_URL + 'admin/settings/refund-methods/data',
        type: 'GET',
        success: function(response) {
            if (response.success && response.data) {
                const method = response.data.find(m => m.id == id);
                if (method) {
                    $('#editRefundMethodId').val(method.id);
                    $('#editRefundMethodName').val(method.name);
                    $('#editRefundMethodCode').val(method.code);
                    $('#editRefundMethodDescription').val(method.description || '');
                    $('#editRefundMethodStatus').val(method.status);
                    $('#editRefundMethodSortOrder').val(method.sort_order || 0);
                    $('#editRefundMethodModal').modal('show');
                } else {
                    if (typeof showNotification === 'function') {
                        showNotification('Refund method not found', 'error');
                    } else {
                        alert('Refund method not found');
                    }
                }
            }
        },
        error: function(xhr) {
            console.error('Failed to load refund method:', xhr);
            if (typeof showNotification === 'function') {
                showNotification('Failed to load refund method details', 'error');
            } else {
                alert('Failed to load refund method details');
            }
        }
    });
};

// Toggle refund method status - make it globally available
window.toggleRefundMethodStatus = window.toggleRefundMethodStatus || function(id) {
    if (!confirm('Are you sure you want to toggle the status of this refund method?')) {
        return;
    }
    
    console.log('toggleRefundMethodStatus called with id:', id);
    $.ajax({
        url: window.APP_BASE_URL + 'admin/settings/refund-methods/toggle-status/' + id,
        type: 'POST',
        success: function(response) {
            if (response.success) {
                window.loadRefundMethods();
                if (typeof showNotification === 'function') {
                    showNotification(response.message || 'Refund method status updated successfully', 'success');
                } else {
                    alert(response.message || 'Refund method status updated successfully');
                }
            } else {
                if (typeof showNotification === 'function') {
                    showNotification(response.message || 'Failed to update refund method status', 'error');
                } else {
                    alert(response.message || 'Failed to update refund method status');
                }
            }
        },
        error: function(xhr) {
            console.error('Failed to toggle status:', xhr);
            if (typeof showNotification === 'function') {
                showNotification('An error occurred while updating the refund method status', 'error');
            } else {
                alert('An error occurred while updating the refund method status');
            }
        }
    });
};

// Delete refund method - make it globally available
window.deleteRefundMethod = window.deleteRefundMethod || function(id) {
    if (!confirm('Are you sure you want to delete this refund method? This action cannot be undone.')) {
        return;
    }
    
    console.log('deleteRefundMethod called with id:', id);
    $.ajax({
        url: window.APP_BASE_URL + 'admin/settings/refund-methods/delete/' + id,
        type: 'POST',
        success: function(response) {
            if (response.success) {
                window.loadRefundMethods();
                if (typeof showNotification === 'function') {
                    showNotification(response.message || 'Refund method deleted successfully', 'success');
                } else {
                    alert(response.message || 'Refund method deleted successfully');
                }
            } else {
                if (typeof showNotification === 'function') {
                    showNotification(response.message || 'Failed to delete refund method', 'error');
                } else {
                    alert(response.message || 'Failed to delete refund method');
                }
            }
        },
        error: function(xhr) {
            console.error('Failed to delete:', xhr);
            if (typeof showNotification === 'function') {
                showNotification('An error occurred while deleting the refund method', 'error');
            } else {
                alert('An error occurred while deleting the refund method');
            }
        }
    });
};

// Setup form handlers
function setupRefundMethodForms() {
    // Auto-generate code from name
    $('#addRefundMethodName, #editRefundMethodName').on('input', function() {
        const name = $(this).val();
        const code = name.toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
        const formId = $(this).attr('id').includes('add') ? 'addRefundMethodCode' : 'editRefundMethodCode';
        $('#' + formId).val(code);
    });

    // Add refund method form submission
    $('#addRefundMethodForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
        
        $.ajax({
            url: window.APP_BASE_URL + 'admin/settings/refund-methods/store',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Create response:', response);
                if (response.success) {
                    // Close only the add modal, keep the main refund methods modal open
                    $('#addRefundMethodModal').modal('hide');
                    $('#addRefundMethodForm')[0].reset();
                    
                    // Wait a moment for modal to close, then refresh
                    setTimeout(function() {
                        if (typeof window.loadRefundMethods === 'function') {
                            console.log('Refreshing refund methods table after create...');
                            window.loadRefundMethods();
                        } else {
                            console.error('loadRefundMethods function not available!');
                        }
                    }, 300);
                    
                    if (typeof showNotification === 'function') {
                        showNotification(response.message || 'Refund method created successfully', 'success');
                    } else {
                        alert(response.message || 'Refund method created successfully');
                    }
                } else {
                    if (typeof showNotification === 'function') {
                        showNotification(response.message || 'Failed to create refund method', 'error');
                    } else {
                        alert(response.message || 'Failed to create refund method');
                    }
                    if (response.errors) {
                        displayValidationErrors('#addRefundMethodForm', response.errors);
                    }
                }
            },
            error: function(xhr) {
                console.error('Create error:', xhr);
                if (typeof showNotification === 'function') {
                    showNotification('An error occurred while creating the refund method', 'error');
                } else {
                    alert('An error occurred while creating the refund method');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Edit refund method form submission
    $('#editRefundMethodForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        
        const id = $('#editRefundMethodId').val();
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Updating...');
        
        $.ajax({
            url: window.APP_BASE_URL + 'admin/settings/refund-methods/update/' + id,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Update response:', response);
                if (response.success) {
                    // Close only the edit modal, keep the main refund methods modal open
                    $('#editRefundMethodModal').modal('hide');
                    
                    // Wait a moment for modal to close, then refresh
                    setTimeout(function() {
                        if (typeof window.loadRefundMethods === 'function') {
                            console.log('Refreshing refund methods table after update...');
                            window.loadRefundMethods();
                        } else {
                            console.error('loadRefundMethods function not available!');
                        }
                    }, 300);
                    
                    if (typeof showNotification === 'function') {
                        showNotification(response.message || 'Refund method updated successfully', 'success');
                    } else {
                        alert(response.message || 'Refund method updated successfully');
                    }
                } else {
                    if (typeof showNotification === 'function') {
                        showNotification(response.message || 'Failed to update refund method', 'error');
                    } else {
                        alert(response.message || 'Failed to update refund method');
                    }
                    if (response.errors) {
                        displayValidationErrors('#editRefundMethodForm', response.errors);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Update error:', {xhr: xhr, status: status, error: error, responseText: xhr.responseText});
                if (typeof showNotification === 'function') {
                    showNotification('An error occurred while updating the refund method', 'error');
                } else {
                    alert('An error occurred while updating the refund method');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // When modals are closed, clear validation errors
    $('#addRefundMethodModal, #editRefundMethodModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
        $(this).find('.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback').text('');
        $(this).find('form')[0].reset();
    });
}

// Helper functions
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function showNotification(type, message) {
    // Use existing notification system if available
    if (typeof window.showFlashMessage === 'function') {
        window.showFlashMessage(message, type);
    } else if (typeof showNotification === 'function' && showNotification !== arguments.callee) {
        window.showNotification(message, type);
    } else {
        alert(message);
    }
}

function displayValidationErrors(formSelector, errors) {
    $(formSelector + ' .is-invalid').removeClass('is-invalid');
    $(formSelector + ' .invalid-feedback').text('');
    
    $.each(errors, function(field, message) {
        const input = $(formSelector).find('[name="' + field + '"]');
        input.addClass('is-invalid');
        const feedback = input.siblings('.invalid-feedback');
        if (feedback.length) {
            feedback.text(message);
        }
    });
}

// Initialize when jQuery and DOM are ready
if (typeof jQuery !== 'undefined') {
    $(document).ready(function() {
        console.log('Refund methods script initialized (jQuery ready)');
        
        // Set up form handlers
        setupRefundMethodForms();
        
        // Load refund methods when modal is shown
        $('#refundMethodsModal').off('shown.bs.modal').on('shown.bs.modal', function() {
            console.log('Refund methods modal opened, loading data...');
            if (typeof window.loadRefundMethods === 'function') {
                window.loadRefundMethods();
            }
        });
        
        // Prevent the main modal from closing when child modals close
        $('#addRefundMethodModal, #editRefundMethodModal').on('hidden.bs.modal', function() {
            console.log('Child modal closed, ensuring main modal stays open');
            // Ensure the main modal backdrop is still there
            if ($('#refundMethodsModal').hasClass('show')) {
                console.log('Main refund methods modal is still open - good!');
            }
        });
        
        // Also trigger on button click as backup
        $('button[data-bs-target="#refundMethodsModal"]').on('click', function() {
            console.log('Refund methods modal button clicked');
            setTimeout(function() {
                if ($('#refundMethodsModal').hasClass('show')) {
                    console.log('Modal is now shown, loading data...');
                    if (typeof window.loadRefundMethods === 'function') {
                        window.loadRefundMethods();
                    }
                }
            }, 200);
        });
    });
} else {
    // Wait for jQuery
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Refund methods script loaded, waiting for jQuery...');
        var checkJQuery = setInterval(function() {
            if (typeof jQuery !== 'undefined') {
                clearInterval(checkJQuery);
                $(document).ready(function() {
                    setupRefundMethodForms();
                    $('#refundMethodsModal').off('shown.bs.modal').on('shown.bs.modal', function() {
                        if (typeof window.loadRefundMethods === 'function') {
                            window.loadRefundMethods();
                        }
                    });
                });
            }
        }, 100);
        setTimeout(function() { clearInterval(checkJQuery); }, 5000);
    });
}

console.log('Refund methods script loaded');

