<?php
/**
 * World Trust ATM - Admin Login
 */

session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit();
}

require_once '../includes/config.php';
require_once '../includes/database.php';

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $admin = verify_admin($username, $password);
        if ($admin) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_id'] = $admin['id'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Admin Login</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1 class="logo"><?php echo APP_NAME; ?></h1>
            <p class="tagline">Admin Portal</p>
        </header>

        <div class="form-container">
            <h2 class="form-title">Admin Login</h2>
            <p class="form-subtitle">Access the activation request dashboard</p>
            
            <?php if ($error): ?>
                <div class="error-message show" style="display: block; margin-bottom: 20px; background: #f8d7da; padding: 15px; border-radius: 8px; border: 1px solid #dc3545;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <p style="margin-top: 20px; text-align: center; font-size: 12px; color: var(--text-light);">
                Default credentials: <strong>admin / admin123</strong>
            </p>
        </div>
    </div>
</body>
</html>
