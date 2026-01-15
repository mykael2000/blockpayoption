# BlockPayOption - Modern Payment Platform

A comprehensive, modern payment website built with PHP and Tailwind CSS. This platform supports both cryptocurrency and traditional bank transfer payment methods, provides educational content about blockchain technology, and recommends trusted platforms.

![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC)

## 🌟 Features

### Public Features
- **Landing Page**: Modern hero section with blockchain-themed animations and educational content
- **Payment Methods**: Browse and search both cryptocurrency and bank transfer payment options
  - **Cryptocurrency**: Bitcoin, Ethereum, USDT with QR codes and wallet addresses
  - **Bank Transfers**: Traditional bank account details with copy-to-clipboard functionality
- **Payment Type Filtering**: Toggle between "All", "Cryptocurrency", and "Bank Transfer" views
- **Tutorials**: Step-by-step guides for beginners and advanced users
- **Platform Recommendations**: Curated list of trusted cryptocurrency exchanges
- **Payment Links**: Generate and share custom payment requests for both crypto and bank transfers

### Admin Panel
- **Secure Authentication**: Password-hashed login with session management
- **Dashboard**: Overview statistics for both crypto and bank payment methods
- **Crypto Payment Methods Management**: Full CRUD operations with logo/QR code uploads
- **Bank Payment Methods Management**: Complete CRUD for bank accounts with validation
- **Tutorials Management**: Create and manage educational content
- **Platforms Management**: Add and manage recommended platforms
- **Payment Links**: Generate trackable payment links with expiration for both payment types

### Security Features
- ✅ SQL Injection Protection (Prepared Statements)
- ✅ XSS Prevention (Output Sanitization)
- ✅ CSRF Protection (Token Validation)
- ✅ Secure Password Hashing (password_hash)
- ✅ File Upload Validation
- ✅ Session Management
- ✅ Input Validation
- ✅ Account Number Masking for Bank Details
- ✅ SWIFT/IBAN/Routing Number Validation

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)
- GD Library (for image handling)

## 🚀 Installation

### Step 1: Clone the Repository

```bash
git clone https://github.com/mykael2000/blockpayoption.git
cd blockpayoption
```

### Step 2: Configure the Database

1. Create a MySQL database:
```sql
CREATE DATABASE blockpayoption CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the database schema:
```bash
mysql -u your_username -p blockpayoption < database/schema.sql
```

3. Import the bank payment methods migration:
```bash
mysql -u your_username -p blockpayoption < database/add_bank_payments.sql
```

Or import both files via phpMyAdmin.

### Step 3: Configure the Application

1. Update the database configuration in `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'blockpayoption');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

2. Update the site URL:
```php
define('SITE_URL', 'http://yoursite.com');
```

### Step 4: Set Permissions

Ensure the `uploads/` directory is writable:

```bash
chmod 755 uploads/
```

### Step 5: Access the Application

- **Public Site**: `http://yoursite.com/`
- **Admin Panel**: `http://yoursite.com/admin/login.php`

## 🔐 Default Admin Credentials

**Username**: `admin`  
**Email**: `admin@blockpayoption.com`  
**Password**: `admin123`

⚠️ **IMPORTANT**: Change the default password immediately after first login!

## 📁 Project Structure

```
blockpayoption/
├── admin/                      # Admin panel
│   ├── dashboard.php          # Dashboard with statistics
│   ├── login.php              # Admin login page
│   ├── logout.php             # Logout handler
│   ├── includes/              # Admin shared components
│   │   ├── nav.php           # Top navigation
│   │   └── sidebar.php       # Sidebar navigation
│   ├── payment-methods/       # Crypto payment methods CRUD
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── edit.php
│   │   └── delete.php
│   ├── bank-methods/          # Bank payment methods CRUD
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── edit.php
│   │   └── delete.php
│   ├── tutorials/             # Tutorials CRUD
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── edit.php
│   │   └── delete.php
│   ├── platforms/             # Platforms CRUD
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── edit.php
│   │   └── delete.php
│   └── payment-links/         # Payment links management
│       ├── index.php
│       ├── create.php
│       └── view.php
├── includes/                   # Core application files
│   ├── config.php             # Configuration settings
│   ├── db.php                 # Database connection
│   ├── auth.php               # Authentication functions
│   └── functions.php          # Helper functions
├── assets/                     # Static assets
│   ├── css/
│   │   └── custom.css         # Custom styles
│   ├── js/
│   │   └── main.js            # JavaScript functionality
│   └── images/                # Static images
├── uploads/                    # User uploads (logos, images)
├── database/
│   ├── schema.sql             # Database schema with sample data
│   └── add_bank_payments.sql  # Bank payment methods migration
├── index.php                   # Landing page
├── payment-methods.php         # Payment methods listing
├── tutorials.php               # Tutorials page
├── platforms.php               # Platforms recommendations
├── pay.php                     # Payment link handler
├── .htaccess                   # Apache configuration
└── README.md                   # This file
```

