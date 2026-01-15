<?php
/**
 * 404 Error Page - Page Not Found
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-purple-600 via-blue-600 to-teal-500 min-h-screen flex items-center justify-center p-4">
    <div class="text-center">
        <h1 class="text-9xl font-bold text-white mb-4">404</h1>
        <h2 class="text-3xl font-bold text-white mb-4">Page Not Found</h2>
        <p class="text-xl text-purple-100 mb-8">The page you're looking for doesn't exist.</p>
        <div class="space-x-4">
            <a href="/" class="inline-block bg-white text-purple-600 px-8 py-3 rounded-lg font-semibold hover:bg-purple-50 transition">
                Go Home
            </a>
            <a href="javascript:history.back()" class="inline-block bg-purple-800 text-white px-8 py-3 rounded-lg font-semibold hover:bg-purple-900 transition">
                Go Back
            </a>
        </div>
    </div>
</body>
</html>
