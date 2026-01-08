<?php
/**
 * World Trust ATM - Card Activation
 * Activation PIN Verification Page
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

// Check session and payment confirmation
check_user_session();
check_session_timeout();

if (!isset($_SESSION['payment_confirmed']) || $_SESSION['payment_confirmed'] !== true) {
    header('Location: payment.php');
    exit();
}

$request_id = $_SESSION['request_id'] ?? null;
$pin_error = '';

// Handle activation PIN verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_activation_pin'])) {
    $activation_pin = sanitize_input($_POST['activation_pin']);
    
    // Validate PIN format
    if (!preg_match('/^\d{6}$/', $activation_pin)) {
        $pin_error = 'Activation PIN must be exactly 6 digits';
    } else {
        // Verify against database
        if (verify_activation_pin($request_id, $activation_pin)) {
            // Update activation status in database
            update_activation_status_with_pin($request_id, 'activated', $activation_pin);
            
            // Set session flag
            $_SESSION['activation_complete'] = true;
            
            // Clear payment confirmed flag
            unset($_SESSION['payment_confirmed']);
            
            // Redirect to success/pending page
            header('Location: pending.php');
            exit();
        } else {
            $pin_error = 'Invalid activation PIN. Please contact support.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Final Activation</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1 class="logo"><?php echo APP_NAME; ?></h1>
            <p class="tagline"><?php echo APP_TAGLINE; ?></p>
        </header>
        
        <div class="activation-verify-container">
            <div class="success-icon">✓</div>
            <h2 class="form-title">Payment Received!</h2>
            <p class="form-subtitle">Your card activation is being processed</p>
            
            <div class="info-box">
                <div class="info-item">
                    <span class="info-label">Reference ID:</span>
                    <span class="info-value"><?php echo str_pad($request_id ?? '000', 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Status:</span>
                    <span class="info-value status-confirmed">Confirmed</span>
                </div>
            </div>
            
            <div class="alert-box" style="background: #fff3cd; border: 1px solid #ffc107; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h3 style="color: #856404; margin-bottom: 15px; font-size: 16px;">📧 Activation PIN Will Be Sent to Your Email</h3>
                <p style="color: #856404; margin-bottom: 10px;">
                    Once we receive blockchain confirmation, we will send you an email containing:
                </p>
                <ul style="margin: 10px 0; padding-left: 20px; color: #856404;">
                    <li><strong>6-digit Activation PIN</strong></li>
                    <li><strong>Unique activation link</strong></li>
                </ul>
                <p style="margin-top: 15px; color: #856404;">
                    Click the link in the email and enter your PIN to complete activation.
                    The email will be sent to: <strong><?php echo htmlspecialchars($_SESSION['user_data']['email'] ?? 'your email'); ?></strong>
                </p>
            </div>
            
            <div class="activation-pin-section">
                <h3 class="section-title">Enter Activation PIN</h3>
                <p class="instruction-text">
                    Please enter your 6-digit activation PIN to finalize your card activation.
                    Contact support if you haven't received your PIN.
                </p>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="activation_pin">Activation PIN <span class="required">*</span></label>
                        <div class="pin-input-wrapper">
                            <input type="password" id="activation_pin" name="activation_pin" 
                                   maxlength="6" placeholder="Enter 6-digit PIN" required>
                            <button type="button" class="toggle-pin" onclick="toggleActivationPin()">👁️</button>
                        </div>
                    </div>
                    <?php if ($pin_error): ?>
                        <div class="error-message show"><?php echo $pin_error; ?></div>
                    <?php endif; ?>
                    <button type="submit" name="verify_activation_pin" class="btn btn-primary">
                        Complete Activation
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Disclaimer -->
        <div class="disclaimer">
            <div class="disclaimer-title">Security Disclaimer</div>
            <p>This is a legitimate card activation service. We will never ask for your PIN, full card number via email/text, or request payment to activate your card. Always verify you're on the correct website URL (https://[yoursite.com]) before entering any information.  If you receive suspicious emails or calls claiming to be from us, do not provide any personal information and contact our security team immediately at [security phone/email]. Your data is protected with bank-level encryption and will never be sold to third parties. </p>
        </div>
    </div>
    
    <script>
        function toggleActivationPin() {
            const input = document.getElementById('activation_pin');
            const btn = input.nextElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = '🙈';
            } else {
                input.type = 'password';
                btn.textContent = '👁️';
            }
        }
    </script>
</body>
</html>
