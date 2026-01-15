<nav class="bg-white shadow-md sticky top-0 z-50">
    <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                <?= SITE_NAME ?>
            </h1>
            <span class="text-sm text-gray-500">Admin Panel</span>
        </div>
        
        <div class="flex items-center space-x-4">
            <a href="/" target="_blank" class="text-gray-600 hover:text-purple-600 transition text-sm">
                View Site →
            </a>
            <div class="border-l pl-4">
                <span class="text-sm text-gray-600"><?= e($_SESSION['admin_username']) ?></span>
                <a href="/admin/logout.php" class="ml-3 text-sm text-red-600 hover:text-red-700 font-medium">
                    Logout
                </a>
            </div>
        </div>
    </div>
</nav>
