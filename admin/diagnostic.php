<?php
/**
 * Diagnostic Page
 * 
 * Helps debug configuration and path issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Diagnostic Information</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-50 p-8'>
    <div class='max-w-4xl mx-auto'>
        <h1 class='text-3xl font-bold text-gray-800 mb-6'>🔍 Diagnostic Information</h1>
";

// Check if files exist
echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>File Existence Check</h2>";

$files = [
    '../includes/config.php',
    '../includes/db.php',
    '../includes/auth.php',
    '../includes/functions.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    $exists = file_exists($path);
    $color = $exists ? 'green' : 'red';
    $icon = $exists ? '✅' : '❌';
    echo "<p class='mb-2'><strong>$file:</strong> <span class='text-$color-600'>$icon " . 
         ($exists ? 'EXISTS' : 'MISSING') . "</span></p>";
}

echo "</div>";

// Try including config
echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>Configuration Check</h2>";

try {
    require_once __DIR__ . '/../includes/config.php';
    echo "<p class='text-green-600 font-semibold mb-2'>✅ Config loaded successfully</p>";
    
    $constants = [
        'SITE_NAME', 'BASE_PATH', 'BASE_URL', 'DB_HOST', 'DB_NAME', 
        'DB_USER', 'SESSION_LIFETIME', 'CSRF_TOKEN_NAME', 'UPLOAD_DIR', 'MAX_FILE_SIZE'
    ];
    
    echo "<div class='mt-4'><h3 class='font-semibold mb-2'>Constants Defined:</h3><ul class='list-disc list-inside'>";
    foreach ($constants as $const) {
        $defined = defined($const);
        $color = $defined ? 'green' : 'red';
        $value = $defined ? constant($const) : 'NOT DEFINED';
        echo "<li class='text-$color-600'>$const: " . htmlspecialchars($value) . "</li>";
    }
    echo "</ul></div>";
} catch (Exception $e) {
    echo "<p class='text-red-600 font-semibold'>❌ Config error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// Try database connection
echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>Database Connection Check</h2>";

try {
    require_once __DIR__ . '/../includes/db.php';
    echo "<p class='text-green-600 font-semibold'>✅ Database connected successfully</p>";
    
    // Try a simple query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payment_methods");
    $count = $stmt->fetch()['count'];
    echo "<p class='mt-2'>Payment Methods Count: <strong>$count</strong></p>";
} catch (Exception $e) {
    echo "<p class='text-red-600 font-semibold'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// Server information
echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>Server Information</h2>
    <ul class='list-disc list-inside space-y-1'>";

echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</li>";
echo "<li><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</li>";
echo "<li><strong>Script Filename:</strong> " . __FILE__ . "</li>";
echo "<li><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</li>";
echo "<li><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</li>";

echo "</ul></div>";

// Path information
if (defined('BASE_URL') && defined('BASE_PATH')) {
    echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>
        <h2 class='text-xl font-semibold text-gray-800 mb-4'>Path Information</h2>
        <ul class='list-disc list-inside space-y-1'>
            <li><strong>BASE_PATH:</strong> " . htmlspecialchars(BASE_PATH) . "</li>
            <li><strong>BASE_URL:</strong> " . htmlspecialchars(BASE_URL) . "</li>
            <li><strong>UPLOAD_DIR:</strong> " . htmlspecialchars(UPLOAD_DIR) . "</li>
        </ul>
    </div>";
}

echo "
        <div class='bg-blue-50 border-l-4 border-blue-500 p-4 rounded'>
            <p class='text-blue-700'><strong>Note:</strong> This diagnostic page should be removed or protected in production environments.</p>
        </div>
    </div>
</body>
</html>";
