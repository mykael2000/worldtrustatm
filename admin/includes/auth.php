<?php
/**
 * Admin Authentication Check
 * Include this file at the top of every admin page to ensure user is logged in
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/db.php';

// Check if user is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Get current admin user
function getAdminUser() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    return $_SESSION['admin_user'] ?? null;
}

// Require admin login
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        set_flash('error', 'Please login to access the admin panel.');
        redirect('/admin/login.php');
    }
    
    // Check session timeout
    if (!checkSessionTimeout()) {
        session_unset();
        session_destroy();
        set_flash('error', 'Session expired. Please login again.');
        redirect('/admin/login.php');
    }
}

// Log admin activity
function logAdminActivity($action, $details = null) {
    try {
        $admin = getAdminUser();
        if (!$admin) {
            return false;
        }
        
        $db = getDB();
        $sql = "INSERT INTO admin_activity_log (admin_id, action, details, ip_address) 
                VALUES (?, ?, ?, ?)";
        
        $db->execute($sql, [
            $admin['id'],
            $action,
            $details,
            get_client_ip()
        ]);
        
        return true;
    } catch (Exception $e) {
        log_error('Failed to log admin activity: ' . $e->getMessage());
        return false;
    }
}

// Update last login time
function updateAdminLastLogin($adminId) {
    try {
        $db = getDB();
        $sql = "UPDATE admin_users SET last_login = NOW() WHERE id = ?";
        $db->execute($sql, [$adminId]);
        return true;
    } catch (Exception $e) {
        log_error('Failed to update last login: ' . $e->getMessage());
        return false;
    }
}

// Check login attempts
function checkLoginAttempts($username) {
    try {
        $db = getDB();
        $ip = get_client_ip();
        
        // Clean up old attempts (older than lockout period)
        $cleanupSql = "DELETE FROM login_attempts 
                      WHERE attempted_at < DATE_SUB(NOW(), INTERVAL ? MINUTE)";
        $db->execute($cleanupSql, [LOGIN_LOCKOUT_MINUTES]);
        
        // Count recent attempts
        $sql = "SELECT COUNT(*) as attempts FROM login_attempts 
                WHERE username = ? AND ip_address = ? 
                AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)";
        
        $result = $db->fetchOne($sql, [$username, $ip, LOGIN_LOCKOUT_MINUTES]);
        
        return (int)($result['attempts'] ?? 0);
    } catch (Exception $e) {
        log_error('Failed to check login attempts: ' . $e->getMessage());
        return 0;
    }
}

// Record failed login attempt
function recordLoginAttempt($username) {
    try {
        $db = getDB();
        $sql = "INSERT INTO login_attempts (username, ip_address) VALUES (?, ?)";
        $db->execute($sql, [$username, get_client_ip()]);
        return true;
    } catch (Exception $e) {
        log_error('Failed to record login attempt: ' . $e->getMessage());
        return false;
    }
}

// Clear login attempts for user
function clearLoginAttempts($username) {
    try {
        $db = getDB();
        $ip = get_client_ip();
        $sql = "DELETE FROM login_attempts WHERE username = ? AND ip_address = ?";
        $db->execute($sql, [$username, $ip]);
        return true;
    } catch (Exception $e) {
        log_error('Failed to clear login attempts: ' . $e->getMessage());
        return false;
    }
}

// Require admin authentication for protected pages
requireAdminLogin();
