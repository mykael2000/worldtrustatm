<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

$success_message = '';
$error_message = '';
$users = [];
$selected_user = null;

// Get all users who have paid but not activated
$pdo = get_db_connection();
if (!$pdo) {
    die('Database connection failed');
}

$stmt = $pdo->query("
    SELECT id, first_name, last_name, email, payment_method, payment_status, status, created_at 
    FROM activation_requests 
    WHERE (payment_status = 'pending' AND payment_method IS NOT NULL) 
       OR (payment_status = 'completed' AND status != 'activated')
    ORDER BY created_at DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle PIN generation/setting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_pin'])) {
    $user_id = intval($_POST['user_id']);
    $custom_pin = sanitize_input($_POST['custom_pin'] ?? '');
    $auto_generate = isset($_POST['auto_generate']);
    
    // Validate user exists
    $stmt = $pdo->prepare("SELECT * FROM activation_requests WHERE id = ?");
    $stmt->execute([$user_id]);
    $selected_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$selected_user) {
        $error_message = 'User not found';
    } else {
        // Generate or use custom PIN
        if ($auto_generate) {
            $activation_pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } else {
            // Validate custom PIN
            if (!preg_match('/^\d{6}$/', $custom_pin)) {
                $error_message = 'PIN must be exactly 6 digits';
            } else {
                $activation_pin = $custom_pin;
            }
        }
        
        if (empty($error_message)) {
            // Save PIN to database
            $stmt = $pdo->prepare("
                UPDATE activation_requests 
                SET activation_pin = ?, 
                    pin_sent_at = NOW(),
                    pin_sent_by = ?
                WHERE id = ?
            ");
            $admin_username = $_SESSION['admin_username'] ?? 'admin';
            $stmt->execute([$activation_pin, $admin_username, $user_id]);
            
            // NOTE: Email sending functionality not implemented yet
            // Admin must manually send the PIN to the user via email
            // TODO: Implement email sending functionality
            // send_activation_pin_email($selected_user['email'], $activation_pin, $selected_user['first_name']);
            
            $success_message = "Activation PIN ({$activation_pin}) has been set for {$selected_user['first_name']} {$selected_user['last_name']}. Please manually send this PIN to {$selected_user['email']}.";
        }
    }
}

// Get user details if viewing
if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $stmt = $pdo->prepare("SELECT * FROM activation_requests WHERE id = ?");
    $stmt->execute([$user_id]);
    $selected_user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Activation PIN - Admin Panel</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            background: #f5f5f5;
        }
        .admin-wrapper {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }
        .admin-header h1 {
            color: var(--primary-blue);
            font-size: 28px;
        }
        .btn-back {
            background: var(--text-light);
            color: var(--white);
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background: var(--primary-blue);
        }
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .card {
            background: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        .card h2 {
            color: var(--primary-blue);
            font-size: 22px;
            margin-bottom: 10px;
        }
        .subtitle {
            color: var(--text-light);
            font-size: 14px;
            margin-bottom: 25px;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        .admin-table th {
            background: var(--primary-blue);
            color: var(--white);
            padding: 12px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .admin-table td {
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
        }
        .admin-table tr:hover {
            background: var(--light-blue);
        }
        .user-details {
            background: var(--light-blue);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-row .label {
            color: var(--text-light);
            font-weight: 500;
        }
        .detail-row .value {
            color: var(--text-dark);
            font-weight: 600;
        }
        .pin-form {
            margin-top: 25px;
        }
        .pin-form .form-group {
            margin-bottom: 20px;
        }
        .pin-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .pin-form input[type="radio"] {
            margin-right: 8px;
        }
        .pin-form input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
        }
        .pin-form input[type="text"]:disabled {
            background: #f5f5f5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <div class="admin-header">
            <h1>Set Activation PIN</h1>
            <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <!-- Users List -->
        <div class="card">
            <h2>Pending Activations</h2>
            <p class="subtitle">Users who have submitted applications but haven't received activation PIN</p>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Payment Status</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo str_pad($user['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="status-badge <?php echo $user['payment_status']; ?>">
                                <?php echo ucfirst($user['payment_status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $user['status']; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <a href="?user_id=<?php echo $user['id']; ?>" class="action-btn btn-view">Set PIN</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px;">No pending activations</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- PIN Setting Form (shows when user is selected) -->
        <?php if ($selected_user): ?>
        <div class="card">
            <h2>Set Activation PIN for <?php echo htmlspecialchars($selected_user['first_name'] . ' ' . $selected_user['last_name']); ?></h2>
            
            <div class="user-details">
                <div class="detail-row">
                    <span class="label">Email:</span>
                    <span class="value"><?php echo htmlspecialchars($selected_user['email']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Reference ID:</span>
                    <span class="value"><?php echo str_pad($selected_user['id'], 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Payment Method:</span>
                    <span class="value"><?php echo strtoupper($selected_user['payment_method'] ?? 'N/A'); ?></span>
                </div>
            </div>
            
            <form method="POST" action="" class="pin-form">
                <input type="hidden" name="user_id" value="<?php echo $selected_user['id']; ?>">
                
                <div class="form-group">
                    <label>
                        <input type="radio" name="pin_type" value="auto" checked onchange="togglePinInput()"> 
                        Auto-generate 6-digit PIN
                    </label>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="radio" name="pin_type" value="custom" onchange="togglePinInput()"> 
                        Set custom PIN
                    </label>
                    <input type="text" id="custom_pin" name="custom_pin" 
                           placeholder="Enter 6-digit PIN" maxlength="6" 
                           pattern="\d{6}" disabled style="margin-top: 10px;">
                </div>
                
                <button type="submit" name="set_pin" id="setPinBtn" class="btn btn-primary">
                    Generate & Send PIN
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function togglePinInput() {
            const customRadio = document.querySelector('input[value="custom"]');
            const autoRadio = document.querySelector('input[value="auto"]');
            const customInput = document.getElementById('custom_pin');
            const submitBtn = document.getElementById('setPinBtn');
            
            if (customRadio.checked) {
                customInput.disabled = false;
                customInput.required = true;
                submitBtn.textContent = 'Set Custom PIN & Send';
            } else {
                customInput.disabled = true;
                customInput.required = false;
                customInput.value = '';
                submitBtn.textContent = 'Generate & Send PIN';
            }
        }
        
        // Initialize the form on page load
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.pin-form');
            if (form) {
                // Create hidden input for auto-generate flag
                const autoInput = document.createElement('input');
                autoInput.type = 'hidden';
                autoInput.name = 'auto_generate';
                autoInput.id = 'auto_generate_input';
                form.appendChild(autoInput);
                
                // Set initial value based on selected radio
                const autoRadio = document.querySelector('input[value="auto"]');
                if (autoRadio && autoRadio.checked) {
                    autoInput.value = '1';
                }
                
                // Update hidden input when radio changes
                const radios = document.querySelectorAll('input[name="pin_type"]');
                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        const autoGenInput = document.getElementById('auto_generate_input');
                        if (autoGenInput) {
                            autoGenInput.value = this.value === 'auto' ? '1' : '';
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>
