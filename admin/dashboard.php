<?php
/**
 * World Trust ATM - Admin Dashboard
 */

session_start();

// Check if logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $request_id = intval($_POST['request_id'] ?? 0);
    $action = $_POST['action'];
    $notes = trim($_POST['notes'] ?? '');
    
    try {
        $db = getDB();
        $status = ($action === 'approve') ? 'active' : 'suspended';
        $sql = "UPDATE activations SET status = ? WHERE id = ?";
        $db->execute($sql, [$status, $request_id]);
    } catch (Exception $e) {
        log_error('Failed to update activation status: ' . $e->getMessage());
    }
    
    header('Location: dashboard.php');
    exit();
}

// Get statistics
try {
    $db = getDB();
    
    $total = $db->fetchOne("SELECT COUNT(*) as count FROM activations")['count'] ?? 0;
    $pending = $db->fetchOne("SELECT COUNT(*) as count FROM activations WHERE status = 'pending'")['count'] ?? 0;
    $approved = $db->fetchOne("SELECT COUNT(*) as count FROM activations WHERE status = 'active'")['count'] ?? 0;
    $rejected = $db->fetchOne("SELECT COUNT(*) as count FROM activations WHERE status = 'suspended'")['count'] ?? 0;
    
    $stats = [
        'total' => $total,
        'pending' => $pending,
        'approved' => $approved,
        'rejected' => $rejected
    ];
    
    // Get activation requests
    $filter = $_GET['filter'] ?? 'all';
    if ($filter === 'all') {
        $requests = $db->fetchAll("SELECT * FROM activations ORDER BY created_at DESC LIMIT 100");
    } else {
        $requests = $db->fetchAll("SELECT * FROM activations WHERE status = ? ORDER BY created_at DESC LIMIT 100", [$filter]);
    }
} catch (Exception $e) {
    log_error('Dashboard error: ' . $e->getMessage());
    $stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
    $requests = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            background: #f5f5f5;
        }
        .container {
            max-width: 1400px;
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
                    <p class="tagline">Admin Dashboard - <?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
                </div>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <div class="admin-container">
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total Requests</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color: #ff9800;"><?php echo $stats['pending']; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color: #28a745;"><?php echo $stats['approved']; ?></div>
                    <div class="stat-label">Approved</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color: #dc3545;"><?php echo $stats['rejected']; ?></div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>

            <!-- Filter -->
            <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <h3 style="margin-bottom: 15px;">Filter Requests</h3>
                <div style="display: flex; gap: 10px;">
                    <a href="?filter=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : ''; ?>" style="width: auto; padding: 10px 20px;">All</a>
                    <a href="?filter=pending" class="btn <?php echo $filter === 'pending' ? 'btn-primary' : ''; ?>" style="width: auto; padding: 10px 20px;">Pending</a>
                    <a href="?filter=approved" class="btn <?php echo $filter === 'approved' ? 'btn-primary' : ''; ?>" style="width: auto; padding: 10px 20px;">Approved</a>
                    <a href="?filter=rejected" class="btn <?php echo $filter === 'rejected' ? 'btn-primary' : ''; ?>" style="width: auto; padding: 10px 20px;">Rejected</a>
                </div>
            </div>

            <!-- Requests Table -->
            <div class="requests-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Account</th>
                            <th>Card Number</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($requests)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-light);">
                                    No requests found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><?php echo str_pad($request['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($request['email']); ?></td>
                                    <td><?php echo htmlspecialchars($request['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($request['account_number']); ?></td>
                                    <td>**** <?php echo substr($request['card_number'], -4); ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($request['created_at'])); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $request['status']; ?>">
                                            <?php echo ucfirst($request['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view.php?id=<?php echo $request['id']; ?>" class="action-btn btn-view">View</a>
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="action-btn btn-approve" onclick="return confirm('Approve this request?')">
                                                    Approve
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="action-btn btn-reject" onclick="return confirm('Reject this request?')">
                                                    Reject
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
