<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$user = currentUser();
$db = getDB();
$uid = $user['id'];

$search = trim($_GET['q'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'due_date';
$overdueOnly = isset($_GET['overdue']);

$sql = "SELECT * FROM tasks WHERE user_id = ?";
$params = [$uid];

if ($search !== '') {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (in_array($statusFilter, ['en_attente', 'terminee'])) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}
if ($categoryFilter !== '') {
    $sql .= " AND category = ?";
    $params[] = $categoryFilter;
}
if ($overdueOnly) {
    $sql .= " AND due_date < CURDATE() AND status != 'terminee'";
}

$allowedSorts = [
    'due_date' => 'due_date ASC',
    'priority' => "FIELD(priority,'Urgent','Élevé','Moyen','Faible')",
    'created' => 'created_at DESC',
];
$sql .= " ORDER BY " . ($allowedSorts[$sort] ?? $allowedSorts['due_date']);

$stmt = $db->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

$catStmt = $db->prepare("SELECT DISTINCT category FROM tasks WHERE user_id = ? ORDER BY category");
$catStmt->execute([$uid]);
$categories = array_column($catStmt->fetchAll(), 'category');

$pageTitle = "Mes tâches";
$activeMenu = "tasks";
include __DIR__ . '/includes/header.php';
?>
<form method="GET" class="filters-bar card">
    <input type="text" class="form-control" name="q" placeholder="🔍 Rechercher une tâche..." value="<?= e($search) ?>">
    <select class="form-control" name="status">
        <option value="">Tous les statuts</option>
        <option value="en_attente" <?= $statusFilter==='en_attente'?'selected':'' ?>>En attente</option>
        <option value="terminee" <?= $statusFilter==='terminee'?'selected':'' ?>>Terminée</option>
    </select>
    <select class="form-control" name="category">
        <option value="">Toutes les catégories</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= e($c) ?>" <?= $categoryFilter===$c?'selected':'' ?>><?= e($c) ?></option>
        <?php endforeach; ?>
    </select>
    <select class="form-control" name="sort">
        <option value="due_date" <?= $sort==='due_date'?'selected':'' ?>>Trier par date</option>
        <option value="priority" <?= $sort==='priority'?'selected':'' ?>>Trier par priorité</option>
        <option value="created" <?= $sort==='created'?'selected':'' ?>>Plus récentes</option>
    </select>
    <label style="display:flex; align-items:center; gap:6px; font-size:14px;">
        <input type="checkbox" name="overdue" <?= $overdueOnly?'checked':'' ?>> En retard
    </label>
    <button type="submit" class="btn btn-primary">Filtrer</button>
    <a href="tasks.php" class="btn btn-secondary">Réinitialiser</a>
</form>

<?php if (empty($tasks)): ?>
    <div class="empty-state card">
        <div class="emoji">🗒️</div>
        <p>Aucune tâche ne correspond à votre recherche.</p>
    </div>
<?php else: ?>
<div class="task-grid">
    <?php foreach ($tasks as $t):
        $isOverdue = $t['due_date'] && $t['due_date'] < date('Y-m-d') && $t['status'] !== 'terminee';
    ?>
        <div class="task-card <?= $t['status']==='terminee'?'done':'' ?>">
            <h3 class="task-title"><?= e($t['title']) ?></h3>
            <?php if (!empty($t['description'])): ?><p class="task-desc"><?= e(mb_strimwidth($t['description'],0,100,'…')) ?></p><?php endif; ?>
            <div class="task-meta">
                <span class="badge <?= priorityClass($t['priority']) ?>"><?= e($t['priority']) ?></span>
                <span><?= e($t['category']) ?></span>
                <span class="<?= $isOverdue ? 'task-overdue' : '' ?>">📅 <?= formatDateFr($t['due_date']) ?><?= $isOverdue ? ' (en retard)' : '' ?></span>
            </div>
            <div class="task-actions">
                <?php if ($t['status'] === 'terminee'): ?>
                    <a href="toggle_task.php?id=<?= (int)$t['id'] ?>&to=en_attente" class="btn btn-secondary">↩️ Remettre en attente</a>
                <?php else: ?>
                    <a href="toggle_task.php?id=<?= (int)$t['id'] ?>&to=terminee" class="btn btn-primary">✔️ Terminer</a>
                <?php endif; ?>
                <a href="edit_task.php?id=<?= (int)$t['id'] ?>" class="btn btn-secondary">✏️ Modifier</a>
                <a href="delete_task.php?id=<?= (int)$t['id'] ?>" class="btn btn-danger js-confirm-delete">🗑️ Supprimer</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
