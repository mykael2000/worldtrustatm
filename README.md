
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

# World Trust ATM - Card Activation Website

A professional, multi-page ATM card activation website built with PHP, HTML5, CSS3, and JavaScript. Features database integration, admin review panel, and a complete pending approval workflow.

## Features

### Page 1: Basic Details Collection (index.php)
- Comprehensive form with all required fields (name, DOB, email, phone, address, SSN, etc.)
- Real-time client-side validation
- Server-side validation with PHP
- Professional banking UI design
- Session-based data persistence

### Page 2: Card Display (card-display.php)
- Animated loading screen with progress bar
- Realistic ATM/debit card design with VISA branding
- Card chip visualization
- Masked card number for security
- Account balance display
- Smooth card reveal animation

### Page 3: PIN Setup (pin-setup.php)
- Card details input with automatic formatting
- Luhn algorithm validation for card numbers
- PIN strength indicator (weak/medium/strong)
- PIN visibility toggle
- Saves all data to database
- Redirects to pending review page

### Page 4: Pending Review (pending.php) **NEW**
- 1-minute loading animation with progress messages
- Displays pending approval status
- Shows unique reference ID for tracking
- Provides information about next steps
- Professional waiting experience

### Admin Panel (/admin) **NEW**
- **Login Page**: Secure authentication for administrators
- **Dashboard**: Statistics overview (total, pending, approved, rejected requests)
- **Request Management**: View, filter, and manage all activation requests
- **Detail View**: Complete information for each activation request
- **Approve/Reject**: One-click actions with optional admin notes
- **Session Management**: Secure admin sessions with timeout

### Database Integration **NEW**
- **SQLite Database**: Lightweight, file-based storage (no MySQL setup needed)
- **Automatic Setup**: Database and tables created on first run
- **Secure Storage**: All activation requests stored with encrypted PINs
- **CRUD Operations**: Full create, read, update functionality
- **Statistics Tracking**: Real-time counts of requests by status

## Technology Stack

- **Backend**: PHP 8.3+
- **Database**: SQLite 3
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Session Management**: PHP Sessions
- **Security**: Input sanitization, password hashing, Luhn validation, SQL injection prevention

## Installation & Usage

### Prerequisites
- PHP 8.0 or higher (with SQLite extension)
- Web server (Apache/Nginx) or use PHP built-in server

### Quick Start

1. Clone the repository:
```bash
git clone https://github.com/mykael2000/worldtrustatm.git
cd worldtrustatm
```

2. Start the PHP development server:
```bash
php -S localhost:8000
```

3. Open your browser and navigate to:
```
http://localhost:8000/index.php
```

4. Follow the 4-step activation process:
   - Fill in your details on the first page
   - View your activated card on the second page
   - Set up your PIN on the third page
   - Wait for admin approval (pending review page)

5. Access the admin panel:
```
http://localhost:8000/admin
Username: admin
Password: admin123
```

## File Structure

```
worldtrustatm/
├── index.php              # Page 1: Basic details form
├── card-display.php       # Page 2: Card display & loading
├── pin-setup.php          # Page 3: PIN setup (saves to DB)
├── pending.php            # Page 4: Pending review page
├── css/
│   └── styles.css        # All styles and animations
├── js/
│   ├── form-validation.js    # Form validation logic
│   ├── card-display.js       # Loading & card animations
│   ├── pin-setup.js          # PIN setup & card validation
│   └── pending.js            # Pending page loader
├── includes/
│   ├── config.php           # Configuration & constants
│   ├── functions.php        # Helper functions
│   └── database.php         # Database operations
├── admin/                   # Admin panel
│   ├── index.php           # Admin login
│   ├── dashboard.php       # Admin dashboard
│   ├── view.php            # View request details
│   └── logout.php          # Admin logout
├── database/               # SQLite database (auto-created)
│   └── activations.db     # Database file
└── assets/
    └── images/            # (Ready for logos/images)
```

## Admin Panel Usage

1. **Login**: Navigate to `/admin` and use default credentials (admin/admin123)
2. **Dashboard**: View statistics showing total, pending, approved, and rejected requests
3. **Filter Requests**: Click filter buttons to view specific categories
4. **View Details**: Click "View" button to see complete user and card information
5. **Approve/Reject**: On detail page, approve or reject requests with optional notes
6. **Manage**: All approved/rejected requests are tracked with timestamps

## User Flow

1. User submits basic details → Validated and saved to session
2. System generates card details → Shows loading animation → Displays card
3. User enters card details and PIN → Validated and submitted
4. **Data saved to database** → 1-minute loading animation plays
5. **Pending review page displayed** with reference ID
6. Admin reviews request in admin panel
7. Admin approves or rejects with optional notes
8. Status updated in database (ready for email notifications)

## Security Features

- **Input Sanitization**: All inputs are sanitized to prevent XSS attacks
- **Server-side Validation**: Double validation (client + server)
- **Session Security**: 30-minute session timeout
- **Password Hashing**: PINs and admin passwords hashed using bcrypt
- **Luhn Algorithm**: Card number validation
- **SQL Injection Prevention**: Prepared statements for all database queries
- **Admin Authentication**: Secure login with password verification
- **Security Disclaimers**: Prominent warnings on all pages

## Database Schema

### activation_requests table
- User information (name, DOB, email, phone, address)
- Account details (account number, SSN last 4, maiden name)
- Card information (card number, CVV, expiry, balance)
- PIN (hashed)
- Status (pending/approved/rejected)
- Timestamps and admin review information

### admin_users table
- Admin credentials (username, password hash)
- Profile information
- Last login tracking

## Key Features

### Form Validation
- Real-time validation with visual feedback
- Email format validation
- Phone number validation
- Account number validation (10-12 digits)
- SSN validation (last 4 digits)
- Card number Luhn algorithm validation
- CVV validation (3 digits)
- PIN validation (4 digits)

### Card Generation
- Automatic card number generation with valid Luhn checksum
- Random CVV generation
- Expiration date (2 years from now)
- Card holder name from user input
- Unique card for each activation

### UI/UX
- Professional banking color scheme
- Smooth animations and transitions
- Mobile-first responsive design
- Loading animations with progress indicators
- Card flip animation
- 1-minute pending review loader
- Admin dashboard with statistics
- Print-friendly design

## Security Disclaimer

⚠️ **IMPORTANT**: This is a **demonstration prototype** for educational purposes only.

Real banking applications require:
- Backend validation and processing
- PCI DSS compliance
- HTTPS encryption (SSL/TLS)
- Secure database storage
- Two-factor authentication
- Additional security measures
- Regulatory compliance

**Never enter real financial information on demonstration sites.**

## Development

### Session Management
- Sessions automatically expire after 30 minutes of inactivity
- Data persists between pages using PHP sessions
- Session timeout redirects to the first page

### Validation
- Client-side: JavaScript validation for immediate feedback
- Server-side: PHP validation for security
- Pattern matching for all input types
- Luhn algorithm for card number validation

### Configuration
Modify `includes/config.php` to customize:
- Session timeout duration
- Default card balance
- Validation patterns
- Error messages

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## Contributing

This is a demonstration project. Feel free to fork and modify for educational purposes.

## License

This project is for demonstration and educational purposes only.

## Contact

For questions or issues, please open an issue on the GitHub repository.

---

**World Trust ATM** - *Your Trust, Our Priority*
