<?php
/**
 * Admin Header - Navigation and Common Elements
 */

$currentPage = basename($_SERVER['PHP_SELF']);
$adminUser = getAdminUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Dashboard'; ?> | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="/admin/css/admin-styles.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>WorldTrust ATM</h2>
                <p>Admin Panel</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="/admin/index.php" class="nav-item <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="/admin/activations.php" class="nav-item <?php echo $currentPage === 'activations.php' ? 'active' : ''; ?>">
                    <span class="icon">📋</span>
                    <span>All Activations</span>
                </a>
                <a href="/admin/logout.php" class="nav-item">
                    <span class="icon">🚪</span>
                    <span>Logout</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="admin-info">
                    <div class="admin-avatar">
                        <?php echo strtoupper(substr($adminUser['username'] ?? 'A', 0, 1)); ?>
                    </div>
                    <div class="admin-details">
                        <div class="admin-name"><?php echo htmlspecialchars($adminUser['username'] ?? 'Admin'); ?></div>
                        <div class="admin-role">Administrator</div>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content Area -->
        <main class="main-content">
            <header class="top-header">
                <h1><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                <div class="header-actions">
                    <span class="user-greeting">
                        Welcome, <strong><?php echo htmlspecialchars($adminUser['username'] ?? 'Admin'); ?></strong>
                    </span>
                    <span class="session-indicator">
                        Session expires: <span id="sessionTimer"></span>
                    </span>
                </div>
            </header>
            
            <?php
            // Display flash messages
            $flash = get_flash();
            if ($flash):
            ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
            <?php endif; ?>
            
            <div class="content-wrapper">
