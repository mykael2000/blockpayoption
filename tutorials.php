<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$category_filter = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$selected_tutorial_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

try {
    if ($category_filter !== 'all') {
        $stmt = $pdo->prepare("SELECT * FROM tutorials WHERE is_published = 1 AND category = ? ORDER BY display_order");
        $stmt->execute([$category_filter]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM tutorials WHERE is_published = 1 ORDER BY display_order");
        $stmt->execute();
    }
    $tutorials = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("SELECT DISTINCT category FROM tutorials WHERE is_published = 1");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $selected_tutorial = null;
    if ($selected_tutorial_id) {
        $stmt = $pdo->prepare("SELECT * FROM tutorials WHERE id = ? AND is_published = 1");
        $stmt->execute([$selected_tutorial_id]);
        $selected_tutorial = $stmt->fetch();
    }
} catch (PDOException $e) {
    $error = "Database error occurred";
}

$category_colors = [
    'beginner' => 'bg-green-100 text-green-800',
    'intermediate' => 'bg-yellow-100 text-yellow-800',
    'advanced' => 'bg-red-100 text-red-800',
    'general' => 'bg-blue-100 text-blue-800'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Learn about cryptocurrency and blockchain technology with our comprehensive tutorials. From beginner to advanced guides.">
    <meta name="keywords" content="cryptocurrency tutorials, blockchain guides, crypto learning, bitcoin tutorials, ethereum guides">
    <title>Tutorials - <?php echo e(SITE_NAME); ?></title>
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
                    <a href="tutorials.php" class="text-gray-900 font-semibold border-b-2 border-purple-600 pb-1">Tutorials</a>
                    <a href="platforms.php" class="text-gray-600 hover:text-purple-600 transition">Platforms</a>
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
                <a href="tutorials.php" class="block py-2 text-gray-900 font-semibold border-l-4 border-purple-600 pl-4">Tutorials</a>
                <a href="platforms.php" class="block py-2 text-gray-600 hover:text-purple-600 hover:border-l-4 hover:border-purple-300 pl-4 transition">Platforms</a>
                <a href="admin/login.php" class="block py-2 px-4 gradient-purple-blue text-white rounded-lg text-center mt-2">Admin</a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="gradient-purple-blue text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center fade-in">
                <h1 class="text-5xl font-bold mb-4">Learn About Crypto</h1>
                <p class="text-xl text-purple-100 max-w-3xl mx-auto">
                    Master cryptocurrency and blockchain technology with our step-by-step tutorials
                </p>
            </div>
        </div>
    </section>

    <!-- Category Filters -->
    <section class="py-8 bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap justify-center gap-3">
                <a href="?category=all" class="px-6 py-3 rounded-full font-semibold transition <?php echo $category_filter === 'all' ? 'gradient-purple-blue text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                    All Tutorials
                </a>
                <?php foreach ($categories as $cat): ?>
                <a href="?category=<?php echo e($cat); ?>" class="px-6 py-3 rounded-full font-semibold transition <?php echo $category_filter === $cat ? 'gradient-purple-blue text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                    <?php echo e(ucfirst($cat)); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Tutorials Grid -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if (empty($tutorials)): ?>
            <div class="text-center py-16">
                <div class="text-6xl mb-4">📚</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">No Tutorials Found</h3>
                <p class="text-gray-600 mb-8">
                    <?php if ($category_filter !== 'all'): ?>
                        No tutorials found in the "<?php echo e(ucfirst($category_filter)); ?>" category.
                    <?php else: ?>
                        There are no published tutorials available at the moment.
                    <?php endif; ?>
                </p>
                <?php if ($category_filter !== 'all'): ?>
                <a href="tutorials.php" class="inline-block px-8 py-3 gradient-purple-blue text-white rounded-lg font-semibold hover:shadow-lg transition">
                    View All Tutorials
                </a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($tutorials as $index => $tutorial): ?>
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl card-hover animate-on-scroll" style="animation-delay: <?php echo ($index % 9) * 0.1; ?>s">
                    <?php if ($tutorial['image_path']): ?>
                    <div class="h-48 bg-gradient-to-br from-purple-400 to-blue-500 overflow-hidden">
                        <img src="<?php echo e($tutorial['image_path']); ?>" alt="<?php echo e($tutorial['title']); ?>" class="w-full h-full object-cover">
                    </div>
                    <?php else: ?>
                    <div class="h-48 bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center">
                        <svg class="w-24 h-24 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <?php endif; ?>
                    
                    <div class="p-6">
                        <div class="mb-3">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $category_colors[$tutorial['category']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo e(ucfirst($tutorial['category'])); ?>
                            </span>
                        </div>
                        
                        <h3 class="text-xl font-bold mb-3 text-gray-900"><?php echo e($tutorial['title']); ?></h3>
                        
                        <p class="text-gray-600 mb-4 text-sm">
                            <?php 
                            // Strip HTML tags, then escape for XSS protection
                            $clean_content = strip_tags($tutorial['content']);
                            echo truncate(e($clean_content), 120);
                            ?>
                        </p>
                        
                        <button 
                            onclick="showTutorial(<?php echo e($tutorial['id']); ?>)"
                            class="w-full px-6 py-3 gradient-purple-blue text-white rounded-lg font-semibold hover:shadow-lg transition"
                        >
                            Read More
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Tutorial Modal -->
    <div id="tutorial-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div id="tutorial-modal-content"></div>
        </div>
    </div>

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
    <script>
        const tutorials = <?php echo json_encode($tutorials); ?>;
        const categoryColors = <?php echo json_encode($category_colors); ?>;
        
        function showTutorial(tutorialId) {
            const tutorial = tutorials.find(t => t.id == tutorialId);
            if (!tutorial) return;
            
            const categoryColor = categoryColors[tutorial.category] || 'bg-gray-100 text-gray-800';
            
            const modalContent = `
                <div class="p-8">
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex-1 pr-4">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full ${categoryColor}">
                                ${tutorial.category.charAt(0).toUpperCase() + tutorial.category.slice(1)}
                            </span>
                            <h2 class="text-3xl font-bold mt-4 gradient-text">${tutorial.title}</h2>
                        </div>
                        <button onclick="closeTutorialModal()" class="text-gray-400 hover:text-gray-600 transition flex-shrink-0">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    ${tutorial.image_path ? `
                    <div class="mb-8 rounded-xl overflow-hidden">
                        <img src="${tutorial.image_path}" alt="${tutorial.title}" class="w-full h-64 object-cover">
                    </div>
                    ` : ''}

                    <div class="prose max-w-none tutorial-content">
                        ${tutorial.content}
                    </div>

                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <button onclick="closeTutorialModal()" class="px-8 py-3 gradient-purple-blue text-white rounded-lg font-semibold hover:shadow-lg transition">
                            Close
                        </button>
                    </div>
                </div>
            `;
            
            document.getElementById('tutorial-modal-content').innerHTML = modalContent;
            document.getElementById('tutorial-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeTutorialModal() {
            document.getElementById('tutorial-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        document.getElementById('tutorial-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeTutorialModal();
            }
        });

        <?php if ($selected_tutorial_id && $selected_tutorial): ?>
        showTutorial(<?php echo $selected_tutorial_id; ?>);
        <?php endif; ?>
    </script>
    <style>
        .tutorial-content h2 {
            font-size: 1.875rem;
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: #1f2937;
        }
        .tutorial-content h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            color: #374151;
        }
        .tutorial-content h4 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            color: #4b5563;
        }
        .tutorial-content p {
            margin-bottom: 1rem;
            line-height: 1.75;
            color: #4b5563;
        }
        .tutorial-content ul, .tutorial-content ol {
            margin-bottom: 1rem;
            padding-left: 2rem;
            color: #4b5563;
        }
        .tutorial-content li {
            margin-bottom: 0.5rem;
            line-height: 1.625;
        }
        .tutorial-content strong {
            font-weight: 600;
            color: #1f2937;
        }
        .tutorial-content code {
            background-color: #f3f4f6;
            padding: 0.125rem 0.375rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            font-family: monospace;
        }
    </style>
</body>
</html>
