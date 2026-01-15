<?php
/**
 * Payment Methods - Create New Payment Method
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
    'symbol' => '',
    'wallet_address' => '',
    'networks' => '',
    'description' => '',
    'display_order' => 0,
    'is_active' => 1
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verify_csrf_token($_POST[CSRF_TOKEN_NAME])) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Get form data
        $form_data['name'] = trim($_POST['name'] ?? '');
        $form_data['symbol'] = trim($_POST['symbol'] ?? '');
        $form_data['wallet_address'] = trim($_POST['wallet_address'] ?? '');
        $form_data['networks'] = trim($_POST['networks'] ?? '');
        $form_data['description'] = trim($_POST['description'] ?? '');
        $form_data['display_order'] = (int)($_POST['display_order'] ?? 0);
        $form_data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
        
        // Validate required fields
        $required_errors = validate_required(['name', 'symbol', 'wallet_address'], $form_data);
        $errors = array_merge($errors, $required_errors);
        
        // Validate symbol (should be uppercase, no spaces)
        if ($form_data['symbol'] && !preg_match('/^[A-Z0-9]+$/', $form_data['symbol'])) {
            $errors[] = 'Symbol must contain only uppercase letters and numbers (e.g., BTC, ETH, USDT)';
        }
        
        // Handle logo upload
        $logo_path = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload_result = upload_file($_FILES['logo']);
            if ($upload_result['success']) {
                $logo_path = $upload_result['path'];
            } else {
                $errors[] = 'Logo: ' . $upload_result['error'];
            }
        }
        
        // Handle QR code upload
        $qr_code_path = null;
        if (isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] === UPLOAD_ERR_OK) {
            $upload_result = upload_file($_FILES['qr_code']);
            if ($upload_result['success']) {
                $qr_code_path = $upload_result['path'];
            } else {
                $errors[] = 'QR Code: ' . $upload_result['error'];
            }
        }
        
        // If no errors, insert into database
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO payment_methods 
                    (name, symbol, logo_path, wallet_address, qr_code_path, networks, description, display_order, is_active) 
                    VALUES 
                    (:name, :symbol, :logo_path, :wallet_address, :qr_code_path, :networks, :description, :display_order, :is_active)
                ");
                
                $stmt->execute([
                    ':name' => $form_data['name'],
                    ':symbol' => $form_data['symbol'],
                    ':logo_path' => $logo_path,
                    ':wallet_address' => $form_data['wallet_address'],
                    ':qr_code_path' => $qr_code_path,
                    ':networks' => $form_data['networks'],
                    ':description' => $form_data['description'],
                    ':display_order' => $form_data['display_order'],
                    ':is_active' => $form_data['is_active']
                ]);
                
                set_flash('success', 'Payment method created successfully!');
                redirect('index.php');
                
            } catch (PDOException $e) {
                error_log("Payment method creation error: " . $e->getMessage());
                
                // Clean up uploaded files on error
                if ($logo_path) delete_file(basename($logo_path));
                if ($qr_code_path) delete_file(basename($qr_code_path));
                
                $errors[] = 'Database error: Failed to create payment method.';
            }
        }
    }
}

$page_title = 'Add Payment Method';
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
                <div class="flex items-center space-x-4 mb-4">
                    <a href="index.php" class="text-gray-600 hover:text-purple-600 transition">
                        ← Back to Payment Methods
                    </a>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Add New Payment Method</h1>
                <p class="text-gray-600 mt-2">Create a new cryptocurrency payment option</p>
            </div>
            
            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded shadow">
                    <div class="flex items-start">
                        <div class="text-red-500 text-xl mr-3">⚠️</div>
                        <div>
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
            
            <!-- Form Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-blue-600 px-6 py-4">
                    <h2 class="text-xl font-semibold text-white">Payment Method Details</h2>
                </div>
                
                <form method="POST" enctype="multipart/form-data" class="p-6">
                    <?= csrf_field() ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="<?= e($form_data['name']) ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                placeholder="e.g., Bitcoin, Ethereum"
                                required
                            >
                            <p class="text-xs text-gray-500 mt-1">Full name of the cryptocurrency</p>
                        </div>
                        
                        <!-- Symbol -->
                        <div>
                            <label for="symbol" class="block text-sm font-semibold text-gray-700 mb-2">
                                Symbol <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="symbol" 
                                name="symbol" 
                                value="<?= e($form_data['symbol']) ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition uppercase"
                                placeholder="e.g., BTC, ETH, USDT"
                                pattern="[A-Z0-9]+"
                                required
                            >
                            <p class="text-xs text-gray-500 mt-1">Uppercase ticker symbol (e.g., BTC, ETH)</p>
                        </div>
                    </div>
                    
                    <!-- Wallet Address -->
                    <div class="mt-6">
                        <label for="wallet_address" class="block text-sm font-semibold text-gray-700 mb-2">
                            Wallet Address <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="wallet_address" 
                            name="wallet_address" 
                            value="<?= e($form_data['wallet_address']) ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition font-mono text-sm"
                            placeholder="e.g., bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh"
                            required
                        >
                        <p class="text-xs text-gray-500 mt-1">Your receiving wallet address for this cryptocurrency</p>
                    </div>
                    
                    <!-- Networks -->
                    <div class="mt-6">
                        <label for="networks" class="block text-sm font-semibold text-gray-700 mb-2">
                            Supported Networks
                        </label>
                        <input 
                            type="text" 
                            id="networks" 
                            name="networks" 
                            value="<?= e($form_data['networks']) ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            placeholder="e.g., Ethereum Mainnet, Polygon, BSC"
                        >
                        <p class="text-xs text-gray-500 mt-1">Comma-separated list of supported networks/blockchains</p>
                    </div>
                    
                    <!-- Description -->
                    <div class="mt-6">
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            placeholder="Brief description of this payment method..."
                        ><?= e($form_data['description']) ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">Optional description to help users understand this payment method</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <!-- Logo Upload -->
                        <div>
                            <label for="logo" class="block text-sm font-semibold text-gray-700 mb-2">
                                Logo Image
                            </label>
                            <input 
                                type="file" 
                                id="logo" 
                                name="logo" 
                                accept="image/*"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100"
                            >
                            <p class="text-xs text-gray-500 mt-1">Upload cryptocurrency logo (PNG, JPG, max 5MB)</p>
                        </div>
                        
                        <!-- QR Code Upload -->
                        <div>
                            <label for="qr_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                QR Code Image
                            </label>
                            <input 
                                type="file" 
                                id="qr_code" 
                                name="qr_code" 
                                accept="image/*"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            >
                            <p class="text-xs text-gray-500 mt-1">Upload QR code for wallet address (PNG, JPG, max 5MB)</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                placeholder="0"
                            >
                            <p class="text-xs text-gray-500 mt-1">Lower numbers appear first (0 = highest priority)</p>
                        </div>
                        
                        <!-- Active Status -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Status
                            </label>
                            <div class="flex items-center h-10">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        name="is_active" 
                                        class="sr-only peer"
                                        <?= $form_data['is_active'] ? 'checked' : '' ?>
                                    >
                                    <div class="w-14 h-7 bg-gray-300 peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-purple-600 peer-checked:to-blue-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-700">Active</span>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Only active methods are shown to customers</p>
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-end space-x-4">
                        <a href="index.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                            Cancel
                        </a>
                        <button 
                            type="submit" 
                            class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-8 py-2 rounded-lg font-medium shadow-lg transform hover:scale-105 transition duration-200"
                        >
                            Create Payment Method
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Help Section -->
            <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded shadow">
                <div class="flex items-start">
                    <div class="text-blue-500 text-xl mr-3">💡</div>
                    <div class="text-sm text-blue-800">
                        <h4 class="font-semibold mb-1">Tips for Adding Payment Methods:</h4>
                        <ul class="list-disc list-inside space-y-1 text-blue-700">
                            <li>Use clear, recognizable cryptocurrency names (Bitcoin, Ethereum, etc.)</li>
                            <li>Symbols should match standard ticker symbols (BTC, ETH, USDT)</li>
                            <li>Double-check wallet addresses - incorrect addresses can result in lost funds</li>
                            <li>Upload logo images for better user recognition</li>
                            <li>QR codes make it easier for customers to send payments</li>
                            <li>List all supported networks to avoid wrong network transfers</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