## 💾 Database Schema

### Tables

1. **admins**: Admin user accounts
2. **payment_methods**: Cryptocurrency payment options
3. **bank_payment_methods**: Bank account payment options (NEW)
4. **tutorials**: Educational content
5. **platforms**: Recommended exchanges
6. **payment_links**: Generated payment requests (supports both crypto and bank)

1. **admins**: Admin user accounts
2. **payment_methods**: Cryptocurrency payment options
3. **tutorials**: Educational content
4. **platforms**: Recommended exchanges
5. **payment_links**: Generated payment requests

See `database/schema.sql` and `database/add_bank_payments.sql` for complete schema details.

## 🎨 Design Features

- **Modern UI**: Tailwind CSS with custom gradients and animations
- **Dual Color Themes**: 
  - Purple/blue gradients for cryptocurrency features
  - Emerald/green gradients for bank transfer features
- **Glassmorphism**: Backdrop blur effects for modern aesthetics
- **Responsive**: Mobile-first design approach
- **Animations**: Smooth transitions, hover effects, and scroll animations
- **Dark Mode Ready**: Foundation for future dark mode support

## 🔧 Configuration Options

Edit `includes/config.php` to customize:

- Database credentials
- Site name and URL
- Upload directory and file size limits
- Session lifetime
- Timezone settings
- Error reporting (disable in production)

## 📚 Usage Guide

### Managing Cryptocurrency Payment Methods

1. Login to admin panel
2. Navigate to "Crypto Payment Methods"
3. Click "Add Payment Method"
4. Fill in details: name, symbol, wallet address, networks, etc.
5. Upload logo and QR code (optional - QR codes can be auto-generated)
6. Set display order and active status
7. Save

### Managing Bank Payment Methods

1. Login to admin panel
2. Navigate to "Bank Payment Methods"
3. Click "Add Bank Method"
4. Fill in required details:
   - Bank name
   - Account holder name
   - Account number
5. Add optional details:
   - Routing number (for US banks)
   - SWIFT/BIC code (for international transfers)
   - IBAN (for European banks)
   - Bank address
   - Account type (Checking/Savings/Business)
   - Currency (USD, EUR, GBP, etc.)
   - Country
   - Special instructions
6. Upload bank logo (optional)
7. Set display order and active status
8. Save

### Creating Tutorials

1. Go to "Tutorials" in admin panel
2. Click "Add New Tutorial"
3. Enter title (slug auto-generates)
4. Select category (Beginner, Intermediate, Advanced, General)
5. Add content (HTML supported)
6. Upload featured image (optional)
7. Set display order and publish status
8. Save

### Generating Payment Links

1. Navigate to "Payment Links"
2. Click "Generate New Link"
3. **Select payment type**: Cryptocurrency or Bank Transfer
4. Select appropriate payment method based on type
5. Enter amount and recipient email (optional)
6. Set expiration date
7. Generate link
8. Share the generated URL with payer

### Adding Platforms

1. Go to "Platforms" in admin panel
2. Click "Add New Platform"
3. Enter platform details: name, description, website URL
4. Upload logo
5. Add rating (0.00 to 5.00)
6. List pros and cons (one per line or pipe-separated)
7. Set display order and status
8. Save

## 🔒 Security Best Practices

