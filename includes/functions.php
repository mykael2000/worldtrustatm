<?php
/**
 * World Trust ATM - Helper Functions
 */

require_once 'config.php';

/**
 * Sanitize input data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email address
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

/**
 * Verify activation PIN
 * For demo purposes, this accepts any 6-digit PIN
 * In production, this should verify against admin-set PINs
 */
function verify_activation_pin($request_id, $activation_pin) {
    // For demo: accept any valid 6-digit PIN
    // In production, verify against database or admin system
    return preg_match('/^\d{6}$/', $activation_pin);
}

/**
 * Update activation status after PIN verification
 */
function update_activation_status_with_pin($request_id, $status, $activation_pin) {
    $db = get_db_connection();
    if (!$db) {
        return false;
    }
    
    try {
        $stmt = $db->prepare("
            UPDATE activation_requests 
            SET status = ?, 
                activation_pin = ?,
                activated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$status, $activation_pin, $request_id]);
    } catch (PDOException $e) {
        error_log('Failed to update activation status: ' . $e->getMessage());
        return false;
    }
}
