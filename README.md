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
