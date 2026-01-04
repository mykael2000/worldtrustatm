# Testing Guide
WorldTrust ATM Card Activation System

## Overview

This guide provides comprehensive testing procedures to validate all features of the ATM Card Activation System before production deployment.

## Testing Environment Setup

### Prerequisites
1. Local or staging server with PHP 7.4+ and MySQL 5.7+
2. Database created and schema imported
3. `.env` file configured with valid credentials
4. Web server configured (Apache/Nginx)

### Sample Test Data

#### Valid Test Activation Data

**Personal Information:**
- First Name: John
- Last Name: Doe
- DOB: 1990-01-15
- Email: john.doe@example.com
- Phone: (555) 123-4567

**Account Information:**
- Account Number: 123456789012
- Street: 123 Main Street
- City: New York
- State: NY
- ZIP: 10001

**Security:**
- SSN Last 4: 1234
- Mother's Maiden Name: Smith

**Card Details:**
- Card Number: 4532015112830366 (Valid test card)
- Expiry: 12/25
- CVV: 123
- Balance: 1000.00

**PIN:**
- PIN: 5678
- Confirm PIN: 5678

## Test Cases

### 1. Front-End Activation Flow

#### Test 1.1: Step 1 - Personal Information
**Objective:** Validate personal and account information form

**Steps:**
1. Navigate to homepage (index.php)
2. Verify progress indicator shows "Step 1 of 3"
3. Fill in all required fields with test data
4. Click "Continue to Card Details"

**Expected Results:**
- ✅ Form accepts valid data
- ✅ Required field validation works
- ✅ Email format validation works
- ✅ Phone number auto-formats: (555) 123-4567
- ✅ Account number accepts only 12 digits
- ✅ SSN last 4 accepts only 4 digits
- ✅ Data stored in session
- ✅ Redirects to Step 2
- ✅ No autocomplete warnings in browser console

**Test with Invalid Data:**
- Empty required fields → Error message displayed
- Invalid email → "Please enter a valid email address"
- Account number < 12 digits → Validation error
- SSN last 4 ≠ 4 digits → Validation error

#### Test 1.2: Step 2 - Card Details
**Objective:** Validate card information form and live preview

**Steps:**
1. Complete Step 1 to reach Step 2
2. Verify progress shows "Step 2 of 3" with Step 1 completed
3. Observe card preview with user's name
4. Enter card number
5. Enter expiry date
6. Enter CVV
7. Enter balance
8. Click "Continue to PIN Setup"

**Expected Results:**
- ✅ Card preview displays name from Step 1
- ✅ Card number auto-formats: 1234 5678 9012 3456
- ✅ Card preview updates as user types card number
- ✅ Expiry auto-formats: MM/YY
- ✅ Expiry preview updates in real-time
- ✅ CVV accepts only 3 digits
- ✅ Luhn algorithm validates card number
- ✅ Future expiry date validation
- ✅ Redirects to Step 3
- ✅ Autocomplete attributes correct (cc-number, cc-exp, cc-csc)

**Test with Invalid Data:**
- Invalid card number (fails Luhn) → Error
- Expired date → Validation error
- CVV ≠ 3 digits → Error
- Negative balance → Error

#### Test 1.3: Step 3 - PIN Setup
**Objective:** Validate PIN setup and complete activation

**Steps:**
1. Complete Steps 1 and 2
2. Verify progress shows "Step 3 of 3" with Steps 1-2 completed
3. Review activation summary
4. Enter 4-digit PIN
5. Confirm PIN
6. Click "Complete Activation"
7. Verify success page

**Expected Results:**
- ✅ Activation summary displays correct data
- ✅ Card number is masked
- ✅ PIN accepts only 4 digits
- ✅ PIN confirmation validation
- ✅ Weak PIN warning (1111, 1234, etc.) - but allows
- ✅ Confirmation prompt before submission
- ✅ Data saved to database with encryption
- ✅ Status set to 'active'
- ✅ Success page displays with confirmation ID
- ✅ Session activation data cleared
- ✅ Autocomplete set to "new-password"

