<?php
/**
 * Admin Dashboard
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_auth();
check_session_timeout();

// Get statistics
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payment_methods WHERE is_active = 1");
    $active_payment_methods = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payment_methods");
    $total_payment_methods = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bank_payment_methods WHERE is_active = 1");
    $active_bank_methods = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bank_payment_methods");
    $total_bank_methods = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tutorials WHERE is_published = 1");
    $published_tutorials = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM platforms WHERE is_active = 1");
    $active_platforms = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payment_links");
    $total_payment_links = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payment_links WHERE status = 'pending'");
    $pending_payment_links = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payment_links WHERE payment_type = 'crypto'");
    $crypto_payment_links = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payment_links WHERE payment_type = 'bank'");
    $bank_payment_links = $stmt->fetch()['count'];
    
    // Recent payment links
    $stmt = $pdo->query("
        SELECT pl.*, 
               pm.name as payment_method_name, 
               pm.symbol,
               bpm.bank_name
        FROM payment_links pl 
        LEFT JOIN payment_methods pm ON pl.payment_method_id = pm.id 
        LEFT JOIN bank_payment_methods bpm ON pl.bank_payment_method_id = bpm.id
        ORDER BY pl.created_at DESC 
        LIMIT 5
    ");
    $recent_payment_links = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $error = "Error loading dashboard data.";
}

$page_title = 'Dashboard';
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
    <?php include __DIR__ . '/includes/nav.php'; ?>
    
    <div class="flex">
        <!-- Sidebar -->
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="flex-1 p-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
                <p class="text-gray-600 mt-2">Welcome back, <?= e($_SESSION['admin_username']) ?>!</p>
            </div>
            
            <!-- Flash Message -->
            <?php if ($flash = get_flash()): ?>
                <div class="mb-6 bg-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-50 border-l-4 border-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-500 p-4 rounded">
                    <p class="text-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-700"><?= e($flash['message']) ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Statistics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Crypto Payment Methods -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm mb-1">Crypto Methods</p>
                            <p class="text-3xl font-bold"><?= $total_payment_methods ?></p>
                            <p class="text-blue-100 text-xs mt-1"><?= $active_payment_methods ?> active</p>
                        </div>
                        <div class="text-blue-200 text-5xl opacity-50">₿</div>
                    </div>
                </div>
                
                <!-- Bank Payment Methods -->
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-emerald-100 text-sm mb-1">Bank Methods</p>
                            <p class="text-3xl font-bold"><?= $total_bank_methods ?></p>
                            <p class="text-emerald-100 text-xs mt-1"><?= $active_bank_methods ?> active</p>
                        </div>
                        <div class="text-emerald-200 text-5xl opacity-50">🏦</div>
                    </div>
                </div>
                
                <!-- Tutorials -->
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm mb-1">Tutorials</p>
                            <p class="text-3xl font-bold"><?= $published_tutorials ?></p>
                            <p class="text-purple-100 text-xs mt-1">Published</p>
                        </div>
                        <div class="text-purple-200 text-5xl opacity-50">📚</div>
                    </div>
                </div>
                
                <!-- Platforms -->
                <div class="bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-teal-100 text-sm mb-1">Platforms</p>
                            <p class="text-3xl font-bold"><?= $active_platforms ?></p>
                            <p class="text-teal-100 text-xs mt-1">Active</p>
                        </div>
                        <div class="text-teal-200 text-5xl opacity-50">🏢</div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Links Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total Payment Links -->
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-amber-100 text-sm mb-1">Total Payment Links</p>
                            <p class="text-3xl font-bold"><?= $total_payment_links ?></p>
                            <p class="text-amber-100 text-xs mt-1"><?= $pending_payment_links ?> pending</p>
                        </div>
                        <div class="text-amber-200 text-5xl opacity-50">🔗</div>
                    </div>
                </div>
                
                <!-- Crypto Payment Links -->
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-indigo-100 text-sm mb-1">Crypto Links</p>
                            <p class="text-3xl font-bold"><?= $crypto_payment_links ?></p>
                            <p class="text-indigo-100 text-xs mt-1">Cryptocurrency payments</p>
                        </div>
                        <div class="text-indigo-200 text-5xl opacity-50">₿</div>
                    </div>
                </div>
                
                <!-- Bank Payment Links -->
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm mb-1">Bank Links</p>
                            <p class="text-3xl font-bold"><?= $bank_payment_links ?></p>
                            <p class="text-green-100 text-xs mt-1">Bank transfer payments</p>
                        </div>
                        <div class="text-green-200 text-5xl opacity-50">💳</div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Payment Links -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Recent Payment Links</h2>
                    <a href="<?= BASE_URL ?>/admin/payment-links/index.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All →</a>
                </div>
                
                <?php if (empty($recent_payment_links)): ?>
                    <p class="text-gray-500 text-center py-8">No payment links created yet.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Unique ID</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Type</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Payment Method</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Amount</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Status</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_payment_links as $link): 
                                    $payment_type = $link['payment_type'] ?? 'crypto';
                                    $is_bank = $payment_type === 'bank';
                                ?>
                                    <tr class="border-b hover:bg-gray-50 transition">
                                        <td class="py-3 px-4 text-sm">
                                            <code class="bg-gray-100 px-2 py-1 rounded text-xs"><?= e($link['unique_id']) ?></code>
                                        </td>
                                        <td class="py-3 px-4 text-sm">
                                            <?php 
                                            $type_badge_class = $is_bank ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800';
                                            ?>
                                            <span class="<?= $type_badge_class ?> px-2 py-1 rounded-full text-xs font-medium">
                                                <?= $is_bank ? '🏦 Bank' : '₿ Crypto' ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm">
                                            <span class="font-medium"><?= e($is_bank ? $link['bank_name'] : $link['payment_method_name']) ?></span>
                                            <?php if (!$is_bank && !empty($link['symbol'])): ?>
                                            <span class="text-gray-500">(<?= e($link['symbol']) ?>)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4 text-sm font-medium">
                                            <?= $is_bank ? e(number_format($link['amount'], 2)) : e($link['amount']) ?> <?= e($link['currency']) ?>
                                        </td>
                                        <td class="py-3 px-4 text-sm">
                                            <?php
                                            $status_classes = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'completed' => 'bg-green-100 text-green-800',
                                                'expired' => 'bg-red-100 text-red-800'
                                            ];
                                            $class = $status_classes[$link['status']] ?? 'bg-gray-100 text-gray-800';
                                            ?>
                                            <span class="<?= $class ?> px-2 py-1 rounded-full text-xs font-medium">
                                                <?= ucfirst(e($link['status'])) ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-gray-600">
                                            <?= format_datetime($link['created_at']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
