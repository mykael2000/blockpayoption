<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'all'; // all, crypto, bank
$selected_method_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$selected_bank_id = isset($_GET['bank_id']) ? (int)$_GET['bank_id'] : null;

try {
    $crypto_methods = [];
    $bank_methods = [];
    
    // Fetch crypto payment methods
    if ($type_filter === 'all' || $type_filter === 'crypto') {
        if ($search) {
            $stmt = $pdo->prepare("SELECT *, 'crypto' as payment_type FROM payment_methods WHERE is_active = 1 AND (name LIKE ? OR symbol LIKE ? OR description LIKE ?) ORDER BY display_order");
            $search_term = "%{$search}%";
            $stmt->execute([$search_term, $search_term, $search_term]);
        } else {
            $stmt = $pdo->prepare("SELECT *, 'crypto' as payment_type FROM payment_methods WHERE is_active = 1 ORDER BY display_order");
            $stmt->execute();
        }
        $crypto_methods = $stmt->fetchAll();
    }
    
    // Fetch bank payment methods
    if ($type_filter === 'all' || $type_filter === 'bank') {
        if ($search) {
            $stmt = $pdo->prepare("SELECT *, 'bank' as payment_type FROM bank_payment_methods WHERE is_active = 1 AND (bank_name LIKE ? OR account_holder_name LIKE ? OR currency LIKE ? OR country LIKE ?) ORDER BY display_order");
            $search_term = "%{$search}%";
            $stmt->execute([$search_term, $search_term, $search_term, $search_term]);
        } else {
            $bank_methods = getBankPaymentMethods();
            // Add payment_type field
            foreach ($bank_methods as &$method) {
                $method['payment_type'] = 'bank';
            }
        }
        if ($search && $stmt) {
            $bank_methods = $stmt->fetchAll();
        }
    }
    
    // Merge methods
    $payment_methods = array_merge($crypto_methods, $bank_methods);
    
    // Count statistics
    $crypto_count = count($crypto_methods);
    $bank_count = count($bank_methods);
    
    $selected_method = null;
    $selected_bank_method = null;
    if ($selected_method_id) {
        $stmt = $pdo->prepare("SELECT *, 'crypto' as payment_type FROM payment_methods WHERE id = ? AND is_active = 1");
        $stmt->execute([$selected_method_id]);
        $selected_method = $stmt->fetch();
    }
    if ($selected_bank_id) {
        $selected_bank_method = getBankPaymentMethodById($selected_bank_id);
        if ($selected_bank_method) {
            $selected_bank_method['payment_type'] = 'bank';
        }
    }
} catch (PDOException $e) {
    $error = "Database error occurred";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explore all available cryptocurrency and bank transfer payment methods. Accept Bitcoin, Ethereum, USDT and traditional bank transfers.">
    <meta name="keywords" content="cryptocurrency payment methods, bitcoin payments, ethereum, USDT, bank transfer, wire transfer, payment options">
    <title>Payment Methods - <?php echo e(SITE_NAME); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/custom.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
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
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-gray-600 hover:text-purple-600 transition">Home</a>
                    <a href="payment-methods.php" class="text-gray-900 font-semibold border-b-2 border-purple-600 pb-1">Payment Methods</a>
                    <a href="tutorials.php" class="text-gray-600 hover:text-purple-600 transition">Tutorials</a>
                    <a href="platforms.php" class="text-gray-600 hover:text-purple-600 transition">Platforms</a>
                    <a href="admin/login.php" class="px-4 py-2 gradient-purple-blue text-white rounded-lg hover:shadow-lg transition">Admin</a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" class="text-gray-600 hover:text-purple-600 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t">
            <div class="px-4 pt-2 pb-4 space-y-2">
                <a href="index.php" class="block py-2 text-gray-600 hover:text-purple-600 hover:border-l-4 hover:border-purple-300 pl-4 transition">Home</a>
                <a href="payment-methods.php" class="block py-2 text-gray-900 font-semibold border-l-4 border-purple-600 pl-4">Payment Methods</a>
                <a href="tutorials.php" class="block py-2 text-gray-600 hover:text-purple-600 hover:border-l-4 hover:border-purple-300 pl-4 transition">Tutorials</a>
                <a href="platforms.php" class="block py-2 text-gray-600 hover:text-purple-600 hover:border-l-4 hover:border-purple-300 pl-4 transition">Platforms</a>
                <a href="admin/login.php" class="block py-2 px-4 gradient-purple-blue text-white rounded-lg text-center mt-2">Admin</a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="gradient-purple-blue text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center fade-in">
                <h1 class="text-5xl font-bold mb-4">Payment Methods</h1>
                <p class="text-xl text-purple-100 max-w-3xl mx-auto">
                    Choose from cryptocurrencies and bank transfers to accept payments
                </p>
                <?php if ($crypto_count > 0 || $bank_count > 0): ?>
                <p class="text-lg text-purple-200 mt-4">
                    <?php if ($crypto_count > 0 && $bank_count > 0): ?>
                        <?php echo $crypto_count; ?> Crypto + <?php echo $bank_count; ?> Bank Options
                    <?php elseif ($crypto_count > 0): ?>
                        <?php echo $crypto_count; ?> Cryptocurrency Options
                    <?php else: ?>
                        <?php echo $bank_count; ?> Bank Transfer Options
                    <?php endif; ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Search Bar -->
    <section class="py-8 bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="GET" class="max-w-2xl mx-auto">
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="<?php echo e($search); ?>"
                        placeholder="Search payment methods..." 
                        class="w-full px-6 py-4 pr-24 rounded-full border-2 border-gray-300 focus:border-purple-600 focus:outline-none text-lg"
                    >
                    <input type="hidden" name="type" value="<?php echo e($type_filter); ?>">
                    <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 px-6 py-2 gradient-purple-blue text-white rounded-full font-semibold hover:shadow-lg transition">
                        Search
                    </button>
                </div>
                <?php if ($search): ?>
                <div class="mt-4 text-center">
                    <a href="payment-methods.php?type=<?php echo e($type_filter); ?>" class="text-purple-600 hover:text-purple-800">Clear search</a>
                </div>
                <?php endif; ?>
            </form>
            
            <!-- Filter Buttons -->
            <div class="flex justify-center gap-3 mt-6 flex-wrap">
                <a href="?type=all<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                   class="px-6 py-2.5 rounded-full font-semibold transition <?php echo $type_filter === 'all' ? 'gradient-purple-blue text-white shadow-lg' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                    ✨ All Methods
                </a>
                <a href="?type=crypto<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                   class="px-6 py-2.5 rounded-full font-semibold transition <?php echo $type_filter === 'crypto' ? 'gradient-purple-blue text-white shadow-lg' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                    ₿ Cryptocurrency
                </a>
                <a href="?type=bank<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                   class="px-6 py-2.5 rounded-full font-semibold transition <?php echo $type_filter === 'bank' ? 'bg-gradient-to-r from-green-500 to-blue-500 text-white shadow-lg' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                    🏦 Bank Transfer
                </a>
            </div>
        </div>
    </section>

    <!-- Payment Methods Grid -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if (empty($payment_methods)): ?>
            <div class="text-center py-16">
                <div class="text-6xl mb-4">🔍</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">No Payment Methods Found</h3>
                <p class="text-gray-600 mb-8">
                    <?php if ($search): ?>
                        No payment methods match your search criteria.
                    <?php else: ?>
                        There are no active payment methods available at the moment.
                    <?php endif; ?>
                </p>
                <?php if ($search): ?>
                <a href="payment-methods.php" class="inline-block px-8 py-3 gradient-purple-blue text-white rounded-lg font-semibold hover:shadow-lg transition">
                    View All Payment Methods
                </a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($payment_methods as $index => $method): ?>
                <?php $is_bank = ($method['payment_type'] === 'bank'); ?>
                <div class="bg-white border-2 border-gray-200 rounded-2xl p-6 hover:border-<?php echo $is_bank ? 'green' : 'purple'; ?>-400 hover:shadow-xl card-hover animate-on-scroll" style="animation-delay: <?php echo ($index % 9) * 0.1; ?>s">
                    <div class="flex items-center justify-between mb-6">
                        <?php if ($is_bank): ?>
                            <?php if (!empty($method['logo_path'])): ?>
                            <img src="<?php echo e($method['logo_path']); ?>" alt="<?php echo e($method['bank_name']); ?>" class="w-16 h-16 object-contain">
                            <?php else: ?>
                            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-blue-500 rounded-full flex items-center justify-center text-white text-3xl">
                                🏦
                            </div>
                            <?php endif; ?>
                            <span class="px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-full font-bold text-sm">
                                Bank Transfer
                            </span>
                        <?php else: ?>
                            <?php if ($method['logo_path']): ?>
                            <img src="<?php echo e($method['logo_path']); ?>" alt="<?php echo e($method['name']); ?>" class="w-16 h-16 object-contain">
                            <?php else: ?>
                            <div class="w-16 h-16 gradient-purple-blue rounded-full flex items-center justify-center text-white text-2xl font-bold">
                                <?php echo e(substr($method['symbol'], 0, 1)); ?>
                            </div>
                            <?php endif; ?>
                            <span class="px-4 py-2 gradient-blue-teal text-white rounded-full font-bold">
                                <?php echo e($method['symbol']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <h3 class="text-2xl font-bold mb-3 text-gray-900">
                        <?php echo $is_bank ? e($method['bank_name']) : e($method['name']); ?>
                    </h3>
                    
                    <?php if ($is_bank): ?>
                        <div class="mb-4 space-y-2">
                            <p class="text-sm text-gray-600">
                                <span class="font-semibold">Account Holder:</span><br>
                                <?php echo e($method['account_holder_name']); ?>
                            </p>
                            <p class="text-sm text-gray-600">
                                <span class="font-semibold">Account:</span> <?php echo e(maskAccountNumber($method['account_number'])); ?>
                            </p>
                            <p class="text-sm text-gray-600">
                                <span class="font-semibold">Currency:</span> <?php echo e($method['currency']); ?>
                                <?php if ($method['country']): ?>
                                | <span class="font-semibold">Country:</span> <?php echo e($method['country']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-600 mb-4 text-sm">
                            <?php echo e(truncate($method['description'], 100)); ?>
                        </p>
                        
                        <?php if ($method['networks']): ?>
                        <div class="mb-6">
                            <p class="text-xs font-semibold text-gray-500 mb-2">Supported Networks:</p>
                            <?php 
                            $networks = explode(',', $method['networks']);
                            foreach (array_slice($networks, 0, 3) as $network): 
                            ?>
                            <span class="network-badge"><?php echo e(trim($network)); ?></span>
                            <?php endforeach; ?>
                            <?php if (count($networks) > 3): ?>
                            <span class="text-xs text-gray-500">+<?php echo count($networks) - 3; ?> more</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <button 
                        onclick="<?php echo $is_bank ? "showBankDetails(" . e($method['id']) . ")" : "showMethodDetails(" . e($method['id']) . ")"; ?>"
                        class="w-full px-6 py-3 <?php echo $is_bank ? 'bg-gradient-to-r from-green-500 to-blue-500' : 'gradient-purple-blue'; ?> text-white rounded-lg font-semibold hover:shadow-lg transition"
                    >
                        View Details
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modal -->
    <div id="method-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div id="modal-content"></div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-10 h-10 gradient-purple-blue rounded-lg flex items-center justify-center">
                            <span class="text-white text-xl font-bold">BP</span>
                        </div>
                        <span class="text-xl font-bold"><?php echo e(SITE_NAME); ?></span>
                    </div>
                    <p class="text-gray-400 text-sm">
                        Simplifying cryptocurrency payments for everyone.
                    </p>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="index.php" class="hover:text-purple-400 transition">Home</a></li>
                        <li><a href="payment-methods.php" class="hover:text-purple-400 transition">Payment Methods</a></li>
                        <li><a href="tutorials.php" class="hover:text-purple-400 transition">Tutorials</a></li>
                        <li><a href="platforms.php" class="hover:text-purple-400 transition">Platforms</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Resources</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="tutorials.php" class="hover:text-purple-400 transition">Getting Started</a></li>
                        <li><a href="tutorials.php" class="hover:text-purple-400 transition">Help Center</a></li>
                        <li><a href="admin/login.php" class="hover:text-purple-400 transition">Admin Login</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Contact</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><?php echo e(ADMIN_EMAIL); ?></li>
                        <li>24/7 Support Available</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center text-gray-400 text-sm">
                <p>&copy; <?php echo date('Y'); ?> <?php echo e(SITE_NAME); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        const paymentMethods = <?php echo json_encode($payment_methods); ?>;
        
        function showMethodDetails(methodId) {
            const method = paymentMethods.find(m => m.id == methodId && m.payment_type === 'crypto');
            if (!method) return;
            
            const networks = method.networks ? method.networks.split(',') : [];
            
            const modalContent = `
                <div class="p-8">
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex items-center space-x-4">
                            ${method.logo_path ? 
                                `<img src="${method.logo_path}" alt="${method.name}" class="w-20 h-20 object-contain">` :
                                `<div class="w-20 h-20 gradient-purple-blue rounded-full flex items-center justify-center text-white text-3xl font-bold">
                                    ${method.symbol.charAt(0)}
                                </div>`
                            }
                            <div>
                                <h2 class="text-3xl font-bold gradient-text">${method.name}</h2>
                                <span class="inline-block px-4 py-1 gradient-blue-teal text-white rounded-full font-bold text-sm mt-2">
                                    ${method.symbol}
                                </span>
                            </div>
                        </div>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-3 text-gray-900">Description</h3>
                        <p class="text-gray-600">${method.description || 'No description available.'}</p>
                    </div>

                    ${networks.length > 0 ? `
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-3 text-gray-900">Supported Networks</h3>
                        <div class="flex flex-wrap gap-2">
                            ${networks.map(network => `<span class="network-badge">${network.trim()}</span>`).join('')}
                        </div>
                    </div>
                    ` : ''}

                    <div class="bg-gradient-to-br from-purple-50 to-blue-50 p-6 rounded-xl mb-8">
                        <h3 class="text-lg font-bold mb-4 text-gray-900">Wallet Address</h3>
                        <div class="bg-white p-4 rounded-lg border-2 border-purple-200 mb-4">
                            <div class="flex items-center justify-between">
                                <code class="text-sm font-mono break-all text-gray-700">${method.wallet_address}</code>
                                <button onclick="copyToClipboard('${method.wallet_address}')" class="ml-4 flex-shrink-0 px-4 py-2 gradient-purple-blue text-white rounded-lg font-semibold hover:shadow-lg copy-btn transition">
                                    Copy
                                </button>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <h4 class="text-sm font-bold mb-3 text-gray-700">Scan QR Code</h4>
                            <div id="qrcode-${methodId}" class="inline-block qr-code-container"></div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Important:</strong> Always verify the wallet address and network before sending any cryptocurrency. Transactions cannot be reversed.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('modal-content').innerHTML = modalContent;
            document.getElementById('method-modal').classList.remove('hidden');
            
            setTimeout(() => {
                new QRCode(document.getElementById(`qrcode-${methodId}`), {
                    text: method.wallet_address,
                    width: 200,
                    height: 200
                });
            }, 100);
        }

        function showBankDetails(bankId) {
            const method = paymentMethods.find(m => m.id == bankId && m.payment_type === 'bank');
            if (!method) return;
            
            const modalContent = `
                <div class="p-8">
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex items-center space-x-4">
                            ${method.logo_path ? 
                                `<img src="${method.logo_path}" alt="${method.bank_name}" class="w-20 h-20 object-contain">` :
                                `<div class="w-20 h-20 bg-gradient-to-br from-green-500 to-blue-500 rounded-full flex items-center justify-center text-white text-3xl">
                                    🏦
                                </div>`
                            }
                            <div>
                                <h2 class="text-3xl font-bold bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent">${method.bank_name}</h2>
                                <span class="inline-block px-4 py-1 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-full font-bold text-sm mt-2">
                                    Bank Transfer
                                </span>
                            </div>
                        </div>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-blue-50 p-6 rounded-xl mb-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-900">Bank Account Details</h3>
                        
                        <div class="space-y-4">
                            <div class="bg-white p-4 rounded-lg border-2 border-green-200">
                                <p class="text-xs font-semibold text-gray-500 mb-1">Account Holder Name</p>
                                <div class="flex items-center justify-between">
                                    <p class="text-lg font-semibold text-gray-900">${method.account_holder_name}</p>
                                    <button onclick="copyToClipboard('${method.account_holder_name}')" class="px-3 py-1 bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-lg text-sm font-semibold hover:shadow-lg transition">
                                        Copy
                                    </button>
                                </div>
                            </div>

                            <div class="bg-white p-4 rounded-lg border-2 border-green-200">
                                <p class="text-xs font-semibold text-gray-500 mb-1">Account Type</p>
                                <p class="text-lg font-semibold text-gray-900 capitalize">${method.account_type || 'N/A'}</p>
                            </div>

                            <div class="bg-white p-4 rounded-lg border-2 border-green-200">
                                <p class="text-xs font-semibold text-gray-500 mb-1">Account Number</p>
                                <div class="flex items-center justify-between">
                                    <code class="text-lg font-mono font-semibold text-gray-900">${method.account_number}</code>
                                    <button onclick="copyToClipboard('${method.account_number}')" class="px-3 py-1 bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-lg text-sm font-semibold hover:shadow-lg transition">
                                        Copy
                                    </button>
                                </div>
                            </div>

                            ${method.routing_number ? `
                            <div class="bg-white p-4 rounded-lg border-2 border-green-200">
                                <p class="text-xs font-semibold text-gray-500 mb-1">Routing Number</p>
                                <div class="flex items-center justify-between">
                                    <code class="text-lg font-mono font-semibold text-gray-900">${method.routing_number}</code>
                                    <button onclick="copyToClipboard('${method.routing_number}')" class="px-3 py-1 bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-lg text-sm font-semibold hover:shadow-lg transition">
                                        Copy
                                    </button>
                                </div>
                            </div>
                            ` : ''}

                            ${method.swift_bic_code ? `
                            <div class="bg-white p-4 rounded-lg border-2 border-green-200">
                                <p class="text-xs font-semibold text-gray-500 mb-1">SWIFT/BIC Code</p>
                                <div class="flex items-center justify-between">
                                    <code class="text-lg font-mono font-semibold text-gray-900">${method.swift_bic_code}</code>
                                    <button onclick="copyToClipboard('${method.swift_bic_code}')" class="px-3 py-1 bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-lg text-sm font-semibold hover:shadow-lg transition">
                                        Copy
                                    </button>
                                </div>
                            </div>
                            ` : ''}

                            ${method.bank_address ? `
                            <div class="bg-white p-4 rounded-lg border-2 border-green-200">
                                <p class="text-xs font-semibold text-gray-500 mb-1">Bank Address</p>
                                <p class="text-sm text-gray-700">${method.bank_address}</p>
                            </div>
                            ` : ''}

                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-white p-4 rounded-lg border-2 border-green-200">
                                    <p class="text-xs font-semibold text-gray-500 mb-1">Currency</p>
                                    <p class="text-lg font-semibold text-gray-900">${method.currency}</p>
                                </div>
                                ${method.country ? `
                                <div class="bg-white p-4 rounded-lg border-2 border-green-200">
                                    <p class="text-xs font-semibold text-gray-500 mb-1">Country</p>
                                    <p class="text-lg font-semibold text-gray-900">${method.country}</p>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>

                    ${method.instructions ? `
                    <div class="mb-6">
                        <h3 class="text-lg font-bold mb-3 text-gray-900">Transfer Instructions</h3>
                        <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-400">
                            <p class="text-sm text-gray-700">${method.instructions}</p>
                        </div>
                    </div>
                    ` : ''}

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Important:</strong> Bank transfers typically take 1-3 business days to process. Always include your payment reference number in the transfer details. Verify all bank details before initiating the transfer.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('modal-content').innerHTML = modalContent;
            document.getElementById('method-modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('method-modal').classList.add('hidden');
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                // Show success feedback
                const btn = event.target;
                const originalText = btn.innerHTML;
                btn.innerHTML = '✓ Copied!';
                btn.classList.add('opacity-75');
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('opacity-75');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
                alert('Failed to copy to clipboard');
            });
        }

        document.getElementById('method-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        <?php if ($selected_method_id && $selected_method): ?>
        showMethodDetails(<?php echo $selected_method_id; ?>);
        <?php endif; ?>
        
        <?php if ($selected_bank_id && $selected_bank_method): ?>
        showBankDetails(<?php echo $selected_bank_id; ?>);
        <?php endif; ?>
    </script>
</body>
</html>
