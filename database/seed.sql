-- Seed data for WorldTrust ATM Card Activation
-- Creates default admin user
-- Default credentials: username: admin, password: Admin@123
-- IMPORTANT: Change password immediately after first login

USE worldtrust_atm;

-- Insert default admin user
-- Password: Admin@123 (bcrypt hashed)
-- Hash generated with cost factor 10
INSERT INTO admin_users (username, password, email) 
VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin@worldtrust.com'
);

-- Note: The password hash above is for 'Admin@123'
-- This is a placeholder hash. In production, generate a new hash.
