<?php
/**
 * Admin Login Page
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    redirect('/admin/index.php');
}

// Generate CSRF token
$csrf_token = generate_csrf_token();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        set_flash('error', 'Invalid security token. Please try again.');
        redirect('/admin/login.php');
    }
    
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Username is required.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }
    
    if (empty($errors)) {
        // Check login attempts
        $attempts = checkLoginAttempts($username);
        
        if ($attempts >= MAX_LOGIN_ATTEMPTS) {
            $errors[] = 'Too many failed login attempts. Please try again in ' . LOGIN_LOCKOUT_MINUTES . ' minutes.';
        } else {
            try {
                $db = getDB();
                $sql = "SELECT * FROM admin_users WHERE username = ?";
                $admin = $db->fetchOne($sql, [$username]);
                
                if ($admin && verify_password($password, $admin['password'])) {
                    // Successful login
                    clearLoginAttempts($username);
                    
                    // Regenerate session ID for security
                    regenerateSession();
                    
                    // Set session variables
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_user'] = [
                        'id' => $admin['id'],
                        'username' => $admin['username'],
                        'email' => $admin['email']
                    ];
                    $_SESSION['last_activity'] = time();
                    
                    // Update last login
                    updateAdminLastLogin($admin['id']);
                    
                    // Log activity
                    logAdminActivity('Login', 'Admin logged in');
                    
                    // Remember me functionality
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        setcookie('remember_token', $token, time() + (86400 * 30), '/admin', '', true, true);
                        // In production, store hashed token in database
                    }
                    
                    set_flash('success', 'Welcome back, ' . htmlspecialchars($admin['username']) . '!');
                    redirect('/admin/index.php');
                } else {
                    // Failed login
                    recordLoginAttempt($username);
                    $remainingAttempts = MAX_LOGIN_ATTEMPTS - ($attempts + 1);
                    
                    if ($remainingAttempts > 0) {
                        $errors[] = 'Invalid username or password. ' . $remainingAttempts . ' attempts remaining.';
                    } else {
                        $errors[] = 'Invalid username or password. Account locked for ' . LOGIN_LOCKOUT_MINUTES . ' minutes.';
                    }
                }
            } catch (Exception $e) {
                log_error('Login error: ' . $e->getMessage());
                $errors[] = 'An error occurred during login. Please try again.';
            }
        }
    }
    
    if (!empty($errors)) {
        set_flash('error', implode('<br>', $errors));
    }
}

// Get flash message
$flash = get_flash();

// Helper functions for this page
function checkLoginAttempts($username) {
    try {
        $db = getDB();
        $ip = get_client_ip();
        
        // Clean up old attempts
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

function logAdminActivity($action, $details = null) {
    try {
        $db = getDB();
        $admin = $_SESSION['admin_user'] ?? null;
        if (!$admin) return false;
        
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="/admin/css/admin-styles.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>WorldTrust ATM</h1>
                <p>Admin Panel Login</p>
            </div>
            
            <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="/admin/login.php" class="login-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           autocomplete="username" required autofocus
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           autocomplete="current-password" required>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="1">
                        <span>Remember me for 30 days</span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Login to Admin Panel
                </button>
            </form>
            
            <div class="login-footer">
                <p>Default credentials: admin / Admin@123</p>
                <p style="font-size: 12px; color: #999;">Change password immediately after first login</p>
            </div>
        </div>
    </div>
</body>
</html>
