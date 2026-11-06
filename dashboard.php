<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$user = currentUser();
if (!$user) { header('Location: login.php'); exit; }

$db = getDB();
$uid = $user['id'];

$total = $db->prepare("SELECT COUNT(*) c FROM tasks WHERE user_id = ?");
$total->execute([$uid]); $total = $total->fetch()['c'];

$done = $db->prepare("SELECT COUNT(*) c FROM tasks WHERE user_id = ? AND status = 'terminee'");
$done->execute([$uid]); $done = $done->fetch()['c'];

$pending = $total - $done;

$urgent = $db->prepare("SELECT COUNT(*) c FROM tasks WHERE user_id = ? AND priority = 'Urgent' AND status != 'terminee'");
$urgent->execute([$uid]); $urgent = $urgent->fetch()['c'];

$progress = $total > 0 ? round(($done / $total) * 100) : 0;

$recent = $db->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC LIMIT 6");
$recent->execute([$uid]);
$recentTasks = $recent->fetchAll();

$pageTitle = "Tableau de bord";
$activeMenu = "dashboard";
include __DIR__ . '/includes/header.php';
?>
<div class="card fade-in" style="margin-bottom:26px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
    <div>
        <h2 style="font-size:20px; font-weight:800;">Bonjour, <?= e($user['first_name']) ?> 👋</h2>
        <p style="color:#8a93a6; margin-top:4px;">Voici un aperçu de vos tâches aujourd'hui.</p>
    </div>
    <a href="add_task.php" class="btn btn-primary">➕ Ajouter une tâche</a>
</div>

<div class="stats-grid fade-in">
    <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-value"><?= (int)$total ?></div>
        <div class="stat-label">Total des tâches</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✔️</div>
        <div class="stat-value"><?= (int)$done ?></div>
        <div class="stat-label">Terminées</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-value"><?= (int)$pending ?></div>
        <div class="stat-label">En attente</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🔥</div>
        <div class="stat-value"><?= (int)$urgent ?></div>
        <div class="stat-label">Urgentes</div>
    </div>
</div>

<div class="progress-card fade-in">
    <div style="display:flex; justify-content:space-between;">
        <strong>Progression globale</strong>
        <strong><?= $progress ?>%</strong>
    </div>
    <div class="progress-bar-track"><div class="progress-bar-fill" style="width: <?= $progress ?>%;"></div></div>
</div>

<h2 class="section-title">Tâches récentes</h2>
<?php if (empty($recentTasks)): ?>
    <div class="empty-state card">
        <div class="emoji">🗒️</div>
        <p>Aucune tâche pour le moment. Commencez par en ajouter une !</p>
    </div>
<?php else: ?>
<div class="task-grid">
    <?php foreach ($recentTasks as $t): ?>
        <div class="task-card <?= $t['status'] === 'terminee' ? 'done' : '' ?>">
            <h3 class="task-title"><?= e($t['title']) ?></h3>
            <p class="task-desc"><?= e(mb_strimwidth($t['description'] ?? '', 0, 90, '…')) ?></p>
            <div class="task-meta">
                <span class="badge <?= priorityClass($t['priority']) ?>"><?= e($t['priority']) ?></span>
                <span>📅 <?= formatDateFr($t['due_date']) ?></span>
            </div>
            <div class="task-actions">
                <a href="edit_task.php?id=<?= (int)$t['id'] ?>" class="btn btn-secondary">Modifier</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
