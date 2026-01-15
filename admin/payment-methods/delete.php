<?php
/**
 * Payment Methods - Delete Payment Method
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_auth();
check_session_timeout();

// Get payment method ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    set_flash('error', 'Invalid payment method ID.');
    redirect('index.php');
}

// Fetch payment method to verify it exists
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

// Check if there are associated payment links
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM payment_links WHERE payment_method_id = :id");
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
        
        // Delete the payment method (payment_links will be cascade deleted due to foreign key)
        $stmt = $pdo->prepare("DELETE FROM payment_methods WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        // Delete associated files
        if ($payment_method['logo_path']) {
            delete_file(basename($payment_method['logo_path']));
        }
        if ($payment_method['qr_code_path']) {
            delete_file(basename($payment_method['qr_code_path']));
        }
        
        // Commit transaction
        $pdo->commit();
        
        set_flash('success', 'Payment method "' . $payment_method['name'] . '" has been deleted successfully.');
        redirect('index.php');
        
    } catch (PDOException $e) {
        // Rollback on error
        $pdo->rollBack();
        error_log("Payment method deletion error: " . $e->getMessage());
        set_flash('error', 'Database error: Failed to delete payment method.');
        redirect('index.php');
    }
}

$page_title = 'Delete Payment Method';
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
                <h1 class="text-3xl font-bold text-gray-800">Delete Payment Method</h1>
                <p class="text-gray-600 mt-2">Confirm deletion of payment method</p>
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
                        <!-- Payment Method Details -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center space-x-4">
                                <?php if ($payment_method['logo_path']): ?>
                                    <img src="/<?= e($payment_method['logo_path']) ?>" alt="<?= e($payment_method['name']) ?>" class="w-16 h-16 rounded-full object-cover border-2 border-gray-300">
                                <?php else: ?>
                                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white font-bold text-2xl">
                                        <?= strtoupper(substr(e($payment_method['symbol']), 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-800"><?= e($payment_method['name']) ?></h3>
                                    <p class="text-gray-600">
                                        <span class="font-semibold"><?= e($payment_method['symbol']) ?></span>
                                        <span class="mx-2">•</span>
                                        <code class="text-xs bg-gray-200 px-2 py-1 rounded"><?= truncate(e($payment_method['wallet_address']), 30) ?></code>
                                    </p>
                                    <?php if ($payment_method['networks']): ?>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Networks: <?= e($payment_method['networks']) ?>
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
                                        Deleting this payment method will:
                                    </p>
                                    <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                                        <li>Permanently remove the payment method from your system</li>
                                        <li>Delete associated logo and QR code images</li>
                                        <?php if ($link_count > 0): ?>
                                            <li class="font-semibold">Delete <?= $link_count ?> associated payment link<?= $link_count !== 1 ? 's' : '' ?></li>
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
                                            This payment method has <strong><?= $link_count ?> payment link<?= $link_count !== 1 ? 's' : '' ?></strong> associated with it. 
                                            These links will also be deleted due to database constraints.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Metadata -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Payment Method Information:</h4>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-gray-600">Created:</span>
                                    <span class="text-gray-800 font-medium ml-2"><?= format_datetime($payment_method['created_at']) ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Last Updated:</span>
                                    <span class="text-gray-800 font-medium ml-2"><?= format_datetime($payment_method['updated_at']) ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Display Order:</span>
                                    <span class="text-gray-800 font-medium ml-2"><?= e($payment_method['display_order']) ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Status:</span>
                                    <span class="<?= $payment_method['is_active'] ? 'text-green-600' : 'text-gray-500' ?> font-medium ml-2">
                                        <?= $payment_method['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </div>
                                <?php if ($link_count > 0): ?>
                                    <div class="col-span-2">
                                        <span class="text-gray-600">Payment Links:</span>
                                        <span class="text-red-600 font-bold ml-2"><?= $link_count ?> link<?= $link_count !== 1 ? 's' : '' ?> will be deleted</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
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
                                    onclick="return confirm('Are you absolutely sure you want to delete this payment method? This action CANNOT be undone!');"
                                >
                                    🗑️ Yes, Delete Permanently
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Alternative Actions -->
                <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded shadow">
                    <div class="flex items-start">
                        <div class="text-blue-500 text-xl mr-3">💡</div>
                        <div class="text-sm text-blue-800">
                            <h4 class="font-semibold mb-1">Consider These Alternatives:</h4>
                            <ul class="list-disc list-inside space-y-1 text-blue-700">
                                <li>If you want to temporarily hide this payment method, you can <a href="edit.php?id=<?= $id ?>" class="underline font-medium">deactivate it instead</a></li>
                                <li>Deactivating preserves all data and payment links while hiding it from customers</li>
                                <li>You can always reactivate it later without losing any information</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
