/**

 * Form Validation and Enhancement for Step 1: Personal & Account Information

 * Form Validation JavaScript
 * Real-time client-side validation for index.php

 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('activationForm');

    const loadingSpinner = document.getElementById('loadingSpinner');
    
    // Form field elements
    const phoneInput = document.getElementById('phone');
    const accountNumberInput = document.getElementById('account_number');
    const ssnInput = document.getElementById('ssn_last4');
    const zipInput = document.getElementById('zip');
    
    /**
     * Format phone number as user types
     */
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 10) {
                value = value.substr(0, 10);
            }
            
            if (value.length >= 6) {
                e.target.value = `(${value.substr(0, 3)}) ${value.substr(3, 3)}-${value.substr(6)}`;
            } else if (value.length >= 3) {
                e.target.value = `(${value.substr(0, 3)}) ${value.substr(3)}`;
            } else {
                e.target.value = value;
            }
        });
    }
    
    /**
     * Format account number as user types
     */
    if (accountNumberInput) {
        accountNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 12) {
                value = value.substr(0, 12);
            }
            e.target.value = value;
        });
    }
    
    /**
     * Format SSN last 4 digits
     */
    if (ssnInput) {
        ssnInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 4) {
                value = value.substr(0, 4);
            }
            e.target.value = value;
        });
    }
    
    /**
     * Format ZIP code
     */
    if (zipInput) {
        zipInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 10) {
                value = value.substr(0, 10);
            }
            e.target.value = value;
        });
    }
    
    /**
     * Real-time validation
     */
    const inputs = form.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateField(this);
            }
        });
    });
    
    /**
     * Validate individual field
     */
    function validateField(field) {
        const formGroup = field.closest('.form-group');
        const fieldName = field.name;
        const fieldValue = field.value.trim();
        
        let isValid = true;
        let errorMessage = '';
        
        // Required field check
        if (field.hasAttribute('required') && !fieldValue) {
            isValid = false;
            errorMessage = 'This field is required';
        }
        
        // Specific validations
        if (isValid && fieldValue) {
            switch(fieldName) {
                case 'first_name':
                case 'last_name':
                    if (fieldValue.length < 2) {
                        isValid = false;
                        errorMessage = 'Must be at least 2 characters';
                    }
                    break;
                    
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(fieldValue)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address';
                    }
                    break;
                    
                case 'phone':
                    const phoneDigits = fieldValue.replace(/\D/g, '');
                    if (phoneDigits.length < 10) {
                        isValid = false;
                        errorMessage = 'Please enter a valid 10-digit phone number';
                    }
                    break;
                    
                case 'account_number':
                    if (fieldValue.length !== 12 || !/^\d{12}$/.test(fieldValue)) {
                        isValid = false;
                        errorMessage = 'Account number must be exactly 12 digits';
                    }
                    break;
                    
                case 'zip':
                    if (fieldValue.length < 5) {
                        isValid = false;
                        errorMessage = 'ZIP code must be at least 5 digits';
                    }
                    break;
                    
                case 'ssn_last4':
                    if (fieldValue.length !== 4 || !/^\d{4}$/.test(fieldValue)) {
                        isValid = false;
                        errorMessage = 'Must be exactly 4 digits';
                    }
                    break;
            }
        }
        
        // Update UI
        if (isValid) {
            formGroup.classList.remove('has-error');
            field.classList.remove('error');
        } else {
            formGroup.classList.add('has-error');
            field.classList.add('error');
            const errorElement = formGroup.querySelector('.error-message');
            if (errorElement && errorMessage) {
                errorElement.textContent = errorMessage;
            }
        }
        
        return isValid;
    }
    
    /**
     * Form submission
     */
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate all fields
            let isValid = true;
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });
            
            if (isValid) {
                // Show loading spinner
                if (loadingSpinner) {
                    loadingSpinner.classList.add('active');
                }
                
                // Submit form
                this.submit();
            } else {
                // Scroll to first error
                const firstError = form.querySelector('.has-error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                showToast('Please correct the errors in the form', 'error');
            }
        });
    }
    
    /**
     * Show toast notification
     */
    function showToast(message, type = 'info') {
        const existingToast = document.querySelector('.toast');
        if (existingToast) {
            existingToast.remove();
        }
        
        const toast = document.createElement('div');
        toast.className = `toast ${type} show`;
        toast.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-weight: 600;">${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});

    
    if (!form) return;
    
    // Email validation
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            validateEmail(this);
        });
    }
    
    // Phone validation
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('blur', function() {
            validatePhone(this);
        });
        
        // Format phone number as user types
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+\-\s()]/g, '');
        });
    }
    
    // Account number validation
    const accountInput = document.getElementById('account_number');
    if (accountInput) {
        accountInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        accountInput.addEventListener('blur', function() {
            validateAccount(this);
        });
    }
    
    // SSN validation
    const ssnInput = document.getElementById('ssn');
    if (ssnInput) {
        ssnInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        ssnInput.addEventListener('blur', function() {
            validateSSN(this);
        });
    }
    
    // ZIP code validation
    const zipInput = document.getElementById('zip');
    if (zipInput) {
        zipInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9\-]/g, '');
        });
    }
    
    // Real-time validation for required fields
    const requiredInputs = form.querySelectorAll('input[required]');
    requiredInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            validateRequired(this);
        });
        
        input.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.classList.remove('error');
                this.classList.add('success');
                hideError(this);
            }
        });
    });
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        requiredInputs.forEach(function(input) {
            if (!validateRequired(input)) {
                isValid = false;
            }
        });
        
        if (emailInput && !validateEmail(emailInput)) {
            isValid = false;
        }
        
        if (phoneInput && !validatePhone(phoneInput)) {
            isValid = false;
        }
        
        if (accountInput && !validateAccount(accountInput)) {
            isValid = false;
        }
        
        if (ssnInput && !validateSSN(ssnInput)) {
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            // Scroll to first error
            const firstError = form.querySelector('.error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
});

