<?php
/**
 * World Trust ATM - Configuration File
 * This file contains configuration settings for the application
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application settings
define('APP_NAME', 'World Trust Holding');
define('APP_TAGLINE', 'Your Trust, Our Priority');

// Security settings
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds

// Card settings
define('CARD_PREFIX', '4532'); // Visa-like prefix
define('DEFAULT_BALANCE', 1300000.00);

// Payment settings
define('ACTIVATION_FEE', 4600.00);

// Validation patterns
define('EMAIL_PATTERN', '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/');
define('PHONE_PATTERN', '/^\+?[1-9]\d{1,14}$/');
define('ACCOUNT_PATTERN', '/^\d{10,12}$/');
define('SSN_PATTERN', '/^\d{4}$/');

// Error messages
$error_messages = [
    'required' => 'This field is required',
    'invalid_email' => 'Please enter a valid email address',
    'invalid_phone' => 'Please enter a valid phone number',
    'invalid_account' => 'Account number must be 10-12 digits',
    'invalid_ssn' => 'Please enter the last 4 digits of your SSN',
    'invalid_card' => 'Please enter a valid 16-digit card number',
    'invalid_cvv' => 'Please enter a valid 3-digit CVV',
    'invalid_pin' => 'PIN must be exactly 4 digits',
    'pin_mismatch' => 'PINs do not match'
];

// Check session timeout
function check_session_timeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        header('Location: index.php?timeout=1');
        exit();
    }
    $_SESSION['last_activity'] = time();
}
