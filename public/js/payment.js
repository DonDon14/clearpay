// Payment Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const paymentForm = document.getElementById('paymentForm');
    const paymentModal = document.getElementById('addPaymentModal');
    
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
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
                    // Always redirect to correct Payer login page
                    window.location.href = '/payer/login';
                    return;
                }
                if (data.success) {
                    // Show success message
                    showNotification(data.message, 'success');
                    
                    // Show reference number if provided
                    if (data.reference_number) {
                        showNotification(`Reference Number: ${data.reference_number}`, 'info');
                    }
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(paymentModal);
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Reset form
                    paymentForm.reset();
                    
                    // Show QR receipt if payment data is provided
                    if (data.payment) {
                        // Wait for payment modal to close, then show QR receipt
                        setTimeout(() => {
                            if (typeof showQRReceipt === 'function') {
                                showQRReceipt(data.payment);
                            } else {
                                // Reload page if QR receipt function not available
                                window.location.reload();
                            }
                        }, 800);
                    } else if (data.payment_id) {
                        // Fallback: Fetch payment data if not in response
                        const baseUrl = window.APP_BASE_URL || '';
                        fetch(`${baseUrl}/payments/recent`)
                            .then(response => response.json())
                            .then(recentData => {
                                if (recentData.success) {
                                    const payment = recentData.payments.find(p => p.id == data.payment_id);
                                    if (payment && typeof showQRReceipt === 'function') {
                                        setTimeout(() => {
                                            showQRReceipt(payment);
                                        }, 800);
                                    } else {
                                        window.location.reload();
                                    }
                                } else {
                                    window.location.reload();
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching payment data:', error);
                                window.location.reload();
                            });
                    } else {
                        // Reload page to show updated data
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                    
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
    if (paymentModal) {
        paymentModal.addEventListener('hidden.bs.modal', function() {
            if (paymentForm) {
                paymentForm.reset();
                clearErrorMessages();
                // Reset remaining balance
                document.getElementById('remainingBalance').value = '0.00';
            }
        });
    }
});

// Function to display field validation errors
function displayFieldErrors(errors) {
    Object.keys(errors).forEach(field => {
        // Map field names to their actual input IDs
        const fieldMap = {
            'payer_name': 'payerName',
            'payer_id': 'payerId',
            'contribution_id': 'contributionId',
            'amount_paid': 'amountPaid',
            'payment_method': 'paymentMethod',
            'is_partial_payment': 'isPartialPayment',
            'payment_date': 'paymentDate',
            'contact_number': 'contactNumber',
            'email_address': 'emailAddress'
        };
        
        const inputId = fieldMap[field] || field;
        const input = document.getElementById(inputId);
        
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
    const form = document.getElementById('paymentForm');
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

// Global variables for add payment functionality
let selectedPayer = null;
let payerSearchTimeout = null;

// Function to scan ID for existing payer
window.scanIDForExistingPayer = function() {
    // This would integrate with a QR scanner
    // For now, we'll show a placeholder
    alert('QR Scanner functionality would be implemented here');
};

// Function to search payers
window.searchPayers = function(query) {
    if (!query || query.length < 2) {
        document.getElementById('payerDropdown').style.display = 'none';
        return;
    }

    // Clear previous timeout
    if (payerSearchTimeout) {
        clearTimeout(payerSearchTimeout);
    }

    // Debounce search
    payerSearchTimeout = setTimeout(() => {
        fetch(`/payments/search-payers?q=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPayerResults(data.results);
            } else {
                console.error('Search failed:', data.message);
                document.getElementById('payerDropdown').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('payerDropdown').style.display = 'none';
        });
    }, 300);
};

// Function to display payer search results
window.displayPayerResults = function(results) {
    const dropdown = document.getElementById('payerDropdown');
    dropdown.innerHTML = '';

    if (results.length === 0) {
        dropdown.innerHTML = '<div class="list-group-item text-muted">No payers found</div>';
        dropdown.style.display = 'block';
        return;
    }

    results.forEach(payer => {
        const item = document.createElement('div');
        item.className = 'list-group-item list-group-item-action';
        item.style.cursor = 'pointer';
        item.innerHTML = `
            <div class="fw-bold">${payer.payer_name}</div>
            <small class="text-muted">ID: ${payer.payer_id}</small>
        `;

        item.addEventListener('click', function() {
            selectedPayer = payer;
            document.getElementById('payerSelect').value = payer.payer_name;
            document.getElementById('existingPayerId').value = payer.id;
            dropdown.style.display = 'none';
            
            // Note: Contribution checking is now handled in the modal
        });

        dropdown.appendChild(item);
    });

    dropdown.style.display = 'block';
};

// Note: Contribution checking functions have been moved to the modal
// and are now handled automatically when contributions are selected

// Function to submit payment
window.submitPayment = function() {
    if (!selectedPayer) {
        alert('Please select a payer');
        return;
    }

    const form = document.getElementById('paymentForm');
    const formData = new FormData(form);

    // Validate required fields
    const requiredFields = ['contribution_id', 'amount_paid', 'payment_method', 'payment_date'];
    const missingFields = requiredFields.filter(field => !formData.get(field));

    if (missingFields.length > 0) {
        alert('Please fill in all required fields');
        return;
    }

    // Calculate remaining balance
    const contributionSelect = document.getElementById('contributionId');
    const contributionAmount = parseFloat(contributionSelect.options[contributionSelect.selectedIndex].dataset.amount) || 0;
    const amountPaid = parseFloat(formData.get('amount_paid')) || 0;
    const remainingBalance = contributionAmount - amountPaid;

    formData.set('is_partial_payment', remainingBalance > 0 ? '1' : '0');
    formData.set('remaining_balance', remainingBalance.toString());
    
    // Ensure payer_id is set for existing payers
    if (selectedPayer) {
        formData.set('payer_id', selectedPayer.id);
    }
    

    const submitBtn = document.querySelector('#addPaymentModal .btn-primary');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitBtn.disabled = true;

    fetch('/payments/save', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Payment added successfully!', 'success');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addPaymentModal'));
            if (modal) {
                modal.hide();
            }
            
            // Reset form
            form.reset();
            selectedPayer = null;
            document.getElementById('existingPayerId').value = '';
            
            // Reload page to show updated data
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Error adding payment', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding payment', 'error');
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
};

// Event listeners for add payment modal
document.addEventListener('DOMContentLoaded', function() {
    const payerSelect = document.getElementById('payerSelect');
    const payerDropdown = document.getElementById('payerDropdown');
    
    if (payerSelect) {
        // Search payers on input
        payerSelect.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length >= 2) {
                searchPayers(query);
            } else {
                payerDropdown.style.display = 'none';
            }
        });

        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!payerSelect.contains(e.target) && !payerDropdown.contains(e.target)) {
                payerDropdown.style.display = 'none';
            }
        });
    }

    // Handle payer type radio buttons
    const existingPayerRadio = document.getElementById('existingPayer');
    const newPayerRadio = document.getElementById('newPayer');
    const existingPayerFields = document.getElementById('existingPayerFields');
    const newPayerFields = document.getElementById('newPayerFields');

    if (existingPayerRadio && newPayerRadio) {
        existingPayerRadio.addEventListener('change', function() {
            if (this.checked) {
                existingPayerFields.style.display = 'block';
                newPayerFields.style.display = 'none';
            }
        });

        newPayerRadio.addEventListener('change', function() {
            if (this.checked) {
                existingPayerFields.style.display = 'none';
                newPayerFields.style.display = 'block';
            }
        });
    }
});