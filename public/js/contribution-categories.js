/**
 * Contribution Categories Management JavaScript
 * Handles all functionality for the contribution categories modal
 */

// Define base URL - this should be set globally, but we'll use a fallback
if (typeof window.APP_BASE_URL === 'undefined') {
    window.APP_BASE_URL = window.location.origin + '/';
}

// Load contribution categories function - must be defined globally
window.loadContributionCategories = function() {
    console.log('loadContributionCategories() called');
    
    if (typeof jQuery === 'undefined') {
        console.error('jQuery not available for loadContributionCategories!');
        return;
    }
    
    const tbody = $('#contributionCategoriesTableBody');
    
    if (!tbody.length) {
        console.error('Table body not found!');
        return;
    }
    
    // Show loading state
    tbody.html('<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin fa-2x mb-2"></i><br>Loading categories...</td></tr>');
    
    const url = window.APP_BASE_URL + 'admin/settings/contribution-categories/data';
    console.log('Fetching from URL:', url);
    
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            console.log('Categories response:', response);
            tbody.empty();
            
            // Check if we got valid data
            if (!response || !response.success) {
                console.error('Invalid response:', response);
                tbody.html('<tr><td colspan="6" class="text-center text-warning py-4"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>Invalid response from server. Please refresh the page.</td></tr>');
                return;
            }
            
            if (response.data && response.data.length > 0) {
                console.log('Found', response.data.length, 'categories');
                response.data.forEach(function(category) {
                    console.log('Processing category:', category);
                    const statusBadge = category.status === 'active' ? 
                        '<span class="badge bg-success">Active</span>' : 
                        '<span class="badge bg-secondary">Inactive</span>';
                    const statusIcon = category.status === 'active' ? 'pause' : 'play';
                    const statusClass = category.status === 'active' ? 'warning' : 'success';
                    
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
                        <tr data-id="${category.id}">
                            <td><strong>${escapeHtml(category.name)}</strong></td>
                            <td><code>${escapeHtml(category.code)}</code></td>
                            <td>${escapeHtml(category.description || 'N/A')}</td>
                            <td>${statusBadge}</td>
                            <td>${category.sort_order || 0}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary" onclick="window.editContributionCategory(${category.id})" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-${statusClass}" onclick="window.toggleContributionCategoryStatus(${category.id})" title="${category.status === 'active' ? 'Deactivate' : 'Activate'}">
                                        <i class="fas fa-${statusIcon}"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="window.deleteContributionCategory(${category.id})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            } else {
                console.log('No categories found');
                tbody.html('<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-folder-open fa-2x mb-2"></i><br>No categories found. Add your first category to get started.</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Failed to load categories:', {
                xhr: xhr,
                status: status,
                error: error,
                responseText: xhr.responseText,
                statusCode: xhr.status
            });
            tbody.html('<tr><td colspan="6" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>Failed to load categories. Please refresh and try again.<br><small>' + error + ' (Status: ' + xhr.status + ')</small></td></tr>');
        }
    });
};

// Edit category - make it globally available
window.editContributionCategory = window.editContributionCategory || function(id) {
    console.log('editContributionCategory called with id:', id);
    $.ajax({
        url: window.APP_BASE_URL + 'admin/settings/contribution-categories/data',
        type: 'GET',
        success: function(response) {
            if (response.success && response.data) {
                const category = response.data.find(c => c.id == id);
                if (category) {
                    $('#editCategoryId').val(category.id);
                    $('#editCategoryName').val(category.name);
                    $('#editCategoryCode').val(category.code);
                    $('#editCategoryDescription').val(category.description || '');
                    $('#editCategoryStatus').val(category.status);
                    $('#editCategorySortOrder').val(category.sort_order || 0);
                    $('#editContributionCategoryModal').modal('show');
                } else {
                    if (typeof showNotification === 'function') {
                        showNotification('Category not found', 'error');
                    } else {
                        alert('Category not found');
                    }
                }
            }
        },
        error: function(xhr) {
            console.error('Failed to load category:', xhr);
            if (typeof showNotification === 'function') {
                showNotification('Failed to load category details', 'error');
            } else {
                alert('Failed to load category details');
            }
        }
    });
};

