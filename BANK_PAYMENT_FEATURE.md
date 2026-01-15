# Bank Payment Methods Feature - Installation Guide

## Overview

This feature extends BlockPayOption to support traditional bank transfer payment methods alongside cryptocurrency payments. Users can now choose between crypto and bank payments, and admins can manage both types through a unified interface.

## What's New

### ✅ Features Added

1. **Bank Payment Methods Management**
   - Full CRUD interface in admin panel
   - Support for multiple banks and currencies
   - Account details management (routing numbers, SWIFT codes, etc.)
   - Logo upload for bank branding

2. **Payment Links Support**
   - Create payment links for both crypto and bank transfers
   - Type-based filtering (All, Crypto, Bank)
   - Distinct visual styling for each payment type

3. **Frontend Integration**
   - Filter payment methods by type
   - Display bank account details with copy-to-clipboard
   - Masked account numbers for security
   - Green color scheme for bank payments vs purple for crypto

4. **Dashboard Updates**
   - Separate statistics for crypto and bank methods
   - Payment links breakdown by type
   - Recent activity tracking for both types

## Installation Steps

### 1. Database Setup

Run the migration SQL file to add the new tables and update existing ones:

```bash
mysql -u your_username -p blockpayoption < database/migrations/add_bank_payment_methods.sql
```

Or manually execute the SQL in your database management tool (phpMyAdmin, etc.).

**What this does:**
- Creates `bank_payment_methods` table
- Adds `payment_type` and `bank_payment_method_id` columns to `payment_links` table
- Inserts 5 sample bank payment methods (Chase, BofA, HSBC, Wells Fargo, Citibank)

### 2. File Structure

The following files have been added/modified:

**New Files:**
```
admin/bank-payment-methods/
├── index.php      # List all bank payment methods
├── create.php     # Add new bank method
├── edit.php       # Edit existing bank method
└── delete.php     # Delete bank method

database/migrations/
└── add_bank_payment_methods.sql
```

**Modified Files:**
```
admin/dashboard.php              # Added bank statistics
admin/includes/sidebar.php       # Added bank methods menu item
admin/payment-links/create.php   # Support for bank payment type
admin/payment-links/index.php    # Show payment type badges
admin/payment-links/view.php     # Display bank payment details
includes/functions.php           # Bank-specific helper functions
payment-methods.php              # Filter and display bank methods
pay.php                         # Handle bank payment links
index.php                       # Show bank methods in preview
```

### 3. Configuration

No additional configuration needed! The feature uses your existing database connection from `includes/config.php`.

### 4. Verify Installation

1. **Login to Admin Panel**
   - Navigate to `/admin/login.php`
   - Default credentials: `admin` / `admin123`

2. **Check Bank Payment Methods**
   - Click "Bank Payment Methods" in the sidebar
   - You should see 5 sample bank accounts
   - Try creating, editing, and deleting methods

3. **Test Payment Links**
   - Go to "Payment Links" → "Create"
   - Toggle between "Cryptocurrency" and "Bank Transfer"
   - Create a test payment link
   - Visit the payment link to verify display

4. **Frontend Verification**
   - Visit `/payment-methods.php`
   - Test the filter buttons (All, Cryptocurrency, Bank Transfer)
   - Click "View Details" on a bank method
   - Test copy-to-clipboard functionality

## Usage Guide

### Admin: Managing Bank Payment Methods

1. **Add New Bank Method:**
   - Navigate to: Admin → Bank Payment Methods → Add Bank Method
   - Required fields:
     - Bank Name (e.g., Chase Bank)
     - Account Holder Name (e.g., BlockPayOption LLC)
     - Account Number
   - Optional fields:
     - Routing Number (9 digits for US banks)
     - SWIFT/BIC Code (for international transfers)
     - Bank Address
     - Account Type (Checking, Savings, Business)
     - Currency (USD, EUR, GBP, etc.)
     - Country
     - Payment Instructions
     - Bank Logo

2. **Edit Bank Method:**
   - Click "Edit" on any bank method
   - Update details and save
   - Logo can be replaced or removed

3. **Delete Bank Method:**
   - Click "Delete" on any bank method
   - System will warn if the method is used in payment links
   - Confirm deletion

### Admin: Creating Payment Links

1. **Select Payment Type:**
   - Choose "Cryptocurrency" or "Bank Transfer"
   - Appropriate payment methods will be shown

