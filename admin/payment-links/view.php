<?php
/**
 * Payment Links - View/Details Page
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_auth();
check_session_timeout();

// Get payment link ID
$id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$id) {
    set_flash('error', 'Invalid payment link ID.');
    redirect('/admin/payment-links/index.php');
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        set_flash('error', 'Invalid security token.');
    } else {
        $new_status = $_POST['status'] ?? '';
        $allowed_statuses = ['pending', 'completed', 'expired'];
        
        if (in_array($new_status, $allowed_statuses)) {
            try {
                $stmt = $pdo->prepare("UPDATE payment_links SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $id]);
                set_flash('success', 'Status updated successfully.');
            } catch (PDOException $e) {
                error_log("Payment link status update error: " . $e->getMessage());
                set_flash('error', 'Error updating status.');
            }
        } else {
            set_flash('error', 'Invalid status value.');
        }
    }
    redirect('/admin/payment-links/view.php?id=' . $id);
}

// Get payment link details
try {
    $stmt = $pdo->prepare("
        SELECT pl.*, pm.name as payment_method_name, pm.symbol as payment_method_symbol, 
               pm.wallet_address, pm.qr_code_path, pm.networks
        FROM payment_links pl
        LEFT JOIN payment_methods pm ON pl.payment_method_id = pm.id
        WHERE pl.id = ?
    ");
    $stmt->execute([$id]);
    $link = $stmt->fetch();

    if (!$link) {
        set_flash('error', 'Payment link not found.');
        redirect('/admin/payment-links/index.php');
    }
} catch (PDOException $e) {
    error_log("Payment link fetch error: " . $e->getMessage());
    set_flash('error', 'Error loading payment link.');
    redirect('/admin/payment-links/index.php');
}

// Generate shareable link
$payment_url = SITE_URL . '/pay.php?id=' . urlencode($link['unique_id']);

$page_title = 'Payment Link Details';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
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
                <a href="/admin/payment-links/index.php" class="text-purple-600 hover:text-purple-700 font-medium mb-4 inline-block">
                    ← Back to Payment Links
                </a>
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Payment Link Details</h1>
                        <p class="text-gray-600 mt-1">View and manage payment link information</p>
                    </div>
                    <?php 
                    $status_colors = [
                        'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                        'completed' => 'bg-green-100 text-green-800 border-green-200',
                        'expired' => 'bg-red-100 text-red-800 border-red-200'
                    ];
                    $status_class = $status_colors[$link['status']] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                    ?>
                    <span class="px-4 py-2 rounded-full text-sm font-semibold border <?= $status_class ?>">
                        <?= e(ucfirst($link['status'])) ?>
                    </span>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if ($flash = get_flash()): ?>
                <div class="mb-6 p-4 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200' ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - Payment Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Payment Information Card -->
                    <div class="bg-white rounded-xl shadow-md p-8">
                        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                            <span class="text-2xl mr-3">💰</span>
                            Payment Information
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Link ID</label>
                                <code class="block px-3 py-2 bg-gray-100 text-purple-700 rounded font-mono text-sm"><?= e($link['unique_id']) ?></code>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Payment Method</label>
                                <p class="text-gray-900 font-medium"><?= e($link['payment_method_name']) ?></p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Amount</label>
                                <p class="text-2xl font-bold text-gray-900">
                                    <?= e(rtrim(rtrim(number_format($link['amount'], 8, '.', ''), '0'), '.')) ?>
                                    <span class="text-lg text-gray-600"><?= e($link['currency']) ?></span>
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Wallet Address</label>
                                <code class="block px-3 py-2 bg-gray-100 text-gray-700 rounded text-xs break-all"><?= e($link['wallet_address']) ?></code>
                            </div>

                            <?php if ($link['recipient_email']): ?>
                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Recipient Email</label>
                                <p class="text-gray-900"><?= e($link['recipient_email']) ?></p>
                            </div>
                            <?php endif; ?>

                            <?php if ($link['networks']): ?>
                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Supported Networks</label>
                                <p class="text-gray-900 text-sm"><?= e($link['networks']) ?></p>
                            </div>
                            <?php endif; ?>

                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Created</label>
                                <p class="text-gray-900"><?= format_datetime($link['created_at']) ?></p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Expires</label>
                                <p class="text-gray-900">
                                    <?php if ($link['expires_at']): ?>
                                        <?= format_datetime($link['expires_at']) ?>
                                    <?php else: ?>
                                        <span class="text-gray-500 italic">Never</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Shareable Link Card -->
                    <div class="bg-gradient-to-br from-purple-500 to-blue-600 rounded-xl shadow-lg p-8 text-white">
                        <h2 class="text-xl font-bold mb-4 flex items-center">
                            <span class="text-2xl mr-3">🔗</span>
                            Shareable Payment Link
                        </h2>
                        
                        <div class="bg-white bg-opacity-20 rounded-lg p-4 mb-4">
                            <code class="text-white break-all text-sm"><?= e($payment_url) ?></code>
                        </div>

                        <button 
                            onclick="copyToClipboard('<?= e($payment_url) ?>')"
                            class="w-full px-6 py-3 bg-white text-purple-600 rounded-lg hover:bg-opacity-90 transition font-medium"
                        >
                            📋 Copy Link to Clipboard
                        </button>
                        
                        <p class="text-purple-100 text-sm mt-4 text-center">
                            Share this link with your customer to receive payment
                        </p>
                    </div>

                    <!-- Status Update Card -->
                    <div class="bg-white rounded-xl shadow-md p-8">
                        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                            <span class="text-2xl mr-3">⚙️</span>
                            Update Status
                        </h2>
                        
                        <form method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update_status">
                            
                            <div class="flex items-center space-x-4">
                                <div class="flex-1">
                                    <select 
                                        name="status" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                    >
                                        <option value="pending" <?= $link['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="completed" <?= $link['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="expired" <?= $link['status'] === 'expired' ? 'selected' : '' ?>>Expired</option>
                                    </select>
                                </div>
                                <button 
                                    type="submit" 
                                    class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg hover:shadow-lg transition font-medium whitespace-nowrap"
                                >
                                    Update Status
                                </button>
                            </div>
                            
                            <p class="text-sm text-gray-500 mt-3">
                                Manually update the payment status (e.g., mark as completed after verifying blockchain transaction)
                            </p>
                        </form>
                    </div>
                </div>

                <!-- Right Column - QR Code & Actions -->
                <div class="space-y-6">
                    <!-- QR Code Card -->
                    <div class="bg-white rounded-xl shadow-md p-8">
                        <h2 class="text-xl font-bold text-gray-800 mb-6 text-center">QR Code</h2>
                        
                        <div class="flex justify-center mb-4">
                            <div id="qrcode" class="p-4 bg-white border-4 border-purple-200 rounded-xl"></div>
                        </div>
                        
                        <p class="text-sm text-gray-600 text-center">
                            Scan this QR code to access the payment page
                        </p>
                    </div>

                    <!-- Quick Actions Card -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h3>
                        
                        <div class="space-y-3">
                            <a 
                                href="<?= e($payment_url) ?>" 
                                target="_blank"
                                class="block w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center font-medium"
                            >
                                🔗 Open Payment Page
                            </a>
                            
                            <button 
                                onclick="window.print()"
                                class="block w-full px-4 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition text-center font-medium"
                            >
                                🖨️ Print Details
                            </button>
                            
                            <a 
                                href="/admin/payment-links/create.php"
                                class="block w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-center font-medium"
                            >
                                ➕ Create New Link
                            </a>
                        </div>
                    </div>

                    <!-- Info Card -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex">
                            <span class="text-blue-500 text-xl mr-3">ℹ️</span>
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold mb-2">Payment Instructions</p>
                                <ol class="list-decimal list-inside space-y-1">
                                    <li>Share the payment link with your customer</li>
                                    <li>Customer sends the exact amount to the wallet address</li>
                                    <li>Verify the transaction on the blockchain</li>
                                    <li>Update the status to "Completed"</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Generate QR Code
        const qrcode = new QRCode(document.getElementById("qrcode"), {
            text: "<?= e($payment_url) ?>",
            width: 200,
            height: 200,
            colorDark: "#7c3aed",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        // Copy to clipboard function
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Show success feedback
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '✅ Copied!';
                button.classList.add('bg-green-500');
                button.classList.remove('bg-white', 'text-purple-600');
                button.classList.add('text-white');
                
                setTimeout(function() {
                    button.innerHTML = originalText;
                    button.classList.remove('bg-green-500', 'text-white');
                    button.classList.add('bg-white', 'text-purple-600');
                }, 2000);
            }, function(err) {
                alert('Failed to copy: ' + err);
            });
        }
    </script>
</body>
</html>
