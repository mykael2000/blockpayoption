<?php
/**
 * Bank Payment Methods - Edit Bank Payment Method
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_auth();
check_session_timeout();

$errors = [];
$bank_method = null;

// Get bank payment method ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch existing bank payment method
if ($id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM bank_payment_methods WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $bank_method = $stmt->fetch();
        
        if (!$bank_method) {
            set_flash('error', 'Bank payment method not found.');
            redirect('index.php');
        }
    } catch (PDOException $e) {
        error_log("Bank payment method fetch error: " . $e->getMessage());
        set_flash('error', 'Error loading bank payment method.');
        redirect('index.php');
    }
} else {
    set_flash('error', 'Invalid bank payment method ID.');
    redirect('index.php');
}

// Initialize form data with existing values
$form_data = [
    'bank_name' => $bank_method['bank_name'],
    'account_holder_name' => $bank_method['account_holder_name'],
    'account_number' => $bank_method['account_number'],
    'routing_number' => $bank_method['routing_number'],
    'swift_bic_code' => $bank_method['swift_bic_code'],
    'bank_address' => $bank_method['bank_address'],
    'account_type' => $bank_method['account_type'],
    'currency' => $bank_method['currency'],
    'country' => $bank_method['country'],
    'instructions' => $bank_method['instructions'],
    'display_order' => $bank_method['display_order'],
    'is_active' => $bank_method['is_active']
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
        $form_data['swift_bic_code'] = trim($_POST['swift_bic_code'] ?? '');
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
        
        // Validate account number
        if ($form_data['account_number'] && !validateAccountNumber($form_data['account_number'])) {
            $errors[] = 'Invalid account number format. Use only letters and numbers.';
        }
        
        // Validate routing number if provided
        if ($form_data['routing_number'] && !validateRoutingNumber($form_data['routing_number'])) {
            $errors[] = 'Invalid routing number format. US routing numbers must be 9 digits.';
        }
        
        // Validate SWIFT code if provided
        if ($form_data['swift_bic_code'] && !validateSwiftCode($form_data['swift_bic_code'])) {
            $errors[] = 'Invalid SWIFT/BIC code format. Must be 8 or 11 characters.';
        }
        
        // Keep existing logo path
        $logo_path = $bank_method['logo_path'];
        
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
        
        // If no errors, update database
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE bank_payment_methods 
                    SET bank_name = :bank_name,
                        account_holder_name = :account_holder_name,
                        account_number = :account_number,
                        routing_number = :routing_number,
                        swift_bic_code = :swift_bic_code,
                        bank_address = :bank_address,
                        account_type = :account_type,
                        currency = :currency,
                        country = :country,
                        instructions = :instructions,
                        logo_path = :logo_path,
                        display_order = :display_order,
                        is_active = :is_active
                    WHERE id = :id
                ");
                
                $stmt->execute([
                    ':bank_name' => $form_data['bank_name'],
                    ':account_holder_name' => $form_data['account_holder_name'],
                    ':account_number' => $form_data['account_number'],
                    ':routing_number' => $form_data['routing_number'] ?: null,
                    ':swift_bic_code' => $form_data['swift_bic_code'] ?: null,
                    ':bank_address' => $form_data['bank_address'] ?: null,
                    ':account_type' => $form_data['account_type'],
                    ':currency' => $form_data['currency'],
                    ':country' => $form_data['country'] ?: null,
                    ':instructions' => $form_data['instructions'] ?: null,
                    ':logo_path' => $logo_path,
                    ':display_order' => $form_data['display_order'],
                    ':is_active' => $form_data['is_active'],
                    ':id' => $id
                ]);
                
                set_flash('success', 'Bank payment method updated successfully!');
                redirect('index.php');
                
            } catch (PDOException $e) {
                error_log("Bank payment method update error: " . $e->getMessage());
                $errors[] = 'Database error: Failed to update bank payment method.';
            }
        }
    }
}

$page_title = 'Edit Bank Payment Method';
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
                    <a href="index.php" class="text-gray-600 hover:text-green-600 transition">
                        ← Back to Bank Payment Methods
                    </a>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Edit Bank Payment Method</h1>
                <p class="text-gray-600 mt-2">Update bank transfer payment option details</p>
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
                <div class="bg-gradient-to-r from-green-600 to-blue-600 px-6 py-4">
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                                placeholder="e.g., Chase Bank, Bank of America"
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition font-mono"
                            placeholder="e.g., 1234567890 or GB29NWBK60161331926819"
                            required
                        >
                        <p class="text-xs text-gray-500 mt-1">Bank account number or IBAN</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition font-mono"
                                placeholder="e.g., 021000021"
                                pattern="[0-9]{9}"
                            >
                            <p class="text-xs text-gray-500 mt-1">9-digit routing number (US banks)</p>
                        </div>
                        
                        <!-- SWIFT/BIC Code -->
                        <div>
                            <label for="swift_bic_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                SWIFT/BIC Code
                            </label>
                            <input 
                                type="text" 
                                id="swift_bic_code" 
                                name="swift_bic_code" 
                                value="<?= e($form_data['swift_bic_code']) ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition font-mono uppercase"
                                placeholder="e.g., HSBCGB2L"
                                maxlength="11"
                            >
                            <p class="text-xs text-gray-500 mt-1">8 or 11 character SWIFT code</p>
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
                            rows="2"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                            placeholder="Bank's physical address (optional)"
                        ><?= e($form_data['bank_address']) ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">Full address of the bank branch</p>
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                            >
                                <option value="checking" <?= $form_data['account_type'] === 'checking' ? 'selected' : '' ?>>Checking</option>
                                <option value="savings" <?= $form_data['account_type'] === 'savings' ? 'selected' : '' ?>>Savings</option>
                                <option value="business" <?= $form_data['account_type'] === 'business' ? 'selected' : '' ?>>Business</option>
                            </select>
                        </div>
                        
                        <!-- Currency -->
                        <div>
                            <label for="currency" class="block text-sm font-semibold text-gray-700 mb-2">
                                Currency
                            </label>
                            <select 
                                id="currency" 
                                name="currency"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                            >
                                <option value="USD" <?= $form_data['currency'] === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                                <option value="EUR" <?= $form_data['currency'] === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                                <option value="GBP" <?= $form_data['currency'] === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
                                <option value="CAD" <?= $form_data['currency'] === 'CAD' ? 'selected' : '' ?>>CAD - Canadian Dollar</option>
                                <option value="AUD" <?= $form_data['currency'] === 'AUD' ? 'selected' : '' ?>>AUD - Australian Dollar</option>
                                <option value="JPY" <?= $form_data['currency'] === 'JPY' ? 'selected' : '' ?>>JPY - Japanese Yen</option>
                                <option value="CHF" <?= $form_data['currency'] === 'CHF' ? 'selected' : '' ?>>CHF - Swiss Franc</option>
                            </select>
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                                placeholder="e.g., United States"
                            >
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                            placeholder="Add any special instructions for customers making payments to this account..."
                        ><?= e($form_data['instructions']) ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">Include any important notes for customers (processing time, reference requirements, etc.)</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <!-- Logo Upload -->
                        <div class="md:col-span-2">
                            <label for="logo" class="block text-sm font-semibold text-gray-700 mb-2">
                                Bank Logo
                            </label>
                            <?php if ($bank_method['logo_path']): ?>
                                <div class="mb-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <p class="text-xs text-gray-600 mb-2">Current logo:</p>
                                    <img src="/<?= e($bank_method['logo_path']) ?>" alt="Current logo" class="w-20 h-20 object-cover rounded border border-gray-300">
                                </div>
                            <?php endif; ?>
                            <input 
                                type="file" 
                                id="logo" 
                                name="logo" 
                                accept="image/*"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                            >
                            <p class="text-xs text-gray-500 mt-1">Upload new logo to replace existing (PNG, JPG, max 5MB)</p>
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                                placeholder="0"
                            >
                            <p class="text-xs text-gray-500 mt-1">Lower = higher priority</p>
                        </div>
                    </div>
                    
                    <!-- Active Status -->
                    <div class="mt-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Status
                        </label>
                        <div class="flex items-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="is_active" 
                                    class="sr-only peer"
                                    <?= $form_data['is_active'] ? 'checked' : '' ?>
                                >
                                <div class="w-14 h-7 bg-gray-300 peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-green-600 peer-checked:to-blue-600"></div>
                                <span class="ml-3 text-sm font-medium text-gray-700">Active</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Only active methods are shown to customers</p>
                    </div>
                    
                    <!-- Metadata -->
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Created:</span>
                                <span class="text-gray-800 font-medium ml-2"><?= format_datetime($bank_method['created_at']) ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Last Updated:</span>
                                <span class="text-gray-800 font-medium ml-2"><?= format_datetime($bank_method['updated_at']) ?></span>
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
                            class="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white px-8 py-2 rounded-lg font-medium shadow-lg transform hover:scale-105 transition duration-200"
                        >
                            Update Bank Payment Method
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
                            <li>Changes to account details will affect all future payments</li>
                            <li>Uploading a new logo will replace the existing one</li>
                            <li>Changing display order affects how payment methods appear to customers</li>
                            <li>Deactivating a bank method hides it from customers but preserves all data</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
