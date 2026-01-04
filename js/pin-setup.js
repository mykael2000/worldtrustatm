/**

 * PIN Setup and Validation for Step 3
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('pinForm');
    const loadingSpinner = document.getElementById('loadingSpinner');
    
    // Form field elements
    const pinInput = document.getElementById('pin');
    const pinConfirmInput = document.getElementById('pin_confirm');
    const pinDisplay = document.getElementById('pinDisplay');
    
    /**
     * Format PIN input (numbers only, max 4 digits)
     */
    if (pinInput) {
        pinInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 4) {
                value = value.substr(0, 4);
            }
            e.target.value = value;
            
            // Update PIN display
            updatePinDisplay(value);
        });
        
        // Show PIN display on focus
        pinInput.addEventListener('focus', function() {
            if (pinDisplay) {
                pinDisplay.style.display = 'flex';
            }
        });
    }
    
    /**
     * Format PIN confirm input
     */
    if (pinConfirmInput) {
        pinConfirmInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 4) {
                value = value.substr(0, 4);
            }
            e.target.value = value;
        });
    }
    
    /**
     * Update PIN display visualization
     */
    function updatePinDisplay(pin) {
        if (!pinDisplay) return;
        
        for (let i = 1; i <= 4; i++) {
            const digit = document.getElementById('digit' + i);
            if (!digit) continue;
            
            if (pin.length >= i) {
                digit.textContent = '•';
                digit.classList.add('filled');
            } else {
                digit.textContent = '';
                digit.classList.remove('filled');
            }
        }
    }
    
    /**
     * Validate PIN strength (basic check)
     */
    function validatePinStrength(pin) {
        // Check for sequential numbers
        const sequential = ['0123', '1234', '2345', '3456', '4567', '5678', '6789'];
        if (sequential.includes(pin)) {
            return {
                valid: false,
                message: 'PIN should not be sequential numbers'
            };
        }
        
        // Check for repeated digits
        if (/^(\d)\1{3}$/.test(pin)) {
            return {
                valid: false,
                message: 'PIN should not be all the same digit'
            };
        }
        
        // Check common weak PINs
        const weakPins = ['1111', '2222', '3333', '4444', '5555', '6666', '7777', '8888', '9999', '0000', '1234', '4321'];
        if (weakPins.includes(pin)) {
            return {
                valid: false,
                message: 'Please choose a stronger PIN'
            };
        }
        
        return {
            valid: true,
            message: 'PIN is acceptable'
        };
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
                case 'pin':
                    if (fieldValue.length !== 4 || !/^\d{4}$/.test(fieldValue)) {
                        isValid = false;
                        errorMessage = 'PIN must be exactly 4 digits';
                    } else {
                        // Check PIN strength (warning only, not blocking)
                        const strengthCheck = validatePinStrength(fieldValue);
                        if (!strengthCheck.valid) {
                            // Show warning but don't block
                            showToast(strengthCheck.message, 'warning');
                        }
                    }
                    break;
                    
                case 'pin_confirm':
                    const pinValue = pinInput ? pinInput.value : '';
                    if (fieldValue !== pinValue) {
                        isValid = false;
                        errorMessage = 'PINs do not match';
                    } else if (fieldValue.length !== 4) {
                        isValid = false;
                        errorMessage = 'PIN must be exactly 4 digits';
                    }
                    break;
            }
        }
        
        // Update UI
        if (isValid) {
            hideError(field);
            field.classList.add('success');
        } else {
            showError(field, errorMessage);
        }
        
        return isValid;
    }
    
    /**
     * Show error message
     */
    function showError(input, message) {
        input.classList.add('error');
        input.classList.remove('success');
        
        const formGroup = input.closest('.form-group');
        if (formGroup) {
            formGroup.classList.add('has-error');
        }
        
        const errorElement = input.parentElement.querySelector('.error-message');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.add('show');
        }
    }
    
    /**
     * Hide error message
     */
    function hideError(input) {
        input.classList.remove('error');
        
        const formGroup = input.closest('.form-group');
        if (formGroup) {
            formGroup.classList.remove('has-error');
        }
        
        const errorElement = input.parentElement.querySelector('.error-message');
        if (errorElement) {
            errorElement.classList.remove('show');
        }
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
                // Final confirmation
                if (confirm('Are you sure you want to complete the activation with this PIN?')) {
                    // Show loading spinner
                    if (loadingSpinner) {
                        loadingSpinner.classList.add('active');
                    }
                    
                    // Submit form
                    this.submit();
                }
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
