<?php
require_once __DIR__ . '/includes/functions.php';
startSecureSession();
header('Location: ' . (empty($_SESSION['user_id']) ? 'login.php' : 'dashboard.php'));
exit;
