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

// Fetch user data from database
$pdo = get_db_connection();
if (!$pdo) {
    die('Database connection failed');
}

$stmt = $pdo->prepare("SELECT * FROM activation_requests WHERE id = ?");
$stmt->execute([$request_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    header('Location: index.php');
    exit();
}

$email = $user_data['email'];
$payment_method = $user_data['payment_method'] ?? 'crypto';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Payment Confirmation</title>
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
            <!-- Warning Icon (Yellow) -->
            <div class="warning-icon">⏳</div>
            
            <h2 class="form-title">Payment Confirmation Pending</h2>
            <p class="form-subtitle">Please wait for blockchain confirmation</p>
            
            <!-- Reference Information -->
            <div class="info-box" style="margin-bottom: 30px;">
                <div class="info-item">
                    <span class="info-label">Reference ID:</span>
                    <span class="info-value"><?php echo str_pad($request_id, 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email Address:</span>
                    <span class="info-value"><?php echo htmlspecialchars($email); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Method:</span>
                    <span class="info-value"><?php echo strtoupper($payment_method); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Status:</span>
                    <span class="info-value status-pending"><?php echo ucfirst($user_data['payment_status']); ?></span>
                </div>
            </div>
            
            <!-- Yellow Alert Box -->
            <div class="alert-box alert-warning" style="border: 2px solid #ffc107; background: #fff3cd;">
                <h3 style="margin-bottom: 15px; font-size: 18px; color: #856404;">
                    📧 Activation PIN Will Be Sent to Your Email
                </h3>
                <p style="color: #856404; margin-bottom: 12px; line-height: 1.8; font-size: 14px;">
                    Your payment is currently being verified on the blockchain. Once we receive confirmation 
                    (usually within 10-60 minutes depending on network congestion), we will send your 
                    <strong>6-digit Activation PIN</strong> to your registered email address:
                </p>
                <p style="color: #856404; font-weight: bold; font-size: 15px; margin-bottom: 12px;">
                    📧 <?php echo htmlspecialchars($email); ?>
                </p>
                <p style="color: #856404; font-size: 13px; line-height: 1.6;">
                    Please check your inbox (and spam folder) for an email from <strong><?php echo APP_NAME; ?></strong> 
                    with your activation PIN. You will need this PIN to complete your card activation.
                </p>
            </div>
            
            <!-- Next Steps -->
            <div class="next-steps-section">
                <h3 class="section-title">What Happens Next?</h3>
                <ol class="steps-list">
                    <li>
                        <strong>Blockchain Confirmation</strong><br>
                        <span class="step-description">We are waiting for your payment to be confirmed on the blockchain network.</span>
                    </li>
                    <li>
                        <strong>Activation PIN Sent</strong><br>
                        <span class="step-description">Once confirmed, a 6-digit PIN will be sent to your email within minutes.</span>
                    </li>
                    <li>
                        <strong>Complete Activation</strong><br>
                        <span class="step-description">Use the PIN to finalize your card activation and access your funds.</span>
                    </li>
                </ol>
            </div>
            
            <!-- Support Contact -->
            <div class="alert-box" style="margin-top: 30px; background: var(--light-blue); border: 1px solid var(--primary-blue);">
                <h4 style="color: var(--primary-blue); margin-bottom: 10px; font-size: 14px;">
                    Need Help?
                </h4>
                <p style="color: var(--text-dark); font-size: 13px; line-height: 1.6;">
                    If you don't receive your activation PIN within 2 hours, please contact our support team 
                    with your Reference ID: <strong><?php echo str_pad($request_id, 6, '0', STR_PAD_LEFT); ?></strong>
                </p>
            </div>
        </div>
        
        <!-- Disclaimer -->
        <div class="disclaimer">
            <div class="disclaimer-title">Security Disclaimer</div>
            <p>This is a legitimate card activation service. We will never ask for your PIN, full card number via email/text, or request payment to activate your card. Always verify you're on the correct website URL (https://[yoursite.com]) before entering any information.  If you receive suspicious emails or calls claiming to be from us, do not provide any personal information and contact our security team immediately at [security phone/email]. Your data is protected with bank-level encryption and will never be sold to third parties. </p>
        </div>
    </div>
</body>
</html>