// Toggle category status - make it globally available
window.toggleContributionCategoryStatus = window.toggleContributionCategoryStatus || function(id) {
    if (!confirm('Are you sure you want to toggle the status of this category?')) {
        return;
    }
    
    console.log('toggleContributionCategoryStatus called with id:', id);
    $.ajax({
        url: window.APP_BASE_URL + 'admin/settings/contribution-categories/toggle-status/' + id,
        type: 'POST',
        success: function(response) {
            if (response.success) {
                window.loadContributionCategories();
                if (typeof showNotification === 'function') {
                    showNotification(response.message || 'Category status updated successfully', 'success');
                } else {
                    alert(response.message || 'Category status updated successfully');
                }
            } else {
                if (typeof showNotification === 'function') {
                    showNotification(response.message || 'Failed to update category status', 'error');
                } else {
                    alert(response.message || 'Failed to update category status');
                }
            }
        },
        error: function(xhr) {
            console.error('Failed to toggle status:', xhr);
            if (typeof showNotification === 'function') {
                showNotification('An error occurred while updating the category status', 'error');
            } else {
                alert('An error occurred while updating the category status');
            }
        }
    });
};

// Delete category - make it globally available
window.deleteContributionCategory = window.deleteContributionCategory || function(id) {
    if (!confirm('Are you sure you want to delete this category? This action cannot be undone.\n\nNote: Categories that are being used by contributions cannot be deleted.')) {
        return;
    }
    
    console.log('deleteContributionCategory called with id:', id);
    $.ajax({
        url: window.APP_BASE_URL + 'admin/settings/contribution-categories/delete/' + id,
        type: 'POST',
        success: function(response) {
            if (response.success) {
                window.loadContributionCategories();
                if (typeof showNotification === 'function') {
                    showNotification(response.message || 'Category deleted successfully', 'success');
                } else {
                    alert(response.message || 'Category deleted successfully');
                }
            } else {
                if (typeof showNotification === 'function') {
                    showNotification(response.message || 'Failed to delete category', 'error');
                } else {
                    alert(response.message || 'Failed to delete category');
                }
            }
        },
        error: function(xhr) {
            console.error('Failed to delete:', xhr);
            if (typeof showNotification === 'function') {
                showNotification('An error occurred while deleting the category', 'error');
            } else {
                alert('An error occurred while deleting the category');
            }
        }
    });
};

