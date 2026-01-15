<?php
/**
 * Admin Logout
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

logout_user();
header('Location: /admin/login.php');
exit;
