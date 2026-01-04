<?php
/**
 * Admin Dashboard - Overview and Statistics
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Dashboard';

// Get statistics
try {
    $db = getDB();
    
    // Total activations
    $totalActivations = $db->fetchOne("SELECT COUNT(*) as count FROM activations")['count'] ?? 0;
    
    // Today's activations
    $todayActivations = $db->fetchOne(
        "SELECT COUNT(*) as count FROM activations WHERE DATE(created_at) = CURDATE()"
    )['count'] ?? 0;
    
    // Active cards
    $activeCards = $db->fetchOne(
        "SELECT COUNT(*) as count FROM activations WHERE status = 'active'"
    )['count'] ?? 0;
    
    // Total balance
    $totalBalance = $db->fetchOne(
        "SELECT SUM(balance) as total FROM activations WHERE status = 'active'"
    )['total'] ?? 0;
    
    // Recent activations (last 10)
    $recentActivations = $db->fetchAll(
        "SELECT id, first_name, last_name, email, account_number, balance, status, created_at 
         FROM activations 
         ORDER BY created_at DESC 
         LIMIT 10"
    );
    
    // Status breakdown
    $statusStats = $db->fetchAll(
        "SELECT status, COUNT(*) as count FROM activations GROUP BY status"
    );
    
} catch (Exception $e) {
    log_error('Dashboard error: ' . $e->getMessage());
    $totalActivations = 0;
    $todayActivations = 0;
    $activeCards = 0;
    $totalBalance = 0;
    $recentActivations = [];
    $statusStats = [];
}

// Include header
include __DIR__ . '/includes/header.php';
?>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card stat-primary">
        <div class="stat-icon">📊</div>
        <div class="stat-details">
            <div class="stat-value"><?php echo number_format($totalActivations); ?></div>
            <div class="stat-label">Total Activations</div>
        </div>
    </div>
    
    <div class="stat-card stat-success">
        <div class="stat-icon">✓</div>
        <div class="stat-details">
            <div class="stat-value"><?php echo number_format($todayActivations); ?></div>
            <div class="stat-label">Today's Activations</div>
        </div>
    </div>
    
    <div class="stat-card stat-info">
        <div class="stat-icon">💳</div>
        <div class="stat-details">
            <div class="stat-value"><?php echo number_format($activeCards); ?></div>
            <div class="stat-label">Active Cards</div>
        </div>
    </div>
    
    <div class="stat-card stat-warning">
        <div class="stat-icon">💰</div>
        <div class="stat-details">
            <div class="stat-value"><?php echo format_currency($totalBalance); ?></div>
            <div class="stat-label">Total Balance</div>
        </div>
    </div>
</div>

<!-- Status Breakdown -->
<?php if (!empty($statusStats)): ?>
<div class="card mt-4">
    <div class="card-header">
        <h3>Status Breakdown</h3>
    </div>
    <div class="card-body">
        <div class="status-breakdown">
            <?php foreach ($statusStats as $stat): ?>
            <div class="status-item">
                <span class="status-badge status-<?php echo $stat['status']; ?>">
                    <?php echo ucfirst($stat['status']); ?>
                </span>
                <span class="status-count"><?php echo number_format($stat['count']); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Activations -->
<div class="card mt-4">
    <div class="card-header">
        <h3>Recent Activations</h3>
        <a href="/admin/activations.php" class="btn btn-sm btn-primary">View All</a>
    </div>
    <div class="card-body">
        <?php if (!empty($recentActivations)): ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Account Number</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentActivations as $activation): ?>
                    <tr>
                        <td>#<?php echo str_pad($activation['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo htmlspecialchars($activation['first_name'] . ' ' . $activation['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($activation['email']); ?></td>
                        <td><?php echo htmlspecialchars($activation['account_number']); ?></td>
                        <td><?php echo format_currency($activation['balance']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $activation['status']; ?>">
                                <?php echo ucfirst($activation['status']); ?>
                            </span>
                        </td>
                        <td><?php echo format_date($activation['created_at'], 'M d, Y g:i A'); ?></td>
                        <td>
                            <a href="/admin/view.php?id=<?php echo $activation['id']; ?>" class="btn btn-sm btn-info">
                                View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted text-center py-4">No activations yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