// Setup form handlers
function setupContributionCategoryForms() {
    // Auto-generate code from name
    $('#addCategoryName, #editCategoryName').on('input', function() {
        const name = $(this).val();
        const code = name.toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
        const formId = $(this).attr('id').includes('add') ? 'addCategoryCode' : 'editCategoryCode';
        $('#' + formId).val(code);
    });

    // Add category form submission
    $('#addContributionCategoryForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
        
        $.ajax({
            url: window.APP_BASE_URL + 'admin/settings/contribution-categories/store',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Create response:', response);
                if (response.success) {
                    // Close only the add modal, keep the main modal open
                    $('#addContributionCategoryModal').modal('hide');
                    $('#addContributionCategoryForm')[0].reset();
                    
                    // Wait a moment for modal to close, then refresh
                    setTimeout(function() {
                        if (typeof window.loadContributionCategories === 'function') {
                            console.log('Refreshing categories table after create...');
                            window.loadContributionCategories();
                        } else {
                            console.error('loadContributionCategories function not available!');
                        }
                    }, 300);
                    
                    if (typeof showNotification === 'function') {
                        showNotification(response.message || 'Category created successfully', 'success');
                    } else {
                        alert(response.message || 'Category created successfully');
                    }
                } else {
                    if (typeof showNotification === 'function') {
                        showNotification(response.message || 'Failed to create category', 'error');
                    } else {
                        alert(response.message || 'Failed to create category');
                    }
                    if (response.errors) {
                        displayValidationErrors('#addContributionCategoryForm', response.errors);
                    }
                }
            },
            error: function(xhr) {
                console.error('Create error:', xhr);
                if (typeof showNotification === 'function') {
                    showNotification('An error occurred while creating the category', 'error');
                } else {
                    alert('An error occurred while creating the category');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Edit category form submission
    $('#editContributionCategoryForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        
        const id = $('#editCategoryId').val();
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Updating...');
        
        $.ajax({
            url: window.APP_BASE_URL + 'admin/settings/contribution-categories/update/' + id,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Update response:', response);
                if (response.success) {
                    // Close only the edit modal, keep the main modal open
                    $('#editContributionCategoryModal').modal('hide');
                    
                    // Wait a moment for modal to close, then refresh
                    setTimeout(function() {
                        if (typeof window.loadContributionCategories === 'function') {
                            console.log('Refreshing categories table after update...');
                            window.loadContributionCategories();
                        } else {
                            console.error('loadContributionCategories function not available!');
                        }
                    }, 300);
                    
                    if (typeof showNotification === 'function') {
                        showNotification(response.message || 'Category updated successfully', 'success');
                    } else {
                        alert(response.message || 'Category updated successfully');
                    }
                } else {
                    if (typeof showNotification === 'function') {
                        showNotification(response.message || 'Failed to update category', 'error');
                    } else {
                        alert(response.message || 'Failed to update category');
                    }
                    if (response.errors) {
                        displayValidationErrors('#editContributionCategoryForm', response.errors);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Update error:', {xhr: xhr, status: status, error: error, responseText: xhr.responseText});
                if (typeof showNotification === 'function') {
                    showNotification('An error occurred while updating the category', 'error');
                } else {
                    alert('An error occurred while updating the category');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // When modals are closed, clear validation errors
    $('#addContributionCategoryModal, #editContributionCategoryModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
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
        console.log('Contribution categories script initialized (jQuery ready)');
        
        // Set up form handlers
        setupContributionCategoryForms();
        
        // Load categories when modal is shown
        $('#contributionCategoriesModal').off('shown.bs.modal').on('shown.bs.modal', function() {
            console.log('Categories modal opened, loading data...');
            if (typeof window.loadContributionCategories === 'function') {
                window.loadContributionCategories();
            }
        });
        
        // Prevent the main modal from closing when child modals close
        $('#addContributionCategoryModal, #editContributionCategoryModal').on('hidden.bs.modal', function() {
            console.log('Child modal closed, ensuring main modal stays open');
            // Ensure the main modal backdrop is still there
            if ($('#contributionCategoriesModal').hasClass('show')) {
                console.log('Main categories modal is still open - good!');
            }
        });
        
        // Also trigger on button click as backup
        $('button[data-bs-target="#contributionCategoriesModal"]').on('click', function() {
            console.log('Categories modal button clicked');
            setTimeout(function() {
                if ($('#contributionCategoriesModal').hasClass('show')) {
                    console.log('Modal is now shown, loading data...');
                    if (typeof window.loadContributionCategories === 'function') {
                        window.loadContributionCategories();
                    }
                }
            }, 200);
        });
    });
} else {
    // Wait for jQuery
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Contribution categories script loaded, waiting for jQuery...');
        var checkJQuery = setInterval(function() {
            if (typeof jQuery !== 'undefined') {
                clearInterval(checkJQuery);
                $(document).ready(function() {
                    setupContributionCategoryForms();
                    $('#contributionCategoriesModal').off('shown.bs.modal').on('shown.bs.modal', function() {
                        if (typeof window.loadContributionCategories === 'function') {
                            window.loadContributionCategories();
                        }
                    });
                });
            }
        }, 100);
        setTimeout(function() { clearInterval(checkJQuery); }, 5000);
    });
}

console.log('Contribution categories script loaded');

