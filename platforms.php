<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'display_order';
$view_mode = isset($_GET['view']) ? $_GET['view'] : 'grid';

$allowed_sorts = ['display_order', 'rating', 'name'];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = 'display_order';
}

try {
    $order_clause = match($sort_by) {
        'rating' => 'rating DESC',
        'name' => 'name ASC',
        default => 'display_order ASC'
    };
    
    $stmt = $pdo->prepare("SELECT * FROM platforms WHERE is_active = 1 ORDER BY {$order_clause}");
    $stmt->execute();
    $platforms = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Database error occurred";
}

function renderStars($rating) {
    $full_stars = floor($rating);
    $half_star = ($rating - $full_stars) >= 0.5;
    $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
    
    $html = '<div class="star-rating inline-flex">';
    for ($i = 0; $i < $full_stars; $i++) {
        $html .= '★';
    }
    if ($half_star) {
        $html .= '☆';
    }
    for ($i = 0; $i < $empty_stars; $i++) {
        $html .= '☆';
    }
    $html .= '</div>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Compare the best cryptocurrency platforms and exchanges. Find the perfect platform for buying, selling, and trading crypto.">
    <meta name="keywords" content="cryptocurrency platforms, crypto exchanges, bitcoin exchanges, trading platforms, crypto comparison">
    <title>Platforms - <?php echo e(SITE_NAME); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/custom.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-2">
                        <div class="w-10 h-10 gradient-purple-blue rounded-lg flex items-center justify-center">
                            <span class="text-white text-xl font-bold">BP</span>
                        </div>
                        <span class="text-xl font-bold gradient-text"><?php echo e(SITE_NAME); ?></span>
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-gray-600 hover:text-purple-600 transition">Home</a>
                    <a href="payment-methods.php" class="text-gray-600 hover:text-purple-600 transition">Payment Methods</a>
                    <a href="tutorials.php" class="text-gray-600 hover:text-purple-600 transition">Tutorials</a>
                    <a href="platforms.php" class="text-gray-900 font-semibold border-b-2 border-purple-600 pb-1">Platforms</a>
                    <a href="admin/login.php" class="px-4 py-2 gradient-purple-blue text-white rounded-lg hover:shadow-lg transition">Admin</a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" class="text-gray-600 hover:text-purple-600 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t">
            <div class="px-4 pt-2 pb-4 space-y-2">
                <a href="index.php" class="block py-2 text-gray-600 hover:text-purple-600 hover:border-l-4 hover:border-purple-300 pl-4 transition">Home</a>
                <a href="payment-methods.php" class="block py-2 text-gray-600 hover:text-purple-600 hover:border-l-4 hover:border-purple-300 pl-4 transition">Payment Methods</a>
                <a href="tutorials.php" class="block py-2 text-gray-600 hover:text-purple-600 hover:border-l-4 hover:border-purple-300 pl-4 transition">Tutorials</a>
                <a href="platforms.php" class="block py-2 text-gray-900 font-semibold border-l-4 border-purple-600 pl-4">Platforms</a>
                <a href="admin/login.php" class="block py-2 px-4 gradient-purple-blue text-white rounded-lg text-center mt-2">Admin</a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="gradient-purple-blue text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center fade-in">
                <h1 class="text-5xl font-bold mb-4">Crypto Platforms</h1>
                <p class="text-xl text-purple-100 max-w-3xl mx-auto">
                    Discover and compare the best cryptocurrency exchanges and platforms
                </p>
            </div>
        </div>
    </section>

    <!-- Sort and View Options -->
    <section class="py-8 bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div class="flex flex-wrap gap-3">
                    <a href="?sort=display_order&view=<?php echo e($view_mode); ?>" class="px-4 py-2 rounded-lg font-semibold transition <?php echo $sort_by === 'display_order' ? 'gradient-purple-blue text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                        Featured
                    </a>
                    <a href="?sort=rating&view=<?php echo e($view_mode); ?>" class="px-4 py-2 rounded-lg font-semibold transition <?php echo $sort_by === 'rating' ? 'gradient-purple-blue text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                        Highest Rated
                    </a>
                    <a href="?sort=name&view=<?php echo e($view_mode); ?>" class="px-4 py-2 rounded-lg font-semibold transition <?php echo $sort_by === 'name' ? 'gradient-purple-blue text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                        Name (A-Z)
                    </a>
                </div>
                <div class="flex gap-3">
                    <a href="?sort=<?php echo e($sort_by); ?>&view=grid" class="px-4 py-2 rounded-lg font-semibold transition <?php echo $view_mode === 'grid' ? 'gradient-purple-blue text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                        Grid View
                    </a>
                    <a href="?sort=<?php echo e($sort_by); ?>&view=table" class="px-4 py-2 rounded-lg font-semibold transition <?php echo $view_mode === 'table' ? 'gradient-purple-blue text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                        Table View
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Platforms Display -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if (empty($platforms)): ?>
            <div class="text-center py-16">
                <div class="text-6xl mb-4">🏢</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">No Platforms Found</h3>
                <p class="text-gray-600 mb-8">
                    There are no active platforms available at the moment.
                </p>
            </div>
            <?php elseif ($view_mode === 'grid'): ?>
            <!-- Grid View -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($platforms as $index => $platform): ?>
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl card-hover animate-on-scroll" style="animation-delay: <?php echo ($index % 9) * 0.1; ?>s">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <?php if ($platform['logo_path']): ?>
                            <img src="<?php echo e($platform['logo_path']); ?>" alt="<?php echo e($platform['name']); ?>" class="h-12 object-contain">
                            <?php else: ?>
                            <div class="w-12 h-12 gradient-purple-blue rounded-lg flex items-center justify-center text-white text-xl font-bold">
                                <?php echo e(substr($platform['name'], 0, 1)); ?>
                            </div>
                            <?php endif; ?>
                            <div class="text-right">
                                <?php echo renderStars($platform['rating']); ?>
                                <div class="text-sm font-semibold text-gray-600 mt-1"><?php echo number_format($platform['rating'], 2); ?>/5</div>
                            </div>
                        </div>
                        
                        <h3 class="text-2xl font-bold mb-3 text-gray-900"><?php echo e($platform['name']); ?></h3>
                        
                        <p class="text-gray-600 mb-4 text-sm">
                            <?php echo e(truncate($platform['description'], 120)); ?>
                        </p>
                        
                        <?php if ($platform['pros']): ?>
                        <div class="mb-4">
                            <h4 class="text-sm font-bold text-green-600 mb-2">✓ Pros</h4>
                            <ul class="text-xs text-gray-600 space-y-1">
                                <?php 
                                $pros = explode('|', $platform['pros']);
                                foreach (array_slice($pros, 0, 3) as $pro): 
                                ?>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">•</span>
                                    <span><?php echo e($pro); ?></span>
                                </li>
                                <?php endforeach; ?>
                                <?php if (count($pros) > 3): ?>
                                <li class="text-gray-400">+<?php echo count($pros) - 3; ?> more</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <a href="<?php echo e($platform['website_url']); ?>" target="_blank" rel="noopener noreferrer" class="block text-center px-6 py-3 gradient-purple-blue text-white rounded-lg font-semibold hover:shadow-lg transition">
                            Visit Site →
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <!-- Table View -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="gradient-purple-blue text-white">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold">Platform</th>
                                <th class="px-6 py-4 text-left font-semibold">Rating</th>
                                <th class="px-6 py-4 text-left font-semibold">Description</th>
                                <th class="px-6 py-4 text-left font-semibold">Pros</th>
                                <th class="px-6 py-4 text-left font-semibold">Cons</th>
                                <th class="px-6 py-4 text-center font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($platforms as $index => $platform): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <?php if ($platform['logo_path']): ?>
                                        <img src="<?php echo e($platform['logo_path']); ?>" alt="<?php echo e($platform['name']); ?>" class="h-10 object-contain">
                                        <?php else: ?>
                                        <div class="w-10 h-10 gradient-purple-blue rounded-lg flex items-center justify-center text-white font-bold">
                                            <?php echo e(substr($platform['name'], 0, 1)); ?>
                                        </div>
                                        <?php endif; ?>
                                        <span class="font-bold text-gray-900"><?php echo e($platform['name']); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <?php echo renderStars($platform['rating']); ?>
                                        <div class="text-sm font-semibold text-gray-600 mt-1"><?php echo number_format($platform['rating'], 2); ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600 max-w-xs">
                                        <?php echo e(truncate($platform['description'], 80)); ?>
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($platform['pros']): ?>
                                    <ul class="text-xs text-gray-600 space-y-1">
                                        <?php 
                                        $pros = explode('|', $platform['pros']);
                                        foreach (array_slice($pros, 0, 2) as $pro): 
                                        ?>
                                        <li class="flex items-start">
                                            <span class="text-green-500 mr-1">✓</span>
                                            <span><?php echo e(truncate($pro, 40)); ?></span>
                                        </li>
                                        <?php endforeach; ?>
                                        <?php if (count($pros) > 2): ?>
                                        <li class="text-gray-400">+<?php echo count($pros) - 2; ?> more</li>
                                        <?php endif; ?>
                                    </ul>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($platform['cons']): ?>
                                    <ul class="text-xs text-gray-600 space-y-1">
                                        <?php 
                                        $cons = explode('|', $platform['cons']);
                                        foreach (array_slice($cons, 0, 2) as $con): 
                                        ?>
                                        <li class="flex items-start">
                                            <span class="text-red-500 mr-1">✗</span>
                                            <span><?php echo e(truncate($con, 40)); ?></span>
                                        </li>
                                        <?php endforeach; ?>
                                        <?php if (count($cons) > 2): ?>
                                        <li class="text-gray-400">+<?php echo count($cons) - 2; ?> more</li>
                                        <?php endif; ?>
                                    </ul>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="<?php echo e($platform['website_url']); ?>" target="_blank" rel="noopener noreferrer" class="inline-block px-4 py-2 gradient-purple-blue text-white rounded-lg font-semibold hover:shadow-lg transition text-sm">
                                        Visit
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-10 h-10 gradient-purple-blue rounded-lg flex items-center justify-center">
                            <span class="text-white text-xl font-bold">BP</span>
                        </div>
                        <span class="text-xl font-bold"><?php echo e(SITE_NAME); ?></span>
                    </div>
                    <p class="text-gray-400 text-sm">
                        Simplifying cryptocurrency payments for everyone.
                    </p>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="index.php" class="hover:text-purple-400 transition">Home</a></li>
                        <li><a href="payment-methods.php" class="hover:text-purple-400 transition">Payment Methods</a></li>
                        <li><a href="tutorials.php" class="hover:text-purple-400 transition">Tutorials</a></li>
                        <li><a href="platforms.php" class="hover:text-purple-400 transition">Platforms</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Resources</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="tutorials.php" class="hover:text-purple-400 transition">Getting Started</a></li>
                        <li><a href="tutorials.php" class="hover:text-purple-400 transition">Help Center</a></li>
                        <li><a href="admin/login.php" class="hover:text-purple-400 transition">Admin Login</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Contact</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><?php echo e(ADMIN_EMAIL); ?></li>
                        <li>24/7 Support Available</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center text-gray-400 text-sm">
                <p>&copy; <?php echo date('Y'); ?> <?php echo e(SITE_NAME); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
