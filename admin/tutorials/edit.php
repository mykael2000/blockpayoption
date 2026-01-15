<?php
/**
 * Tutorials - Edit Page
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_auth();
check_session_timeout();

$errors = [];
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

// Set form values from database
$title = $tutorial['title'];
$slug = $tutorial['slug'];
$content = $tutorial['content'];
$category = $tutorial['category'];
$display_order = $tutorial['display_order'];
$is_published = $tutorial['is_published'];
$current_image = $tutorial['image_path'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        set_flash('error', 'Invalid security token. Please try again.');
        redirect('edit.php?id=' . $tutorial_id);
    }
    
    // Get form data
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? 'general');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    
    // Validate required fields
    $errors = validate_required(['title', 'content', 'category'], $_POST);
    
    // Auto-generate slug if empty
    if (empty($slug)) {
        $slug = create_slug($title);
    } else {
        $slug = create_slug($slug);
    }
    
    // Validate category
    $allowed_categories = ['beginner', 'intermediate', 'advanced', 'general'];
    if (!in_array($category, $allowed_categories)) {
        $errors[] = 'Invalid category selected';
    }
    
    // Check if slug is unique (excluding current tutorial)
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM tutorials WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $tutorial_id]);
            if ($stmt->fetch()) {
                $errors[] = 'A tutorial with this slug already exists';
            }
        } catch (PDOException $e) {
            error_log("Slug check error: " . $e->getMessage());
            $errors[] = 'Error checking slug uniqueness';
        }
    }
    
    // Handle image upload
    $image_path = $current_image;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = upload_file($_FILES['image']);
        if ($upload_result['success']) {
            // Delete old image if exists
            if ($current_image) {
                delete_file(basename($current_image));
            }
            $image_path = $upload_result['path'];
        } else {
            $errors[] = $upload_result['error'];
        }
    }
    
    // Handle image removal
    if (isset($_POST['remove_image']) && $current_image) {
        delete_file(basename($current_image));
        $image_path = null;
    }
    
    // Update tutorial if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE tutorials 
                SET title = ?, slug = ?, content = ?, category = ?, image_path = ?, 
                    display_order = ?, is_published = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $title,
                $slug,
                $content,
                $category,
                $image_path,
                $display_order,
                $is_published,
                $tutorial_id
            ]);
            
            set_flash('success', 'Tutorial updated successfully!');
            redirect('index.php');
        } catch (PDOException $e) {
            error_log("Tutorial update error: " . $e->getMessage());
            $errors[] = 'Error updating tutorial. Please try again.';
        }
    }
}

$page_title = 'Edit Tutorial';
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
                        <h1 class="text-3xl font-bold text-gray-800">Edit Tutorial</h1>
                        <p class="text-gray-600 mt-2">Update tutorial information</p>
                    </div>
                </div>
            </div>
            
            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded shadow">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= e($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Form -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <?= csrf_field() ?>
                    
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            value="<?= e($title) ?>" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            placeholder="Enter tutorial title"
                        >
                        <p class="mt-1 text-sm text-gray-500">The main title of the tutorial</p>
                    </div>
                    
                    <!-- Slug -->
                    <div>
                        <label for="slug" class="block text-sm font-semibold text-gray-700 mb-2">
                            Slug
                        </label>
                        <input 
                            type="text" 
                            id="slug" 
                            name="slug" 
                            value="<?= e($slug) ?>" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition font-mono text-sm"
                            placeholder="auto-generated-from-title"
                        >
                        <p class="mt-1 text-sm text-gray-500">URL-friendly identifier (auto-generated if left empty)</p>
                    </div>
                    
                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="category" 
                            name="category" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                        >
                            <option value="beginner" <?= $category === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                            <option value="intermediate" <?= $category === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                            <option value="advanced" <?= $category === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                            <option value="general" <?= $category === 'general' ? 'selected' : '' ?>>General</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Difficulty level or type of tutorial</p>
                    </div>
                    
                    <!-- Content -->
                    <div>
                        <label for="content" class="block text-sm font-semibold text-gray-700 mb-2">
                            Content <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="content" 
                            name="content" 
                            rows="15" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition font-mono text-sm"
                            placeholder="Enter tutorial content (HTML supported)"
                        ><?= e($content) ?></textarea>
                        <p class="mt-1 text-sm text-gray-500">HTML tags are supported. Paste your formatted content here.</p>
                    </div>
                    
                    <!-- Current Image -->
                    <?php if ($current_image): ?>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Current Image
                            </label>
                            <div class="flex items-start space-x-4">
                                <img src="/<?= e($current_image) ?>" alt="Current" class="w-32 h-32 object-cover rounded-lg border-2 border-gray-200 shadow">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        name="remove_image" 
                                        value="1"
                                        class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500"
                                    >
                                    <span class="text-sm text-red-600 font-medium">Remove current image</span>
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Image Upload -->
                    <div>
                        <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">
                            <?= $current_image ? 'Replace Image' : 'Tutorial Image' ?>
                        </label>
                        <div class="mt-1 flex items-center">
                            <input 
                                type="file" 
                                id="image" 
                                name="image" 
                                accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 transition"
                            >
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Upload a featured image for the tutorial (JPEG, PNG, GIF, WebP - Max 5MB)</p>
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
                            value="<?= e($display_order) ?>" 
                            min="0"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            placeholder="0"
                        >
                        <p class="mt-1 text-sm text-gray-500">Lower numbers appear first (0 = default)</p>
                    </div>
                    
                    <!-- Is Published -->
                    <div class="border-t pt-6">
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input 
                                type="checkbox" 
                                id="is_published" 
                                name="is_published" 
                                value="1"
                                <?= $is_published ? 'checked' : '' ?>
                                class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500 focus:ring-2 transition"
                            >
                            <span class="text-sm font-semibold text-gray-700">Publish this tutorial</span>
                        </label>
                        <p class="mt-1 ml-8 text-sm text-gray-500">Make this tutorial visible to the public</p>
                    </div>
                    
                    <!-- Metadata -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Metadata</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                            <div>
                                <span class="font-medium">Created:</span> 
                                <?= format_datetime($tutorial['created_at']) ?>
                            </div>
                            <div>
                                <span class="font-medium">Last Updated:</span> 
                                <?= format_datetime($tutorial['updated_at']) ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="flex items-center space-x-4 pt-6 border-t">
                        <button 
                            type="submit" 
                            class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-8 py-3 rounded-lg font-medium shadow-lg transform hover:scale-105 transition duration-200"
                        >
                            Update Tutorial
                        </button>
                        <a 
                            href="index.php" 
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-8 py-3 rounded-lg font-medium transition"
                        >
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
