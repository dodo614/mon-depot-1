<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$user = currentUser();

$id = (int)($_GET['id'] ?? 0);
$stmt = getDB()->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user['id']]);

header('Location: tasks.php');
exit;