1. **Change default admin password** immediately
2. **Use HTTPS** in production (update SITE_URL to https://)
3. **Disable error display** in production (set `display_errors = 0`)
4. **Regular backups** of database and uploads
5. **Keep PHP updated** to latest stable version
6. **Restrict uploads/ directory** from direct PHP execution
7. **Use strong database passwords**
8. **Enable firewall** on your server
9. **Protect bank account details** - ensure only authorized admins have access
10. **Validate bank details** - use built-in SWIFT/IBAN/routing number validators

## 🌐 Deployment

### Apache Configuration

Ensure `.htaccess` is enabled:
```apache
AllowOverride All
```

### Nginx Configuration

For Nginx, use this configuration:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
}
```

## 📱 Features Breakdown

### Frontend Features
- ✅ Modern landing page with hero section
- ✅ Blockchain education section
- ✅ Dual payment methods display (crypto + bank)
- ✅ Payment type filtering (All/Crypto/Bank)
- ✅ Search across both payment types
- ✅ Tutorial library with categories
- ✅ Platform recommendations with ratings
- ✅ Payment link handler for both crypto and bank
- ✅ QR codes for crypto payments
- ✅ Copy-to-clipboard for bank account details
- ✅ Mobile-responsive navigation
- ✅ Smooth scroll animations

### Admin Panel Features
- ✅ Secure authentication system
- ✅ Dashboard with crypto and bank statistics
- ✅ Crypto payment methods CRUD
- ✅ Bank payment methods CRUD with validation
- ✅ Tutorials CRUD with HTML editor
- ✅ Platforms CRUD with rating system
- ✅ Payment links generator (crypto & bank)
- ✅ File upload management
- ✅ Flash message notifications
- ✅ Modern sidebar navigation

## 🛠️ Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **CSS Framework**: Tailwind CSS (via CDN)
- **Libraries**: 
  - QRCode.js (QR code generation)
  - PDO (Database abstraction)

## 🐛 Troubleshooting

### Database Connection Error
- Check database credentials in `includes/config.php`
- Ensure MySQL service is running
- Verify database exists and user has permissions
- Make sure both schema.sql and add_bank_payments.sql are imported

### Bank Payment Methods Not Showing
- Ensure `database/add_bank_payments.sql` has been imported
- Check that bank methods have `is_active = 1` in the database
- Verify the bank-methods directory exists in admin/

### File Upload Issues
- Check `uploads/` directory permissions (755 or 777)
- Verify `upload_max_filesize` and `post_max_size` in php.ini
- Ensure directory ownership is correct

### Blank Page / PHP Errors
- Check PHP error logs
- Enable error display temporarily: `ini_set('display_errors', 1);`
- Verify PHP version is 7.4 or higher
- Ensure bcmath extension is installed (required for IBAN validation)

### .htaccess Not Working
- Ensure mod_rewrite is enabled: `sudo a2enmod rewrite`
- Check AllowOverride is set to All
- Restart Apache: `sudo service apache2 restart`

## 📝 Sample Data

The database schema includes sample data:

- **1 Admin User**: admin/admin123
- **3 Crypto Payment Methods**: Bitcoin, Ethereum, USDT
- **2 Bank Payment Methods**: Chase Bank (USD), HSBC International (EUR)
- **3 Tutorials**: Crypto basics, wallet creation, making payments
- **4 Platforms**: Binance, Coinbase, Kraken, Crypto.com
- **Sample Payment Links**: Both crypto and bank payment link examples

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is open-source and available under the MIT License.

## 👤 Author

**mykael2000**
- GitHub: [@mykael2000](https://github.com/mykael2000)

## 📞 Support

For support, please open an issue on GitHub or contact the administrator.

## 🔄 Version History

### Version 2.0.0 (Current)
- ✅ **Bank Payment Methods Feature**
  - Complete CRUD for bank payment methods
  - Support for routing numbers, SWIFT codes, and IBAN
  - Account number masking for security
  - Bank-specific validation functions
- ✅ **Dual Payment Type Support**
  - Payment links now support both crypto and bank transfers
  - Separate color themes (purple/blue for crypto, emerald/green for bank)
  - Payment type filtering on frontend
- ✅ **Enhanced Security**
  - XSS vulnerability fixes
  - Improved input validation
  - CSRF protection across all forms

### Version 1.0.0
- Initial release
- Complete admin panel with CRUD operations
- Modern frontend with Tailwind CSS
- Cryptocurrency payment link generation system
- Tutorial and platform management
- Security features implemented

## 🎯 Future Enhancements

- [ ] Dark mode support
- [ ] Multi-language support
- [ ] Email notifications for payment confirmations
- [ ] Payment status webhooks
- [ ] Advanced analytics dashboard
- [ ] Two-factor authentication
- [ ] API for external integrations
- [ ] Mobile app

## ⚡ Quick Start

```bash
# Clone repository
git clone https://github.com/mykael2000/blockpayoption.git
cd blockpayoption

# Import database schema
mysql -u root -p blockpayoption < database/schema.sql

# Import bank payments feature
mysql -u root -p blockpayoption < database/add_bank_payments.sql

# Update config
nano includes/config.php

# Set permissions
chmod 755 uploads/

# Access admin panel
http://localhost/admin/login.php
# Username: admin
# Password: admin123
```

## 💡 Key Features of Bank Payment Integration

### For Administrators
- **Secure Bank Account Management**: Add, edit, and manage bank accounts with full validation
- **International Support**: SWIFT codes for international transfers, IBAN for European accounts
- **Flexible Configuration**: Support for checking, savings, and business account types
- **Multi-Currency**: Accept payments in USD, EUR, GBP, and other currencies
- **Custom Instructions**: Add specific transfer instructions for each bank account
- **Account Security**: Automatic masking of account numbers in list views

### For Users
- **Dual Payment Options**: Choose between cryptocurrency and traditional bank transfers
- **Easy Filtering**: Toggle between "All", "Cryptocurrency", and "Bank Transfer" views
- **Copy-to-Clipboard**: Quick copy buttons for all bank account details
- **Clear Instructions**: Step-by-step guidance for completing bank transfers
- **Processing Time Info**: Transparent communication about transfer times (1-3 days domestic, 2-5 days international)
- **Secure Display**: Full account details only shown on actual payment pages

---

**Built with ❤️ for the crypto community**
