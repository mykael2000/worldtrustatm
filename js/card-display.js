/**

 * Card Display and Validation for Step 2: Card Details
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('cardForm');
    const loadingSpinner = document.getElementById('loadingSpinner');
    
    // Form field elements
    const cardNumberInput = document.getElementById('card_number');
    const expiryDateInput = document.getElementById('expiry_date');
    const cvvInput = document.getElementById('cvv');
    const balanceInput = document.getElementById('balance');
    
    // Card preview elements
    const cardNumberDisplay = document.getElementById('cardNumberDisplay');
    const cardExpiryDisplay = document.getElementById('cardExpiryDisplay');
    
    /**
     * Format card number as user types
     */
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '');
            if (value.length > 16) {
                value = value.substr(0, 16);
            }
            
            // Add spaces every 4 digits
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
            
            // Update card preview
            if (cardNumberDisplay) {
                if (value.length > 0) {
                    let displayValue = value.padEnd(16, '*').match(/.{1,4}/g).join(' ');
                    cardNumberDisplay.textContent = displayValue;
                } else {
                    cardNumberDisplay.textContent = '**** **** **** ****';
                }
            }
        });
    }
    
    /**
     * Format expiry date as user types (MM/YY)
     */
    if (expiryDateInput) {
        expiryDateInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length >= 2) {
                value = value.substr(0, 2) + '/' + value.substr(2, 2);
            }
            
            e.target.value = value.substr(0, 5);
            
            // Update card preview
            if (cardExpiryDisplay) {
                cardExpiryDisplay.textContent = value || 'MM/YY';
            }
        });
    }
    
    /**
     * Format CVV input (numbers only, max 3 digits)
     */
    if (cvvInput) {
        cvvInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 3) {
                value = value.substr(0, 3);
            }
            e.target.value = value;
        });
    }
    
    /**
     * Format balance input
     */
    if (balanceInput) {
        balanceInput.addEventListener('blur', function(e) {
            let value = parseFloat(e.target.value);
            if (!isNaN(value)) {
                e.target.value = value.toFixed(2);
            }
        });
    }
    
    /**
     * Luhn algorithm for card number validation
     */
    function validateCardNumber(number) {
        const digits = number.replace(/\D/g, '');
        
        if (digits.length !== 16) {
            return false;
        }
        
        let sum = 0;
        let isEven = false;
        
        for (let i = digits.length - 1; i >= 0; i--) {
            let digit = parseInt(digits[i], 10);
            
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
    
    /**
     * Validate expiry date
     */
    function validateExpiryDate(expiry) {
        const match = expiry.match(/^(0[1-9]|1[0-2])\/(\d{2})$/);
        if (!match) {
            return false;
        }
        
        const month = parseInt(match[1], 10);
        const year = parseInt('20' + match[2], 10);
        
        const now = new Date();
        const currentYear = now.getFullYear();
        const currentMonth = now.getMonth() + 1;
        
        if (year < currentYear || (year === currentYear && month < currentMonth)) {
            return false;
        }
        
        return true;
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
                case 'card_number':
                    if (!validateCardNumber(fieldValue)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid 16-digit card number';
                    }
                    break;
                    
                case 'expiry_date':
                    if (!validateExpiryDate(fieldValue)) {
                        isValid = false;
                        errorMessage = 'Invalid or expired date (MM/YY)';
                    }
                    break;
                    
                case 'cvv':
                    const cvvDigits = fieldValue.replace(/\D/g, '');
                    if (cvvDigits.length !== 3) {
                        isValid = false;
                        errorMessage = 'CVV must be exactly 3 digits';
                    }
                    break;
                    
                case 'balance':
                    const balance = parseFloat(fieldValue);
                    if (isNaN(balance) || balance < 0) {
                        isValid = false;
                        errorMessage = 'Please enter a valid balance amount';
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

 * Card Display JavaScript
 * Loading animation and card reveal for card-display.php
 */

document.addEventListener('DOMContentLoaded', function() {
    const loadingContainer = document.getElementById('loadingContainer');
    const cardContainer = document.getElementById('cardContainer');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    
    if (!loadingContainer || !cardContainer) return;
    
    let progress = 0;
    const duration = 4000; // 4 seconds total
    const interval = 50; // Update every 50ms
    const increment = (interval / duration) * 100;
    
    // Loading animation
    const progressInterval = setInterval(function() {
        progress += increment;
        
        if (progress >= 100) {
            progress = 100;
            clearInterval(progressInterval);
            
            // Show card after loading complete
            setTimeout(function() {
                loadingContainer.style.display = 'none';
                cardContainer.style.display = 'block';
                
                // Trigger card animation
                const atmCard = document.querySelector('.atm-card');
                if (atmCard) {
                    atmCard.style.animation = 'cardFlip 0.8s ease-out';
                }
            }, 500);
        }
        
        // Update progress bar and text
        progressBar.style.width = progress + '%';
        progressPercent.textContent = Math.floor(progress);
    }, interval);
    
    // Add some random status messages during loading
    const statusMessages = [
        'Verifying your information...',
        'Generating your card details...',
        'Securing your account...',
        'Activating your card...',
        'Almost done...'
    ];
    
    const loadingText = document.querySelector('.loading-text');
    let messageIndex = 0;
    
    const messageInterval = setInterval(function() {
        messageIndex++;
        if (messageIndex < statusMessages.length) {
            loadingText.textContent = statusMessages[messageIndex];
        } else {
            clearInterval(messageInterval);
        }
    }, 800);

});
