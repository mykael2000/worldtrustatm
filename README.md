# WorldTrust ATM Card Activation System

A professional, production-ready ATM card activation system with comprehensive admin panel, database integration, and robust security features.

## Features

### User-Facing Features
- **3-Step Activation Process**
  - Step 1: Personal & Account Information
  - Step 2: Card Details & Security
  - Step 3: PIN Setup & Completion
- Professional, responsive UI design
- Real-time form validation
- Progress indicators
- Loading states and toast notifications
- Success confirmation page

### Admin Panel Features
- **Secure Admin Authentication**
  - Login with username/password
  - Session management (30-minute timeout)
  - Login attempt limiting (max 5 attempts)
  - Remember me functionality
  
- **Dashboard Overview**
  - Total activations count
  - Today's activations
  - Active cards count
  - Total balance sum
  - Recent activations list
  - Status breakdown
  
- **Comprehensive Data Management**
  - View all activations with complete details
  - Search by name, email, or account number
  - Filter by status and date range
  - Sort by any column
  - Pagination (20 records per page)
  - Export to CSV
  - View individual records with full details
  - Update activation status

### Security Features
- **Data Encryption**
  - AES-256-CBC encryption for sensitive data (SSN, card numbers, CVV)
  - Secure key management via environment variables
  - Bcrypt password hashing
  
- **Security Measures**
  - CSRF protection on all forms
  - SQL injection protection (prepared statements)
  - XSS protection (input sanitization)
  - Rate limiting for form submissions
  - Session timeout
  - Secure headers (X-Frame-Options, CSP, etc.)
  - Admin activity logging
  - IP address and user agent tracking

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- OpenSSL extension enabled

### Quick Start

1. **Clone the repository**
   ```bash
   git clone https://github.com/mykael2000/worldtrustatm.git
   cd worldtrustatm
   ```

2. **Create database**
   ```bash
   mysql -u root -p < database/schema.sql
   mysql -u root -p worldtrust_atm < database/seed.sql
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   ```
   
   Edit `.env` and configure:
   - Database credentials
   - Encryption key (generate with: `openssl rand -hex 32`)
   - Other settings as needed

4. **Set permissions**
   ```bash
   chmod 755 /path/to/worldtrustatm
   chmod 644 .env
   ```

5. **Configure web server**
   
   For Apache, create a virtual host:
   ```apache
   <VirtualHost *:80>
       ServerName worldtrust.local
       DocumentRoot /path/to/worldtrustatm
       
       <Directory /path/to/worldtrustatm>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

6. **Access the application**
   - User activation: `http://worldtrust.local/`
   - Admin panel: `http://worldtrust.local/admin/`
   - Default admin credentials: `admin` / `Admin@123`

## Database Setup

### Create Database Manually
```sql
CREATE DATABASE worldtrust_atm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Import Schema
```bash
mysql -u root -p worldtrust_atm < database/schema.sql
```

### Create Admin User
```bash
mysql -u root -p worldtrust_atm < database/seed.sql
```

Or manually:
```sql
INSERT INTO admin_users (username, password, email) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@worldtrust.com');
```

## Configuration

### Environment Variables

Edit `.env` file:

```ini
# Database Configuration
DB_HOST=localhost
DB_NAME=worldtrust_atm
DB_USER=root
DB_PASSWORD=your_password

# Security
ENCRYPTION_KEY=your-32-character-encryption-key-here

# Application
APP_ENV=production
APP_DEBUG=false
SESSION_TIMEOUT=1800
```

### Generate Encryption Key
```bash
openssl rand -hex 32
```

## Security Best Practices

### For Production Deployment

1. **Change Default Credentials**
   - Change admin password immediately after first login
   - Use strong, unique passwords

2. **Secure Environment File**
   ```bash
   chmod 600 .env
   ```

3. **Enable HTTPS**
   - Install SSL certificate
   - Force HTTPS redirects

4. **Database Security**
   - Use strong database passwords
   - Restrict database user privileges
   - Enable MySQL encryption

5. **File Permissions**
   - Set proper file ownership
   - Restrict write permissions
   - Protect sensitive directories

6. **Regular Updates**
   - Keep PHP and MySQL updated
   - Monitor security advisories
   - Regular security audits

7. **Backup Strategy**
   - Regular database backups
   - Secure backup storage
   - Test restore procedures

## Admin Panel Usage

### Login
- Navigate to `/admin/`
- Use your admin credentials
- Enable "Remember Me" for 30-day sessions

### Dashboard
- View activation statistics
- Monitor recent activations
- Check status breakdown

### View All Activations
- Search and filter records
- Sort by any column
- Export data to CSV
- Click "View" to see full details

### Managing Records
- View complete activation details
- Change activation status
- Print records for archiving

## Troubleshooting

### Database Connection Errors
- Verify database credentials in `.env`
- Check if MySQL service is running
- Ensure database exists

### Encryption Errors
- Verify `ENCRYPTION_KEY` is set in `.env`
- Ensure OpenSSL extension is enabled
- Check key length (32 characters for hex)

### Session Issues
- Check PHP session configuration
- Verify session directory permissions
- Clear browser cookies

### Admin Login Failed
- Verify credentials
- Check login attempt limit
- Wait for lockout period to expire

## File Structure

```
/
├── index.php                   # Step 1: Personal Information
├── card-display.php           # Step 2: Card Details
├── pin-setup.php              # Step 3: PIN Setup
├── success.php                # Completion page
├── css/
│   └── styles.css            # Main stylesheet
├── js/
│   ├── form-validation.js    # Form validation
│   ├── card-display.js       # Card display logic
│   └── pin-setup.js          # PIN setup logic
├── includes/
│   ├── config.php            # Application config
│   ├── db-config.php         # Environment loader
│   ├── db.php                # Database connection
│   └── functions.php         # Utility functions
├── database/
│   ├── schema.sql            # Database schema
│   └── seed.sql              # Default data
├── admin/
│   ├── login.php             # Admin login
│   ├── index.php             # Dashboard
│   ├── activations.php       # All activations
│   ├── view.php              # Single record
│   ├── logout.php            # Logout
│   ├── includes/
│   │   ├── auth.php          # Authentication
│   │   ├── header.php        # Admin header
│   │   └── footer.php        # Admin footer
│   └── css/
│       └── admin-styles.css  # Admin stylesheet
├── .env.example              # Environment template
├── .gitignore               # Git ignore rules
├── README.md                # This file
└── INSTALL.md               # Installation guide
```

## Support

For issues and questions:
- Create an issue on GitHub
- Contact: admin@worldtrust.com

## License

Copyright © 2024 WorldTrust Banking. All rights reserved.

## Changelog

### Version 1.0.0 (2024-01-04)
- Initial release
- Complete 3-step activation process
- Admin panel with full CRUD operations
- Database integration
- Security features (encryption, CSRF, rate limiting)
- Search, filter, and export functionality
- Responsive design
