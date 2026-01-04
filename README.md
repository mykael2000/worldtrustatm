# World Trust ATM - Card Activation Website

A professional, multi-page ATM card activation website built with PHP, HTML5, CSS3, and JavaScript.

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
- Confirmation modal on successful activation
- Print-friendly confirmation page

## Technology Stack

- **Backend**: PHP 8.3+
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Session Management**: PHP Sessions
- **Security**: Input sanitization, password hashing, Luhn validation

## Installation & Usage

### Prerequisites
- PHP 8.0 or higher
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

4. Follow the 3-step activation process:
   - Fill in your details on the first page
   - View your activated card on the second page
   - Set up your PIN on the third page

## File Structure

```
worldtrustatm/
├── index.php              # Page 1: Basic details form
├── card-display.php       # Page 2: Card display & loading
├── pin-setup.php          # Page 3: PIN setup & completion
├── css/
│   └── styles.css        # All styles and animations
├── js/
│   ├── form-validation.js    # Form validation logic
│   ├── card-display.js       # Loading & card animations
│   └── pin-setup.js          # PIN setup & card validation
├── includes/
│   ├── config.php           # Configuration & constants
│   └── functions.php        # Helper functions
└── assets/
    └── images/              # (Ready for logos/images)
```

## Security Features

- **Input Sanitization**: All inputs are sanitized to prevent XSS attacks
- **Server-side Validation**: Double validation (client + server)
- **Session Security**: 30-minute session timeout
- **Password Hashing**: PINs are hashed using bcrypt
- **Luhn Algorithm**: Card number validation
- **Security Disclaimers**: Prominent warnings on all pages

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
- Automatic card number generation
- Valid Luhn checksum
- Random CVV generation
- Expiration date (2 years from now)
- Card holder name from user input

### UI/UX
- Professional banking color scheme
- Smooth animations and transitions
- Mobile-first responsive design
- Loading animations with progress indicators
- Card flip animation
- Success modal with activation details
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
