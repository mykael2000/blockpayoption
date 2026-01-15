<?php
/**
 * Database Configuration Template
 * 
 * IMPORTANT: Copy this file to config.php and update with your database credentials
 * The config.php file is ignored by git for security reasons
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'blockpayoption');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('SITE_NAME', 'BlockPayOption');
define('SITE_URL', 'http://localhost/blockpayoption');
define('ADMIN_EMAIL', 'admin@blockpayoption.com');

// File Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Security Configuration
define('SESSION_LIFETIME', 3600 * 2); // 2 hours
define('CSRF_TOKEN_NAME', 'csrf_token');

// Timezone
date_default_timezone_set('UTC');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