// Validation functions
function validateRequired(input) {
    if (input.value.trim() === '') {
        showError(input, 'This field is required');
        return false;
    }
    hideError(input);
    input.classList.add('success');
    return true;
}

function validateEmail(input) {
    const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!input.value.trim()) {
        showError(input, 'Email is required');
        return false;
    }
    if (!emailPattern.test(input.value)) {
        showError(input, 'Please enter a valid email address');
        return false;
    }
    hideError(input);
    input.classList.add('success');
    return true;
}

function validatePhone(input) {
    const phonePattern = /^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/;
    if (!input.value.trim()) {
        showError(input, 'Phone number is required');
        return false;
    }
    if (!phonePattern.test(input.value.replace(/[\s\-\(\)]/g, ''))) {
        showError(input, 'Please enter a valid phone number');
        return false;
    }
    hideError(input);
    input.classList.add('success');
    return true;
}

function validateAccount(input) {
    const accountPattern = /^\d{10,12}$/;
    if (!input.value.trim()) {
        showError(input, 'Account number is required');
        return false;
    }
    if (!accountPattern.test(input.value)) {
        showError(input, 'Account number must be 10-12 digits');
        return false;
    }
    hideError(input);
    input.classList.add('success');
    return true;
}

function validateSSN(input) {
    const ssnPattern = /^\d{4}$/;
    if (!input.value.trim()) {
        showError(input, 'Last 4 digits of SSN required');
        return false;
    }
    if (!ssnPattern.test(input.value)) {
        showError(input, 'Please enter exactly 4 digits');
        return false;
    }
    hideError(input);
    input.classList.add('success');
    return true;
}

function showError(input, message) {
    input.classList.add('error');
    input.classList.remove('success');
    
    let errorElement = input.parentElement.querySelector('.error-message');
    if (!errorElement) {
        errorElement = document.createElement('span');
        errorElement.className = 'error-message';
        input.parentElement.appendChild(errorElement);
    }
    errorElement.textContent = message;
    errorElement.classList.add('show');
}

function hideError(input) {
    input.classList.remove('error');
    const errorElement = input.parentElement.querySelector('.error-message');
    if (errorElement) {
        errorElement.classList.remove('show');
    }
}
