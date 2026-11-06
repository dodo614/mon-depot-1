<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$user = currentUser();
$db = getDB();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user['id']]);
$task = $stmt->fetch();
if (!$task) { header('Location: tasks.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck($_POST['csrf_token'] ?? '')) {
        $errors[] = "Session expirée, veuillez réessayer.";
    }
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '') ?: 'Général';
    $start_date = $_POST['start_date'] ?? '';
    $due_date = $_POST['due_date'] ?? '';
    $due_time = $_POST['due_time'] ?? '';
    $priority = $_POST['priority'] ?? 'Moyen';

    if ($title === '') $errors[] = "Le titre est obligatoire.";

    if (empty($errors)) {
        $upd = $db->prepare("UPDATE tasks SET title=?, description=?, category=?, start_date=?, due_date=?, due_time=?, priority=? WHERE id=? AND user_id=?");
        $upd->execute([$title, $description, $category, $start_date ?: null, $due_date ?: null, $due_time ?: null, $priority, $id, $user['id']]);
        header('Location: tasks.php');
        exit;
    }
    $task = array_merge($task, compact('title','description','category','start_date','due_date','due_time','priority'));
}

$pageTitle = "Modifier la tâche";
$activeMenu = "tasks";
include __DIR__ . '/includes/header.php';
?>
<div class="card form-card fade-in">
    <?php if ($errors): ?>
        <div class="alert alert-error"><?php foreach ($errors as $err): ?>• <?= e($err) ?><br><?php endforeach; ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="id" value="<?= (int)$task['id'] ?>">
        <div class="form-group">
            <label for="title">Titre de la tâche</label>
            <input class="form-control" type="text" id="title" name="title" value="<?= e($task['title']) ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description détaillée</label>
            <textarea class="form-control" id="description" name="description" rows="4"><?= e($task['description']) ?></textarea>
        </div>
        <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
            <div class="form-group">
                <label for="category">Catégorie</label>
                <input class="form-control" type="text" id="category" name="category" value="<?= e($task['category']) ?>">
            </div>
            <div class="form-group">
                <label for="priority">Niveau de priorité</label>
                <select class="form-control" id="priority" name="priority">
                    <?php foreach (['Faible','Moyen','Élevé','Urgent'] as $p): ?>
                        <option value="<?= $p ?>" <?= $task['priority'] === $p ? 'selected' : '' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px;">
            <div class="form-group">
                <label for="start_date">Date de début</label>
                <input class="form-control" type="date" id="start_date" name="start_date" value="<?= e($task['start_date']) ?>">
            </div>
            <div class="form-group">
                <label for="due_date">Date limite</label>
                <input class="form-control" type="date" id="due_date" name="due_date" value="<?= e($task['due_date']) ?>">
            </div>
            <div class="form-group">
                <label for="due_time">Heure</label>
                <input class="form-control" type="time" id="due_time" name="due_time" value="<?= e($task['due_time']) ?>">
            </div>
        </div>
        <div style="display:flex; gap:12px;">
            <button type="submit" class="btn btn-primary">💾 Enregistrer les modifications</button>
            <a href="tasks.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
