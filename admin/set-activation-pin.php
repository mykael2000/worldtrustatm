<?php
/**
 * World Trust ATM - Set Activation PIN
 * Admin page to set activation PINs for approved requests
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
$activation_pin = '';
$activation_link = '';

// Handle PIN setting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_pin'])) {
    $request_id = intval($_POST['request_id'] ?? 0);
    $pin_type = $_POST['pin_type'] ?? 'auto';
    
    if ($pin_type === 'custom') {
        $activation_pin = sanitize_input($_POST['custom_pin']);
        
        // Validate custom PIN
        if (!preg_match('/^\d{6}$/', $activation_pin)) {
            $error_message = 'PIN must be exactly 6 digits';
        }
    } else {
        // Auto-generate 6-digit PIN
        $activation_pin = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    if (empty($error_message) && $request_id > 0) {
        $pdo = get_db_connection();
        if ($pdo) {
            // Generate unique activation token
            $activation_token = bin2hex(random_bytes(32));
            
            // Update database with PIN and token
            $stmt = $pdo->prepare("
                UPDATE activation_requests 
                SET activation_pin = ?, 
                    activation_token = ?,
                    pin_sent_at = NOW(),
                    pin_sent_by = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([$activation_pin, $activation_token, $_SESSION['admin_username'], $request_id])) {
                $success_message = 'Activation PIN set successfully!';
                
                // Generate activation link
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $base_path = dirname(dirname($_SERVER['PHP_SELF']));
                $activation_link = $protocol . '://' . $host . $base_path . '/activate.php?token=' . $activation_token;
            } else {
                $error_message = 'Failed to set activation PIN';
            }
        } else {
            $error_message = 'Database connection error';
        }
    }
}

// Get pending activation requests (payment completed but not activated)
$pdo = get_db_connection();
$pending_requests = [];

if ($pdo) {
    $stmt = $pdo->prepare("
        SELECT * FROM activation_requests 
        WHERE payment_status = 'completed' 
        AND status != 'activated' 
        AND status != 'rejected'
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $pending_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Activation PIN - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
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
                    <p class="tagline">Set Activation PIN</p>
                </div>
                <div>
                    <a href="dashboard.php" class="btn" style="width: auto; padding: 10px 20px; margin-right: 10px; background: var(--text-light);">← Back to Dashboard</a>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
        </header>

        <div class="admin-container">
            <?php if ($success_message && !empty($activation_link)): ?>
                <div class="alert alert-success" style="background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <h3 style="color: #155724; margin-bottom: 15px;">✓ <?php echo $success_message; ?></h3>
                    
                    <div style="margin-top: 15px; padding: 15px; background: var(--light-blue); border-radius: 8px;">
                        <strong>Activation Link (Send this to user):</strong>
                        <div style="display: flex; gap: 10px; margin-top: 10px;">
                            <input type="text" 
                                   id="activationLink" 
                                   value="<?php echo htmlspecialchars($activation_link); ?>" 
                                   readonly 
                                   style="flex: 1; padding: 10px; border: 1px solid var(--border-color); border-radius: 5px; font-family: monospace; font-size: 12px;">
                            <button onclick="copyActivationLink()" class="btn btn-primary" style="width: auto; white-space: nowrap; padding: 10px 20px;">
                                📋 Copy Link
                            </button>
                        </div>
                        <p style="margin-top: 10px; font-size: 12px; color: var(--text-light);">
                            Send this link to the user via email along with their PIN: <strong><?php echo $activation_pin; ?></strong>
                        </p>
                    </div>
                </div>
            <?php elseif ($error_message): ?>
                <div class="alert alert-error" style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 8px; margin-bottom: 20px; color: #721c24;">
                    ❌ <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <h2 style="margin-bottom: 20px; color: var(--primary-blue);">Pending Activation Requests</h2>
                
                <?php if (empty($pending_requests)): ?>
                    <p style="text-align: center; padding: 40px; color: var(--text-light);">
                        No pending activation requests with completed payments
                    </p>
                <?php else: ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--light-blue); border-bottom: 2px solid var(--border-color);">
                                <th style="padding: 12px; text-align: left;">ID</th>
                                <th style="padding: 12px; text-align: left;">Name</th>
                                <th style="padding: 12px; text-align: left;">Email</th>
                                <th style="padding: 12px; text-align: left;">Card</th>
                                <th style="padding: 12px; text-align: left;">Payment Date</th>
                                <th style="padding: 12px; text-align: left;">Current PIN</th>
                                <th style="padding: 12px; text-align: left;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_requests as $request): ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 12px;"><?php echo str_pad($request['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($request['email']); ?></td>
                                    <td style="padding: 12px;">**** <?php echo substr($request['card_number'], -4); ?></td>
                                    <td style="padding: 12px;"><?php echo date('M j, Y', strtotime($request['updated_at'])); ?></td>
                                    <td style="padding: 12px;">
                                        <?php if (!empty($request['activation_pin'])): ?>
                                            <span style="color: var(--success-green); font-weight: bold;"><?php echo $request['activation_pin']; ?></span>
                                        <?php else: ?>
                                            <span style="color: var(--text-light);">Not Set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 12px;">
                                        <button onclick="showPinModal(<?php echo $request['id']; ?>, '<?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?>')" 
                                                class="btn btn-primary" 
                                                style="width: auto; padding: 8px 16px; font-size: 14px;">
                                            <?php echo !empty($request['activation_pin']) ? 'Reset PIN' : 'Set PIN'; ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- PIN Setting Modal -->
    <div id="pinModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%;">
            <h3 style="margin-bottom: 20px; color: var(--primary-blue);">Set Activation PIN</h3>
            <p style="margin-bottom: 20px; color: var(--text-light);" id="modalUserName"></p>
            
            <form method="POST" action="">
                <input type="hidden" name="request_id" id="modal_request_id">
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 10px;">
                        <input type="radio" name="pin_type" value="auto" checked onchange="toggleCustomPin()">
                        Auto-generate 6-digit PIN
                    </label>
                    <label style="display: block;">
                        <input type="radio" name="pin_type" value="custom" onchange="toggleCustomPin()">
                        Enter custom PIN
                    </label>
                </div>
                
                <div id="customPinInput" style="display: none; margin-bottom: 20px;">
                    <label for="custom_pin" style="display: block; margin-bottom: 8px; font-weight: 500;">Custom PIN (6 digits)</label>
                    <input type="text" 
                           id="custom_pin" 
                           name="custom_pin" 
                           maxlength="6" 
                           pattern="\d{6}"
                           placeholder="Enter 6-digit PIN"
                           style="width: 100%; padding: 10px; border: 2px solid var(--border-color); border-radius: 8px;">
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="set_pin" class="btn btn-primary" style="flex: 1;">
                        Set PIN
                    </button>
                    <button type="button" onclick="closePinModal()" class="btn" style="flex: 1; background: var(--text-light);">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showPinModal(requestId, userName) {
            document.getElementById('modal_request_id').value = requestId;
            document.getElementById('modalUserName').textContent = 'Setting PIN for: ' + userName;
            document.getElementById('pinModal').style.display = 'flex';
        }
        
        function closePinModal() {
            document.getElementById('pinModal').style.display = 'none';
        }
        
        function toggleCustomPin() {
            const customRadio = document.querySelector('input[name="pin_type"][value="custom"]');
            const customPinInput = document.getElementById('customPinInput');
            customPinInput.style.display = customRadio.checked ? 'block' : 'none';
        }
        
        function copyActivationLink() {
            const linkInput = document.getElementById('activationLink');
            linkInput.select();
            linkInput.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                document.execCommand('copy');
                alert('Activation link copied to clipboard!');
            } catch (err) {
                // Fallback for modern browsers
                navigator.clipboard.writeText(linkInput.value).then(function() {
                    alert('Activation link copied to clipboard!');
                }, function(err) {
                    alert('Failed to copy link. Please copy manually.');
                });
            }
        }
        
        // Close modal when clicking outside
        document.getElementById('pinModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePinModal();
            }
        });
    </script>
</body>
</html>
