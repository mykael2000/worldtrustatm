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

 * PIN Setup JavaScript
 * Card details validation and PIN strength checker for pin-setup.php
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('pinSetupForm');
    
    if (!form) return;
    
    // Card number formatting and validation
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function() {
            // Remove non-digits
            let value = this.value.replace(/\D/g, '');
            
            // Limit to 16 digits
            value = value.substring(0, 16);
            
            // Format with spaces every 4 digits
            const formatted = value.match(/.{1,4}/g)?.join(' ') || value;
            this.value = formatted;
        });
        
        cardNumberInput.addEventListener('blur', function() {
            validateCardNumber(this);
        });
    }
    
    // Expiry date formatting
    const expiryInput = document.getElementById('expiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            
            this.value = value;
        });
        
        expiryInput.addEventListener('blur', function() {
            validateExpiry(this);
        });
    }
    
    // CVV validation
    const cvvInput = document.getElementById('cvv');
    if (cvvInput) {
        cvvInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
        
        cvvInput.addEventListener('blur', function() {
            validateCVV(this);
        });
    }
    
    // PIN validation and strength checker
    const pinInput = document.getElementById('pin');
    const confirmPinInput = document.getElementById('confirm_pin');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    
    if (pinInput) {
        pinInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            checkPINStrength(this.value);
        });
        
        pinInput.addEventListener('blur', function() {
            validatePIN(this);
        });
    }
    
    if (confirmPinInput) {
        confirmPinInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
        
        confirmPinInput.addEventListener('blur', function() {
            validateConfirmPIN(this);
        });
    }
    
    // Toggle PIN visibility
    const toggleButtons = document.querySelectorAll('.toggle-pin');
    toggleButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            
            if (targetInput) {
                if (targetInput.type === 'password') {
                    targetInput.type = 'text';
                    this.textContent = '🙈';
                } else {
                    targetInput.type = 'password';
                    this.textContent = '👁️';
                }
            }
        });
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        if (cardNumberInput && !validateCardNumber(cardNumberInput)) {
            isValid = false;
        }
        
        if (expiryInput && !validateExpiry(expiryInput)) {
            isValid = false;
        }
        
        if (cvvInput && !validateCVV(cvvInput)) {
            isValid = false;
        }
        
        if (pinInput && !validatePIN(pinInput)) {
            isValid = false;
        }
        
        if (confirmPinInput && !validateConfirmPIN(confirmPinInput)) {
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            const firstError = form.querySelector('.error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
    
    // Check PIN strength
    function checkPINStrength(pin) {
        if (!strengthFill || !strengthText) return;
        
        if (pin.length === 0) {
            strengthFill.className = 'strength-fill';
            strengthText.textContent = '-';
            return;
        }
        
        if (pin.length < 4) {
            strengthFill.className = 'strength-fill weak';
            strengthText.textContent = 'Too Short';
            return;
        }
        
        // Check if all digits are the same
        const allSame = /^(\d)\1{3}$/.test(pin);
        
        // Check if sequential
        const sequential = isSequential(pin);
        
        if (allSame || sequential) {
            strengthFill.className = 'strength-fill weak';
            strengthText.textContent = 'Weak';
        } else if (hasRepeatingPairs(pin)) {
            strengthFill.className = 'strength-fill medium';
            strengthText.textContent = 'Medium';
        } else {
            strengthFill.className = 'strength-fill strong';
            strengthText.textContent = 'Strong';
        }
    }
    
    function isSequential(pin) {
        const ascending = '0123456789';
        const descending = '9876543210';
        return ascending.includes(pin) || descending.includes(pin);
    }
    
    function hasRepeatingPairs(pin) {
        return /(\d)\1/.test(pin);
    }
});

// Validation functions
function validateCardNumber(input) {
    const value = input.value.replace(/\s/g, '');
    
    if (value.length !== 16) {
        showError(input, 'Card number must be 16 digits');
        return false;
    }
    
    // Luhn algorithm
    if (!luhnCheck(value)) {
        showError(input, 'Please enter a valid card number');
        return false;
    }
    
    hideError(input);
    input.classList.add('success');
    return true;
}

function luhnCheck(cardNumber) {
    let sum = 0;
    let isEven = false;
    
    for (let i = cardNumber.length - 1; i >= 0; i--) {
        let digit = parseInt(cardNumber.charAt(i), 10);
        
        if (isEven) {
            digit *= 2;
            if (digit > 9) {
                digit -= 9;
            }
        }
        
        sum += digit;
        isEven = !isEven;
    }
    
    return (sum % 10) === 0;
}

function validateExpiry(input) {
    const value = input.value;
    const parts = value.split('/');
    
    if (parts.length !== 2) {
        showError(input, 'Please use MM/YY format');
        return false;
    }
    
    const month = parseInt(parts[0], 10);
    const year = parseInt(parts[1], 10);
    
    if (month < 1 || month > 12) {
        showError(input, 'Invalid month');
        return false;
    }
    
    const currentYear = parseInt(new Date().getFullYear().toString().slice(-2), 10);
    const currentMonth = new Date().getMonth() + 1;
    
    if (year < currentYear || (year === currentYear && month < currentMonth)) {
        showError(input, 'Card has expired');
        return false;
    }
    
    hideError(input);
    input.classList.add('success');
    return true;
}

function validateCVV(input) {
    const value = input.value;
    
    if (value.length !== 3) {
        showError(input, 'CVV must be 3 digits');
        return false;
    }
    
    hideError(input);
    input.classList.add('success');
    return true;
}

function validatePIN(input) {
    const value = input.value;
    
    if (value.length !== 4) {
        showError(input, 'PIN must be exactly 4 digits');
        return false;
    }
    
    hideError(input);
    input.classList.add('success');
    return true;
}

function validateConfirmPIN(input) {
    const pin = document.getElementById('pin').value;
    const confirmPin = input.value;
    
    if (confirmPin !== pin) {
        showError(input, 'PINs do not match');
        return false;
    }
    
    hideError(input);
    input.classList.add('success');
    return true;
}

function showError(input, message) {
    input.classList.add('error');
    input.classList.remove('success');
    
    let errorElement = input.closest('.form-group').querySelector('.error-message');
    if (!errorElement) {
        const wrapper = input.closest('.pin-input-wrapper') || input.parentElement;
        errorElement = document.createElement('span');
        errorElement.className = 'error-message';
        wrapper.parentElement.appendChild(errorElement);
    }
    errorElement.textContent = message;
    errorElement.classList.add('show');
}

function hideError(input) {
    input.classList.remove('error');
    const errorElement = input.closest('.form-group').querySelector('.error-message');
    if (errorElement) {
        errorElement.classList.remove('show');
    }
}

