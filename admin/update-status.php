<?php
/**
 * Update Activation Status
 * Admin only
 */

require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/activations.php');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    set_flash('error', 'Invalid security token.');
    redirect('/admin/activations.php');
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? sanitize_input($_POST['status']) : '';

// Validate status
$validStatuses = ['pending', 'active', 'suspended'];
if (!in_array($status, $validStatuses)) {
    set_flash('error', 'Invalid status value.');
    redirect('/admin/view.php?id=' . $id);
}

try {
    $db = getDB();
    $sql = "UPDATE activations SET status = ?, updated_at = NOW() WHERE id = ?";
    $db->execute($sql, [$status, $id]);
    
    // Log activity
    logAdminActivity('Status Update', "Changed activation #$id status to $status");
    
    set_flash('success', 'Status updated successfully.');
} catch (Exception $e) {
    log_error('Status update error: ' . $e->getMessage());
    set_flash('error', 'Error updating status.');
}

redirect('/admin/view.php?id=' . $id);
