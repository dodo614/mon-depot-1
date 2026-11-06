<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$user = currentUser();
$db = getDB();
$uid = $user['id'];

$byPriority = $db->prepare("SELECT priority, COUNT(*) c FROM tasks WHERE user_id = ? GROUP BY priority");
$byPriority->execute([$uid]);
$byPriority = $byPriority->fetchAll();

$byCategory = $db->prepare("SELECT category, COUNT(*) c FROM tasks WHERE user_id = ? GROUP BY category ORDER BY c DESC");
$byCategory->execute([$uid]);
$byCategory = $byCategory->fetchAll();

$total = $db->prepare("SELECT COUNT(*) c FROM tasks WHERE user_id = ?");
$total->execute([$uid]); $total = (int)$total->fetch()['c'];

$done = $db->prepare("SELECT COUNT(*) c FROM tasks WHERE user_id = ? AND status='terminee'");
$done->execute([$uid]); $done = (int)$done->fetch()['c'];

$overdue = $db->prepare("SELECT COUNT(*) c FROM tasks WHERE user_id = ? AND due_date < CURDATE() AND status != 'terminee'");
$overdue->execute([$uid]); $overdue = (int)$overdue->fetch()['c'];

$pageTitle = "Statistiques";
$activeMenu = "stats";
include __DIR__ . '/includes/header.php';
$catCounts = array_column($byCategory, 'c');
$maxCat = empty($catCounts) ? 1 : max(1, max($catCounts));
?>
<div class="stats-grid fade-in">
    <div class="stat-card"><div class="stat-icon">📋</div><div class="stat-value"><?= $total ?></div><div class="stat-label">Total</div></div>
    <div class="stat-card"><div class="stat-icon">✔️</div><div class="stat-value"><?= $done ?></div><div class="stat-label">Terminées</div></div>
    <div class="stat-card"><div class="stat-icon">⏰</div><div class="stat-value"><?= $overdue ?></div><div class="stat-label">En retard</div></div>
    <div class="stat-card"><div class="stat-icon">📈</div><div class="stat-value"><?= $total ? round($done/$total*100) : 0 ?>%</div><div class="stat-label">Taux de complétion</div></div>
</div>

<div class="card fade-in" style="margin-bottom:20px;">
    <h2 class="section-title">Répartition par priorité</h2>
    <?php foreach (['Urgent','Élevé','Moyen','Faible'] as $p):
        $row = array_filter($byPriority, fn($r)=>$r['priority']===$p);
        $count = $row ? array_values($row)[0]['c'] : 0;
        $pct = $total ? round($count/$total*100) : 0;
    ?>
        <div style="margin-bottom:14px;">
            <div style="display:flex; justify-content:space-between; font-size:14px; margin-bottom:4px;">
                <span class="badge <?= priorityClass($p) ?>"><?= $p ?></span><span><?= $count ?></span>
            </div>
            <div class="progress-bar-track"><div class="progress-bar-fill" style="width:<?= $pct ?>%;"></div></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card fade-in">
    <h2 class="section-title">Répartition par catégorie</h2>
    <?php if (empty($byCategory)): ?>
        <p style="color:#8a93a6;">Aucune donnée disponible.</p>
    <?php endif; ?>
    <?php foreach ($byCategory as $row): ?>
        <div style="margin-bottom:14px;">
            <div style="display:flex; justify-content:space-between; font-size:14px; margin-bottom:4px;">
                <span><?= e($row['category']) ?></span><span><?= $row['c'] ?></span>
            </div>
            <div class="progress-bar-track"><div class="progress-bar-fill" style="width:<?= round($row['c']/$maxCat*100) ?>%;"></div></div>
        </div>
    <?php endforeach; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
