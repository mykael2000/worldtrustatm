# Installation Guide
WorldTrust ATM Card Activation System

## Table of Contents
1. [System Requirements](#system-requirements)
2. [Pre-Installation Checklist](#pre-installation-checklist)
3. [Step-by-Step Installation](#step-by-step-installation)
4. [Post-Installation Configuration](#post-installation-configuration)
5. [Testing](#testing)
6. [Troubleshooting](#troubleshooting)

## System Requirements

### Minimum Requirements
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher (or MariaDB 10.2+)
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 256 MB RAM minimum
- **Disk Space**: 50 MB minimum

### Required PHP Extensions
- PDO
- PDO_MySQL
- OpenSSL
- mbstring
- session
- json

### Verify PHP Extensions
```bash
php -m | grep -E "PDO|pdo_mysql|openssl|mbstring|session|json"
```

## Pre-Installation Checklist

- [ ] Web server installed and running
- [ ] MySQL/MariaDB installed and running
- [ ] PHP installed with required extensions
- [ ] Root or sudo access to the server
- [ ] Database credentials ready
- [ ] SSL certificate (recommended for production)

## Step-by-Step Installation

### Step 1: Download/Clone Repository

**Option A: Using Git**
```bash
cd /var/www/html
git clone https://github.com/mykael2000/worldtrustatm.git
cd worldtrustatm
```

**Option B: Manual Download**
```bash
cd /var/www/html
wget https://github.com/mykael2000/worldtrustatm/archive/main.zip
unzip main.zip
mv worldtrustatm-main worldtrustatm
cd worldtrustatm
```

### Step 2: Set File Permissions

```bash
# Set ownership (replace www-data with your web server user)
sudo chown -R www-data:www-data /var/www/html/worldtrustatm

# Set directory permissions
sudo find /var/www/html/worldtrustatm -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/html/worldtrustatm -type f -exec chmod 644 {} \;
```

### Step 3: Create Database

**Login to MySQL**
```bash
mysql -u root -p
```

**Create Database and User**
```sql
-- Create database
CREATE DATABASE worldtrust_atm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (replace 'password' with a strong password)
CREATE USER 'worldtrust_user'@'localhost' IDENTIFIED BY 'your_strong_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON worldtrust_atm.* TO 'worldtrust_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Exit
EXIT;
```

### Step 4: Import Database Schema

```bash
# Import schema
mysql -u worldtrust_user -p worldtrust_atm < database/schema.sql

# Import seed data (default admin user)
mysql -u worldtrust_user -p worldtrust_atm < database/seed.sql
```

**Verify Import**
```bash
mysql -u worldtrust_user -p worldtrust_atm -e "SHOW TABLES;"
```

Expected output:
```
+---------------------------+
| Tables_in_worldtrust_atm  |
+---------------------------+
| activations               |
| admin_activity_log        |
| admin_users               |
| login_attempts            |
+---------------------------+
```

### Step 5: Configure Environment

**Copy environment template**
```bash
cp .env.example .env
```

**Generate encryption key**
```bash
openssl rand -hex 32
```

**Edit .env file**
```bash
nano .env
```

**Configure the following:**
```ini
# Database Configuration
DB_HOST=localhost
DB_NAME=worldtrust_atm
DB_USER=worldtrust_user
DB_PASSWORD=your_strong_password

# Admin Configuration
ADMIN_EMAIL=admin@yourdomain.com

# Security Configuration (paste the generated key)
ENCRYPTION_KEY=your_generated_32_character_hex_key

# Application Settings
APP_ENV=production
APP_DEBUG=false
SESSION_TIMEOUT=1800

# Email Configuration (optional - for future use)
SMTP_HOST=smtp.yourdomain.com
SMTP_PORT=587
SMTP_USER=noreply@yourdomain.com
SMTP_PASSWORD=email_password
SMTP_FROM_EMAIL=noreply@yourdomain.com
SMTP_FROM_NAME=WorldTrust ATM

# Security Settings
MAX_LOGIN_ATTEMPTS=5
LOGIN_LOCKOUT_MINUTES=30
CSRF_TOKEN_EXPIRY=3600

# Rate Limiting
RATE_LIMIT_SUBMISSIONS=10
RATE_LIMIT_WINDOW=3600
```

**Secure the .env file**
```bash
chmod 600 .env
```

### Step 6: Configure Web Server

**For Apache:**

Create virtual host configuration:
```bash
sudo nano /etc/apache2/sites-available/worldtrust.conf
```

Add the following:
```apache
<VirtualHost *:80>
    ServerName worldtrust.yourdomain.com
    ServerAlias www.worldtrust.yourdomain.com
    DocumentRoot /var/www/html/worldtrustatm
    
    <Directory /var/www/html/worldtrustatm>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/worldtrust_error.log
    CustomLog ${APACHE_LOG_DIR}/worldtrust_access.log combined
</VirtualHost>
```

Enable site and rewrite module:
```bash
sudo a2ensite worldtrust.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**For Nginx:**

Create server block:
```bash
sudo nano /etc/nginx/sites-available/worldtrust
```

Add the following:
```nginx
server {
    listen 80;
    server_name worldtrust.yourdomain.com www.worldtrust.yourdomain.com;
    root /var/www/html/worldtrustatm;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    access_log /var/log/nginx/worldtrust_access.log;
    error_log /var/log/nginx/worldtrust_error.log;
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/worldtrust /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Step 7: SSL Certificate (Recommended)

**Using Let's Encrypt (Free)**
```bash
sudo apt install certbot python3-certbot-apache  # For Apache
# OR
sudo apt install certbot python3-certbot-nginx   # For Nginx

sudo certbot --apache -d worldtrust.yourdomain.com  # For Apache
# OR
sudo certbot --nginx -d worldtrust.yourdomain.com   # For Nginx
```

## Post-Installation Configuration

### 1. Create Logs Directory
```bash
mkdir -p /var/www/html/worldtrustatm/logs
chmod 755 /var/www/html/worldtrustatm/logs
chown www-data:www-data /var/www/html/worldtrustatm/logs
```

### 2. Verify Database Connection
```bash
php -r "require 'includes/db-config.php'; echo 'Config loaded successfully';"
```

### 3. Change Default Admin Password
1. Navigate to: `https://worldtrust.yourdomain.com/admin/`
2. Login with default credentials:
   - Username: `admin`
   - Password: `Admin@123`
3. Change password immediately after first login

### 4. Update Admin Email
```bash
mysql -u worldtrust_user -p worldtrust_atm
```

```sql
UPDATE admin_users SET email = 'your-email@yourdomain.com' WHERE username = 'admin';
EXIT;
```

## Testing

### Test 1: Homepage Access
```bash
curl -I http://worldtrust.yourdomain.com/
```
Expected: HTTP 200 OK

### Test 2: Admin Login Page
```bash
curl -I http://worldtrust.yourdomain.com/admin/
```
Expected: HTTP 200 OK

### Test 3: Database Connection
Navigate to homepage - should load without database errors

### Test 4: Complete Activation Flow
1. Navigate to homepage
2. Fill out Step 1 form
3. Continue to Step 2
4. Complete Step 3
5. Verify success page
6. Check admin panel for new record

### Test 5: Admin Functionality
1. Login to admin panel
2. Verify dashboard statistics
3. Navigate to "All Activations"
4. Test search and filter
5. View a single record
6. Test export to CSV

## Troubleshooting

### Issue: Database Connection Failed

**Check 1: Verify credentials**
```bash
mysql -u worldtrust_user -p worldtrust_atm
```

**Check 2: Verify .env file**
```bash
cat .env | grep DB_
```

**Check 3: PHP PDO extension**
```bash
php -m | grep pdo
```

### Issue: 500 Internal Server Error

**Check 1: Apache/Nginx error logs**
```bash
# Apache
sudo tail -f /var/log/apache2/error.log

# Nginx
sudo tail -f /var/log/nginx/error.log
```

**Check 2: PHP error logs**
```bash
sudo tail -f /var/log/php7.4-fpm.log
```

**Check 3: Application error logs**
```bash
tail -f logs/error.log
```

### Issue: Blank Page

**Enable PHP error display temporarily**
```bash
nano includes/config.php
```
Set:
```php
define('APP_DEBUG', true);
```

### Issue: Permission Denied

**Reset permissions**
```bash
sudo chown -R www-data:www-data /var/www/html/worldtrustatm
sudo chmod -R 755 /var/www/html/worldtrustatm
sudo chmod 600 /var/www/html/worldtrustatm/.env
```

### Issue: Session Errors

**Check session directory**
```bash
ls -la /var/lib/php/sessions/
```

**Fix permissions**
```bash
sudo chmod 1733 /var/lib/php/sessions/
```

### Issue: Encryption Errors

**Verify OpenSSL**
```bash
php -i | grep -i openssl
```

**Regenerate encryption key**
```bash
openssl rand -hex 32
```
Update in `.env`

## Security Checklist Post-Installation

- [ ] Changed default admin password
- [ ] Secured .env file (chmod 600)
- [ ] Enabled HTTPS/SSL
- [ ] Configured firewall rules
- [ ] Disabled directory listing
- [ ] Set strong database password
- [ ] Configured regular backups
- [ ] Reviewed error logs
- [ ] Tested all functionality
- [ ] Documented custom configuration

## Backup Recommendations

### Database Backup Script
Create `/usr/local/bin/backup-worldtrust.sh`:
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/worldtrust"
mkdir -p $BACKUP_DIR

mysqldump -u worldtrust_user -p'your_password' worldtrust_atm > $BACKUP_DIR/db_$DATE.sql
gzip $BACKUP_DIR/db_$DATE.sql

# Keep only last 30 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete
```

Make executable and schedule:
```bash
chmod +x /usr/local/bin/backup-worldtrust.sh
crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-worldtrust.sh
```

## Support

If you encounter issues not covered in this guide:
- Review application logs: `/var/www/html/worldtrustatm/logs/error.log`
- Check web server logs
- Create an issue on GitHub
- Contact: admin@worldtrust.com

## Next Steps

After successful installation:
1. Review security settings
2. Configure email notifications (if needed)
3. Customize branding/styling
4. Set up monitoring
5. Configure backups
6. Document your deployment

---

**Installation Complete!** 🎉

Your WorldTrust ATM Card Activation System is now ready to use.
