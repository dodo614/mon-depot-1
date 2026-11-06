<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$user = currentUser();

$stmt = getDB()->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$user['id']]);

startSecureSession();
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
