# Admin Panel Documentation
WorldTrust ATM Card Activation System

## Overview

The admin panel provides comprehensive management tools for viewing, searching, filtering, and exporting ATM card activation submissions. It features secure authentication, real-time statistics, and detailed record viewing.

## Accessing the Admin Panel

**URL:** `https://yourdomain.com/admin/`

**Default Credentials:**
- Username: `admin`
- Password: `Admin@123`

⚠️ **IMPORTANT:** Change the default password immediately after first login!

## Features

### 1. Dashboard

The dashboard provides an at-a-glance overview of activation statistics:

#### Statistics Cards
- **Total Activations**: Cumulative count of all card activations
- **Today's Activations**: Number of activations submitted today
- **Active Cards**: Count of currently active cards
- **Total Balance**: Sum of all account balances

#### Recent Activations
- View the last 10 activation submissions
- Quick access to view full details
- Status indicators for each record

#### Status Breakdown
- Visual representation of activations by status
- Pending, Active, and Suspended counts

### 2. All Activations

Comprehensive listing of all activation submissions with advanced features:

#### Display Columns
The table displays ALL submitted information:
- ID number
- Full name (first + last)
- Date of birth
- Email address
- Phone number
- Account number
- Complete address (street, city, state, ZIP)
- Last 4 digits of SSN (encrypted, show/hide button)
- Mother's maiden name
- Card number (encrypted, show/hide button)
- Expiry date
- CVV (encrypted, show/hide button)
- Account balance
- Status badge
- IP address
- Submission date and time
- Action buttons

#### Search Functionality
- **Global Search**: Search across name, email, and account number
- Real-time filtering as you type
- Case-insensitive matching

#### Filtering Options
- **Status Filter**: Filter by pending, active, or suspended
- **Date Range**: Filter by submission date (from/to)
- **Combined Filters**: Use multiple filters simultaneously

#### Sorting
- Click any column header to sort
- Toggle between ascending and descending order
- Maintains current search/filter settings

#### Pagination
- 20 records per page (default)
- Easy navigation between pages
- Shows current page and total pages

#### Export to CSV
- Export current filtered results to CSV
- Includes all data fields (decrypted)
- Filename includes timestamp: `activations_YYYY-MM-DD_HHMMSS.csv`

#### Sensitive Data Display
For security, sensitive fields have show/hide buttons:
- Click "Show" to decrypt and display
- Click "Hide" to conceal again
- Decryption actions are logged

### 3. View Single Record

Detailed view of individual activation with complete information:

#### Information Sections

**Personal Information**
- First name
- Last name
- Date of birth
- Email address (clickable to send email)
- Phone number (clickable to call)

**Account Information**
- Account number
- Current balance (highlighted)
- Complete address details

**Security Information**
- Last 4 digits of SSN (visible, marked as sensitive)
- Mother's maiden name

**Card Details**
- Full card number (visible, marked as sensitive)
- Expiry date
- CVV (visible, marked as sensitive)

**Submission Metadata**
- IP address of submission
- Exact submission date and time
- Last update timestamp
- User agent string

#### Actions
- **Change Status**: Update activation status (pending/active/suspended)
- **Print**: Print-friendly view for archiving
- **Back to List**: Return to all activations

### 4. Session Management

#### Security Features
- **Session Timeout**: 30 minutes of inactivity
- **Session Timer**: Live countdown in header
- **Auto Logout**: Automatic logout when session expires
- **Session Warning**: Alert 5 minutes before expiration

#### Remember Me
- Optional 30-day persistent login
- Secure cookie-based authentication
- Can be disabled on logout

### 5. Activity Logging

All admin actions are logged for security auditing:
- Login/logout events
- Record views
- Status changes
- Data decryption
- Failed login attempts

## User Guide

### Logging In

1. Navigate to `/admin/`
2. Enter your username and password
3. (Optional) Check "Remember Me" for persistent login
4. Click "Login to Admin Panel"

**Login Attempt Limits:**
- Maximum 5 failed attempts
- 30-minute lockout after limit exceeded
- Attempts tracked by IP address

### Searching for Records

1. Go to "All Activations"
2. Use the search box to enter:
   - Customer name
   - Email address
   - Account number
3. Click "Filter" or press Enter
4. Results update automatically

### Filtering Records

1. Go to "All Activations"
2. Use filter options:
   - **Status**: Select pending, active, or suspended
   - **Date From**: Select start date
   - **Date To**: Select end date