**Test Database Entry:**
```sql
SELECT * FROM activations ORDER BY id DESC LIMIT 1;
```
Verify:
- All fields populated
- SSN, card number, CVV are encrypted (not readable)
- PIN is hashed (not readable)
- Status is 'active'
- IP address captured
- Timestamps set

#### Test 1.4: Success Page
**Objective:** Verify success confirmation

**Expected Results:**
- ✅ Displays confirmation ID
- ✅ Shows activation details
- ✅ Displays success message
- ✅ Shows security tips
- ✅ Link to activate another card

### 2. Security Testing

#### Test 2.1: CSRF Protection
**Steps:**
1. Submit form without CSRF token
2. Submit form with invalid CSRF token

**Expected Results:**
- ✅ "Invalid security token" error
- ✅ Form not processed

#### Test 2.2: Rate Limiting
**Steps:**
1. Submit activation form 11 times rapidly

**Expected Results:**
- ✅ First 10 submissions allowed
- ✅ 11th submission blocked
- ✅ "Too many requests" error
- ✅ Wait 60 minutes or reset session to continue

#### Test 2.3: Data Encryption
**Steps:**
1. Complete an activation
2. Check database directly

**Expected Results:**
- ✅ SSN, card number, CVV are encrypted (unreadable)
- ✅ PIN is hashed (unreadable)
- ✅ Other fields are readable

#### Test 2.4: Session Security
**Steps:**
1. Start activation, leave idle for 31 minutes
2. Try to submit form

**Expected Results:**
- ✅ Session expired
- ✅ Redirected or session cleared

### 3. Admin Panel Testing

#### Test 3.1: Admin Login
**Objective:** Validate authentication system

**Steps:**
1. Navigate to /admin/
2. Attempt login with default credentials: admin / Admin@123
3. Test failed login attempts
4. Test "Remember Me" functionality

**Expected Results:**
- ✅ Login page displays
- ✅ Valid credentials → Dashboard
- ✅ Invalid credentials → Error message
- ✅ Login attempts counted
- ✅ After 5 failed attempts → Account locked for 30 minutes
- ✅ Remaining attempts shown
- ✅ Remember Me sets secure cookie
- ✅ Session created with timeout

#### Test 3.2: Dashboard
**Objective:** Verify statistics and overview

**Steps:**
1. Login to admin panel
2. View dashboard

**Expected Results:**
- ✅ Total activations count correct
- ✅ Today's activations count correct
- ✅ Active cards count correct
- ✅ Total balance sum correct
- ✅ Recent activations table shows last 10
- ✅ Status breakdown displays
- ✅ Navigation menu visible
- ✅ Session timer countdown works

#### Test 3.3: All Activations
**Objective:** Test comprehensive data table with all features

**Steps:**
1. Navigate to "All Activations"
2. Verify all columns display
3. Test search
4. Test filters
5. Test sorting
6. Test pagination
7. Test show/hide sensitive data
8. Test export

**Expected Results:**
- ✅ ALL submitted data visible in table
- ✅ Columns: ID, Name, DOB, Email, Phone, Account, Address, SSN, Maiden Name, Card, Expiry, CVV, Balance, Status, IP, Date, Actions
- ✅ Search finds by name, email, account
- ✅ Status filter works
- ✅ Date range filter works
- ✅ Combined filters work
- ✅ Clicking column header sorts (toggles asc/desc)
- ✅ Pagination shows 20 per page
- ✅ "Show" button decrypts SSN, card number, CVV
- ✅ "Hide" button conceals data again
- ✅ Export CSV downloads with all data
- ✅ CSV includes decrypted values

**Test Export CSV:**
1. Click "Export CSV"
2. Open downloaded file
3. Verify all columns present
4. Verify sensitive data is decrypted in CSV

#### Test 3.4: View Single Record
**Objective:** Validate detailed record view

**Steps:**
1. Click "View" on any activation
2. Review all sections
3. Test status update

**Expected Results:**
- ✅ All personal information displayed
- ✅ All account information displayed
- ✅ All security information displayed (decrypted)
- ✅ All card details displayed (decrypted)
- ✅ Submission metadata displayed
- ✅ Sensitive fields marked with warning
- ✅ Status change dropdown works
- ✅ Update status button saves changes
- ✅ Print button creates print-friendly view
- ✅ Back button returns to list

