<?php
/**
 * Bank Payment Methods - Delete Bank Payment Method
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_auth();
check_session_timeout();

// Get bank payment method ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    set_flash('error', 'Invalid bank payment method ID.');
    redirect('index.php');
}

// Fetch bank payment method to verify it exists
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

// Check if there are associated payment links
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM payment_links WHERE bank_payment_method_id = :id");
    $stmt->execute([':id' => $id]);
    $link_count = $stmt->fetch()['count'];
} catch (PDOException $e) {
    error_log("Payment links check error: " . $e->getMessage());
    $link_count = 0;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verify_csrf_token($_POST[CSRF_TOKEN_NAME])) {
        set_flash('error', 'Invalid security token. Please try again.');
        redirect('index.php');
    }
    
    // Perform deletion
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Delete the bank payment method (payment_links will be cascade deleted due to foreign key)
        $stmt = $pdo->prepare("DELETE FROM bank_payment_methods WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        // Delete associated logo file
        if ($bank_method['logo_path']) {
            delete_file(basename($bank_method['logo_path']));
        }
        
        // Commit transaction
        $pdo->commit();
        
        set_flash('success', 'Bank payment method "' . $bank_method['bank_name'] . '" has been deleted successfully.');
        redirect('index.php');
        
    } catch (PDOException $e) {
        // Rollback on error
        $pdo->rollBack();
        error_log("Bank payment method deletion error: " . $e->getMessage());
        set_flash('error', 'Database error: Failed to delete bank payment method.');
        redirect('index.php');
    }
}

$page_title = 'Delete Bank Payment Method';
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
                <h1 class="text-3xl font-bold text-gray-800">Delete Bank Payment Method</h1>
                <p class="text-gray-600 mt-2">Confirm deletion of bank payment method</p>
            </div>
            
            <!-- Confirmation Card -->
            <div class="max-w-2xl">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border-2 border-red-200">
                    <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <span class="text-3xl">⚠️</span>
                            <h2 class="text-xl font-semibold text-white">Confirm Deletion</h2>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <!-- Bank Payment Method Details -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center space-x-4">
                                <?php if ($bank_method['logo_path']): ?>
                                    <img src="/<?= e($bank_method['logo_path']) ?>" alt="<?= e($bank_method['bank_name']) ?>" class="w-16 h-16 rounded-full object-cover border-2 border-gray-300">
                                <?php else: ?>
                                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center text-white font-bold text-2xl">
                                        <?= strtoupper(substr(e($bank_method['bank_name']), 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-800"><?= e($bank_method['bank_name']) ?></h3>
                                    <p class="text-gray-600">
                                        <span class="font-semibold"><?= e($bank_method['account_holder_name']) ?></span>
                                        <span class="mx-2">•</span>
                                        <code class="text-xs bg-gray-200 px-2 py-1 rounded">
                                            <?php 
                                            $maskedAccount = maskAccountNumber($bank_method['account_number']);
                                            echo e($maskedAccount);
                                            ?>
                                        </code>
                                    </p>
                                    <?php if ($bank_method['country']): ?>
                                        <p class="text-sm text-gray-500 mt-1">
                                            <?= e($bank_method['country']) ?>
                                            <?php if ($bank_method['currency']): ?>
                                                <span class="mx-2">•</span>
                                                <?= e($bank_method['currency']) ?>
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Warning Message -->
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                            <div class="flex items-start">
                                <div class="text-red-500 text-xl mr-3 mt-1">🔥</div>
                                <div>
                                    <h4 class="text-red-800 font-semibold mb-2">Warning: This action cannot be undone!</h4>
                                    <p class="text-red-700 text-sm mb-3">
                                        Deleting this bank payment method will:
                                    </p>
                                    <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                                        <li>Permanently remove the bank payment method from your system</li>
                                        <li>Delete associated logo image if present</li>
                                        <?php if ($link_count > 0): ?>
                                            <li class="font-semibold">Affect <?= $link_count ?> associated payment link<?= $link_count !== 1 ? 's' : '' ?></li>
                                        <?php endif; ?>
                                        <li>This data cannot be recovered once deleted</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($link_count > 0): ?>
                            <!-- Associated Payment Links Warning -->
                            <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                                <div class="flex items-start">
                                    <div class="text-yellow-500 text-xl mr-3 mt-1">⚡</div>
                                    <div>
                                        <h4 class="text-yellow-800 font-semibold mb-1">Associated Payment Links</h4>
                                        <p class="text-yellow-700 text-sm">
                                            This bank payment method has <strong><?= $link_count ?> payment link<?= $link_count !== 1 ? 's' : '' ?></strong> associated with it. 
                                            Deleting this method may affect these payment links. The foreign key constraint will set the bank_payment_method_id to NULL.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Metadata -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Bank Payment Method Information:</h4>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-gray-600">Created:</span>
                                    <span class="text-gray-800 font-medium ml-2"><?= format_datetime($bank_method['created_at']) ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Last Updated:</span>
                                    <span class="text-gray-800 font-medium ml-2"><?= format_datetime($bank_method['updated_at']) ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Account Type:</span>
                                    <span class="text-gray-800 font-medium ml-2"><?= ucfirst(e($bank_method['account_type'])) ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Display Order:</span>
                                    <span class="text-gray-800 font-medium ml-2"><?= e($bank_method['display_order']) ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Status:</span>
                                    <span class="<?= $bank_method['is_active'] ? 'text-green-600' : 'text-gray-500' ?> font-medium ml-2">
                                        <?= $bank_method['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </div>
                                <?php if ($link_count > 0): ?>
                                    <div>
                                        <span class="text-gray-600">Payment Links:</span>
                                        <span class="text-yellow-600 font-bold ml-2"><?= $link_count ?> link<?= $link_count !== 1 ? 's' : '' ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Additional Bank Details -->
                        <?php if ($bank_method['swift_code'] || $bank_method['iban'] || $bank_method['routing_number']): ?>
                            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Banking Details:</h4>
                                <div class="space-y-2 text-sm">
                                    <?php if ($bank_method['routing_number']): ?>
                                        <div>
                                            <span class="text-gray-600">Routing Number:</span>
                                            <code class="text-gray-800 font-mono ml-2"><?= e($bank_method['routing_number']) ?></code>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($bank_method['swift_code']): ?>
                                        <div>
                                            <span class="text-gray-600">SWIFT Code:</span>
                                            <code class="text-gray-800 font-mono ml-2"><?= e($bank_method['swift_code']) ?></code>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($bank_method['iban']): ?>
                                        <div>
                                            <span class="text-gray-600">IBAN:</span>
                                            <code class="text-gray-800 font-mono ml-2"><?= e($bank_method['iban']) ?></code>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Action Buttons -->
                        <form method="POST" class="space-y-4">
                            <?= csrf_field() ?>
                            
                            <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                                <a href="index.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                                    Cancel
                                </a>
                                <button 
                                    type="submit" 
                                    class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-8 py-2 rounded-lg font-medium shadow-lg transform hover:scale-105 transition duration-200"
                                    onclick="return confirm('Are you absolutely sure you want to delete this bank payment method? This action CANNOT be undone!');"
                                >
                                    🗑️ Yes, Delete Permanently
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Alternative Actions -->
                <div class="mt-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded shadow">
                    <div class="flex items-start">
                        <div class="text-emerald-500 text-xl mr-3">💡</div>
                        <div class="text-sm text-emerald-800">
                            <h4 class="font-semibold mb-1">Consider These Alternatives:</h4>
                            <ul class="list-disc list-inside space-y-1 text-emerald-700">
                                <li>If you want to temporarily hide this bank payment method, you can <a href="edit.php?id=<?= $id ?>" class="underline font-medium">deactivate it instead</a></li>
                                <li>Deactivating preserves all data and payment links while hiding it from customers</li>
                                <li>You can always reactivate it later without losing any information</li>
                                <li>Consider updating account details if information has changed rather than deleting</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
