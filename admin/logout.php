<?php
/**
 * Admin Logout
 * Terminates admin session and redirects to login page
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Log activity before destroying session
if (isset($_SESSION['admin_user'])) {
    require_once __DIR__ . '/../includes/db.php';
    
    try {
        $db = getDB();
        $sql = "INSERT INTO admin_activity_log (admin_id, action, details, ip_address) 
                VALUES (?, ?, ?, ?)";
        
        $db->execute($sql, [
            $_SESSION['admin_user']['id'],
            'Logout',
            'Admin logged out',
            get_client_ip()
        ]);
    } catch (Exception $e) {
        // Silent fail - don't block logout
        log_error('Failed to log logout activity: ' . $e->getMessage());
    }
}

// Clear all session variables
$_SESSION = array();

// Delete session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Delete remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 42000, '/admin', '', true, true);
}

// Destroy the session
session_destroy();

// Redirect to login page
set_flash('success', 'You have been successfully logged out.');
redirect('/admin/login.php');

 * World Trust ATM - Admin Logout
 */

session_start();
session_destroy();
header('Location: index.php');
exit();

