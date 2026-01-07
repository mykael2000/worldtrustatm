-- Add activation PIN verification system
-- Migration: Add activation_pin_verified column and system_settings table

-- Update activation_requests table
ALTER TABLE activation_requests 
ADD COLUMN activation_pin_verified TINYINT(1) DEFAULT 0 AFTER pin_hash;

-- Create system_settings table
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by VARCHAR(100) DEFAULT NULL,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default activation PIN (default: 123456)
INSERT INTO system_settings (setting_key, setting_value, updated_by) 
VALUES ('activation_pin', '123456', 'system')
ON DUPLICATE KEY UPDATE setting_value = setting_value;
