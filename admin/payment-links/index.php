<?php
/**
 * Payment Links - List/Index Page
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_auth();
check_session_timeout();

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        set_flash('error', 'Invalid security token.');
    } else {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if ($id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM payment_links WHERE id = ?");
                $stmt->execute([$id]);
                set_flash('success', 'Payment link deleted successfully.');
            } catch (PDOException $e) {
                error_log("Payment link delete error: " . $e->getMessage());
                set_flash('error', 'Error deleting payment link.');
            }
        }
    }
    redirect('/admin/payment-links/index.php');
}

// Get all payment links with payment method details
try {
    $stmt = $pdo->query("
        SELECT pl.*, 
               pm.name as payment_method_name, 
               pm.symbol as payment_method_symbol,
               bpm.bank_name,
               bpm.account_number
        FROM payment_links pl
        LEFT JOIN payment_methods pm ON pl.payment_method_id = pm.id
        LEFT JOIN bank_payment_methods bpm ON pl.bank_payment_method_id = bpm.id
        ORDER BY pl.created_at DESC
    ");
    $payment_links = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Payment links fetch error: " . $e->getMessage());
    set_flash('error', 'Error loading payment links.');
    $payment_links = [];
}

$page_title = 'Payment Links';
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
                    <h1 class="text-3xl font-bold text-gray-800">Payment Links</h1>
                    <p class="text-gray-600 mt-1">Create and manage cryptocurrency payment links</p>
                </div>
                <a href="<?= BASE_URL ?>/admin/payment-links/create.php" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg hover:shadow-lg transition font-medium">
                    + Create Payment Link
                </a>
            </div>

            <!-- Flash Messages -->
            <?php if ($flash = get_flash()): ?>
                <div class="mb-6 p-4 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200' ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Payment Links Table -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <?php if (empty($payment_links)): ?>
                    <div class="p-12 text-center">
                        <div class="text-6xl mb-4">🔗</div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">No Payment Links Yet</h3>
                        <p class="text-gray-600 mb-6">Create your first payment link to start accepting cryptocurrency payments.</p>
                        <a href="<?= BASE_URL ?>/admin/payment-links/create.php" class="inline-block px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg hover:shadow-lg transition font-medium">
                            Create Your First Link
                        </a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-purple-50 to-blue-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Link ID</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Payment Type</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Payment Method</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Recipient</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Expires</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Created</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($payment_links as $link): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <code class="px-2 py-1 bg-gray-100 text-purple-700 rounded text-sm font-mono"><?= e($link['unique_id']) ?></code>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php 
                                            $payment_type = $link['payment_type'] ?? 'crypto';
                                            $type_badge_class = $payment_type === 'bank' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-purple-100 text-purple-800 border-purple-200';
                                            ?>
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold border <?= $type_badge_class ?>">
                                                <?= $payment_type === 'bank' ? '🏦 Bank' : '₿ Crypto' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="font-medium text-gray-900">
                                                    <?= e(($link['payment_type'] ?? 'crypto') === 'bank' ? ($link['bank_name'] ?? 'N/A') : ($link['payment_method_name'] ?? 'N/A')) ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-semibold text-gray-900">
                                                <?= ($link['payment_type'] ?? 'crypto') === 'bank' ? e(number_format($link['amount'], 2, '.', ',')) : e(rtrim(rtrim(number_format($link['amount'], 8, '.', ''), '0'), '.')) ?>
                                            </span>
                                            <span class="text-gray-600 ml-1"><?= e($link['currency']) ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php 
                                            $status_colors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                'completed' => 'bg-green-100 text-green-800 border-green-200',
                                                'expired' => 'bg-red-100 text-red-800 border-red-200'
                                            ];
                                            $status_class = $status_colors[$link['status']] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                            ?>
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold border <?= $status_class ?>">
                                                <?= e(ucfirst($link['status'])) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($link['recipient_email']): ?>
                                                <span class="text-gray-700 text-sm"><?= e($link['recipient_email']) ?></span>
                                            <?php else: ?>
                                                <span class="text-gray-400 text-sm italic">Not specified</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($link['expires_at']): ?>
                                                <span class="text-gray-700 text-sm"><?= format_datetime($link['expires_at']) ?></span>
                                            <?php else: ?>
                                                <span class="text-gray-400 text-sm italic">Never</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <?= format_date($link['created_at']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="flex items-center space-x-3">
                                                <a href="<?= BASE_URL ?>/admin/payment-links/view.php?id=<?= $link['id'] ?>" class="text-blue-600 hover:text-blue-800 font-medium transition">
                                                    View
                                                </a>
                                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this payment link?');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $link['id'] ?>">
                                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium transition">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Stats Cards -->
            <?php if (!empty($payment_links)): 
                $total_links = count($payment_links);
                
                // Calculate status counts
                $pending_count = 0;
                $completed_count = 0;
                $expired_count = 0;
                foreach ($payment_links as $l) {
                    if ($l['status'] === 'pending') {
                        $pending_count++;
                    } elseif ($l['status'] === 'completed') {
                        $completed_count++;
                    } elseif ($l['status'] === 'expired') {
                        $expired_count++;
                    }
                }
            ?>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-8">
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm">Total Links</p>
                            <p class="text-3xl font-bold mt-1"><?= $total_links ?></p>
                        </div>
                        <div class="text-4xl opacity-50">🔗</div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-sm">Pending</p>
                            <p class="text-3xl font-bold mt-1"><?= $pending_count ?></p>
                        </div>
                        <div class="text-4xl opacity-50">⏳</div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm">Completed</p>
                            <p class="text-3xl font-bold mt-1"><?= $completed_count ?></p>
                        </div>
                        <div class="text-4xl opacity-50">✅</div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-red-100 text-sm">Expired</p>
                            <p class="text-3xl font-bold mt-1"><?= $expired_count ?></p>
                        </div>
                        <div class="text-4xl opacity-50">❌</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