3. Click "Filter" to apply
4. Click "Reset" to clear all filters

### Viewing Sensitive Data

Encrypted fields (SSN, Card Number, CVV) require decryption:

1. Locate the field with "Show" button
2. Click "Show" to decrypt and display
3. Data appears in the same row
4. Click "Hide" to conceal again
5. Decryption is logged for security audit

### Exporting Data

1. Apply desired filters/search
2. Click "Export CSV" button
3. CSV file downloads automatically
4. File includes all filtered records with decrypted data

### Viewing Record Details

1. Find the record in "All Activations"
2. Click "View" button in Actions column
3. View complete details in organized sections
4. Use "Print" for physical copy
5. Change status if needed
6. Click "Back" to return to list

### Changing Record Status

**From All Activations:**
1. Click "View" on desired record
2. Scroll to "Actions" section
3. Select new status from dropdown
4. Click "Update Status"

**Status Options:**
- **Pending**: Initial state, awaiting approval
- **Active**: Card is activated and usable
- **Suspended**: Card access temporarily disabled

### Sorting Records

1. Go to "All Activations"
2. Click any column header to sort by that column
3. Click again to reverse sort order
4. Arrow indicates current sort direction

### Managing Your Account

**Change Password:**
1. Currently requires database update
2. Future versions will include profile settings

**Update Email:**
```sql
mysql -u root -p worldtrust_atm
UPDATE admin_users SET email = 'new-email@domain.com' WHERE username = 'admin';
```

### Logging Out

1. Click "Logout" in sidebar navigation
2. Session is destroyed
3. Remember Me cookie is cleared
4. Redirected to login page

## Best Practices

### Security
- Never share your admin credentials
- Change default password immediately
- Use strong, unique passwords
- Log out when not in use
- Monitor activity logs regularly
- Review failed login attempts

### Data Handling
- Only decrypt sensitive data when necessary
- Never share decrypted data via insecure channels
- Use export feature sparingly
- Secure exported CSV files
- Delete exports after use

### Record Management
- Update status promptly
- Investigate suspicious submissions
- Verify customer details before activation
- Document status changes
- Regular data cleanup

### Performance
- Use filters to limit large result sets
- Export filtered data rather than full dataset
- Close unused browser tabs
- Clear browser cache if issues occur

## Troubleshooting

### Cannot Login

**Check 1:** Verify credentials
- Ensure caps lock is off
- Check for typos
- Use default credentials if reset needed

**Check 2:** Account locked
- Wait 30 minutes after failed attempts
- Contact system administrator

**Check 3:** Session issues
- Clear browser cookies
- Try incognito/private mode
- Check browser compatibility

### Session Expired

**Cause:** 30 minutes of inactivity

**Solution:**
- Log in again
- Enable "Remember Me" for longer sessions
- Keep tab active

### Decryption Failed

**Possible Causes:**
- Incorrect encryption key in `.env`
- Database corruption
- OpenSSL extension disabled

**Solution:**
- Verify `ENCRYPTION_KEY` in `.env`
- Check PHP extensions
- Contact system administrator

### Export Not Working

**Check 1:** Browser pop-up blocker
- Disable for this site
- Allow downloads

**Check 2:** File permissions
- Verify temp directory is writable

**Check 3:** Large dataset
- Apply filters to reduce records
- Export in smaller batches

### Blank or Missing Data

**Cause:** Database connection issue

**Solution:**
- Check database service is running
- Verify `.env` credentials
- Check error logs

## Keyboard Shortcuts

- `Tab`: Navigate form fields
- `Enter`: Submit search/filter
- `Ctrl+P`: Print current page
- `Esc`: Close modals (future feature)

## Browser Compatibility

**Fully Supported:**
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

**Basic Support:**
- IE 11 (limited features)
- Older browser versions

## Support

For technical assistance:
- Check application error logs
- Review this documentation
- Contact system administrator
- Email: admin@worldtrust.com

## Future Features

Planned enhancements:
- Profile settings page
- Password change in UI
- Bulk status updates
- Advanced reporting
- Email notifications
- Two-factor authentication
- Dark mode toggle
- Custom date ranges
- More export formats (PDF, Excel)

---

**Need Help?**
Refer to the main [README.md](../README.md) for general information and [INSTALL.md](../INSTALL.md) for installation guidance.
