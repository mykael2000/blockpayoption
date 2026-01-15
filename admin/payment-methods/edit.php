<?php
/**
 * Payment Methods - Edit Payment Method
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_auth();
check_session_timeout();

$errors = [];
$payment_method = null;

// Get payment method ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch existing payment method
if ($id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $payment_method = $stmt->fetch();
        
        if (!$payment_method) {
            set_flash('error', 'Payment method not found.');
            redirect('index.php');
        }
    } catch (PDOException $e) {
        error_log("Payment method fetch error: " . $e->getMessage());
        set_flash('error', 'Error loading payment method.');
        redirect('index.php');
    }
} else {
    set_flash('error', 'Invalid payment method ID.');
    redirect('index.php');
}

// Initialize form data with existing values
$form_data = [
    'name' => $payment_method['name'],
    'symbol' => $payment_method['symbol'],
    'wallet_address' => $payment_method['wallet_address'],
    'networks' => $payment_method['networks'],
    'description' => $payment_method['description'],
    'display_order' => $payment_method['display_order'],
    'is_active' => $payment_method['is_active']
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
        
        // Keep existing paths
        $logo_path = $payment_method['logo_path'];
        $qr_code_path = $payment_method['qr_code_path'];
        
        // Handle logo upload (if new file uploaded)
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload_result = upload_file($_FILES['logo']);
            if ($upload_result['success']) {
                // Delete old logo if exists
                if ($logo_path) {
                    delete_file(basename($logo_path));
                }
                $logo_path = $upload_result['path'];
            } else {
                $errors[] = 'Logo: ' . $upload_result['error'];
            }
        }
        
        // Handle QR code upload (if new file uploaded)
        if (isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] === UPLOAD_ERR_OK) {
            $upload_result = upload_file($_FILES['qr_code']);
            if ($upload_result['success']) {
                // Delete old QR code if exists
                if ($qr_code_path) {
                    delete_file(basename($qr_code_path));
                }
                $qr_code_path = $upload_result['path'];
            } else {
                $errors[] = 'QR Code: ' . $upload_result['error'];
            }
        }
        
        // If no errors, update database
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE payment_methods 
                    SET name = :name,
                        symbol = :symbol,
                        logo_path = :logo_path,
                        wallet_address = :wallet_address,
                        qr_code_path = :qr_code_path,
                        networks = :networks,
                        description = :description,
                        display_order = :display_order,
                        is_active = :is_active
                    WHERE id = :id
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
                    ':is_active' => $form_data['is_active'],
                    ':id' => $id
                ]);
                
                set_flash('success', 'Payment method updated successfully!');
                redirect('index.php');
                
            } catch (PDOException $e) {
                error_log("Payment method update error: " . $e->getMessage());
                $errors[] = 'Database error: Failed to update payment method.';
            }
        }
    }
}

$page_title = 'Edit Payment Method';
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
                <h1 class="text-3xl font-bold text-gray-800">Edit Payment Method</h1>
                <p class="text-gray-600 mt-2">Update cryptocurrency payment option details</p>
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
                            <?php if ($payment_method['logo_path']): ?>
                                <div class="mb-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <p class="text-xs text-gray-600 mb-2">Current logo:</p>
                                    <img src="/<?= e($payment_method['logo_path']) ?>" alt="Current logo" class="w-20 h-20 object-cover rounded border border-gray-300">
                                </div>
                            <?php endif; ?>
                            <input 
                                type="file" 
                                id="logo" 
                                name="logo" 
                                accept="image/*"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100"
                            >
                            <p class="text-xs text-gray-500 mt-1">Upload new logo to replace existing (PNG, JPG, max 5MB)</p>
                        </div>
                        
                        <!-- QR Code Upload -->
                        <div>
                            <label for="qr_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                QR Code Image
                            </label>
                            <?php if ($payment_method['qr_code_path']): ?>
                                <div class="mb-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <p class="text-xs text-gray-600 mb-2">Current QR code:</p>
                                    <img src="/<?= e($payment_method['qr_code_path']) ?>" alt="Current QR code" class="w-20 h-20 object-cover rounded border border-gray-300">
                                </div>
                            <?php endif; ?>
                            <input 
                                type="file" 
                                id="qr_code" 
                                name="qr_code" 
                                accept="image/*"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            >
                            <p class="text-xs text-gray-500 mt-1">Upload new QR code to replace existing (PNG, JPG, max 5MB)</p>
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
                    
                    <!-- Metadata -->
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Created:</span>
                                <span class="text-gray-800 font-medium ml-2"><?= format_datetime($payment_method['created_at']) ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Last Updated:</span>
                                <span class="text-gray-800 font-medium ml-2"><?= format_datetime($payment_method['updated_at']) ?></span>
                            </div>
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
                            Update Payment Method
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Help Section -->
            <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded shadow">
                <div class="flex items-start">
                    <div class="text-blue-500 text-xl mr-3">💡</div>
                    <div class="text-sm text-blue-800">
                        <h4 class="font-semibold mb-1">Important Notes:</h4>
                        <ul class="list-disc list-inside space-y-1 text-blue-700">
                            <li>Changes to wallet address will affect all future payments</li>
                            <li>Uploading new images will replace the existing ones</li>
                            <li>Changing display order affects how payment methods appear to customers</li>
                            <li>Deactivating a payment method hides it from customers but preserves all data</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
