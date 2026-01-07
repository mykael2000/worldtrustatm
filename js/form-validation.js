/**
 * Form Validation JavaScript
 * Real-time client-side validation for index.php
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('activationForm');
    
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
