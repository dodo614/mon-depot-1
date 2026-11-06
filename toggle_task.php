<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$user = currentUser();

$id = (int)($_GET['id'] ?? 0);
$to = $_GET['to'] ?? '';
if (!in_array($to, ['terminee', 'en_attente'])) { header('Location: tasks.php'); exit; }

$stmt = getDB()->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
$stmt->execute([$to, $id, $user['id']]);

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'tasks.php'));
exit;
