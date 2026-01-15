<?php
/**
 * Platforms - List/Index Page
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_auth();
check_session_timeout();

// Get all platforms
try {
    $stmt = $pdo->query("
        SELECT * FROM platforms 
        ORDER BY display_order ASC, created_at DESC
    ");
    $platforms = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Platforms fetch error: " . $e->getMessage());
    set_flash('error', 'Error loading platforms.');
    $platforms = [];
}

$page_title = 'Platforms';
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
                    <h1 class="text-3xl font-bold text-gray-800">Platforms</h1>
                    <p class="text-gray-600 mt-2">Manage cryptocurrency payment platforms</p>
                </div>
                <a href="create.php" class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-6 py-3 rounded-lg font-medium shadow-lg transform hover:scale-105 transition duration-200">
                    + Add Platform
                </a>
            </div>
            
            <!-- Flash Message -->
            <?php if ($flash = get_flash()): ?>
                <div class="mb-6 bg-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-50 border-l-4 border-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-500 p-4 rounded shadow">
                    <p class="text-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-700 font-medium"><?= e($flash['message']) ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Platforms List -->
            <?php if (empty($platforms)): ?>
                <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                    <div class="text-6xl mb-4">🏢</div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Platforms Yet</h3>
                    <p class="text-gray-500 mb-6">Get started by creating your first platform.</p>
                    <a href="create.php" class="inline-block bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-6 py-3 rounded-lg font-medium shadow-lg transition duration-200">
                        Create Your First Platform
                    </a>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-200">
                                <tr>
                                    <th class="text-left py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Platform</th>
                                    <th class="text-left py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Website URL</th>
                                    <th class="text-center py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Rating</th>
                                    <th class="text-center py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Order</th>
                                    <th class="text-center py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                    <th class="text-center py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($platforms as $platform): ?>
                                    <tr class="hover:bg-gradient-to-r hover:from-purple-50 hover:to-blue-50 transition duration-150">
                                        <td class="py-4 px-6">
                                            <div class="flex items-center space-x-3">
                                                <?php if ($platform['logo_path']): ?>
                                                    <img src="/<?= e($platform['logo_path']) ?>" alt="<?= e($platform['name']) ?>" class="w-16 h-16 rounded-lg object-cover border-2 border-gray-200 shadow-sm">
                                                <?php else: ?>
                                                    <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white font-bold text-2xl">
                                                        🏢
                                                    </div>
                                                <?php endif; ?>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-semibold text-gray-900 truncate"><?= e($platform['name']) ?></p>
                                                    <p class="text-sm text-gray-500 truncate mt-1">
                                                        <?= e(truncate($platform['description'], 60)) ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <a href="<?= e($platform['website_url']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline text-sm">
                                                <?= e(parse_url($platform['website_url'], PHP_URL_HOST) ?? $platform['website_url']) ?> →
                                            </a>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <div class="flex items-center justify-center space-x-1">
                                                <?php 
                                                $rating = floatval($platform['rating']);
                                                for ($i = 1; $i <= 5; $i++): 
                                                    if ($i <= floor($rating)): ?>
                                                        <span class="text-yellow-400 text-lg">★</span>
                                                    <?php elseif ($i == ceil($rating) && $rating - floor($rating) >= 0.5): ?>
                                                        <span class="text-yellow-400 text-lg">★</span>
                                                    <?php else: ?>
                                                        <span class="text-gray-300 text-lg">★</span>
                                                    <?php endif;
                                                endfor; ?>
                                            </div>
                                            <div class="text-xs text-gray-600 mt-1 font-medium">
                                                <?= number_format($rating, 2) ?> / 5.00
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 text-purple-700 font-semibold text-sm">
                                                <?= e($platform['display_order']) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <?php if ($platform['is_active']): ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                                    ✓ Active
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                                                    ○ Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <a href="edit.php?id=<?= $platform['id'] ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm hover:shadow">
                                                    Edit
                                                </a>
                                                <a href="delete.php?id=<?= $platform['id'] ?>" onclick="return confirm('Are you sure you want to delete this platform?')" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm hover:shadow">
                                                    Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Summary -->
                <div class="mt-6 bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg p-4 border border-purple-200">
                    <?php
                    // Calculate counts
                    $active_count = 0;
                    $inactive_count = 0;
                    foreach ($platforms as $p) {
                        if ($p['is_active']) {
                            $active_count++;
                        } else {
                            $inactive_count++;
                        }
                    }
                    // Calculate average rating
                    $avg_rating = count($platforms) > 0 ? array_sum(array_column($platforms, 'rating')) / count($platforms) : 0;
                    ?>
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold"><?= count($platforms) ?></span> platform<?= count($platforms) !== 1 ? 's' : '' ?> total
                        • 
                        <span class="font-semibold"><?= $active_count ?></span> active
                        •
                        <span class="font-semibold"><?= $inactive_count ?></span> inactive
                        •
                        Average rating: <span class="font-semibold"><?= number_format($avg_rating, 2) ?></span>
                    </p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
