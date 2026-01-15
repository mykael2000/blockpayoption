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
        SELECT pl.*, 
               pm.name as payment_method_name, 
               pm.symbol as payment_method_symbol, 
               pm.wallet_address, 
               pm.qr_code_path, 
               pm.networks,
               bpm.bank_name,
               bpm.account_holder_name,
               bpm.account_number,
               bpm.routing_number,
               bpm.swift_code,
               bpm.iban,
               bpm.account_type,
               bpm.country,
               bpm.instructions as bank_instructions
        FROM payment_links pl
        LEFT JOIN payment_methods pm ON pl.payment_method_id = pm.id
        LEFT JOIN bank_payment_methods bpm ON pl.bank_payment_method_id = bpm.id
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
                <?php 
                $payment_type = $link['payment_type'] ?? 'crypto';
                $is_bank = $payment_type === 'bank';
                $back_link_color = $is_bank ? 'emerald' : 'purple';
                ?>
                <a href="/admin/payment-links/index.php" class="text-<?= $back_link_color ?>-600 hover:text-<?= $back_link_color ?>-700 font-medium mb-4 inline-block">
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
                            <span class="text-2xl mr-3"><?= $is_bank ? '🏦' : '💰' ?></span>
                            Payment Information
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Link ID</label>
                                <code class="block px-3 py-2 bg-gray-100 text-purple-700 rounded font-mono text-sm"><?= e($link['unique_id']) ?></code>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Payment Type</label>
                                <?php 
                                $type_badge_class = $is_bank ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-purple-100 text-purple-800 border-purple-200';
                                ?>
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold border <?= $type_badge_class ?>">
                                    <?= $is_bank ? '🏦 Bank Transfer' : '₿ Cryptocurrency' ?>
                                </span>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1"><?= $is_bank ? 'Bank Name' : 'Payment Method' ?></label>
                                <p class="text-gray-900 font-medium"><?= e($is_bank ? $link['bank_name'] : $link['payment_method_name']) ?></p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Amount</label>
                                <p class="text-2xl font-bold text-gray-900">
                                    <?= $is_bank ? e(number_format($link['amount'], 2, '.', ',')) : e(rtrim(rtrim(number_format($link['amount'], 8, '.', ''), '0'), '.')) ?>
                                    <span class="text-lg text-gray-600"><?= e($link['currency']) ?></span>
                                </p>
                            </div>

                            <?php if ($is_bank): ?>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-600 mb-3">Bank Account Details</label>
                                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 space-y-3">
                                        <?php 
                                        $bankDetails = [
                                            'Account Holder' => $link['account_holder_name'],
                                            'Account Number' => $link['account_number'],
                                            'Routing Number' => $link['routing_number'],
                                            'SWIFT Code' => $link['swift_code'],
                                            'IBAN' => $link['iban'],
                                            'Account Type' => $link['account_type'] ? ucfirst($link['account_type']) : null,
                                            'Country' => $link['country']
                                        ];
                                        
                                        foreach ($bankDetails as $label => $value):
                                            if (!empty($value)):
                                        ?>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-700"><?= e($label) ?>:</span>
                                            <div class="flex items-center gap-2">
                                                <code class="px-2 py-1 bg-white text-gray-800 rounded text-sm"><?= e($value) ?></code>
                                                <?php if (in_array($label, ['Account Number', 'Routing Number', 'SWIFT Code', 'IBAN'])): ?>
                                                <button 
                                                    onclick="copyBankDetail('<?= e($value) ?>', this)"
                                                    class="px-2 py-1 text-xs bg-emerald-600 text-white rounded hover:bg-emerald-700 transition"
                                                >
                                                    📋 Copy
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($link['bank_instructions'])): ?>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-600 mb-1">Instructions</label>
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <p class="text-sm text-gray-700"><?= nl2br(e($link['bank_instructions'])) ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-600 mb-1">Wallet Address</label>
                                    <div class="flex items-center gap-2">
                                        <code class="block px-3 py-2 bg-gray-100 text-gray-700 rounded text-xs break-all flex-1"><?= e($link['wallet_address']) ?></code>
                                        <button 
                                            onclick="copyBankDetail('<?= e($link['wallet_address']) ?>', this)"
                                            class="px-2 py-1 text-xs bg-purple-600 text-white rounded hover:bg-purple-700 transition whitespace-nowrap"
                                        >
                                            📋 Copy
                                        </button>
                                    </div>
                                </div>

                                <?php if ($link['networks']): ?>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-600 mb-1">Supported Networks</label>
                                    <p class="text-gray-900 text-sm"><?= e($link['networks']) ?></p>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($link['recipient_email']): ?>
                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Recipient Email</label>
                                <p class="text-gray-900"><?= e($link['recipient_email']) ?></p>
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
                    <div class="bg-gradient-to-br from-<?= $is_bank ? 'emerald' : 'purple' ?>-500 to-<?= $is_bank ? 'green' : 'blue' ?>-600 rounded-xl shadow-lg p-8 text-white">
                        <h2 class="text-xl font-bold mb-4 flex items-center">
                            <span class="text-2xl mr-3">🔗</span>
                            Shareable Payment Link
                        </h2>
                        
                        <div class="bg-white bg-opacity-20 rounded-lg p-4 mb-4">
                            <code class="text-white break-all text-sm"><?= e($payment_url) ?></code>
                        </div>

                        <button 
                            onclick="copyToClipboard('<?= e($payment_url) ?>')"
                            class="w-full px-6 py-3 bg-white text-<?= $is_bank ? 'emerald' : 'purple' ?>-600 rounded-lg hover:bg-opacity-90 transition font-medium"
                            data-theme-color="<?= $is_bank ? 'emerald' : 'purple' ?>"
                        >
                            📋 Copy Link to Clipboard
                        </button>
                        
                        <p class="text-<?= $is_bank ? 'emerald' : 'purple' ?>-100 text-sm mt-4 text-center">
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
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-<?= $is_bank ? 'emerald' : 'purple' ?>-500 focus:border-transparent transition"
                                    >
                                        <option value="pending" <?= $link['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="completed" <?= $link['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="expired" <?= $link['status'] === 'expired' ? 'selected' : '' ?>>Expired</option>
                                    </select>
                                </div>
                                <button 
                                    type="submit" 
                                    class="px-6 py-3 bg-gradient-to-r from-<?= $is_bank ? 'emerald' : 'purple' ?>-600 to-<?= $is_bank ? 'green' : 'blue' ?>-600 text-white rounded-lg hover:shadow-lg transition font-medium whitespace-nowrap"
                                >
                                    Update Status
                                </button>
                            </div>
                            
                            <p class="text-sm text-gray-500 mt-3">
                                Manually update the payment status (e.g., mark as completed after verifying <?= $is_bank ? 'bank transfer' : 'blockchain transaction' ?>)
                            </p>
                        </form>
                    </div>
                </div>

                <!-- Right Column - QR Code & Actions -->
                <div class="space-y-6">
                    <?php if (!$is_bank): ?>
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
                    <?php else: ?>
                    <!-- Bank Payment Info Card -->
                    <div class="bg-white rounded-xl shadow-md p-8">
                        <h2 class="text-xl font-bold text-gray-800 mb-6 text-center">🏦 Bank Transfer</h2>
                        
                        <div class="flex justify-center mb-4">
                            <div class="p-8 bg-gradient-to-br from-emerald-50 to-green-50 border-4 border-emerald-200 rounded-xl text-center">
                                <div class="text-5xl mb-3">💳</div>
                                <p class="text-sm text-gray-600">
                                    Traditional bank transfer payment
                                </p>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 text-center">
                            Customer will transfer funds to the bank account details above
                        </p>
                    </div>
                    <?php endif; ?>

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
                    <div class="bg-<?= $is_bank ? 'emerald' : 'blue' ?>-50 border border-<?= $is_bank ? 'emerald' : 'blue' ?>-200 rounded-lg p-4">
                        <div class="flex">
                            <span class="text-<?= $is_bank ? 'emerald' : 'blue' ?>-500 text-xl mr-3">ℹ️</span>
                            <div class="text-sm text-<?= $is_bank ? 'emerald' : 'blue' ?>-800">
                                <p class="font-semibold mb-2">Payment Instructions</p>
                                <ol class="list-decimal list-inside space-y-1">
                                    <li>Share the payment link with your customer</li>
                                    <?php if ($is_bank): ?>
                                    <li>Customer transfers the exact amount to the bank account</li>
                                    <li>Verify the transaction in your bank account</li>
                                    <?php else: ?>
                                    <li>Customer sends the exact amount to the wallet address</li>
                                    <li>Verify the transaction on the blockchain</li>
                                    <?php endif; ?>
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
        <?php if (!$is_bank): ?>
        // Generate QR Code
        const qrcode = new QRCode(document.getElementById("qrcode"), {
            text: "<?= e($payment_url) ?>",
            width: 200,
            height: 200,
            colorDark: "#7c3aed",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
        <?php endif; ?>

        // Copy to clipboard function
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Show success feedback
                const button = event.target;
                const originalText = button.innerHTML;
                const themeColor = button.getAttribute('data-theme-color') || 'purple';
                const textClass = `text-${themeColor}-600`;
                
                button.innerHTML = '✅ Copied!';
                button.classList.add('bg-green-500');
                button.classList.remove('bg-white', textClass);
                button.classList.add('text-white');
                
                setTimeout(function() {
                    button.innerHTML = originalText;
                    button.classList.remove('bg-green-500', 'text-white');
                    button.classList.add('bg-white', textClass);
                }, 2000);
            }, function(err) {
                alert('Failed to copy: ' + err);
            });
        }

        // Copy bank detail function
        function copyBankDetail(text, button) {
            navigator.clipboard.writeText(text).then(function() {
                const originalText = button.innerHTML;
                const themeColor = '<?= $is_bank ? "emerald" : "purple" ?>';
                const bgClass = `bg-${themeColor}-600`;
                
                button.innerHTML = '✅ Copied!';
                button.classList.add('bg-green-600');
                button.classList.remove(bgClass);
                
                setTimeout(function() {
                    button.innerHTML = originalText;
                    button.classList.remove('bg-green-600');
                    button.classList.add(bgClass);
                }, 2000);
            }, function(err) {
                alert('Failed to copy: ' + err);
            });
        }
    </script>
</body>
</html>
