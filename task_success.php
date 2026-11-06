<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$user = currentUser();

$id = (int)($_GET['id'] ?? 0);
$stmt = getDB()->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user['id']]);
$task = $stmt->fetch();
if (!$task) { header('Location: dashboard.php'); exit; }

$theme = $user['theme'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tâche ajoutée — MY TODO</title>
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/theme-<?= e($theme) ?>.css">
<link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<div class="success-wrap">
    <div class="success-card fade-in">
        <div class="success-icon">🎉</div>
        <h1>Votre tâche a été ajoutée avec succès !</h1>
        <p style="color:#8a93a6;">Voici un récapitulatif de votre nouvelle tâche.</p>
        <div class="success-info card">
            <div><strong>Titre :</strong> <?= e($task['title']) ?></div>
            <div><strong>Catégorie :</strong> <?= e($task['category']) ?></div>
            <div><strong>Priorité :</strong> <span class="badge <?= priorityClass($task['priority']) ?>"><?= e($task['priority']) ?></span></div>
            <div><strong>Date limite :</strong> <?= formatDateFr($task['due_date']) ?> <?= e($task['due_time']) ?></div>
            <?php if (!empty($task['description'])): ?><div><strong>Description :</strong> <?= e($task['description']) ?></div><?php endif; ?>
        </div>
        <div class="success-actions">
            <a href="tasks.php" class="btn btn-primary">Voir mes tâches</a>
            <a href="add_task.php" class="btn btn-outline">Ajouter une autre tâche</a>
            <a href="dashboard.php" class="btn btn-secondary">Retour au tableau de bord</a>
        </div>
    </div>
</div>
</body>
</html>
