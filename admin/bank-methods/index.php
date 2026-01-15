<?php
/**
 * Bank Payment Methods - List/Index Page
 */

// require_once __DIR__ . '/../../includes/config.php';
// require_once __DIR__ . '/../../includes/db.php';
// require_once __DIR__ . '/../../includes/auth.php';
// require_once __DIR__ . '/../../includes/functions.php';

require_auth();
check_session_timeout();

// Get all bank payment methods
try {
    $stmt = $pdo->query("
        SELECT * FROM bank_payment_methods 
        ORDER BY display_order ASC, created_at DESC
    ");
    $bank_methods = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Bank methods fetch error: " . $e->getMessage());
    set_flash('error', 'Error loading bank payment methods.');
    $bank_methods = [];
}

$page_title = 'Bank Payment Methods';
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
    <?php //require_once __DIR__ . '/../includes/nav.php'; ?>
    
    <div class="flex">
        <!-- Sidebar -->
        <?php //require_once __DIR__ . '/../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="flex-1 p-8">
            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Bank Payment Methods</h1>
                    <p class="text-gray-600 mt-2">Manage traditional bank transfer payment options</p>
                </div>
                <a href="create.php" class="bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white px-6 py-3 rounded-lg font-medium shadow-lg transform hover:scale-105 transition duration-200">
                    + Add Bank Method
                </a>
            </div>
            
            <!-- Flash Message -->
            <?php if ($flash = get_flash()): ?>
                <div class="mb-6 bg-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-50 border-l-4 border-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-500 p-4 rounded shadow">
                    <p class="text-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-700 font-medium"><?= e($flash['message']) ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Bank Methods List -->
            <?php if (empty($bank_methods)): ?>
                <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                    <div class="text-6xl mb-4">🏦</div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Bank Payment Methods Yet</h3>
                    <p class="text-gray-500 mb-6">Get started by adding your first bank payment method.</p>
                    <a href="create.php" class="inline-block bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white px-6 py-3 rounded-lg font-medium shadow-lg transition duration-200">
                        Add Your First Bank Method
                    </a>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-200">
                                <tr>
                                    <th class="text-left py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Bank Details</th>
                                    <th class="text-left py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Account Info</th>
                                    <th class="text-left py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Type & Currency</th>
                                    <th class="text-center py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Order</th>
                                    <th class="text-center py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                    <th class="text-center py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            
                        </table>
                    </div>
                </div>
                
                <!-- Statistics Footer -->
                <div class="mt-6 bg-gradient-to-r from-emerald-100 to-green-100 rounded-lg p-4 border border-emerald-200">
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center space-x-6">
                            <div>
                                <span class="text-gray-600">Total Methods:</span>
                                <span class="font-bold text-gray-800 ml-2"><?= count($bank_methods) ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Active:</span>
                                <span class="font-bold text-green-600 ml-2"><?= count(array_filter($bank_methods, fn($m) => $m['is_active'])) ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Inactive:</span>
                                <span class="font-bold text-gray-500 ml-2"><?= count(array_filter($bank_methods, fn($m) => !$m['is_active'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
