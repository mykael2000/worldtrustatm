<?php
/**
 * World Trust ATM - Card Activation
 * Page 3: PIN Setup & Card Details
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check session
check_user_session();
check_card_session();
check_session_timeout();

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card_number = sanitize_input($_POST['card_number'] ?? '');
    $cvv = sanitize_input($_POST['cvv'] ?? '');
    $expiry = sanitize_input($_POST['expiry'] ?? '');
    $pin = sanitize_input($_POST['pin'] ?? '');
    $confirm_pin = sanitize_input($_POST['confirm_pin'] ?? '');
    
    // Validate card number
    if (empty($card_number)) {
        $errors['card_number'] = 'Card number is required';
    } elseif (!validate_card_number($card_number)) {
        $errors['card_number'] = 'Please enter a valid 16-digit card number';
    }
    
    // Validate CVV
    if (empty($cvv)) {
        $errors['cvv'] = 'CVV is required';
    } elseif (!validate_cvv($cvv)) {
        $errors['cvv'] = 'Please enter a valid 3-digit CVV';
    }
    
    // Validate expiry
    if (empty($expiry)) {
        $errors['expiry'] = 'Expiration date is required';
    }
    
    // Validate PIN
    if (empty($pin)) {
        $errors['pin'] = 'PIN is required';
    } elseif (!validate_pin($pin)) {
        $errors['pin'] = 'PIN must be exactly 4 digits';
    }
    
    // Validate confirm PIN
    if (empty($confirm_pin)) {
        $errors['confirm_pin'] = 'Please confirm your PIN';
    } elseif ($pin !== $confirm_pin) {
        $errors['confirm_pin'] = 'PINs do not match';
    }
    
    // If no errors, complete activation
    if (empty($errors)) {
        $_SESSION['pin_data'] = [
            'pin' => password_hash($pin, PASSWORD_DEFAULT),
            'activated_at' => date('Y-m-d H:i:s')
        ];
        $success = true;
    }
}

$user_name = get_full_name();
$card_data = $_SESSION['card_data'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - PIN Setup</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1 class="logo"><?php echo APP_NAME; ?></h1>
            <p class="tagline"><?php echo APP_TAGLINE; ?></p>
        </header>

        <!-- PIN Setup Container -->
        <div class="pin-setup-container">
            <h2 class="form-title">Complete Card Activation</h2>
            <p class="form-subtitle">Enter your card details and set up your secure PIN</p>
            
            <span class="security-badge">Secure Form</span>
            
            <form id="pinSetupForm" method="POST" action="" novalidate>
                <!-- Card Details Section -->
                <div class="card-details-section">
                    <h3 class="section-title">Card Details</h3>
                    
                    <div class="form-group">
                        <label for="card_number">Card Number <span class="required">*</span></label>
                        <input type="text" id="card_number" name="card_number" 
                               placeholder="1234 5678 9012 3456" 
                               maxlength="19"
                               required aria-required="true">
                        <?php if (isset($errors['card_number'])): ?>
                            <span class="error-message show"><?php echo $errors['card_number']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry">Expiration Date (MM/YY) <span class="required">*</span></label>
                            <input type="text" id="expiry" name="expiry" 
                                   placeholder="MM/YY" 
                                   maxlength="5"
                                   required aria-required="true">
                            <?php if (isset($errors['expiry'])): ?>
                                <span class="error-message show"><?php echo $errors['expiry']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="cvv">CVV <span class="required">*</span></label>
                            <input type="password" id="cvv" name="cvv" 
                                   placeholder="123" 
                                   maxlength="3"
                                   required aria-required="true">
                            <?php if (isset($errors['cvv'])): ?>
                                <span class="error-message show"><?php echo $errors['cvv']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- PIN Setup Section -->
                <div class="pin-section">
                    <h3 class="section-title">Set Your PIN</h3>
                    
                    <div class="form-group">
                        <label for="pin">Enter PIN (4 digits) <span class="required">*</span></label>
                        <div class="pin-input-wrapper">
                            <input type="password" id="pin" name="pin" 
                                   maxlength="4" 
                                   placeholder="****"
                                   required aria-required="true">
                            <button type="button" class="toggle-pin" data-target="pin" aria-label="Toggle PIN visibility">
                                👁️
                            </button>
                        </div>
                        <?php if (isset($errors['pin'])): ?>
                            <span class="error-message show"><?php echo $errors['pin']; ?></span>
                        <?php endif; ?>
                        <div class="pin-strength">
                            <div class="strength-label">PIN Strength: <span id="strengthText">-</span></div>
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_pin">Confirm PIN <span class="required">*</span></label>
                        <div class="pin-input-wrapper">
                            <input type="password" id="confirm_pin" name="confirm_pin" 
                                   maxlength="4" 
                                   placeholder="****"
                                   required aria-required="true">
                            <button type="button" class="toggle-pin" data-target="confirm_pin" aria-label="Toggle PIN visibility">
                                👁️
                            </button>
                        </div>
                        <?php if (isset($errors['confirm_pin'])): ?>
                            <span class="error-message show"><?php echo $errors['confirm_pin']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Complete Activation</button>
            </form>
        </div>
        
        <!-- Disclaimer -->
        <div class="disclaimer">
            <div class="disclaimer-title">Security Disclaimer</div>
            <p>This is a demonstration prototype. Real banking applications require backend validation, PCI DSS compliance, HTTPS encryption, secure database storage, and two-factor authentication. Never enter real financial information on demonstration sites.</p>
        </div>
    </div>
    
    <!-- Success Modal -->
    <div class="modal" id="successModal">
        <div class="modal-content">
            <div class="modal-icon">✓</div>
            <h2 class="modal-title">Activation Complete!</h2>
            <p class="modal-message">
                Your ATM card has been successfully activated. You can now use your card for transactions.
            </p>
            <div class="modal-details">
                <div class="modal-detail-item">
                    <span class="detail-label">Card Holder:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user_name); ?></span>
                </div>
                <div class="modal-detail-item">
                    <span class="detail-label">Card Number:</span>
                    <span class="detail-value"><?php echo format_card_number_masked($card_data['card_number']); ?></span>
                </div>
                <div class="modal-detail-item">
                    <span class="detail-label">Available Balance:</span>
                    <span class="detail-value"><?php echo format_currency($card_data['balance']); ?></span>
                </div>
                <div class="modal-detail-item">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">Active</span>
                </div>
            </div>
            <p style="font-size: 12px; color: var(--text-light); margin-top: 15px;">
                Please keep your PIN secure and never share it with anyone.
            </p>
        </div>
    </div>
    
    <script src="js/pin-setup.js"></script>
    
    <?php if ($success): ?>
    <script>
        // Show success modal on successful activation
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('successModal');
            modal.classList.add('show');
            
            // Allow printing
            setTimeout(function() {
                if (confirm('Would you like to print your activation confirmation?')) {
                    window.print();
                }
            }, 2000);
        });
    </script>
    <?php endif; ?>
</body>
</html>
