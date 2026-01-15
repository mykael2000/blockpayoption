<?php
/**
 * Payment Methods - List/Index Page
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_auth();
check_session_timeout();

// Get all payment methods
try {
    $stmt = $pdo->query("
        SELECT * FROM payment_methods 
        ORDER BY display_order ASC, created_at DESC
    ");
    $payment_methods = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Payment methods fetch error: " . $e->getMessage());
    set_flash('error', 'Error loading payment methods.');
    $payment_methods = [];
}

$page_title = 'Payment Methods';
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
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Payment Methods</h1>
                    <p class="text-gray-600 mt-2">Manage cryptocurrency payment options</p>
                </div>
                <a href="create.php" class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-6 py-3 rounded-lg font-medium shadow-lg transform hover:scale-105 transition duration-200">
                    + Add Payment Method
                </a>
            </div>
            
            <!-- Flash Message -->
            <?php if ($flash = get_flash()): ?>
                <div class="mb-6 bg-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-50 border-l-4 border-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-500 p-4 rounded shadow">
                    <p class="text-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-700 font-medium"><?= e($flash['message']) ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Payment Methods List -->
            <?php if (empty($payment_methods)): ?>
                <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                    <div class="text-6xl mb-4">💳</div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Payment Methods Yet</h3>
                    <p class="text-gray-500 mb-6">Get started by adding your first cryptocurrency payment method.</p>
                    <a href="create.php" class="inline-block bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-6 py-3 rounded-lg font-medium shadow-lg transition duration-200">
                        Add Your First Payment Method
                    </a>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-200">
                                <tr>
                                    <th class="text-left py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Payment Method</th>
                                    <th class="text-left py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Symbol</th>
                                    <th class="text-left py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Wallet Address</th>
                                    <th class="text-left py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Networks</th>
                                    <th class="text-center py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Order</th>
                                    <th class="text-center py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                    <th class="text-center py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($payment_methods as $method): ?>
                                    <tr class="hover:bg-gradient-to-r hover:from-purple-50 hover:to-blue-50 transition duration-150">
                                        <td class="py-4 px-6">
                                            <div class="flex items-center space-x-3">
                                                <?php if ($method['logo_path']): ?>
                                                    <img src="/<?= e($method['logo_path']) ?>" alt="<?= e($method['name']) ?>" class="w-10 h-10 rounded-full object-cover border-2 border-gray-200">
                                                <?php else: ?>
                                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white font-bold text-lg">
                                                        <?= strtoupper(substr(e($method['symbol']), 0, 1)) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <div class="font-semibold text-gray-800"><?= e($method['name']) ?></div>
                                                    <?php if ($method['description']): ?>
                                                        <div class="text-xs text-gray-500"><?= truncate(e($method['description']), 50) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <code class="bg-gradient-to-r from-purple-100 to-blue-100 text-purple-800 px-3 py-1 rounded-full text-sm font-semibold">
                                                <?= e($method['symbol']) ?>
                                            </code>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="flex items-center space-x-2">
                                                <code class="bg-gray-100 px-2 py-1 rounded text-xs text-gray-700 font-mono">
                                                    <?= truncate(e($method['wallet_address']), 20) ?>
                                                </code>
                                                <?php if ($method['qr_code_path']): ?>
                                                    <span class="text-green-500 text-xs" title="QR Code Available">🔲</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <?php if ($method['networks']): ?>
                                                <div class="flex flex-wrap gap-1">
                                                    <?php
                                                    $networks = explode(',', $method['networks']);
                                                    $display_networks = array_slice($networks, 0, 2);
                                                    foreach ($display_networks as $network):
                                                    ?>
                                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                                            <?= e(trim($network)) ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                    <?php if (count($networks) > 2): ?>
                                                        <span class="text-gray-500 text-xs">+<?= count($networks) - 2 ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400 text-sm">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-200 text-gray-700 rounded-full font-semibold text-sm">
                                                <?= e($method['display_order']) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <?php if ($method['is_active']): ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 shadow-sm">
                                                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                                    Active
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 shadow-sm">
                                                    <span class="w-2 h-2 bg-gray-400 rounded-full mr-2"></span>
                                                    Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="flex items-center justify-center space-x-2">
                                                <a href="edit.php?id=<?= $method['id'] ?>" 
                                                   class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200 shadow hover:shadow-lg transform hover:scale-105" 
                                                   title="Edit">
                                                    ✏️ Edit
                                                </a>
                                                <a href="delete.php?id=<?= $method['id'] ?>" 
                                                   class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200 shadow hover:shadow-lg transform hover:scale-105" 
                                                   onclick="return confirm('Are you sure you want to delete this payment method? This action cannot be undone.');"
                                                   title="Delete">
                                                    🗑️ Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <?php
                // Calculate counts before HTML
                $active_count = 0;
                $inactive_count = 0;
                foreach ($bank_methods as $method) {
                    if ($method['is_active']) {
                        $active_count++;
                    } else {
                        $inactive_count++;
                    }
                }
                ?>

                <div class="mt-6 bg-gradient-to-r from-emerald-100 to-green-100 rounded-lg p-4 border border-emerald-200">
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center space-x-6">
                            <div>
                                <span class="text-gray-600">Total Methods:</span>
                                <span class="font-bold text-gray-800 ml-2"><? = count($bank_methods) ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Active:</span>
                                <span class="font-bold text-green-600 ml-2"><?= $active_count ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Inactive:</span>
                                <span class="font-bold text-gray-500 ml-2"><?= $inactive_count ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
