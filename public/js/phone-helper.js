// Phone Number Validation Helper
// Reusable JavaScript functions for phone number validation across modals

(function() {
    'use strict';
    
    // Phone number validation helper (must be exactly 11 digits, numbers only)
    window.validatePhoneNumber = function(phoneNumber) {
        if (!phoneNumber || phoneNumber.trim() === '') {
            return true; // Empty is allowed (optional field)
        }
        // Remove whitespace and check if exactly 11 digits
        const cleaned = phoneNumber.replace(/\s+/g, '').replace(/[^0-9]/g, '');
        return /^[0-9]{11}$/.test(cleaned);
    };
    
    // Sanitize phone number (remove non-numeric characters)
    window.sanitizePhoneNumber = function(phoneNumber) {
        if (!phoneNumber) return '';
        return phoneNumber.replace(/[^0-9]/g, '');
    };
    
    // Initialize phone number field with validation
    window.initPhoneNumberField = function(fieldId, options = {}) {
        const field = document.getElementById(fieldId);
        if (!field) return;
        
        const required = options.required !== false; // Default to true if not specified
        const errorMessage = options.errorMessage || 'Contact number must be exactly 11 digits';
        
        // Helper function to show field error
        function showFieldError(message) {
            field.classList.add('is-invalid');
            const feedback = field.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = message;
            }
        }
        
        // Helper function to clear field error
        function clearFieldError() {
            field.classList.remove('is-invalid');
            const feedback = field.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = '';
            }
        }
        
        // Phone number input handler - only allow numbers and limit to 11 digits
        field.addEventListener('input', function(e) {
            // Remove any non-numeric characters
            let value = window.sanitizePhoneNumber(e.target.value);
            
            // Limit to 11 digits
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            e.target.value = value;
            
            // Validate
            if (value && !window.validatePhoneNumber(value)) {
                showFieldError(errorMessage);
            } else {
                clearFieldError();
            }
        });
        
        // Validate on blur
        field.addEventListener('blur', function(e) {
            const value = e.target.value.trim();
            if (required && !value) {
                showFieldError('Phone number is required');
            } else if (value && !window.validatePhoneNumber(value)) {
                showFieldError(errorMessage);
            } else {
                clearFieldError();
            }
        });
        
        // Prevent non-numeric input
        field.addEventListener('keypress', function(e) {
            // Allow: backspace, delete, tab, escape, enter
            if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true)) {
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    };
})();

