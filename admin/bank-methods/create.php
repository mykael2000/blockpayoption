<?php
/**
 * Bank Payment Methods - Create New Bank Payment Method
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_auth();
check_session_timeout();

$errors = [];
$form_data = [
    'bank_name' => '',
    'account_holder_name' => '',
    'account_number' => '',
    'routing_number' => '',
    'swift_code' => '',
    'iban' => '',
    'bank_address' => '',
    'account_type' => 'checking',
    'currency' => 'USD',
    'country' => '',
    'instructions' => '',
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
        $form_data['bank_name'] = trim($_POST['bank_name'] ?? '');
        $form_data['account_holder_name'] = trim($_POST['account_holder_name'] ?? '');
        $form_data['account_number'] = trim($_POST['account_number'] ?? '');
        $form_data['routing_number'] = trim($_POST['routing_number'] ?? '');
        $form_data['swift_code'] = trim($_POST['swift_code'] ?? '');
        $form_data['iban'] = trim($_POST['iban'] ?? '');
        $form_data['bank_address'] = trim($_POST['bank_address'] ?? '');
        $form_data['account_type'] = trim($_POST['account_type'] ?? 'checking');
        $form_data['currency'] = trim($_POST['currency'] ?? 'USD');
        $form_data['country'] = trim($_POST['country'] ?? '');
        $form_data['instructions'] = trim($_POST['instructions'] ?? '');
        $form_data['display_order'] = (int)($_POST['display_order'] ?? 0);
        $form_data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
        
        // Validate required fields
        $required_errors = validate_required(['bank_name', 'account_holder_name', 'account_number'], $form_data);
        $errors = array_merge($errors, $required_errors);
        
        // Validate SWIFT code if provided
        if ($form_data['swift_code'] && !validateSwiftCode($form_data['swift_code'])) {
            $errors[] = 'Invalid SWIFT/BIC code format. Should be 8 or 11 characters (e.g., HBUKGB4B)';
        }
        
        // Validate IBAN if provided
        if ($form_data['iban'] && !validateIBAN($form_data['iban'])) {
            $errors[] = 'Invalid IBAN format. Please check the account number.';
        }
        
        // Validate routing number if provided
        if ($form_data['routing_number'] && !validateRoutingNumber($form_data['routing_number'])) {
            $errors[] = 'Invalid routing number format. Should be 6-11 digits.';
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
        
        // If no errors, insert into database
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO bank_payment_methods 
                    (bank_name, account_holder_name, account_number, routing_number, swift_code, iban, 
                     bank_address, account_type, currency, country, instructions, logo_path, display_order, is_active) 
                    VALUES 
                    (:bank_name, :account_holder_name, :account_number, :routing_number, :swift_code, :iban,
                     :bank_address, :account_type, :currency, :country, :instructions, :logo_path, :display_order, :is_active)
                ");
                
                $stmt->execute([
                    ':bank_name' => $form_data['bank_name'],
                    ':account_holder_name' => $form_data['account_holder_name'],
                    ':account_number' => $form_data['account_number'],
                    ':routing_number' => $form_data['routing_number'] ?: null,
                    ':swift_code' => $form_data['swift_code'] ?: null,
                    ':iban' => $form_data['iban'] ?: null,
                    ':bank_address' => $form_data['bank_address'] ?: null,
                    ':account_type' => $form_data['account_type'],
                    ':currency' => $form_data['currency'],
                    ':country' => $form_data['country'] ?: null,
                    ':instructions' => $form_data['instructions'] ?: null,
                    ':logo_path' => $logo_path,
                    ':display_order' => $form_data['display_order'],
                    ':is_active' => $form_data['is_active']
                ]);
                
                set_flash('success', 'Bank payment method created successfully!');
                redirect('index.php');
                
            } catch (PDOException $e) {
                error_log("Bank payment method creation error: " . $e->getMessage());
                
                // Clean up uploaded file on error
                if ($logo_path) delete_file(basename($logo_path));
                
                $errors[] = 'Database error: Failed to create bank payment method.';
            }
        }
    }
}

$page_title = 'Add Bank Payment Method';
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
                    <a href="index.php" class="text-gray-600 hover:text-emerald-600 transition">
                        ← Back to Bank Payment Methods
                    </a>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Add New Bank Payment Method</h1>
                <p class="text-gray-600 mt-2">Create a new bank transfer payment option</p>
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
                <div class="bg-gradient-to-r from-emerald-600 to-green-600 px-6 py-4">
                    <h2 class="text-xl font-semibold text-white">Bank Account Details</h2>
                </div>
                
                <form method="POST" enctype="multipart/form-data" class="p-6">
                    <?= csrf_field() ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Bank Name -->
                        <div>
                            <label for="bank_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Bank Name <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="bank_name" 
                                name="bank_name" 
                                value="<?= e($form_data['bank_name']) ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                                placeholder="e.g., Chase Bank, HSBC"
                                required
                            >
                            <p class="text-xs text-gray-500 mt-1">Name of the financial institution</p>
                        </div>
                        
                        <!-- Account Holder Name -->
                        <div>
                            <label for="account_holder_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Account Holder Name <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="account_holder_name" 
                                name="account_holder_name" 
                                value="<?= e($form_data['account_holder_name']) ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                                placeholder="e.g., BlockPayOption LLC"
                                required
                            >
                            <p class="text-xs text-gray-500 mt-1">Name on the bank account</p>
                        </div>
                    </div>
                    
                    <!-- Account Number -->
                    <div class="mt-6">
                        <label for="account_number" class="block text-sm font-semibold text-gray-700 mb-2">
                            Account Number <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="account_number" 
                            name="account_number" 
                            value="<?= e($form_data['account_number']) ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition font-mono text-sm"
                            placeholder="e.g., 123456789012"
                            required
                        >
                        <p class="text-xs text-gray-500 mt-1">Bank account number or IBAN</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <!-- Routing Number -->
                        <div>
                            <label for="routing_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                Routing Number
                            </label>
                            <input 
                                type="text" 
                                id="routing_number" 
                                name="routing_number" 
                                value="<?= e($form_data['routing_number']) ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition font-mono"
                                placeholder="e.g., 021000021"
                            >
                            <p class="text-xs text-gray-500 mt-1">ABA/ACH routing number (US)</p>
                        </div>
                        
                        <!-- SWIFT Code -->
                        <div>
                            <label for="swift_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                SWIFT/BIC Code
                            </label>
                            <input 
                                type="text" 
                                id="swift_code" 
                                name="swift_code" 
                                value="<?= e($form_data['swift_code']) ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition font-mono uppercase"
                                placeholder="e.g., HBUKGB4B"
                                pattern="[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?"
                            >
                            <p class="text-xs text-gray-500 mt-1">For international transfers</p>
                        </div>
                        
                        <!-- IBAN -->
                        <div>
                            <label for="iban" class="block text-sm font-semibold text-gray-700 mb-2">
                                IBAN
                            </label>
                            <input 
                                type="text" 
                                id="iban" 
                                name="iban" 
                                value="<?= e($form_data['iban']) ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition font-mono uppercase"
                                placeholder="e.g., GB29NWBK60161331926819"
                            >
                            <p class="text-xs text-gray-500 mt-1">International Bank Account Number</p>
                        </div>
                    </div>
                    
                    <!-- Bank Address -->
                    <div class="mt-6">
                        <label for="bank_address" class="block text-sm font-semibold text-gray-700 mb-2">
                            Bank Address
                        </label>
                        <textarea 
                            id="bank_address" 
                            name="bank_address" 
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                            placeholder="Full address of the bank branch..."
                        ><?= e($form_data['bank_address']) ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">Complete bank branch address (optional)</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <!-- Account Type -->
                        <div>
                            <label for="account_type" class="block text-sm font-semibold text-gray-700 mb-2">
                                Account Type
                            </label>
                            <select 
                                id="account_type" 
                                name="account_type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                            >
                                <option value="checking" <?= $form_data['account_type'] === 'checking' ? 'selected' : '' ?>>Checking</option>
                                <option value="savings" <?= $form_data['account_type'] === 'savings' ? 'selected' : '' ?>>Savings</option>
                                <option value="business" <?= $form_data['account_type'] === 'business' ? 'selected' : '' ?>>Business</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Type of bank account</p>
                        </div>
                        
                        <!-- Currency -->
                        <div>
                            <label for="currency" class="block text-sm font-semibold text-gray-700 mb-2">
                                Currency
                            </label>
                            <input 
                                type="text" 
                                id="currency" 
                                name="currency" 
                                value="<?= e($form_data['currency']) ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition uppercase"
                                placeholder="e.g., USD, EUR, GBP"
                                maxlength="10"
                            >
                            <p class="text-xs text-gray-500 mt-1">Account currency code</p>
                        </div>
                        
                        <!-- Country -->
                        <div>
                            <label for="country" class="block text-sm font-semibold text-gray-700 mb-2">
                                Country
                            </label>
                            <input 
                                type="text" 
                                id="country" 
                                name="country" 
                                value="<?= e($form_data['country']) ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                                placeholder="e.g., United States, United Kingdom"
                            >
                            <p class="text-xs text-gray-500 mt-1">Country where bank is located</p>
                        </div>
                    </div>
                    
                    <!-- Instructions -->
                    <div class="mt-6">
                        <label for="instructions" class="block text-sm font-semibold text-gray-700 mb-2">
                            Payment Instructions
                        </label>
                        <textarea 
                            id="instructions" 
                            name="instructions" 
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                            placeholder="Special instructions for customers making bank transfers..."
                        ><?= e($form_data['instructions']) ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">Additional instructions for customers (e.g., reference to include, processing time)</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <!-- Logo Upload -->
                        <div>
                            <label for="logo" class="block text-sm font-semibold text-gray-700 mb-2">
                                Bank Logo
                            </label>
                            <input 
                                type="file" 
                                id="logo" 
                                name="logo" 
                                accept="image/*"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
                            >
                            <p class="text-xs text-gray-500 mt-1">Upload bank logo (PNG, JPG, max 5MB)</p>
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                                placeholder="0"
                            >
                            <p class="text-xs text-gray-500 mt-1">Lower numbers appear first (0 = highest priority)</p>
                        </div>
                    </div>
                    
                    <!-- Active Status -->
                    <div class="mt-6">
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
                                <div class="w-14 h-7 bg-gray-300 peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-emerald-600 peer-checked:to-green-600"></div>
                                <span class="ml-3 text-sm font-medium text-gray-700">Active</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Only active methods are shown to customers</p>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-end space-x-4">
                        <a href="index.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                            Cancel
                        </a>
                        <button 
                            type="submit" 
                            class="bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white px-8 py-2 rounded-lg font-medium shadow-lg transform hover:scale-105 transition duration-200"
                        >
                            Create Bank Payment Method
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Help Section -->
            <div class="mt-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded shadow">
                <div class="flex items-start">
                    <div class="text-emerald-500 text-xl mr-3">💡</div>
                    <div class="text-sm text-emerald-800">
                        <h4 class="font-semibold mb-1">Tips for Adding Bank Payment Methods:</h4>
                        <ul class="list-disc list-inside space-y-1 text-emerald-700">
                            <li>Double-check all account numbers and routing details to ensure accuracy</li>
                            <li>SWIFT/BIC codes are required for international wire transfers</li>
                            <li>IBAN format is commonly used in Europe and other international regions</li>
                            <li>Provide clear payment instructions including reference format and processing times</li>
                            <li>Bank logos help customers quickly identify supported payment methods</li>
                            <li>Keep sensitive bank information secure and only share what's necessary</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
