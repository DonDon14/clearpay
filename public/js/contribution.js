// Contribution Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const contributionForm = document.getElementById('contributionForm');
    const contributionModal = document.getElementById('contributionModal');
    
    if (contributionForm) {
        contributionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;
            
            // Clear previous error messages
            clearErrorMessages();
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.session_expired) {
                    window.location.href = '/payer/login';
                    return;
                }
                if (data.success) {
                    // Show success message
                    showNotification(data.message, 'success');
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(contributionModal);
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Reset form
                    contributionForm.reset();
                    
                    // Reload page to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                    
                } else {
                    // Show error message
                    showNotification(data.message || 'An error occurred', 'error');
                    
                    // Display field errors if any
                    if (data.errors) {
                        displayFieldErrors(data.errors);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An unexpected error occurred', 'error');
            })
            .finally(() => {
                // Restore button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    
    // Reset form when modal is closed
    if (contributionModal) {
        contributionModal.addEventListener('hidden.bs.modal', function() {
            if (contributionForm) {
                contributionForm.reset();
                clearErrorMessages();
            }
        });
    }
});

// Function to display field validation errors
function displayFieldErrors(errors) {
    Object.keys(errors).forEach(field => {
        const input = document.getElementById('contribution' + capitalizeFirstLetter(field));
        if (input) {
            input.classList.add('is-invalid');
            
            // Find or create error message element
            let errorElement = input.parentNode.querySelector('.invalid-feedback');
            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.className = 'invalid-feedback';
                input.parentNode.appendChild(errorElement);
            }
            errorElement.textContent = errors[field];
        }
    });
}

// Function to clear error messages
function clearErrorMessages() {
    const form = document.getElementById('contributionForm');
    if (form) {
        // Remove is-invalid class from all inputs
        form.querySelectorAll('.is-invalid').forEach(input => {
            input.classList.remove('is-invalid');
        });
        
        // Remove all error message elements
        form.querySelectorAll('.invalid-feedback').forEach(element => {
            element.remove();
        });
    }
}

// Function to show notifications
function showNotification(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
}

// Utility function to capitalize first letter
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}