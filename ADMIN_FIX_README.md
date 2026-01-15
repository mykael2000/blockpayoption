# Fix for Admin Panel 500 Errors

This document explains the changes made to fix 500 Internal Server Errors in the admin panel.

## Problem

The admin panel sub-links (payment-methods, tutorials, platforms, payment-links, bank-methods) were returning 500 errors due to:

1. **Missing `includes/config.php` file** - The primary cause of all 500 errors
2. **Hardcoded absolute paths** - Links using `/admin/` that fail if site isn't in root directory

## Solution

### Files Created

1. **includes/config.php** - Main configuration file with:
   - Site settings (SITE_NAME, BASE_URL, BASE_PATH)
   - Database configuration (DB_HOST, DB_NAME, DB_USER, DB_PASS)
   - Security settings (CSRF_TOKEN_NAME, SESSION_LIFETIME)
   - File upload settings (UPLOAD_DIR, UPLOAD_URL, MAX_FILE_SIZE, ALLOWED_EXTENSIONS)
   - Dynamic BASE_URL calculation for subdirectory support
   - DEBUG_MODE toggle for error reporting

2. **includes/config.example.php** - Template for production deployments

3. **admin/diagnostic.php** - Diagnostic page for troubleshooting configuration issues

### Files Modified

1. **includes/db.php** - Enhanced error handling
2. **includes/auth.php** - Dynamic BASE_URL for login redirects
3. **includes/functions.php** - Enhanced redirect() function with BASE_URL support
4. **admin/includes/sidebar.php** - Dynamic navigation URLs using BASE_URL
5. **admin/includes/nav.php** - Dynamic navigation URLs using BASE_URL
6. **admin/login.php** - Fixed hardcoded paths
7. **admin/logout.php** - Fixed redirect paths
8. **admin/dashboard.php** - Fixed internal links
9. **admin/payment-links/*.php** - Fixed all hardcoded paths
10. **.htaccess** - Enhanced with error pages and security rules

## Key Features

### Dynamic URL Handling

The BASE_URL is calculated dynamically based on the current request:

```php
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = preg_replace('#/admin(/.*)?$#', '', $script_dir);
define('BASE_URL', $protocol . '://' . $host . $base_dir);
```

This means the site works correctly whether installed at:
- `http://localhost/` (root)
- `http://localhost/blockpayoption/` (subdirectory)
- `https://example.com/mysite/` (production subdirectory)

### Enhanced Security

- CSRF token support with configurable token name
- Session timeout management
- File upload validation with MIME type checking
- Protected sensitive files via .htaccess
- DEBUG_MODE toggle to prevent information disclosure

## Testing

All admin pages now:
- ✅ Load without 500 errors
- ✅ Have working navigation links
- ✅ Support subdirectory installations
- ✅ Have proper error handling

## Deployment Instructions

1. **Database Setup**
   ```bash
   mysql -u root -p
   CREATE DATABASE blockpayoption;
   USE blockpayoption;
   source database/schema.sql;
   source database/add_bank_payments.sql;
   ```

2. **Configure Database**
   Edit `includes/config.php` and update:
   ```php
   define('DB_HOST', 'your_host');
   define('DB_NAME', 'your_database');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

3. **Production Settings**
   For production, set in `includes/config.php`:
   ```php
   define('DEBUG_MODE', false);
   ```

4. **File Permissions**
   ```bash
   chmod 755 uploads/
   chmod 644 includes/config.php
   ```

## Diagnostic Tool

Access `/admin/diagnostic.php` to check:
- File existence
- Configuration loading
- Database connectivity
- Path information
- Server environment

**Important:** Remove or protect `admin/diagnostic.php` in production!

## Troubleshooting

### Still Getting 500 Errors?

1. Check `error.log` in the root directory
2. Verify `includes/config.php` exists and is readable
3. Run `/admin/diagnostic.php` to identify issues
4. Ensure database credentials are correct
5. Check PHP error logs in your web server

### Links Not Working?

1. Verify .htaccess is being processed
2. Check that mod_rewrite is enabled (if needed)
3. Confirm BASE_URL is calculated correctly in diagnostic page

### Database Connection Failed?

1. Verify MySQL/MariaDB is running
2. Check database credentials in config.php
3. Ensure database exists and schema is imported
4. Verify database user has proper permissions

## Security Notes

⚠️ **Important for Production:**

1. Set `DEBUG_MODE = false` in `includes/config.php`
2. Use strong database passwords
3. Consider environment variables for sensitive data
4. Remove or protect `admin/diagnostic.php`
5. Set proper file permissions (644 for files, 755 for directories)
6. Keep `includes/config.php` out of version control (already in .gitignore)

## Changes Summary

- **Root Cause Fixed:** Created missing `includes/config.php` with all required constants
- **Path Issues Fixed:** All absolute paths now use BASE_URL for subdirectory support
- **Error Handling:** Enhanced error messages and debugging capabilities
- **Security:** Added CSRF protection, session management, and file upload validation
- **Documentation:** Added diagnostic tools and comprehensive documentation

All admin panel features should now work correctly! 🎉
