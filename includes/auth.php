<?php
/**
 * Authentication Functions
 * 
 * Handles user authentication and session management
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

/**
 * Require authentication - redirect to login if not logged in
 */
function require_auth() {
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /admin/login.php');
        exit;
    }
}

/**
 * Login user
 */
function login_user($admin_id, $username, $email) {
    $_SESSION['admin_id'] = $admin_id;
    $_SESSION['admin_username'] = $username;
    $_SESSION['admin_email'] = $email;
    $_SESSION['last_activity'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
}

/**
 * Logout user
 */
function logout_user() {
    $_SESSION = array();
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

/**
 * Check session timeout
 */
function check_session_timeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
        logout_user();
        return true;
    }
    $_SESSION['last_activity'] = time();
    return false;
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Get CSRF token input field
 */
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
}
