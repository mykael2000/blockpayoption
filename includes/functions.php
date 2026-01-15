<?php
/**
 * Helper Functions
 * 
 * Utility functions used throughout the application
 */

/**
 * Sanitize output for HTML
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Set flash message
 */
function set_flash($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

/**
 * Get and clear flash message
 */
function get_flash() {
    if (isset($_SESSION['flash_message'])) {
        $flash = [
            'type' => $_SESSION['flash_type'],
            'message' => $_SESSION['flash_message']
        ];
        unset($_SESSION['flash_type'], $_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Generate unique ID
 */
function generate_unique_id($prefix = '') {
    return $prefix . bin2hex(random_bytes(16));
}

/**
 * Create URL-friendly slug
 */
function create_slug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Format date
 */
function format_date($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function format_datetime($datetime, $format = 'M d, Y g:i A') {
    return date($format, strtotime($datetime));
}

/**
 * Validate email
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Upload file
 */
function upload_file($file, $allowed_types = null, $max_size = null) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'No file uploaded or upload error occurred'];
    }
    
    $allowed_types = $allowed_types ?? ALLOWED_IMAGE_TYPES;
    $max_size = $max_size ?? MAX_FILE_SIZE;
    
    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }
    
    // Validate file size
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'File size exceeds maximum allowed size'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = UPLOAD_DIR . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $filename, 'path' => 'uploads/' . $filename];
    }
    
    return ['success' => false, 'error' => 'Failed to move uploaded file'];
}

/**
 * Delete file
 */
function delete_file($filename) {
    $filepath = UPLOAD_DIR . basename($filename);
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Paginate results
 */
function paginate($total_items, $items_per_page, $current_page) {
    $total_pages = ceil($total_items / $items_per_page);
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $items_per_page;
    
    return [
        'total_items' => $total_items,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'items_per_page' => $items_per_page,
        'offset' => $offset,
        'has_previous' => $current_page > 1,
        'has_next' => $current_page < $total_pages
    ];
}

/**
 * Truncate text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Get client IP address
 */
function get_client_ip() {
    $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * JSON response
 */
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Validate required fields
 */
function validate_required($fields, $data) {
    $errors = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    return $errors;
}
