<?php
/**
 * Configuration File
 * 
 * Core configuration constants and settings for BlockPayOption
 */

// Prevent direct access
if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);
}

// Debug Mode (set to false in production)
define('DEBUG_MODE', true);

// Error reporting configuration
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../error.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../error.log');
}

// Base Path - Root directory of the application
define('BASE_PATH', dirname(__DIR__));

// Site Configuration
define('SITE_NAME', 'BlockPayOption');

// Determine BASE_URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_dir = dirname($_SERVER['SCRIPT_NAME']);
// Remove /admin or /admin/* from path to get base
$base_dir = preg_replace('#/admin(/.*)?$#', '', $script_dir);
define('BASE_URL', $protocol . '://' . $host . $base_dir);

// Security Configuration
define('CSRF_TOKEN_NAME', '_token');
define('SESSION_LIFETIME', 3600); // 1 hour in seconds

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'blockpayoption');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// File Upload Configuration
define('UPLOAD_DIR', BASE_PATH . '/uploads/');
define('UPLOAD_URL', '/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);

// Allowed image MIME types for upload validation
define('ALLOWED_IMAGE_TYPES', [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/gif',
    'image/webp',
    'image/svg+xml'
]);

// Timezone
date_default_timezone_set('UTC');

// Ensure uploads directory exists
if (!file_exists(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0755, true);
}
