<?php
/**

 * Utility Functions
 * Encryption, validation, sanitization, and helper functions
 */

require_once __DIR__ . '/config.php';

/**
 * Encrypt sensitive data using AES-256-CBC
 * @param string $data Data to encrypt
 * @return string Encrypted data (base64 encoded)
 */
function encrypt_data($data) {
    if (empty($data)) {
        return '';
    }
    
    $key = ENCRYPTION_KEY;
    $method = ENCRYPTION_METHOD;
    
    // Generate a random IV
    $ivLength = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($ivLength);
    
    // Encrypt the data
    $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);
    
    // Combine IV and encrypted data
    $result = base64_encode($iv . $encrypted);
    
    return $result;
}

/**
 * Decrypt encrypted data
 * @param string $encryptedData Encrypted data (base64 encoded)
 * @return string Decrypted data
 */
function decrypt_data($encryptedData) {
    if (empty($encryptedData)) {
        return '';
    }
    
    $key = ENCRYPTION_KEY;
    $method = ENCRYPTION_METHOD;
    
    // Decode the base64
    $data = base64_decode($encryptedData);
    
    // Extract IV and encrypted data
    $ivLength = openssl_cipher_iv_length($method);
    $iv = substr($data, 0, $ivLength);
    $encrypted = substr($data, $ivLength);
    
    // Decrypt the data
    $decrypted = openssl_decrypt($encrypted, $method, $key, 0, $iv);
    
    return $decrypted;
}

/**
 * Hash password using bcrypt
 * @param string $password Password to hash
 * @return string Hashed password
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify password against hash
 * @param string $password Password to verify
 * @param string $hash Hash to verify against
 * @return bool True if password matches
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_EXPIRY) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if valid
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    if ((time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_EXPIRY) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize input data
 * @param string $data Data to sanitize
 * @return string Sanitized data

 * World Trust ATM - Helper Functions
 */

require_once 'config.php';

/**
 * Sanitize input data

 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);

    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

    $data = htmlspecialchars($data);

    return $data;
}

/**
 * Validate email address

 * @param string $email Email to validate
 * @return bool True if valid
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (basic)
 * @param string $phone Phone to validate
 * @return bool True if valid
 */
function validate_phone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10 && strlen($phone) <= 15;
}

/**
 * Validate date (YYYY-MM-DD format)
 * @param string $date Date to validate
 * @return bool True if valid
 */
function validate_date($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Validate card number (Luhn algorithm)
 * @param string $number Card number
 * @return bool True if valid
 */
function validate_card_number($number) {
    $number = preg_replace('/[^0-9]/', '', $number);
    
    if (strlen($number) !== 16) {
        return false;
    }
    
    // Luhn algorithm

 */
function validate_email($email) {
    return preg_match(EMAIL_PATTERN, $email);
}

/**
 * Validate phone number
 */
function validate_phone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return preg_match(PHONE_PATTERN, $phone);
}

/**
 * Validate account number
 */
function validate_account($account) {
    return preg_match(ACCOUNT_PATTERN, $account);
}

/**
 * Validate SSN (last 4 digits)
 */
function validate_ssn($ssn) {
    return preg_match(SSN_PATTERN, $ssn);
}

/**
 * Validate card number using Luhn algorithm
 */
function validate_card_number($number) {
    $number = preg_replace('/[^0-9]/', '', $number);
    if (strlen($number) != 16) {
        return false;
    }
    

    $sum = 0;
    $numDigits = strlen($number);
    $parity = $numDigits % 2;
    
    for ($i = 0; $i < $numDigits; $i++) {

        $digit = (int)$number[$i];
        if ($i % 2 == $parity) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }

        $digit = intval($number[$i]);
        if ($i % 2 == $parity) {
            $digit *= 2;
        }
        if ($digit > 9) {
            $digit -= 9;

        }
        $sum += $digit;
    }
    
    return ($sum % 10) == 0;
}

/**

 * Get client IP address
 * @return string IP address
 */
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

/**
 * Get user agent
 * @return string User agent
 */
function get_user_agent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}

