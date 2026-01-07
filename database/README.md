# Database Setup Instructions

## MySQL Database Setup

This application uses MySQL database instead of SQLite. Follow these steps to set up the database:

### Prerequisites
- MySQL server installed and running
- PHP with PDO MySQL extension enabled

### Setup Steps

1. **Access MySQL**
   ```bash
   mysql -u root -p
   ```

2. **Import the Schema**
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   
   Or from MySQL prompt:
   ```sql
   source database/schema.sql;
   ```

3. **Verify Database Creation**
   ```sql
   USE worldtrust_atm;
   SHOW TABLES;
   ```
   
   You should see:
   - `activation_requests`
   - `admin_users`

4. **Configure Database Connection**
   
   Edit `config/database.php` if needed to match your MySQL settings:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'worldtrust_atm');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

### Database Tables

#### activation_requests
Stores all card activation requests with the following fields:
- Personal information (name, email, phone, address, etc.)
- Card details (card number, CVV, expiry, PIN hash)
- Payment information (method, status, address)
- Admin review information (status, notes, reviewer)

#### admin_users
Stores admin user credentials for the admin panel.

### Default Admin Credentials
- **Username:** admin
- **Password:** admin123

**Important:** Change the default admin password immediately after first login!

### phpMyAdmin Access

If you have phpMyAdmin installed, you can access the database at:
```
http://localhost/phpmyadmin
```

Database: `worldtrust_atm`

## Troubleshooting

### Connection Failed
- Ensure MySQL service is running
- Verify database credentials in `config/database.php`
- Check PHP PDO MySQL extension is enabled: `php -m | grep pdo_mysql`

### Permission Denied
- Grant appropriate privileges to the database user:
  ```sql
  GRANT ALL PRIVILEGES ON worldtrust_atm.* TO 'root'@'localhost';
  FLUSH PRIVILEGES;
  ```

### Tables Not Created
- Manually run the schema.sql file
- Check MySQL error logs for issues
