<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM payment_methods WHERE is_active = 1");
    $stmt->execute();
    $crypto_methods_count = $stmt->fetch()['count'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bank_payment_methods WHERE is_active = 1");
    $stmt->execute();
    $bank_methods_count = $stmt->fetch()['count'];
    
    $payment_methods_count = $crypto_methods_count + $bank_methods_count;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tutorials WHERE is_published = 1");
    $stmt->execute();
    $tutorials_count = $stmt->fetch()['count'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM platforms WHERE is_active = 1");
    $stmt->execute();
    $platforms_count = $stmt->fetch()['count'];
    
    // Fetch featured crypto methods
    $stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY display_order LIMIT 2");
    $stmt->execute();
    $featured_crypto = $stmt->fetchAll();
    
    // Fetch featured bank methods
    $stmt = $pdo->prepare("SELECT * FROM bank_payment_methods WHERE is_active = 1 ORDER BY display_order LIMIT 1");
    $stmt->execute();
    $featured_banks = $stmt->fetchAll();
    
    $featured_methods = array_merge($featured_crypto, $featured_banks);
} catch (PDOException $e) {
    $error = "Database error occurred";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BlockPayOption - Accept cryptocurrency payments easily. Learn about blockchain technology and start accepting crypto payments today.">
    <meta name="keywords" content="cryptocurrency, blockchain, crypto payments, bitcoin, ethereum, payment gateway">
    <title><?php echo e(SITE_NAME); ?> - Accept Crypto Payments Easily</title>
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
                    <a href="index.php" class="text-gray-900 font-semibold border-b-2 border-purple-600 pb-1">Home</a>
                    <a href="payment-methods.php" class="text-gray-600 hover:text-purple-600 transition">Payment Methods</a>
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
                <a href="index.php" class="block py-2 text-gray-900 font-semibold border-l-4 border-purple-600 pl-4">Home</a>
                <a href="payment-methods.php" class="block py-2 text-gray-600 hover:text-purple-600 hover:border-l-4 hover:border-purple-300 pl-4 transition">Payment Methods</a>
                <a href="tutorials.php" class="block py-2 text-gray-600 hover:text-purple-600 hover:border-l-4 hover:border-purple-300 pl-4 transition">Tutorials</a>
                <a href="platforms.php" class="block py-2 text-gray-600 hover:text-purple-600 hover:border-l-4 hover:border-purple-300 pl-4 transition">Platforms</a>
                <a href="admin/login.php" class="block py-2 px-4 gradient-purple-blue text-white rounded-lg text-center mt-2">Admin</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-bg text-white py-20 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="fade-in">
                    <h1 class="text-5xl md:text-6xl font-bold mb-6 leading-tight">
                        Accept Crypto Payments<br>
                        <span class="text-cyan-300">The Easy Way</span>
                    </h1>
                    <p class="text-xl mb-8 text-purple-100">
                        Start accepting cryptocurrency payments with our simple, secure platform. 
                        Support for Bitcoin, Ethereum, USDT, and more.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="payment-methods.php" class="px-8 py-4 bg-white text-purple-700 rounded-lg font-semibold hover:shadow-2xl hover-lift transition">
                            View Payment Methods
                        </a>
                        <a href="tutorials.php" class="px-8 py-4 border-2 border-white text-white rounded-lg font-semibold hover:bg-white hover:text-purple-700 transition">
                            Learn How It Works
                        </a>
                    </div>
                </div>
                <div class="hidden md:block slide-in-right">
                    <div class="relative">
                        <div class="absolute inset-0 bg-cyan-400 rounded-full opacity-20 blur-3xl float-animation"></div>
                        <div class="relative glass-dark p-8 rounded-2xl">
                            <div class="text-center mb-6">
                                <div class="text-6xl mb-4">💰</div>
                                <h3 class="text-2xl font-bold">Secure Payments</h3>
                                <p class="text-purple-200 mt-2">Blockchain-powered transactions</p>
                            </div>
                            <div class="space-y-4">
                                <div class="glass p-4 rounded-lg flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-green-400 rounded-full pulse-animation"></div>
                                    <span>Fast Processing</span>
                                </div>
                                <div class="glass p-4 rounded-lg flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-blue-400 rounded-full pulse-animation" style="animation-delay: 0.5s"></div>
                                    <span>Low Fees</span>
                                </div>
                                <div class="glass p-4 rounded-lg flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-purple-400 rounded-full pulse-animation" style="animation-delay: 1s"></div>
                                    <span>Global Access</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center animate-on-scroll">
                    <div class="inline-block p-4 gradient-purple-blue rounded-full mb-4">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="text-4xl font-bold gradient-text mb-2"><?php echo e($payment_methods_count); ?>+</div>
                    <div class="text-gray-600 font-medium">Payment Methods</div>
                    <div class="text-sm text-gray-500 mt-1"><?php echo e($crypto_methods_count); ?> Crypto + <?php echo e($bank_methods_count); ?> Bank</div>
                </div>
                <div class="text-center animate-on-scroll" style="animation-delay: 0.2s">
                    <div class="inline-block p-4 gradient-blue-teal rounded-full mb-4">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div class="text-4xl font-bold gradient-text-blue mb-2"><?php echo e($tutorials_count); ?>+</div>
                    <div class="text-gray-600 font-medium">Tutorials Available</div>
                </div>
                <div class="text-center animate-on-scroll" style="animation-delay: 0.4s">
                    <div class="inline-block p-4 gradient-purple-pink rounded-full mb-4">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="text-4xl font-bold gradient-text mb-2"><?php echo e($platforms_count); ?>+</div>
                    <div class="text-gray-600 font-medium">Trusted Platforms</div>
                </div>
            </div>
        </div>
    </section>

    <!-- What is Blockchain Section -->
    <section class="py-20 bg-gradient-to-br from-gray-50 to-purple-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-on-scroll">
                <h2 class="text-4xl font-bold mb-4 gradient-text">What is Blockchain?</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Understanding the technology that powers cryptocurrency payments
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-2xl shadow-lg hover-lift animate-on-scroll">
                    <div class="text-5xl mb-4">🔗</div>
                    <h3 class="text-2xl font-bold mb-4 text-gray-900">Decentralized</h3>
                    <p class="text-gray-600">
                        No single authority controls the network. Transactions are verified by a distributed network of computers worldwide, ensuring transparency and security.
                    </p>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-lg hover-lift animate-on-scroll" style="animation-delay: 0.2s">
                    <div class="text-5xl mb-4">🔒</div>
                    <h3 class="text-2xl font-bold mb-4 text-gray-900">Secure</h3>
                    <p class="text-gray-600">
                        Advanced cryptography protects every transaction. Once recorded on the blockchain, data cannot be altered or deleted, creating an immutable record.
                    </p>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-lg hover-lift animate-on-scroll" style="animation-delay: 0.4s">
                    <div class="text-5xl mb-4">⚡</div>
                    <h3 class="text-2xl font-bold mb-4 text-gray-900">Fast & Efficient</h3>
                    <p class="text-gray-600">
                        Send payments anywhere in the world in minutes, not days. Lower fees compared to traditional banking systems make it cost-effective.
                    </p>
                </div>
            </div>

            <div class="mt-16 bg-white p-8 md:p-12 rounded-2xl shadow-xl animate-on-scroll">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h3 class="text-3xl font-bold mb-6 gradient-text">How Blockchain Works</h3>
                        <div class="space-y-4">
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0 w-8 h-8 gradient-purple-blue rounded-full flex items-center justify-center text-white font-bold">1</div>
                                <div>
                                    <h4 class="font-bold text-gray-900 mb-1">Transaction Initiated</h4>
                                    <p class="text-gray-600">A user initiates a transaction to send cryptocurrency to another user.</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0 w-8 h-8 gradient-purple-blue rounded-full flex items-center justify-center text-white font-bold">2</div>
                                <div>
                                    <h4 class="font-bold text-gray-900 mb-1">Network Verification</h4>
                                    <p class="text-gray-600">The transaction is broadcast to a network of computers (nodes) for verification.</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0 w-8 h-8 gradient-purple-blue rounded-full flex items-center justify-center text-white font-bold">3</div>
                                <div>
                                    <h4 class="font-bold text-gray-900 mb-1">Block Creation</h4>
                                    <p class="text-gray-600">Verified transactions are combined with others to form a new block of data.</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0 w-8 h-8 gradient-purple-blue rounded-full flex items-center justify-center text-white font-bold">4</div>
                                <div>
                                    <h4 class="font-bold text-gray-900 mb-1">Chain Added</h4>
                                    <p class="text-gray-600">The new block is added to the blockchain, creating a permanent, unalterable record.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="relative">
                            <div class="absolute inset-0 bg-purple-400 rounded-lg opacity-20 blur-2xl"></div>
                            <div class="relative bg-gradient-to-br from-purple-600 to-blue-600 p-8 rounded-2xl text-white">
                                <div class="space-y-6">
                                    <div class="glass p-4 rounded-lg">
                                        <div class="font-mono text-sm mb-2">Block #1</div>
                                        <div class="text-xs opacity-75">Hash: 0x1a2b3c...</div>
                                    </div>
                                    <div class="flex justify-center">
                                        <div class="w-1 h-8 bg-white opacity-50"></div>
                                    </div>
                                    <div class="glass p-4 rounded-lg">
                                        <div class="font-mono text-sm mb-2">Block #2</div>
                                        <div class="text-xs opacity-75">Hash: 0x4d5e6f...</div>
                                    </div>
                                    <div class="flex justify-center">
                                        <div class="w-1 h-8 bg-white opacity-50"></div>
                                    </div>
                                    <div class="glass p-4 rounded-lg">
                                        <div class="font-mono text-sm mb-2">Block #3</div>
                                        <div class="text-xs opacity-75">Hash: 0x7g8h9i...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Payment Methods -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-on-scroll">
                <h2 class="text-4xl font-bold mb-4 gradient-text">Popular Payment Methods</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Start accepting cryptocurrency and bank transfers today
                </p>
            </div>

            <?php if (!empty($featured_methods)): ?>
            <div class="grid md:grid-cols-3 gap-8 mb-12">
                <?php foreach ($featured_methods as $index => $method): ?>
                <?php 
                $is_bank = isset($method['bank_name']);
                $gradient_class = $is_bank ? 'gradient-green-teal' : 'gradient-purple-blue';
                $border_class = $is_bank ? 'hover:border-green-400' : 'hover:border-purple-400';
                ?>
                <div class="bg-white border-2 border-gray-200 rounded-2xl p-8 <?php echo $border_class; ?> hover:shadow-xl card-hover animate-on-scroll" style="animation-delay: <?php echo $index * 0.2; ?>s">
                    <?php if ($is_bank): ?>
                        <!-- Bank Method Card -->
                        <div class="flex items-center justify-between mb-6">
                            <?php if ($method['logo_path']): ?>
                            <img src="<?php echo e($method['logo_path']); ?>" alt="<?php echo e($method['bank_name']); ?>" class="w-16 h-16 object-contain">
                            <?php else: ?>
                            <div class="w-16 h-16 gradient-green-teal rounded-full flex items-center justify-center text-white text-2xl font-bold">
                                🏦
                            </div>
                            <?php endif; ?>
                            <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full font-bold text-sm">
                                <?php echo e($method['currency']); ?>
                            </span>
                        </div>
                        <h3 class="text-2xl font-bold mb-4 text-gray-900"><?php echo e($method['bank_name']); ?></h3>
                        <p class="text-gray-600 mb-6">
                            Bank Transfer • <?php echo e($method['country'] ?? 'International'); ?>
                            <br>Account Type: <?php echo e(ucfirst($method['account_type'])); ?>
                        </p>
                        <div class="mb-6">
                            <span class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                🏦 Bank Transfer
                            </span>
                        </div>
                    <?php else: ?>
                        <!-- Crypto Method Card -->
                        <div class="flex items-center justify-between mb-6">
                            <?php if ($method['logo_path']): ?>
                            <img src="<?php echo e($method['logo_path']); ?>" alt="<?php echo e($method['name']); ?>" class="w-16 h-16 object-contain">
                            <?php else: ?>
                            <div class="w-16 h-16 gradient-purple-blue rounded-full flex items-center justify-center text-white text-2xl font-bold">
                                <?php echo e(substr($method['symbol'], 0, 1)); ?>
                            </div>
                            <?php endif; ?>
                            <span class="px-4 py-2 gradient-blue-teal text-white rounded-full font-bold text-sm">
                                <?php echo e($method['symbol']); ?>
                            </span>
                        </div>
                        <h3 class="text-2xl font-bold mb-4 text-gray-900"><?php echo e($method['name']); ?></h3>
                        <p class="text-gray-600 mb-6">
                            <?php echo e(truncate($method['description'], 120)); ?>
                        </p>
                        <?php if ($method['networks']): ?>
                        <div class="mb-6">
                            <?php 
                            $networks = explode(',', $method['networks']);
                            foreach (array_slice($networks, 0, 2) as $network): 
                            ?>
                            <span class="network-badge"><?php echo e(trim($network)); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <a href="payment-methods.php" class="block text-center px-6 py-3 <?php echo $gradient_class; ?> text-white rounded-lg font-semibold hover:shadow-lg transition">
                        View Details
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="text-center animate-on-scroll">
                <a href="payment-methods.php" class="inline-block px-8 py-4 border-2 border-purple-600 text-purple-600 rounded-lg font-semibold hover:bg-purple-600 hover:text-white transition">
                    View All Payment Methods →
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 gradient-purple-blue text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-on-scroll">
                <h2 class="text-4xl font-bold mb-4">Why Choose BlockPayOption?</h2>
                <p class="text-xl text-purple-100 max-w-3xl mx-auto">
                    The most trusted platform for cryptocurrency payments
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="glass-dark p-6 rounded-xl text-center animate-on-scroll">
                    <div class="text-4xl mb-4">⚡</div>
                    <h3 class="text-xl font-bold mb-2">Instant Setup</h3>
                    <p class="text-purple-100 text-sm">Get started in minutes with our simple integration</p>
                </div>
                <div class="glass-dark p-6 rounded-xl text-center animate-on-scroll" style="animation-delay: 0.1s">
                    <div class="text-4xl mb-4">🔐</div>
                    <h3 class="text-xl font-bold mb-2">Bank-Level Security</h3>
                    <p class="text-purple-100 text-sm">Military-grade encryption protects your transactions</p>
                </div>
                <div class="glass-dark p-6 rounded-xl text-center animate-on-scroll" style="animation-delay: 0.2s">
                    <div class="text-4xl mb-4">💎</div>
                    <h3 class="text-xl font-bold mb-2">Low Fees</h3>
                    <p class="text-purple-100 text-sm">Save money with our competitive transaction fees</p>
                </div>
                <div class="glass-dark p-6 rounded-xl text-center animate-on-scroll" style="animation-delay: 0.3s">
                    <div class="text-4xl mb-4">🌍</div>
                    <h3 class="text-xl font-bold mb-2">Global Access</h3>
                    <p class="text-purple-100 text-sm">Accept payments from anywhere in the world</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-br from-gray-50 to-blue-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center animate-on-scroll">
            <h2 class="text-4xl font-bold mb-6 gradient-text">Ready to Get Started?</h2>
            <p class="text-xl text-gray-600 mb-8">
                Join thousands of businesses already accepting cryptocurrency payments
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="payment-methods.php" class="px-8 py-4 gradient-purple-blue text-white rounded-lg font-semibold hover:shadow-2xl hover-lift transition">
                    Explore Payment Methods
                </a>
                <a href="tutorials.php" class="px-8 py-4 bg-white border-2 border-purple-600 text-purple-600 rounded-lg font-semibold hover:bg-purple-600 hover:text-white transition">
                    Read Tutorials
                </a>
            </div>
        </div>
    </section>

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
</body>
</html>
