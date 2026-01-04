<?php
/**
 * AJAX Endpoint for Decrypting Sensitive Data
 * Admin only
 */

require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$encrypted = $_POST['encrypted'] ?? '';

if (empty($encrypted)) {
    echo json_encode(['success' => false, 'error' => 'No data provided']);
    exit;
}

try {
    $decrypted = decrypt_data($encrypted);
    
    // Log the decryption activity
    logAdminActivity('Data Decryption', 'Admin decrypted sensitive data');
    
    echo json_encode([
        'success' => true,
        'decrypted' => $decrypted
    ]);
} catch (Exception $e) {
    log_error('Decryption error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Decryption failed'
    ]);
}
