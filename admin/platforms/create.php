<?php
/**
 * Platforms - Create Page
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_auth();
check_session_timeout();

$errors = [];
$form_data = [
    'name' => '',
    'description' => '',
    'website_url' => '',
    'rating' => '0.00',
    'pros' => '',
    'cons' => '',
    'display_order' => 0,
    'is_active' => 1
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ??  '')) {
        $errors[] = 'Invalid CSRF token. Please try again.';
    } else {
        // Get form data
        $form_data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'website_url' => trim($_POST['website_url'] ?? ''),
            'rating' => trim($_POST['rating'] ?? '0.00'),
            'pros' => trim($_POST['pros'] ?? ''),
            'cons' => trim($_POST['cons'] ?? ''),
            'display_order' => intval($_POST['display_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // Validate required fields
        if (empty($form_data['name'])) {
            $errors[] = 'Platform name is required.';
        }
        
        if (empty($form_data['website_url'])) {
            $errors[] = 'Website URL is required.';
        } elseif (!filter_var($form_data['website_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Please enter a valid website URL.';
        }
        
        // Validate rating
        $rating = floatval($form_data['rating']);
        if ($rating < 0 || $rating > 5) {
            $errors[] = 'Rating must be between 0.00 and 5.00.';
        }
        
        // Handle logo upload
        $logo_path = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload_result = upload_file($_FILES['logo']);
            if ($upload_result['success']) {
                $logo_path = $upload_result['path'];
            } else {
                $errors[] = 'Logo upload error: ' . $upload_result['error'];
            }
        }
        
        // If no errors, insert into database
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO platforms (
                        name, logo_path, description, website_url, rating, 
                        pros, cons, display_order, is_active
                    ) VALUES (
                        :name, :logo_path, :description, :website_url, :rating,
                        :pros, :cons, :display_order, :is_active
                    )
                ");
                
                $stmt->execute([
                    'name' => $form_data['name'],
                    'logo_path' => $logo_path,
                    'description' => $form_data['description'],
                    'website_url' => $form_data['website_url'],
                    'rating' => number_format($rating, 2, '.', ''),
                    'pros' => $form_data['pros'],
                    'cons' => $form_data['cons'],
                    'display_order' => $form_data['display_order'],
                    'is_active' => $form_data['is_active']
                ]);
                
                set_flash('success', 'Platform created successfully!');
                redirect('index.php');
            } catch (PDOException $e) {
                error_log("Platform creation error: " . $e->getMessage());
                $errors[] = 'Error creating platform. Please try again.';
                
                // Clean up uploaded file if database insert fails
                if ($logo_path) {
                    delete_file(basename($logo_path));
                }
            }
        }
    }
}

$page_title = 'Create Platform';
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
                    <h1 class="text-3xl font-bold text-gray-800">Create Platform</h1>
                    <p class="text-gray-600 mt-2">Add a new cryptocurrency payment platform</p>
                </div>
                <a href="index.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-medium transition duration-200">
                    ← Back to Platforms
                </a>
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
            
            <!-- Form -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-blue-600 px-8 py-6">
                    <h2 class="text-2xl font-bold text-white">Platform Information</h2>
                </div>
                
                <form method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
                    <?= csrf_field() ?>
                    
                    <!-- Basic Information -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Platform Name <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="<?= e($form_data['name']) ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                required
                            >
                        </div>
                        
                        <!-- Website URL -->
                        <div>
                            <label for="website_url" class="block text-sm font-semibold text-gray-700 mb-2">
                                Website URL <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="url" 
                                id="website_url" 
                                name="website_url" 
                                value="<?= e($form_data['website_url']) ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                placeholder="https://example.com"
                                required
                            >
                        </div>
                    </div>
                    
                    <!-- Logo Upload -->
                    <div>
                        <label for="logo" class="block text-sm font-semibold text-gray-700 mb-2">
                            Platform Logo
                        </label>
                        <input 
                            type="file" 
                            id="logo" 
                            name="logo" 
                            accept="image/*"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100"
                        >
                        <p class="mt-2 text-sm text-gray-500">Supported formats: JPG, PNG, GIF, WebP. Max size: 5MB</p>
                    </div>
                    
                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="4"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            placeholder="Brief description of the platform..."
                        ><?= e($form_data['description']) ?></textarea>
                    </div>
                    
                    <!-- Rating and Display Order -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Rating -->
                        <div>
                            <label for="rating" class="block text-sm font-semibold text-gray-700 mb-2">
                                Rating (0.00 - 5.00)
                            </label>
                            <input 
                                type="number" 
                                id="rating" 
                                name="rating" 
                                value="<?= e($form_data['rating']) ?>"
                                min="0" 
                                max="5" 
                                step="0.01"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            >
                            <p class="mt-2 text-sm text-gray-500">Enter a decimal value between 0.00 and 5.00</p>
                        </div>
                        
                        <!-- Display Order -->
                        <div>
                            <label for="display_order" class="block text-sm font-semibold text-gray-700 mb-2">
                                Display Order
                            </label>
                            <input 
                                type="number" 
                                id="display_order" 
                                name="display_order" 
                                value="<?= e($form_data['display_order']) ?>"
                                min="0"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            >
                            <p class="mt-2 text-sm text-gray-500">Lower numbers appear first</p>
                        </div>
                    </div>
                    
                    <!-- Pros and Cons -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Pros -->
                        <div>
                            <label for="pros" class="block text-sm font-semibold text-gray-700 mb-2">
                                Pros
                            </label>
                            <textarea 
                                id="pros" 
                                name="pros" 
                                rows="6"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                placeholder="Enter each pro on a new line or separated by |"
                            ><?= e($form_data['pros']) ?></textarea>
                            <p class="mt-2 text-sm text-gray-500">One per line or use | separator</p>
                        </div>
                        
                        <!-- Cons -->
                        <div>
                            <label for="cons" class="block text-sm font-semibold text-gray-700 mb-2">
                                Cons
                            </label>
                            <textarea 
                                id="cons" 
                                name="cons" 
                                rows="6"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                placeholder="Enter each con on a new line or separated by |"
                            ><?= e($form_data['cons']) ?></textarea>
                            <p class="mt-2 text-sm text-gray-500">One per line or use | separator</p>
                        </div>
                    </div>
                    
                    <!-- Status -->
                    <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg p-6 border border-purple-200">
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input 
                                type="checkbox" 
                                id="is_active" 
                                name="is_active" 
                                <?= $form_data['is_active'] ? 'checked' : '' ?>
                                class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-2 focus:ring-purple-500"
                            >
                            <span class="text-gray-700 font-medium">Platform is Active</span>
                        </label>
                        <p class="ml-8 mt-2 text-sm text-gray-600">
                            Active platforms will be displayed on the website
                        </p>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="index.php" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition duration-200">
                            Cancel
                        </a>
                        <button 
                            type="submit" 
                            class="px-8 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-medium rounded-lg shadow-lg transform hover:scale-105 transition duration-200"
                        >
                            Create Platform
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
