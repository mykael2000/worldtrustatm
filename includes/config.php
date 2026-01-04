<?php
/**

 * Application Configuration
 * Global configuration settings

 * World Trust ATM - Configuration File
 * This file contains configuration settings for the application

 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {

    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Load environment configuration
require_once __DIR__ . '/db-config.php';

// Application settings
define('APP_NAME', 'WorldTrust ATM Card Activation');
define('APP_VERSION', '1.0.0');
define('APP_ENV', EnvLoader::get('APP_ENV', 'development'));
define('APP_DEBUG', EnvLoader::get('APP_DEBUG', 'false') === 'true');

// Security settings
define('SESSION_TIMEOUT', (int)EnvLoader::get('SESSION_TIMEOUT', 1800)); // 30 minutes
define('MAX_LOGIN_ATTEMPTS', (int)EnvLoader::get('MAX_LOGIN_ATTEMPTS', 5));
define('LOGIN_LOCKOUT_MINUTES', (int)EnvLoader::get('LOGIN_LOCKOUT_MINUTES', 30));
define('CSRF_TOKEN_EXPIRY', (int)EnvLoader::get('CSRF_TOKEN_EXPIRY', 3600));

// Rate limiting
define('RATE_LIMIT_SUBMISSIONS', (int)EnvLoader::get('RATE_LIMIT_SUBMISSIONS', 10));
define('RATE_LIMIT_WINDOW', (int)EnvLoader::get('RATE_LIMIT_WINDOW', 3600));

// Encryption key
define('ENCRYPTION_KEY', EnvLoader::get('ENCRYPTION_KEY', 'default-key-change-this-immediately'));
define('ENCRYPTION_METHOD', 'AES-256-CBC');

// Timezone
date_default_timezone_set('UTC');

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}

// Security headers
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Content Security Policy (basic)
$csp = "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;";
header("Content-Security-Policy: $csp");

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Regenerate session ID for security
 */
function regenerateSession() {
    session_regenerate_id(true);

    session_start();
}

// Application settings

define('APP_TAGLINE', 'Your Trust, Our Priority');


// Card settings
define('CARD_PREFIX', '4532'); // Visa-like prefix
define('DEFAULT_BALANCE', 5000.00);

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