#### Test 3.5: Session Management
**Objective:** Test timeout and security

**Steps:**
1. Login to admin panel
2. Observe session timer
3. Leave idle for 30 minutes
4. Try to navigate

**Expected Results:**
- ✅ Timer counts down
- ✅ After 30 minutes → Auto logout
- ✅ Redirected to login
- ✅ Message: "Session expired"

#### Test 3.6: Logout
**Steps:**
1. Click Logout in sidebar

**Expected Results:**
- ✅ Session destroyed
- ✅ Redirected to login page
- ✅ Success message displayed
- ✅ Cannot access admin pages without re-login

### 4. Cross-Browser Testing

Test in multiple browsers:

**Chrome/Edge (Chromium):**
- ✅ All forms work
- ✅ Card preview updates
- ✅ Admin panel displays correctly
- ✅ No console errors

**Firefox:**
- ✅ All forms work
- ✅ Card preview updates
- ✅ Admin panel displays correctly
- ✅ No console errors

**Safari:**
- ✅ All forms work
- ✅ Card preview updates
- ✅ Admin panel displays correctly
- ✅ No console errors

### 5. Mobile Responsiveness

Test on mobile devices or browser dev tools:

**iPhone/Android:**
- ✅ Forms are usable
- ✅ Buttons are touchable
- ✅ Text is readable
- ✅ Tables scroll horizontally
- ✅ Admin sidebar adapts

### 6. Accessibility Testing

**Keyboard Navigation:**
- ✅ Tab through all form fields
- ✅ Enter submits forms
- ✅ All interactive elements reachable

**Screen Reader:**
- ✅ Labels read correctly
- ✅ Error messages announced
- ✅ Form structure logical

### 7. Performance Testing

**Load Time:**
- ✅ Homepage loads < 2 seconds
- ✅ Admin dashboard loads < 2 seconds
- ✅ All activations page loads < 3 seconds (with 100 records)

**Database:**
- ✅ Queries execute < 100ms
- ✅ Export handles 1000+ records

### 8. Error Handling

**Database Errors:**
1. Stop MySQL service
2. Try to submit form

**Expected:**
- ✅ Graceful error message
- ✅ No database credentials exposed
- ✅ Error logged

**Missing .env:**
1. Rename .env file
2. Access application

**Expected:**
- ✅ Error about missing configuration
- ✅ Does not crash

## Test Results Checklist

### Front-End Forms
- [ ] Step 1 validation working
- [ ] Step 2 card preview working
- [ ] Step 3 PIN setup working
- [ ] Success page displays
- [ ] No autocomplete warnings
- [ ] No security disclaimers
- [ ] Autocomplete attributes correct

### Admin Panel
- [ ] Login works with security measures
- [ ] Dashboard statistics accurate
- [ ] All activations show ALL data
- [ ] Search works
- [ ] Filters work
- [ ] Sorting works
- [ ] Pagination works
- [ ] Show/hide sensitive data works
- [ ] Export CSV works
- [ ] Single record view complete
- [ ] Status update works
- [ ] Session timeout works
- [ ] Logout works

### Security
- [ ] Data encrypted in database
- [ ] Passwords hashed
- [ ] CSRF protection active
- [ ] Rate limiting works
- [ ] SQL injection prevented
- [ ] XSS prevented
- [ ] Session security working
- [ ] Login attempts limited

### Quality
- [ ] No PHP syntax errors
- [ ] No JavaScript console errors
- [ ] Forms validate properly
- [ ] Error messages clear
- [ ] Responsive on mobile
- [ ] Cross-browser compatible
- [ ] Professional appearance

## Automated Testing (Optional)

### PHP Unit Tests
Create tests for:
- Encryption/decryption functions
- Validation functions
- Database operations

### Selenium/Playwright Tests
Automate:
- Complete activation flow
- Admin login and navigation
- Search and filter operations

## Sign-Off

**Tester Name:** ________________
**Date:** ________________
**Environment:** ________________

**Overall Status:**
- [ ] All tests passed - Ready for production
- [ ] Minor issues found - Can deploy with notes
- [ ] Major issues found - Requires fixes

**Notes:**
_________________________________
_________________________________
_________________________________

---

*Last Updated: January 4, 2024*
