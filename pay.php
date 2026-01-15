<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$payment_link = null;
$payment_method = null;
$error = null;
$is_expired = false;

$unique_id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($unique_id)) {
    $error = "Invalid payment link. No payment ID provided.";
} else {
    try {
        $stmt = $pdo->prepare("
            SELECT pl.*, 
                   pm.name as method_name, pm.symbol, pm.wallet_address, pm.logo_path, pm.networks,
                   bpm.bank_name, bpm.account_holder_name, bpm.account_number, bpm.routing_number, 
                   bpm.swift_code, bpm.iban, bpm.bank_address, bpm.account_type, bpm.currency as bank_currency, 
                   bpm.country, bpm.instructions as bank_instructions, bpm.logo_path as bank_logo_path
            FROM payment_links pl
            LEFT JOIN payment_methods pm ON pl.payment_method_id = pm.id
            LEFT JOIN bank_payment_methods bpm ON pl.bank_payment_method_id = bpm.id
            WHERE pl.unique_id = ?
        ");
        $stmt->execute([$unique_id]);
        $payment_link = $stmt->fetch();
        
        if (!$payment_link) {
            $error = "Payment link not found. Please check the link and try again.";
        } else {
            if ($payment_link['expires_at'] && strtotime($payment_link['expires_at']) < time()) {
                $is_expired = true;
                if ($payment_link['status'] === 'pending') {
                    $update_stmt = $pdo->prepare("UPDATE payment_links SET status = 'expired' WHERE id = ?");
                    $update_stmt->execute([$payment_link['id']]);
                    $payment_link['status'] = 'expired';
                }
            }
        }
    } catch (PDOException $e) {
        $error = "Database error occurred. Please try again later.";
    }
}

function getStatusBadgeClass($status) {
    return match($status) {
        'completed' => 'status-badge completed',
        'expired' => 'status-badge expired',
        default => 'status-badge pending'
    };
}

function getStatusIcon($status) {
    return match($status) {
        'completed' => '✓',
        'expired' => '✗',
        default => '⏳'
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Complete your payment securely with BlockPayOption.">
    <meta name="robots" content="noindex, nofollow">
    <title>Payment - <?php echo e(SITE_NAME); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/custom.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
</head>
<body class="bg-gradient-to-br from-gray-50 to-<?php echo ($payment_link && $payment_link['payment_type'] === 'bank') ? 'green' : 'purple'; ?>-50">
    <!-- Simple Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-2">
                        <div class="w-10 h-10 gradient-purple-blue rounded-lg flex items-center justify-center">
                            <span class="text-white text-xl font-bold">BP</span>
                        </div>
                        <span class="text-xl font-bold gradient-text"><?php echo e(SITE_NAME); ?></span>
                    </a>
                </div>
                <div class="flex items-center">
                    <span class="text-sm text-gray-500">Secure Payment</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="py-12 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if ($error): ?>
            <!-- Error State -->
            <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12 text-center fade-in">
                <div class="text-6xl mb-6">❌</div>
                <h1 class="text-3xl font-bold text-gray-900 mb-4">Payment Link Error</h1>
                <p class="text-xl text-gray-600 mb-8"><?php echo e($error); ?></p>
                <a href="index.php" class="inline-block px-8 py-4 gradient-purple-blue text-white rounded-lg font-semibold hover:shadow-lg transition">
                    Return to Home
                </a>
            </div>
            
            <?php elseif ($is_expired): ?>
            <!-- Expired State -->
            <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12 text-center fade-in">
                <div class="text-6xl mb-6">⏰</div>
                <h1 class="text-3xl font-bold text-gray-900 mb-4">Payment Link Expired</h1>
                <p class="text-xl text-gray-600 mb-4">
                    This payment link expired on <?php echo e(format_datetime($payment_link['expires_at'])); ?>
                </p>
                <p class="text-gray-500 mb-8">
                    Please contact the merchant to request a new payment link.
                </p>
                <div class="bg-gray-50 rounded-lg p-6 mb-8">
                    <div class="grid md:grid-cols-2 gap-4 text-left">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Payment Method</p>
                            <p class="font-semibold text-gray-900">
                                <?php 
                                if ($payment_link['payment_type'] === 'bank') {
                                    echo e($payment_link['bank_name']);
                                } else {
                                    echo e($payment_link['method_name']) . ' (' . e($payment_link['symbol']) . ')';
                                }
                                ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Amount</p>
                            <p class="font-semibold text-gray-900"><?php echo e($payment_link['amount']); ?> <?php echo e($payment_link['currency']); ?></p>
                        </div>
                    </div>
                </div>
                <a href="index.php" class="inline-block px-8 py-4 gradient-purple-blue text-white rounded-lg font-semibold hover:shadow-lg transition">
                    Return to Home
                </a>
            </div>
            
            <?php elseif ($payment_link['status'] === 'completed'): ?>
            <!-- Completed State -->
            <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12 text-center fade-in">
                <div class="text-6xl mb-6">✅</div>
                <h1 class="text-3xl font-bold text-gray-900 mb-4">Payment Completed</h1>
                <p class="text-xl text-gray-600 mb-8">
                    This payment has already been completed.
                </p>
                <div class="bg-green-50 rounded-lg p-6 mb-8">
                    <div class="grid md:grid-cols-3 gap-4 text-left">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Payment Method</p>
                            <p class="font-semibold text-gray-900">
                                <?php 
                                if ($payment_link['payment_type'] === 'bank') {
                                    echo e($payment_link['bank_name']);
                                } else {
                                    echo e($payment_link['method_name']);
                                }
                                ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Amount</p>
                            <p class="font-semibold text-gray-900"><?php echo e($payment_link['amount']); ?> <?php echo e($payment_link['currency']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Completed</p>
                            <p class="font-semibold text-gray-900"><?php echo e(format_datetime($payment_link['updated_at'])); ?></p>
                        </div>
                    </div>
                </div>
                <a href="index.php" class="inline-block px-8 py-4 gradient-purple-blue text-white rounded-lg font-semibold hover:shadow-lg transition">
                    Return to Home
                </a>
            </div>
            
            <?php else: ?>
            <!-- Active Payment -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden fade-in">
                <!-- Header -->
                <div class="gradient-<?php echo ($payment_link['payment_type'] === 'bank') ? 'emerald-green' : 'purple-blue'; ?> text-white p-8 text-center">
                    <div class="flex items-center justify-center space-x-3 mb-4">
                        <?php if ($payment_link['payment_type'] === 'bank'): ?>
                            <?php if ($payment_link['bank_logo_path']): ?>
                            <img src="<?php echo e($payment_link['bank_logo_path']); ?>" alt="<?php echo e($payment_link['bank_name']); ?>" class="h-16 object-contain">
                            <?php else: ?>
                            <div class="text-6xl">🏦</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ($payment_link['logo_path']): ?>
                            <img src="<?php echo e($payment_link['logo_path']); ?>" alt="<?php echo e($payment_link['method_name']); ?>" class="h-16 object-contain">
                            <?php else: ?>
                            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-3xl font-bold">
                                <?php echo e(substr($payment_link['symbol'], 0, 1)); ?>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <h1 class="text-3xl font-bold mb-2">
                        <?php echo ($payment_link['payment_type'] === 'bank') ? 'Complete Your Bank Transfer' : 'Complete Your Payment'; ?>
                    </h1>
                    <p class="<?php echo ($payment_link['payment_type'] === 'bank') ? 'text-green-100' : 'text-purple-100'; ?>">
                        <?php 
                        if ($payment_link['payment_type'] === 'bank') {
                            echo 'Transfer funds to the bank account below';
                        } else {
                            echo 'Send ' . e($payment_link['method_name']) . ' to the address below';
                        }
                        ?>
                    </p>
                </div>

                <div class="p-8 md:p-12">
                    <!-- Payment Details -->
                    <div class="bg-gradient-to-br from-<?php echo ($payment_link['payment_type'] === 'bank') ? 'green' : 'purple'; ?>-50 to-<?php echo ($payment_link['payment_type'] === 'bank') ? 'emerald' : 'blue'; ?>-50 rounded-xl p-6 mb-8">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-500 mb-2">Payment Amount</p>
                                <p class="text-3xl font-bold gradient-text"><?php echo e($payment_link['amount']); ?> <?php echo e($payment_link['currency']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-2">Payment Status</p>
                                <span class="<?php echo getStatusBadgeClass($payment_link['status']); ?>">
                                    <?php echo getStatusIcon($payment_link['status']); ?> <?php echo e(ucfirst($payment_link['status'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Expiry Countdown -->
                    <?php if ($payment_link['expires_at']): ?>
                    <div id="countdown-container" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded mb-8">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Time Remaining:</strong> <span id="countdown" class="font-mono font-bold">Calculating...</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($payment_link['payment_type'] === 'bank'): ?>
                    <!-- BANK PAYMENT CONTENT -->
                    
                    <!-- Bank Instructions (Prominent) -->
                    <?php if ($payment_link['bank_instructions']): ?>
                    <div class="mb-8 bg-emerald-50 border-2 border-emerald-300 rounded-xl p-6">
                        <h3 class="text-lg font-bold mb-3 text-emerald-900 flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            Important Instructions
                        </h3>
                        <p class="text-emerald-800"><?php echo nl2br(e($payment_link['bank_instructions'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Bank Details -->
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 flex items-center">
                            <span class="w-8 h-8 gradient-emerald-green rounded-full flex items-center justify-center text-white text-sm mr-3">1</span>
                            Bank Account Details
                        </h3>
                        
                        <div class="bg-white border-2 border-green-200 rounded-xl p-6 space-y-4">
                            <?php 
                            $bankDetails = formatBankDetails($payment_link);
                            if (is_array($bankDetails) && !empty($bankDetails)):
                                foreach ($bankDetails as $label => $value): 
                            ?>
                            <div class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-sm font-semibold text-gray-600"><?php echo e($label); ?>:</p>
                                    <button data-copy-value="<?php echo e($value); ?>" class="copy-detail-btn px-3 py-1 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg font-semibold text-xs transition">
                                        Copy
                                    </button>
                                </div>
                                <code class="block text-sm md:text-base font-mono break-all text-gray-800 bg-gray-50 p-3 rounded"><?php echo e($value); ?></code>
                            </div>
                            <?php 
                                endforeach;
                            else:
                            ?>
                            <p class="text-gray-500 text-sm">Bank details are not available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Processing Time Notice -->
                    <div class="mb-8 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-bold text-blue-800 mb-1">Processing Time</h4>
                                <p class="text-sm text-blue-700">
                                    <?php 
                                    // Check payment currency (not bank currency) to determine if it's a domestic transfer
                                    if ($payment_link['country'] === 'United States' || $payment_link['currency'] === 'USD') {
                                        echo 'Domestic transfers typically take 1-3 business days to process.';
                                    } else {
                                        echo 'International transfers typically take 2-5 business days to process.';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Bank Payment Instructions -->
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 flex items-center">
                            <span class="w-8 h-8 gradient-emerald-green rounded-full flex items-center justify-center text-white text-sm mr-3">2</span>
                            Transfer Instructions
                        </h3>
                        <div class="bg-gray-50 rounded-xl p-6">
                            <ol class="space-y-3 text-gray-700">
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 w-6 h-6 gradient-blue-teal rounded-full flex items-center justify-center text-white text-xs font-bold mr-3 mt-0.5">1</span>
                                    <span>Copy the bank account details using the buttons above</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 w-6 h-6 gradient-blue-teal rounded-full flex items-center justify-center text-white text-xs font-bold mr-3 mt-0.5">2</span>
                                    <span>Log in to your online banking or visit your bank branch</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 w-6 h-6 gradient-blue-teal rounded-full flex items-center justify-center text-white text-xs font-bold mr-3 mt-0.5">3</span>
                                    <span>Initiate a transfer for exactly <strong><?php echo e($payment_link['amount']); ?> <?php echo e($payment_link['currency']); ?></strong></span>
                                </li>
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 w-6 h-6 gradient-blue-teal rounded-full flex items-center justify-center text-white text-xs font-bold mr-3 mt-0.5">4</span>
                                    <span>Include your payment reference or invoice number in the transfer memo</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 w-6 h-6 gradient-blue-teal rounded-full flex items-center justify-center text-white text-xs font-bold mr-3 mt-0.5">5</span>
                                    <span>Keep your transfer receipt for your records</span>
                                </li>
                            </ol>
                        </div>
                    </div>

                    <!-- Warning -->
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded mb-8">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-bold text-red-800 mb-1">Important Warnings</h4>
                                <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
                                    <li>Double-check all bank account details before transferring</li>
                                    <li>Ensure you transfer the exact amount specified</li>
                                    <li>Include the payment reference in your transfer</li>
                                    <li>Keep your transfer receipt as proof of payment</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <?php else: ?>
                    <!-- CRYPTO PAYMENT CONTENT -->
                    
                    <!-- Wallet Address -->
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 flex items-center">
                            <span class="w-8 h-8 gradient-purple-blue rounded-full flex items-center justify-center text-white text-sm mr-3">1</span>
                            Send to This Wallet Address
                        </h3>
                        
                        <?php if ($payment_link['networks']): ?>
                        <div class="mb-4">
                            <p class="text-sm font-semibold text-gray-600 mb-2">Supported Networks:</p>
                            <?php 
                            $networks = explode(',', $payment_link['networks']);
                            foreach ($networks as $network): 
                            ?>
                            <span class="network-badge"><?php echo e(trim($network)); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="bg-white border-2 border-purple-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-semibold text-gray-600">Wallet Address:</p>
                                <button onclick="copyAddress()" id="copy-btn" class="px-4 py-2 gradient-purple-blue text-white rounded-lg font-semibold hover:shadow-lg copy-btn transition text-sm">
                                    Copy Address
                                </button>
                            </div>
                            <code id="wallet-address" class="block text-sm md:text-base font-mono break-all text-gray-800 bg-gray-50 p-3 rounded"><?php echo e($payment_link['wallet_address']); ?></code>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 flex items-center">
                            <span class="w-8 h-8 gradient-purple-blue rounded-full flex items-center justify-center text-white text-sm mr-3">2</span>
                            Or Scan QR Code
                        </h3>
                        <div class="text-center">
                            <div id="qrcode" class="inline-block qr-code-container"></div>
                            <p class="text-sm text-gray-500 mt-4">Scan this QR code with your crypto wallet app</p>
                        </div>
                    </div>

                    <!-- Payment Instructions -->
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 flex items-center">
                            <span class="w-8 h-8 gradient-purple-blue rounded-full flex items-center justify-center text-white text-sm mr-3">3</span>
                            Payment Instructions
                        </h3>
                        <div class="bg-gray-50 rounded-xl p-6">
                            <ol class="space-y-3 text-gray-700">
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 w-6 h-6 gradient-blue-teal rounded-full flex items-center justify-center text-white text-xs font-bold mr-3 mt-0.5">1</span>
                                    <span>Copy the wallet address above or scan the QR code</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 w-6 h-6 gradient-blue-teal rounded-full flex items-center justify-center text-white text-xs font-bold mr-3 mt-0.5">2</span>
                                    <span>Open your <?php echo e($payment_link['method_name']); ?> wallet application</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 w-6 h-6 gradient-blue-teal rounded-full flex items-center justify-center text-white text-xs font-bold mr-3 mt-0.5">3</span>
                                    <span>Send exactly <strong><?php echo e($payment_link['amount']); ?> <?php echo e($payment_link['currency']); ?></strong> to the address</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 w-6 h-6 gradient-blue-teal rounded-full flex items-center justify-center text-white text-xs font-bold mr-3 mt-0.5">4</span>
                                    <span>Ensure you select the correct network when sending</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 w-6 h-6 gradient-blue-teal rounded-full flex items-center justify-center text-white text-xs font-bold mr-3 mt-0.5">5</span>
                                    <span>Wait for the transaction to be confirmed on the blockchain</span>
                                </li>
                            </ol>
                        </div>
                    </div>

                    <!-- Warning -->
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded mb-8">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-bold text-red-800 mb-1">Important Warnings</h4>
                                <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
                                    <li>Double-check the wallet address before sending</li>
                                    <li>Cryptocurrency transactions cannot be reversed</li>
                                    <li>Always verify the network matches your wallet</li>
                                    <li>Send only the exact amount specified</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($payment_link['recipient_email']): ?>
                    <!-- Contact Info -->
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    Questions? Contact us at <strong><?php echo e($payment_link['recipient_email']); ?></strong>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="flex items-center justify-center space-x-2 mb-4">
                <div class="w-8 h-8 gradient-purple-blue rounded-lg flex items-center justify-center">
                    <span class="text-white text-lg font-bold">BP</span>
                </div>
                <span class="text-lg font-bold"><?php echo e(SITE_NAME); ?></span>
            </div>
            <p class="text-gray-400 text-sm">&copy; <?php echo date('Y'); ?> <?php echo e(SITE_NAME); ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        <?php if ($payment_link && !$error && !$is_expired && $payment_link['status'] === 'pending'): ?>
        
        <?php if ($payment_link['payment_type'] === 'crypto'): ?>
        // Generate QR code for crypto payments only
        new QRCode(document.getElementById("qrcode"), {
            text: "<?php echo e($payment_link['wallet_address']); ?>",
            width: 200,
            height: 200
        });

        function copyAddress() {
            const address = document.getElementById('wallet-address').textContent;
            const btn = document.getElementById('copy-btn');
            
            navigator.clipboard.writeText(address).then(() => {
                const originalText = btn.textContent;
                btn.textContent = '✓ Copied!';
                btn.classList.add('bg-green-500');
                
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.classList.remove('bg-green-500');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
                alert('Failed to copy address. Please copy manually.');
            });
        }
        <?php else: ?>
        // Copy bank detail function for bank payments
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.copy-detail-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const value = this.getAttribute('data-copy-value');
                    navigator.clipboard.writeText(value).then(() => {
                        const originalText = this.textContent;
                        this.textContent = '✓ Copied!';
                        this.classList.remove('bg-emerald-500', 'hover:bg-emerald-600');
                        this.classList.add('bg-green-600');
                        
                        setTimeout(() => {
                            this.textContent = originalText;
                            this.classList.remove('bg-green-600');
                            this.classList.add('bg-emerald-500', 'hover:bg-emerald-600');
                        }, 2000);
                    }).catch(err => {
                        console.error('Failed to copy:', err);
                        alert('Failed to copy. Please copy manually.');
                    });
                });
            });
        });
        <?php endif; ?>

        <?php if ($payment_link['expires_at']): ?>
        const expiryTime = new Date("<?php echo date('Y-m-d\TH:i:s', strtotime($payment_link['expires_at'])); ?>").getTime();
        
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = expiryTime - now;
            
            if (distance < 0) {
                document.getElementById('countdown').textContent = 'EXPIRED';
                document.getElementById('countdown-container').classList.remove('bg-yellow-50', 'border-yellow-400');
                document.getElementById('countdown-container').classList.add('bg-red-50', 'border-red-400');
                location.reload();
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            let countdownText = '';
            if (days > 0) countdownText += days + 'd ';
            if (hours > 0 || days > 0) countdownText += hours + 'h ';
            countdownText += minutes + 'm ' + seconds + 's';
            
            document.getElementById('countdown').textContent = countdownText;
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
        <?php endif; ?>
        <?php endif; ?>
    </script>
</body>
</html>
