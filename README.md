# World Trust ATM - Card Activation Website

A professional, multi-page ATM card activation website built with PHP, HTML5, CSS3, and JavaScript. Features database integration, admin review panel, and a complete pending approval workflow.

## Features

### Page 1: Personal Information (index.php)
- Comprehensive form with all required fields (name, DOB, email, phone, address, SSN, etc.)
- Real-time client-side validation
- Server-side validation with PHP
- Professional banking UI design
- Session-based data persistence
- Creates initial activation request in database

### Page 2: Card Information & PIN Setup (pin-setup.php)
- User provides their card details (card number, CVV, expiry date)
- Card number validation using Luhn algorithm
- PIN setup with strength indicator (weak/medium/strong)
- PIN visibility toggle
- Updates activation request with card details and hashed PIN
- Stores card data in session for display

### Page 3: Card Display (card-display.php)
- Realistic ATM/debit card design with VISA branding
- Card chip visualization
- Displays card using user-provided details
- Shows masked card number for security
- Account balance display ($1,300,000.00)
- "Activate Card" button to proceed to payment

### Page 4: Payment (payment.php)
- Activation fee payment ($4,600)
- Cryptocurrency payment options (BTC/ETH/USDT)
- QR code and wallet address display
- Payment confirmation tracking
- Redirects to activation PIN verification

### Page 5: Activation PIN Verification (activation-verify.php) **NEW**
- Final verification step after payment
- 6-digit activation PIN input
- Payment confirmation display
- Updates activation status in database
- Redirects to success page

### Page 6: Activation Complete (pending.php)
- Success confirmation with reference ID
- Displays activation complete status
- Shows next steps for admin approval
- Professional completion experience

### Admin Panel (/admin) **NEW**
- **Login Page**: Secure authentication for administrators
- **Dashboard**: Statistics overview (total, pending, approved, rejected requests)
- **Request Management**: View, filter, and manage all activation requests
- **Detail View**: Complete information for each activation request including card details
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

4. Follow the 6-step activation process:
   - **Step 1**: Fill in your personal details (name, email, phone, address, etc.)
   - **Step 2**: Enter your card information and set up a 4-digit PIN
   - **Step 3**: View your card details displayed on a virtual card
   - **Step 4**: Select payment method and complete activation fee ($4,600)
   - **Step 5**: Enter 6-digit activation PIN to verify
   - **Step 6**: View activation complete confirmation (pending admin approval)

5. Access the admin panel:
```
http://localhost:8000/admin
Username: admin
Password: admin123
```

## File Structure

```
worldtrustatm/
├── index.php              # Page 1: Personal information form
├── pin-setup.php          # Page 2: Card details & PIN setup
├── card-display.php       # Page 3: Card display
├── payment.php            # Page 4: Activation fee payment
├── activation-verify.php  # Page 5: Activation PIN verification
├── pending.php            # Page 6: Activation complete
├── css/
│   └── styles.css        # All styles and animations
├── js/
│   ├── form-validation.js    # Form validation logic
│   ├── card-display.js       # Card animations
│   ├── pin-setup.js          # PIN setup & card validation
│   ├── payment.js            # Payment handling
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
├── database/               # Database schema
│   └── schema.sql          # MySQL schema file
└── config/
    └── database.php        # Database configuration
```

## Admin Panel Usage

1. **Login**: Navigate to `/admin` and use default credentials (admin/admin123)
2. **Dashboard**: View statistics showing total, pending, approved, and rejected requests
3. **Filter Requests**: Click filter buttons to view specific categories
4. **View Details**: Click "View" button to see complete user and card information
5. **Approve/Reject**: On detail page, approve or reject requests with optional notes
6. **Manage**: All approved/rejected requests are tracked with timestamps

## User Flow

1. **User submits personal information** → Validated and saved to database (partial record)
2. **User provides card details and sets PIN** → Card info and PIN added to database record
3. **System displays virtual card** → Shows card with user's details
4. **User selects payment method** → Payment info saved to database
5. **User confirms payment** → Redirected to activation PIN verification
6. **User enters 6-digit activation PIN** → Verified and status updated to 'activated'
7. **Activation complete page displayed** with reference ID
8. **Admin reviews request** in admin panel
9. **Admin approves or rejects** with optional notes
10. **Status updated in database** (ready for email notifications)

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
- Card information (card number, CVV, expiry, balance) - populated in step 2
- PIN (hashed) - populated in step 2
- Payment information (method, status, address) - populated in step 4
- Activation PIN (6 digits) - populated in step 5
- Status (pending/activated/approved/rejected)
- Timestamps (created_at, updated_at, activated_at, reviewed_at)
- Admin review information (reviewed_by, admin_notes)

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

### Card Validation
- Automatic card number generation removed - users provide their own card details
- Card number validation using Luhn algorithm
- CVV validation (3 digits)
- Expiry date validation (MM/YY format)
- PIN validation (4 digits)
- Activation PIN validation (6 digits)

### UI/UX
- Professional banking color scheme
- Smooth animations and transitions
- Mobile-first responsive design
- Loading animations with progress indicators
- Virtual card display with user's details
- Multi-step activation flow
- Payment gateway with cryptocurrency options
- Activation PIN verification step
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
