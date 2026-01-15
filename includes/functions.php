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
function redirect($path) {
    // If path starts with /, treat as absolute from site root
    if (strpos($path, '/') === 0) {
        $url = BASE_URL . $path;
    } else {
        $url = $path;
    }
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

/**
 * Mask account number for security
 */
function maskAccountNumber($accountNumber) {
    if (strlen($accountNumber) <= 4) {
        return $accountNumber;
    }
    $visibleDigits = 4;
    $maskedPart = str_repeat('*', strlen($accountNumber) - $visibleDigits);
    return $maskedPart . substr($accountNumber, -$visibleDigits);
}

/**
 * Validate routing number format
 */
function validateRoutingNumber($routingNumber) {
    // US routing number: 9 digits
    if (preg_match('/^\d{9}$/', $routingNumber)) {
        return true;
    }
    // Allow other country formats (6-11 digits)
    if (preg_match('/^\d{6,11}$/', $routingNumber)) {
        return true;
    }
    return false;
}

/**
 * Validate SWIFT/BIC code format
 */
function validateSwiftCode($swiftCode) {
    // SWIFT code: 8 or 11 alphanumeric characters
    return preg_match('/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/i', $swiftCode);
}

/**
 * Validate IBAN format (basic validation)
 */
function validateIBAN($iban) {
    // Remove spaces and convert to uppercase
    $iban = strtoupper(str_replace(' ', '', $iban));
    
    // IBAN should be 15-34 alphanumeric characters
    if (!preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{11,30}$/', $iban)) {
        return false;
    }
    
    // Move first 4 characters to end
    $rearranged = substr($iban, 4) . substr($iban, 0, 4);
    
    // Convert letters to numbers (A=10, B=11, ..., Z=35)
    $numericString = '';
    for ($i = 0; $i < strlen($rearranged); $i++) {
        $char = $rearranged[$i];
        if (is_numeric($char)) {
            $numericString .= $char;
        } else {
            $numericString .= (ord($char) - ord('A') + 10);
        }
    }
    
    // Check if mod 97 equals 1
    return bcmod($numericString, '97') == '1';
}

/**
 * Format bank details for display
 */
function formatBankDetails($bankMethod) {
    $details = [];
    
    if (!empty($bankMethod['bank_name'])) {
        $details['Bank Name'] = $bankMethod['bank_name'];
    }
    
    if (!empty($bankMethod['account_holder_name'])) {
        $details['Account Holder'] = $bankMethod['account_holder_name'];
    }
    
    if (!empty($bankMethod['account_number'])) {
        $details['Account Number'] = $bankMethod['account_number'];
    }
    
    if (!empty($bankMethod['routing_number'])) {
        $details['Routing Number'] = $bankMethod['routing_number'];
    }
    
    if (!empty($bankMethod['swift_code'])) {
        $details['SWIFT/BIC Code'] = $bankMethod['swift_code'];
    }
    
    if (!empty($bankMethod['iban'])) {
        $details['IBAN'] = $bankMethod['iban'];
    }
    
    if (!empty($bankMethod['account_type'])) {
        $details['Account Type'] = ucfirst($bankMethod['account_type']);
    }
    
    if (!empty($bankMethod['bank_currency'])) {
        $details['Currency'] = $bankMethod['bank_currency'];
    }
    
    if (!empty($bankMethod['country'])) {
        $details['Country'] = $bankMethod['country'];
    }
    
    return $details;
}