/**
 * Check rate limiting
 * @param string $key Rate limit key (e.g., IP address)
 * @param int $limit Maximum number of requests
 * @param int $window Time window in seconds
 * @return bool True if rate limit exceeded
 */
function check_rate_limit($key, $limit = RATE_LIMIT_SUBMISSIONS, $window = RATE_LIMIT_WINDOW) {
    $sessionKey = 'rate_limit_' . md5($key);
    
    if (!isset($_SESSION[$sessionKey])) {
        $_SESSION[$sessionKey] = [
            'count' => 1,
            'start_time' => time()
        ];
        return false;
    }
    
    $elapsed = time() - $_SESSION[$sessionKey]['start_time'];
    
    if ($elapsed > $window) {
        // Reset window
        $_SESSION[$sessionKey] = [
            'count' => 1,
            'start_time' => time()
        ];
        return false;
    }
    
    $_SESSION[$sessionKey]['count']++;
    
    return $_SESSION[$sessionKey]['count'] > $limit;
}

/**
 * Format currency
 * @param float $amount Amount to format
 * @return string Formatted currency
 */
function format_currency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Format string
 * @return string Formatted date
 */
function format_date($date, $format = 'M d, Y g:i A') {
    return date($format, strtotime($date));
}

/**
 * Log error to file
 * @param string $message Error message
 * @param string $file Log file path
 */
function log_error($message, $file = null) {
    if ($file === null) {
        $file = __DIR__ . '/../logs/error.log';
    }
    
    $logDir = dirname($file);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    error_log($logMessage, 3, $file);
}

/**
 * Redirect to URL
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Set flash message
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message text
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return array|null Flash message or null
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Mask card number for display
 * @param string $cardNumber Full card number
 * @return string Masked card number (e.g., **** **** **** 1234)
 */
function mask_card_number($cardNumber) {
    $cleaned = preg_replace('/[^0-9]/', '', $cardNumber);
    if (strlen($cleaned) === 16) {
        return '**** **** **** ' . substr($cleaned, -4);
    }
    return '****';

 * Validate CVV
 */
function validate_cvv($cvv) {
    return preg_match('/^\d{3}$/', $cvv);
}

/**
 * Validate PIN
 */
function validate_pin($pin) {
    return preg_match('/^\d{4}$/', $pin);
}

/**
 * Generate random card number
 */
function generate_card_number() {
    $prefix = CARD_PREFIX;
    $random = '';
    for ($i = 0; $i < 12 - strlen($prefix); $i++) {
        $random .= rand(0, 9);
    }
    $partial = $prefix . $random;
    
    // Calculate Luhn check digit
    $sum = 0;
    $parity = strlen($partial) % 2;
    for ($i = 0; $i < strlen($partial); $i++) {
        $digit = intval($partial[$i]);
        if ($i % 2 == $parity) {
            $digit *= 2;
        }
        if ($digit > 9) {
            $digit -= 9;
        }
        $sum += $digit;
    }
    $checkDigit = (10 - ($sum % 10)) % 10;
    
    return $partial . $checkDigit;
}

/**
 * Format card number for display (masked)
 */
function format_card_number_masked($number) {
    return '**** **** **** ' . substr($number, -4);
}

/**
 * Format card number for display (full)
 */
function format_card_number_full($number) {
    return chunk_split($number, 4, ' ');
}

/**
 * Generate expiration date (2 years from now)
 */
function generate_expiration_date() {
    $month = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
    $year = date('y', strtotime('+2 years'));
    return $month . '/' . $year;
}

/**
 * Generate CVV
 */
function generate_cvv() {
    return str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
}

/**
 * Format currency
 */
function format_currency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Get user's full name
 */
function get_full_name() {
    if (isset($_SESSION['user_data'])) {
        return $_SESSION['user_data']['first_name'] . ' ' . $_SESSION['user_data']['last_name'];
    }
    return '';
}

/**
 * Check if user data exists in session
 */
function check_user_session() {
    if (!isset($_SESSION['user_data'])) {
        header('Location: index.php');
        exit();
    }
}

/**
 * Check if card data exists in session
 */
function check_card_session() {
    if (!isset($_SESSION['card_data'])) {
        header('Location: card-display.php');
        exit();
    }

}
