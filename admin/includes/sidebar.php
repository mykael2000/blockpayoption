<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

function is_active($page, $dir = '') {
    global $current_page, $current_dir;
    if ($dir) {
        return $current_dir === $dir ? 'bg-purple-100 text-purple-700' : 'text-gray-600 hover:bg-gray-100';
    }
    return $current_page === $page ? 'bg-purple-100 text-purple-700' : 'text-gray-600 hover:bg-gray-100';
}
?>
<aside class="w-64 bg-white shadow-lg min-h-screen">
    <nav class="p-4 space-y-2">
        <!-- Dashboard -->
        <a href="/admin/dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?= is_active('dashboard.php') ?>">
            <span class="text-xl">📊</span>
            <span class="font-medium">Dashboard</span>
        </a>
        
        <!-- Payment Methods -->
        <a href="/admin/payment-methods/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?= is_active('', 'payment-methods') ?>">
            <span class="text-xl">💳</span>
            <span class="font-medium">Crypto Payment Methods</span>
        </a>
        
        <!-- Bank Payment Methods -->
        <a href="/admin/bank-methods/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?= is_active('', 'bank-methods') ?>">
            <span class="text-xl">🏦</span>
            <span class="font-medium">Bank Payment Methods</span>
        </a>
        
        <!-- Tutorials -->
        <a href="/admin/tutorials/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?= is_active('', 'tutorials') ?>">
            <span class="text-xl">📚</span>
            <span class="font-medium">Tutorials</span>
        </a>
        
        <!-- Platforms -->
        <a href="/admin/platforms/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?= is_active('', 'platforms') ?>">
            <span class="text-xl">🏢</span>
            <span class="font-medium">Platforms</span>
        </a>
        
        <!-- Payment Links -->
        <a href="/admin/payment-links/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?= is_active('', 'payment-links') ?>">
            <span class="text-xl">🔗</span>
            <span class="font-medium">Payment Links</span>
        </a>
    </nav>
</aside>
