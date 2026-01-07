<?php
/**
 * World Trust ATM - Admin Settings
 * Manage system settings including activation PIN
 */

session_start();

// Check if logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';

$success_message = '';
$error_message = '';

// Handle PIN update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_pin'])) {
    $new_pin = sanitize_input($_POST['new_pin'] ?? '');
    $confirm_pin = sanitize_input($_POST['confirm_pin'] ?? '');
    
    // Validate new PIN
    if (empty($new_pin)) {
        $error_message = 'Please enter a new activation PIN.';
    } elseif (!validate_activation_pin($new_pin)) {
        $error_message = 'Activation PIN must be exactly 6 numeric digits.';
    } elseif (empty($confirm_pin)) {
        $error_message = 'Please confirm the new activation PIN.';
    } elseif ($new_pin !== $confirm_pin) {
        $error_message = 'PINs do not match. Please try again.';
    } else {
        // Update the PIN
        if (update_activation_pin($new_pin, $_SESSION['admin_username'])) {
            $success_message = 'Activation PIN updated successfully!';
        } else {
            $error_message = 'Failed to update activation PIN. Please try again.';
        }
    }
}

// Get current activation PIN with metadata
$pin_data = get_system_setting_with_metadata('activation_pin');
$current_pin = $pin_data ? $pin_data['setting_value'] : '123456';
$last_updated = $pin_data ? $pin_data['updated_at'] : 'Never';
$updated_by = $pin_data ? $pin_data['updated_by'] : 'system';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Admin Settings</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 class="logo"><?php echo APP_NAME; ?></h1>
                    <p class="tagline">Admin Panel - System Settings</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="dashboard.php" class="logout-btn" style="background: var(--secondary-blue);">← Dashboard</a>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
        </header>

        <div class="settings-container">
            <?php if (!empty($success_message)): ?>
                <div class="success-alert">
                    <strong>Success!</strong> <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-alert">
                    <strong>Error!</strong> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Current PIN Display -->
            <div class="setting-box">
                <h2 style="color: var(--primary-blue); margin-bottom: 20px;">Current Activation PIN</h2>
                
                <div class="pin-display-wrapper">
                    <div class="current-pin-display" id="currentPinDisplay" data-pin="<?php echo htmlspecialchars($current_pin); ?>">
                        ••••••
                    </div>
                    <button type="button" class="toggle-current-pin" onclick="toggleCurrentPin()">👁️</button>
                </div>
                
                <div class="last-updated">
                    Last Updated: <?php echo date('M j, Y g:i A', strtotime($last_updated)); ?>
                    <?php if ($updated_by): ?>
                        by <strong><?php echo htmlspecialchars($updated_by); ?></strong>
                    <?php endif; ?>
                </div>
                
                <div style="margin-top: 15px; padding: 15px; background: #fff3cd; border-radius: 8px; border: 1px solid #ffc107;">
                    <strong style="color: #856404;">⚠️ Important:</strong>
                    <p style="font-size: 13px; color: #856404; margin: 5px 0 0 0;">
                        This PIN is required by all users to activate their cards. Make sure to communicate 
                        any changes to your users through secure channels.
                    </p>
                </div>
            </div>

            <!-- Change PIN Form -->
            <div class="setting-box">
                <h2 style="color: var(--primary-blue); margin-bottom: 20px;">Change Activation PIN</h2>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="new_pin">New PIN (6 digits) <span class="required">*</span></label>
                        <div class="pin-input-wrapper">
                            <input type="password" id="new_pin" name="new_pin" 
                                   maxlength="6" placeholder="Enter 6-digit PIN" 
                                   pattern="\d{6}" required>
                            <button type="button" class="toggle-pin" onclick="togglePinField('new_pin')">👁️</button>
                        </div>
                        <small style="color: var(--text-light); font-size: 12px; display: block; margin-top: 5px;">
                            Must be exactly 6 numeric digits
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_pin">Confirm New PIN <span class="required">*</span></label>
                        <div class="pin-input-wrapper">
                            <input type="password" id="confirm_pin" name="confirm_pin" 
                                   maxlength="6" placeholder="Re-enter 6-digit PIN" 
                                   pattern="\d{6}" required>
                            <button type="button" class="toggle-pin" onclick="togglePinField('confirm_pin')">👁️</button>
                        </div>
                    </div>
                    
                    <button type="submit" name="update_pin" class="btn btn-primary">
                        Update Activation PIN
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle current PIN visibility
        function toggleCurrentPin() {
            const display = document.getElementById('currentPinDisplay');
            const toggleBtn = document.querySelector('.toggle-current-pin');
            const actualPin = display.getAttribute('data-pin');
            
            if (display.textContent === '••••••') {
                display.textContent = actualPin;
                toggleBtn.textContent = '🔒';
            } else {
                display.textContent = '••••••';
                toggleBtn.textContent = '👁️';
            }
        }

        // Toggle PIN field visibility
        function togglePinField(fieldId) {
            const field = document.getElementById(fieldId);
            const toggleBtn = field.nextElementSibling;
            
            if (field.type === 'password') {
                field.type = 'text';
                toggleBtn.textContent = '🔒';
            } else {
                field.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }

        // Validate PIN format on input
        document.getElementById('new_pin').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        document.getElementById('confirm_pin').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>
