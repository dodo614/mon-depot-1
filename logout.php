<?php
require_once __DIR__ . '/includes/functions.php';
startSecureSession();
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
