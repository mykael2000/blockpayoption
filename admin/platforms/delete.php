<?php
/**
 * Platforms - Delete Page
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_auth();
check_session_timeout();

$platform_id = intval($_GET['id'] ?? 0);
$errors = [];

// Fetch platform data
if ($platform_id <= 0) {
    set_flash('error', 'Invalid platform ID.');
    redirect('index.php');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM platforms WHERE id = :id");
    $stmt->execute(['id' => $platform_id]);
    $platform = $stmt->fetch();
    
    if (!$platform) {
        set_flash('error', 'Platform not found.');
        redirect('index.php');
    }
} catch (PDOException $e) {
    error_log("Platform fetch error: " . $e->getMessage());
    set_flash('error', 'Error loading platform.');
    redirect('index.php');
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token. Please try again.';
    } else {
        try {
            // Delete platform from database
            $stmt = $pdo->prepare("DELETE FROM platforms WHERE id = :id");
            $stmt->execute(['id' => $platform_id]);
            
            // Delete associated logo file if exists
            if ($platform['logo_path']) {
                delete_file(basename($platform['logo_path']));
            }
            
            set_flash('success', 'Platform deleted successfully!');
            redirect('index.php');
        } catch (PDOException $e) {
            error_log("Platform deletion error: " . $e->getMessage());
            $errors[] = 'Error deleting platform. Please try again.';
        }
    }
}

$page_title = 'Delete Platform';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <?php require_once __DIR__ . '/../includes/nav.php'; ?>
    
    <div class="flex">
        <!-- Sidebar -->
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="flex-1 p-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Delete Platform</h1>
                <p class="text-gray-600 mt-2">Confirm platform deletion</p>
            </div>
            
            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded shadow">
                    <div class="flex">
                        <div class="flex-1">
                            <h3 class="text-red-800 font-semibold mb-2">Please correct the following errors:</h3>
                            <ul class="list-disc list-inside text-red-700 space-y-1">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= e($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Confirmation Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden max-w-2xl">
                <!-- Warning Header -->
                <div class="bg-gradient-to-r from-red-600 to-red-700 px-8 py-6">
                    <div class="flex items-center space-x-3">
                        <span class="text-4xl">⚠️</span>
                        <h2 class="text-2xl font-bold text-white">Confirm Deletion</h2>
                    </div>
                </div>
                
                <!-- Platform Details -->
                <div class="p-8">
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-6">
                        <p class="text-red-800 font-semibold mb-2">
                            You are about to delete the following platform:
                        </p>
                    </div>
                    
                    <div class="space-y-6 mb-8">
                        <!-- Platform Preview -->
                        <div class="flex items-start space-x-4 p-6 bg-gray-50 rounded-lg border border-gray-200">
                            <?php if ($platform['logo_path']): ?>
                                <img src="/<?= e($platform['logo_path']) ?>" alt="<?= e($platform['name']) ?>" class="w-24 h-24 rounded-lg object-cover border-2 border-gray-300 shadow">
                            <?php else: ?>
                                <div class="w-24 h-24 rounded-lg bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white font-bold text-3xl">
                                    🏢
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-900 mb-2"><?= e($platform['name']) ?></h3>
                                
                                <div class="space-y-2 text-sm">
                                    <?php if ($platform['description']): ?>
                                        <p class="text-gray-600"><?= e(truncate($platform['description'], 150)) ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="flex items-center space-x-4 pt-2">
                                        <div>
                                            <span class="text-gray-500">Website:</span>
                                            <a href="<?= e($platform['website_url']) ?>" target="_blank" class="text-blue-600 hover:underline ml-1">
                                                <?= e(parse_url($platform['website_url'], PHP_URL_HOST) ?? $platform['website_url']) ?> →
                                            </a>
                                        </div>
                                        
                                        <div>
                                            <span class="text-gray-500">Rating:</span>
                                            <span class="font-medium text-gray-900 ml-1">
                                                <?= number_format(floatval($platform['rating']), 2) ?> / 5.00
                                            </span>
                                        </div>
                                        
                                        <div>
                                            <?php if ($platform['is_active']): ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                                    ✓ Active
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                                                    ○ Inactive
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Warning Message -->
                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <span class="text-2xl">⚠️</span>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-semibold text-yellow-800 mb-1">
                                        Warning: This action cannot be undone
                                    </h3>
                                    <ul class="text-sm text-yellow-700 space-y-1 list-disc list-inside">
                                        <li>The platform will be permanently removed from the database</li>
                                        <?php if ($platform['logo_path']): ?>
                                            <li>The associated logo image will be deleted from the server</li>
                                        <?php endif; ?>
                                        <li>All platform data including description, pros, cons, and ratings will be lost</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <form method="POST" class="space-y-4">
                        <?= csrf_field() ?>
                        
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a href="index.php" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition duration-200">
                                Cancel
                            </a>
                            <button 
                                type="submit" 
                                class="px-8 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-medium rounded-lg shadow-lg transform hover:scale-105 transition duration-200"
                                onclick="return confirm('Are you absolutely sure? This action cannot be undone!');"
                            >
                                Yes, Delete Platform
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="mt-6 max-w-2xl">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <strong>Alternative:</strong> Instead of deleting, you can 
                        <a href="edit.php?id=<?= $platform['id'] ?>" class="text-blue-600 hover:underline font-semibold">edit this platform</a>
                        to deactivate it or make changes.
                    </p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
