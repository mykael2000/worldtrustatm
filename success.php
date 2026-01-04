<?php
/**
 * ATM Card Activation - Success Page
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if activation was successful
if (!isset($_SESSION['activation_success'])) {
    redirect('index.php');
}

$success = $_SESSION['activation_success'];
unset($_SESSION['activation_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activation Successful | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ATM Card Activation</h1>
            <p>Your card has been successfully activated</p>
        </div>
        
        <div class="success-message">
            <div style="font-size: 64px; color: white; margin-bottom: 20px;">✓</div>
            <h2 style="margin-bottom: 10px; font-size: 24px;">Activation Complete!</h2>
            <p style="font-size: 16px;">Your ATM card is now ready to use.</p>
        </div>
        
        <div style="background: #f8f9fa; padding: 30px; border-radius: 8px; margin: 30px 0;">
            <h3 style="color: var(--primary-color); margin-bottom: 20px; text-align: center;">
                Activation Details
            </h3>
            <div style="display: grid; gap: 15px; font-size: 15px;">
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="color: var(--text-muted);">Confirmation ID:</span>
                    <span style="font-weight: 600;">#<?php echo str_pad($success['id'], 8, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="color: var(--text-muted);">Cardholder Name:</span>
                    <span style="font-weight: 600;"><?php echo htmlspecialchars($success['name']); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="color: var(--text-muted);">Card Number:</span>
                    <span style="font-weight: 600;">**** **** **** <?php echo htmlspecialchars($success['card_last4']); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="color: var(--text-muted);">Email:</span>
                    <span style="font-weight: 600;"><?php echo htmlspecialchars($success['email']); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                    <span style="color: var(--text-muted);">Activation Date:</span>
                    <span style="font-weight: 600;"><?php echo date('F d, Y \a\t g:i A'); ?></span>
                </div>
            </div>
        </div>
        
        <div style="background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h4 style="color: #0c5460; margin-bottom: 10px; font-size: 16px;">
                📧 Confirmation Email Sent
            </h4>
            <p style="color: #0c5460; font-size: 14px; margin: 0;">
                A confirmation email has been sent to <strong><?php echo htmlspecialchars($success['email']); ?></strong>. 
                Please check your inbox for activation details and next steps.
            </p>
        </div>
        
        <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h4 style="color: #856404; margin-bottom: 15px; font-size: 16px;">
                ⚠️ Important Security Tips
            </h4>
            <ul style="color: #856404; font-size: 14px; margin-left: 20px; line-height: 1.8;">
                <li>Keep your PIN confidential and never share it with anyone</li>
                <li>Memorize your PIN - do not write it down</li>
                <li>Sign the back of your card immediately</li>
                <li>Report lost or stolen cards immediately</li>
                <li>Monitor your account regularly for unauthorized transactions</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn btn-primary">
                Activate Another Card
            </a>
        </div>
        
        <div style="text-align: center; margin-top: 20px; color: var(--text-muted); font-size: 13px;">
            <p>Thank you for choosing WorldTrust Banking.</p>
            <p>For assistance, please contact our customer service at 1-800-WORLDTRUST</p>
        </div>
    </div>
</body>
</html>