2. **Fill Payment Details:**
   - Select payment method
   - Enter amount
   - Set expiration (optional)
   - Add recipient email (optional)

3. **Share Payment Link:**
   - Copy the generated link
   - Send to customer
   - Link displays appropriate payment details based on type

### Frontend: User Experience

1. **Browse Payment Methods:**
   - Visit `/payment-methods.php`
   - Use filters to view specific types
   - Search across all methods

2. **View Details:**
   - Click on any payment method
   - For crypto: See wallet address, QR code, networks
   - For bank: See account details, routing numbers, SWIFT codes

3. **Make Payment:**
   - Receive payment link from merchant
   - View payment details
   - Copy account information
   - Complete transfer through your bank
   - Reference the payment ID in your transfer

## Security Features

### Account Number Masking
- Account numbers are masked in list views (shows last 4 digits)
- Full numbers shown only when viewing details
- Uses `maskAccountNumber()` helper function

### Input Validation
- Account numbers: Alphanumeric only
- Routing numbers: 9 digits (US format)
- SWIFT codes: 8 or 11 characters
- All inputs sanitized for XSS protection

### CSRF Protection
- All forms use CSRF tokens
- Prevents unauthorized form submissions

### Database Security
- Prepared statements prevent SQL injection
- No direct SQL concatenation

## Helper Functions

New functions in `includes/functions.php`:

```php
getBankPaymentMethods($active_only = true)
// Fetch all bank payment methods

getBankPaymentMethodById($id)
// Get single bank method by ID

maskAccountNumber($accountNumber, $visibleDigits = 4)
// Mask account number (e.g., ******7890)

validateAccountNumber($number)
// Validate account number format

validateRoutingNumber($routing)
// Validate US routing number (9 digits)

validateSwiftCode($swift)
// Validate SWIFT/BIC code (8 or 11 chars)

formatRoutingNumber($routing)
// Format routing number with spaces
```

## Sample Data

The migration includes 5 sample bank methods:

1. **Chase Bank (USD)**
   - Business checking account
   - US routing number included

2. **Bank of America (USD)**
   - Checking account
   - Domestic and international wire info

3. **HSBC Bank (GBP)**
   - International business account
   - IBAN and SWIFT code

4. **Wells Fargo (USD)**
   - Business account
   - ACH and wire instructions

5. **Citibank (EUR)**
   - International business account
   - SWIFT transfers supported

## Styling Guidelines

### Color Schemes

**Cryptocurrency:**
- Primary: Purple to Blue gradient
- Badge: Purple background
- Icon: ₿ or crypto logo

**Bank Transfer:**
- Primary: Green to Blue gradient
- Badge: Green background
- Icon: 🏦 or bank logo

### CSS Classes

```css
.gradient-purple-blue   /* Crypto gradient */
.gradient-green-teal    /* Bank gradient */
.gradient-blue-teal     /* Neutral gradient */
```

## Troubleshooting

### Issue: Migration fails
**Solution:** Ensure you're using the correct database name and have proper permissions.

### Issue: Bank methods not showing
**Solution:** Check that `is_active = 1` and run the migration to insert sample data.

### Issue: Payment link creation fails
**Solution:** Verify that the `payment_links` table has been updated with new columns.

### Issue: Copy-to-clipboard not working
**Solution:** Ensure site is served over HTTPS (clipboard API requires secure context) or localhost.

### Issue: Account number masking not working
**Solution:** Verify `maskAccountNumber()` function exists in `includes/functions.php`.

## Future Enhancements

Potential additions for future versions:

- [ ] ACH/Wire transfer instructions templates
- [ ] Multi-currency exchange rate display
- [ ] Bank verification status (verified badge)
- [ ] Automatic payment confirmation via bank API
- [ ] Payment receipt generation (PDF)
- [ ] Email notifications for bank transfers
- [ ] Multiple account support per bank
- [ ] Recurring payment support
- [ ] Payment history tracking
- [ ] Export bank details to CSV/PDF

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review the code comments in the files
3. Check database migration has been applied
4. Verify all files are uploaded correctly
5. Contact the development team

## Credits

- Feature developed for BlockPayOption
- Compatible with PHP 7.4+
- Uses Tailwind CSS for styling
- MySQL/MariaDB database required

---

**Version:** 1.0.0  
**Last Updated:** January 15, 2026  
**License:** Same as BlockPayOption
