<?php
/**
 * Tutorials - List/Index Page
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_auth();
check_session_timeout();

// Get all tutorials
try {
    $stmt = $pdo->query("
        SELECT * FROM tutorials 
        ORDER BY display_order ASC, created_at DESC
    ");
    $tutorials = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Tutorials fetch error: " . $e->getMessage());
    set_flash('error', 'Error loading tutorials.');
    $tutorials = [];
}

$page_title = 'Tutorials';
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
                    <h1 class="text-3xl font-bold text-gray-800">Tutorials</h1>
                    <p class="text-gray-600 mt-2">Manage educational content and guides</p>
                </div>
                <a href="create.php" class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-6 py-3 rounded-lg font-medium shadow-lg transform hover:scale-105 transition duration-200">
                    + Add Tutorial
                </a>
            </div>
            
            <!-- Flash Message -->
            <?php if ($flash = get_flash()): ?>
                <div class="mb-6 bg-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-50 border-l-4 border-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-500 p-4 rounded shadow">
                    <p class="text-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-700 font-medium"><?= e($flash['message']) ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Tutorials List -->
            <?php if (empty($tutorials)): ?>
                <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                    <div class="text-6xl mb-4">📚</div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Tutorials Yet</h3>
                    <p class="text-gray-500 mb-6">Get started by creating your first tutorial.</p>
                    <a href="create.php" class="inline-block bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-6 py-3 rounded-lg font-medium shadow-lg transition duration-200">
                        Create Your First Tutorial
                    </a>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-200">
                                <tr>
                                    <th class="text-left py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Tutorial</th>
                                    <th class="text-left py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Slug</th>
                                    <th class="text-left py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Category</th>
                                    <th class="text-center py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Order</th>
                                    <th class="text-center py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                    <th class="text-center py-4 px-6 text-sm font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($tutorials as $tutorial): ?>
                                    <tr class="hover:bg-gradient-to-r hover:from-purple-50 hover:to-blue-50 transition duration-150">
                                        <td class="py-4 px-6">
                                            <div class="flex items-center space-x-3">
                                                <?php if ($tutorial['image_path']): ?>
                                                    <img src="/<?= e($tutorial['image_path']) ?>" alt="<?= e($tutorial['title']) ?>" class="w-16 h-16 rounded-lg object-cover border-2 border-gray-200 shadow-sm">
                                                <?php else: ?>
                                                    <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white font-bold text-2xl">
                                                        📖
                                                    </div>
                                                <?php endif; ?>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-semibold text-gray-900 truncate"><?= e($tutorial['title']) ?></p>
                                                    <p class="text-sm text-gray-500 truncate mt-1">
                                                        <?= e(truncate(strip_tags($tutorial['content']), 60)) ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <code class="text-sm bg-gray-100 px-2 py-1 rounded text-purple-600 font-mono">
                                                <?= e($tutorial['slug']) ?>
                                            </code>
                                        </td>
                                        <td class="py-4 px-6">
                                            <?php
                                            $category_colors = [
                                                'beginner' => 'bg-green-100 text-green-800 border-green-200',
                                                'intermediate' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                'advanced' => 'bg-red-100 text-red-800 border-red-200',
                                                'general' => 'bg-blue-100 text-blue-800 border-blue-200'
                                            ];
                                            $color_class = $category_colors[$tutorial['category']] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                            ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border <?= $color_class ?>">
                                                <?= ucfirst(e($tutorial['category'])) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 text-purple-700 font-semibold text-sm">
                                                <?= e($tutorial['display_order']) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <?php if ($tutorial['is_published']): ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                                    ✓ Published
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                                                    ○ Draft
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <a href="edit.php?id=<?= $tutorial['id'] ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm hover:shadow">
                                                    Edit
                                                </a>
                                                <a href="delete.php?id=<?= $tutorial['id'] ?>" onclick="return confirm('Are you sure you want to delete this tutorial?')" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm hover:shadow">
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
                    $published_count = count(array_filter($tutorials, fn($t) => $t['is_published']));
                    $draft_count = count(array_filter($tutorials, fn($t) => !$t['is_published']));
                    ?>
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold"><?= count($tutorials) ?></span> tutorial<?= count($tutorials) !== 1 ? 's' : '' ?> total
                        • 
                        <span class="font-semibold"><?= $published_count ?></span> published
                        •
                        <span class="font-semibold"><?= $draft_count ?></span> draft<?= $draft_count !== 1 ? 's' : '' ?>
                    </p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
