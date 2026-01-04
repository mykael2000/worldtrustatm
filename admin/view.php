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
