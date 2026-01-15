# BlockPayOption - Modern Crypto Payment Platform

A comprehensive, modern crypto payment website built with PHP and Tailwind CSS. This platform educates users about blockchain technology, displays various cryptocurrency payment methods, provides tutorials, and recommends trusted platforms.

![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC)

## 🌟 Features

### Public Features
- **Landing Page**: Modern hero section with blockchain-themed animations and educational content
- **Payment Methods**: Browse and search cryptocurrency payment options with QR codes
- **Tutorials**: Step-by-step guides for beginners and advanced users
- **Platform Recommendations**: Curated list of trusted cryptocurrency exchanges
- **Payment Links**: Generate and share custom payment requests

### Admin Panel
- **Secure Authentication**: Password-hashed login with session management
- **Dashboard**: Overview statistics and recent activities
- **Payment Methods Management**: Full CRUD operations with logo/QR code uploads
- **Tutorials Management**: Create and manage educational content
- **Platforms Management**: Add and manage recommended platforms
- **Payment Links**: Generate trackable payment links with expiration

### Security Features
- ✅ SQL Injection Protection (Prepared Statements)
- ✅ XSS Prevention (Output Sanitization)
- ✅ CSRF Protection (Token Validation)
- ✅ Secure Password Hashing (password_hash)
- ✅ File Upload Validation
- ✅ Session Management
- ✅ Input Validation

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

Or import via phpMyAdmin by importing the `database/schema.sql` file.

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
│   ├── payment-methods/       # Payment methods CRUD
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
│   └── schema.sql             # Database schema with sample data
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
3. **tutorials**: Educational content
4. **platforms**: Recommended exchanges
5. **payment_links**: Generated payment requests

See `database/schema.sql` for complete schema details.

## 🎨 Design Features

- **Modern UI**: Tailwind CSS with custom gradients and animations
- **Glassmorphism**: Backdrop blur effects for modern aesthetics
- **Responsive**: Mobile-first design approach
- **Animations**: Smooth transitions, hover effects, and scroll animations
- **Color Scheme**: Purple, blue, and teal gradients for crypto theme
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

### Managing Payment Methods

1. Login to admin panel
2. Navigate to "Payment Methods"
3. Click "Add New Payment Method"
4. Fill in details: name, symbol, wallet address, networks, etc.
5. Upload logo and QR code (optional - QR codes can be auto-generated)
6. Set display order and active status
7. Save

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
3. Select payment method
4. Enter amount and recipient email (optional)
5. Set expiration date
6. Generate link
7. Share the generated URL with payer

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
6. **Restrict uploads/** directory** from direct PHP execution
7. **Use strong database passwords**
8. **Enable firewall** on your server

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
- ✅ Payment methods with search and filter
- ✅ Tutorial library with categories
- ✅ Platform recommendations with ratings
- ✅ Payment link handler with QR codes
- ✅ Copy-to-clipboard functionality
- ✅ Mobile-responsive navigation
- ✅ Smooth scroll animations

### Admin Panel Features
- ✅ Secure authentication system
- ✅ Dashboard with statistics
- ✅ Payment methods CRUD
- ✅ Tutorials CRUD with HTML editor
- ✅ Platforms CRUD with rating system
- ✅ Payment links generator
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

### File Upload Issues
- Check `uploads/` directory permissions (755 or 777)
- Verify `upload_max_filesize` and `post_max_size` in php.ini
- Ensure directory ownership is correct

### Blank Page / PHP Errors
- Check PHP error logs
- Enable error display temporarily: `ini_set('display_errors', 1);`
- Verify PHP version is 7.4 or higher

### .htaccess Not Working
- Ensure mod_rewrite is enabled: `sudo a2enmod rewrite`
- Check AllowOverride is set to All
- Restart Apache: `sudo service apache2 restart`

## 📝 Sample Data

The database schema includes sample data:

- **1 Admin User**: admin/admin123
- **3 Payment Methods**: Bitcoin, Ethereum, USDT
- **3 Tutorials**: Crypto basics, wallet creation, making payments
- **4 Platforms**: Binance, Coinbase, Kraken, Crypto.com

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

### Version 1.0.0 (Current)
- Initial release
- Complete admin panel with CRUD operations
- Modern frontend with Tailwind CSS
- Payment link generation system
- Tutorial and platform management
- Security features implemented

## 🎯 Future Enhancements

- [ ] Dark mode support
- [ ] Multi-language support
- [ ] Email notifications
- [ ] Payment status webhooks
- [ ] Advanced analytics dashboard
- [ ] Two-factor authentication
- [ ] API for external integrations
- [ ] Mobile app

## ⚡ Quick Start

```bash
# Clone repository
git clone https://github.com/mykael2000/blockpayoption.git

# Import database
mysql -u root -p < database/schema.sql

# Update config
nano includes/config.php

# Set permissions
chmod 755 uploads/

# Access admin panel
http://localhost/admin/login.php
# Username: admin
# Password: admin123
```

---

**Built with ❤️ for the crypto community**
