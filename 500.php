<?php
/**
 * 500 Error Page - Internal Server Error
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-red-600 via-orange-600 to-yellow-500 min-h-screen flex items-center justify-center p-4">
    <div class="text-center">
        <h1 class="text-9xl font-bold text-white mb-4">500</h1>
        <h2 class="text-3xl font-bold text-white mb-4">Server Error</h2>
        <p class="text-xl text-red-100 mb-8">Something went wrong on our end. Please try again later.</p>
        <div class="space-x-4">
            <a href="/" class="inline-block bg-white text-red-600 px-8 py-3 rounded-lg font-semibold hover:bg-red-50 transition">
                Go Home
            </a>
            <a href="javascript:location.reload()" class="inline-block bg-red-800 text-white px-8 py-3 rounded-lg font-semibold hover:bg-red-900 transition">
                Retry
            </a>
        </div>
    </div>
</body>
</html>
