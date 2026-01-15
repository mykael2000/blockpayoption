<?php
/**
 * Tutorials - Delete Page
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_auth();
check_session_timeout();

$tutorial_id = (int)($_GET['id'] ?? 0);

if ($tutorial_id <= 0) {
    set_flash('error', 'Invalid tutorial ID.');
    redirect('index.php');
}

// Get tutorial
try {
    $stmt = $pdo->prepare("SELECT * FROM tutorials WHERE id = ?");
    $stmt->execute([$tutorial_id]);
    $tutorial = $stmt->fetch();
    
    if (!$tutorial) {
        set_flash('error', 'Tutorial not found.');
        redirect('index.php');
    }
} catch (PDOException $e) {
    error_log("Tutorial fetch error: " . $e->getMessage());
    set_flash('error', 'Error loading tutorial.');
    redirect('index.php');
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        set_flash('error', 'Invalid security token. Please try again.');
        redirect('index.php');
    }
    
    try {
        // Delete tutorial from database
        $stmt = $pdo->prepare("DELETE FROM tutorials WHERE id = ?");
        $stmt->execute([$tutorial_id]);
        
        // Delete image file if exists
        if ($tutorial['image_path']) {
            delete_file(basename($tutorial['image_path']));
        }
        
        set_flash('success', 'Tutorial deleted successfully!');
        redirect('index.php');
    } catch (PDOException $e) {
        error_log("Tutorial deletion error: " . $e->getMessage());
        set_flash('error', 'Error deleting tutorial. Please try again.');
        redirect('index.php');
    }
}

$page_title = 'Delete Tutorial';
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
                <div class="flex items-center space-x-3 mb-4">
                    <a href="index.php" class="text-gray-600 hover:text-gray-800 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Delete Tutorial</h1>
                        <p class="text-gray-600 mt-2">Confirm tutorial deletion</p>
                    </div>
                </div>
            </div>
            
            <!-- Confirmation Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden max-w-2xl">
                <!-- Warning Banner -->
                <div class="bg-gradient-to-r from-red-500 to-red-600 p-6 text-white">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">Warning: Permanent Deletion</h2>
                            <p class="mt-1 text-red-100">This action cannot be undone</p>
                        </div>
                    </div>
                </div>
                
                <!-- Tutorial Details -->
                <div class="p-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">You are about to delete:</h3>
                    
                    <div class="bg-gray-50 rounded-lg p-6 border-2 border-gray-200 mb-6">
                        <div class="flex items-start space-x-4">
                            <?php if ($tutorial['image_path']): ?>
                                <img src="/<?= e($tutorial['image_path']) ?>" alt="<?= e($tutorial['title']) ?>" class="w-24 h-24 rounded-lg object-cover border-2 border-gray-300">
                            <?php else: ?>
                                <div class="w-24 h-24 rounded-lg bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white font-bold text-3xl">
                                    📖
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex-1">
                                <h4 class="text-xl font-bold text-gray-900 mb-2"><?= e($tutorial['title']) ?></h4>
                                
                                <div class="space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-600 font-medium">Slug:</span>
                                        <code class="text-sm bg-white px-2 py-1 rounded border border-gray-300 text-purple-600">
                                            <?= e($tutorial['slug']) ?>
                                        </code>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-600 font-medium">Category:</span>
                                        <?php
                                        $category_colors = [
                                            'beginner' => 'bg-green-100 text-green-800 border-green-200',
                                            'intermediate' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                            'advanced' => 'bg-red-100 text-red-800 border-red-200',
                                            'general' => 'bg-blue-100 text-blue-800 border-blue-200'
                                        ];
                                        $color_class = $category_colors[$tutorial['category']] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                        ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold border <?= $color_class ?>">
                                            <?= ucfirst(e($tutorial['category'])) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-600 font-medium">Status:</span>
                                        <?php if ($tutorial['is_published']): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                                ✓ Published
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                                                ○ Draft
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-600 font-medium">Created:</span>
                                        <span class="text-sm text-gray-700"><?= format_datetime($tutorial['created_at']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Consequences List -->
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                        <h4 class="text-sm font-semibold text-red-800 mb-2">This will:</h4>
                        <ul class="text-sm text-red-700 space-y-1 ml-5 list-disc">
                            <li>Permanently delete the tutorial from the database</li>
                            <?php if ($tutorial['image_path']): ?>
                                <li>Delete the associated image file</li>
                            <?php endif; ?>
                            <li>Remove all tutorial data and content</li>
                            <li>Make the tutorial unavailable to users</li>
                        </ul>
                    </div>
                    
                    <!-- Action Buttons -->
                    <form method="POST" class="space-y-4">
                        <?= csrf_field() ?>
                        
                        <div class="flex items-center space-x-4">
                            <button 
                                type="submit" 
                                class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-8 py-3 rounded-lg font-medium shadow-lg transform hover:scale-105 transition duration-200"
                            >
                                Yes, Delete Tutorial
                            </button>
                            <a 
                                href="index.php" 
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-8 py-3 rounded-lg font-medium transition"
                            >
                                Cancel
                            </a>
                        </div>
                        
                        <p class="text-xs text-gray-500 italic">
                            By clicking "Yes, Delete Tutorial", you confirm that you understand this action is permanent and cannot be reversed.
                        </p>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
