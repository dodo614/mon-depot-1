<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$user = currentUser();
$errors = [];
$old = ['title'=>'','description'=>'','category'=>'','start_date'=>'','due_date'=>'','due_time'=>'','priority'=>'Moyen'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck($_POST['csrf_token'] ?? '')) {
        $errors[] = "Session expirée, veuillez réessayer.";
    }
    $old['title'] = trim($_POST['title'] ?? '');
    $old['description'] = trim($_POST['description'] ?? '');
    $old['category'] = trim($_POST['category'] ?? '') ?: 'Général';
    $old['start_date'] = $_POST['start_date'] ?? '';
    $old['due_date'] = $_POST['due_date'] ?? '';
    $old['due_time'] = $_POST['due_time'] ?? '';
    $old['priority'] = $_POST['priority'] ?? 'Moyen';

    if ($old['title'] === '') $errors[] = "Le titre de la tâche est obligatoire.";
    if (!in_array($old['priority'], ['Faible','Moyen','Élevé','Urgent'])) $errors[] = "Priorité invalide.";

    if (empty($errors)) {
        $stmt = getDB()->prepare(
            "INSERT INTO tasks (user_id, title, description, category, start_date, due_date, due_time, priority) VALUES (?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([
            $user['id'], $old['title'], $old['description'], $old['category'],
            $old['start_date'] ?: null, $old['due_date'] ?: null, $old['due_time'] ?: null, $old['priority']
        ]);
        $newId = getDB()->lastInsertId();
        header('Location: task_success.php?id=' . $newId);
        exit;
    }
}

$pageTitle = "Ajouter une tâche";
$activeMenu = "add";
include __DIR__ . '/includes/header.php';
?>
<div class="card form-card fade-in">
    <?php if ($errors): ?>
        <div class="alert alert-error"><?php foreach ($errors as $err): ?>• <?= e($err) ?><br><?php endforeach; ?></div>
    <?php endif; ?>
    <form method="POST" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <div class="form-group">
            <label for="title">Titre de la tâche</label>
            <input class="form-control" type="text" id="title" name="title" value="<?= e($old['title']) ?>" placeholder="Ex : Préparer la présentation" required>
        </div>
        <div class="form-group">
            <label for="description">Description détaillée</label>
            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Détails de la tâche..."><?= e($old['description']) ?></textarea>
        </div>
        <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
            <div class="form-group">
                <label for="category">Catégorie</label>
                <input class="form-control" type="text" id="category" name="category" value="<?= e($old['category']) ?>" placeholder="Ex : Études, Travail, Perso">
            </div>
            <div class="form-group">
                <label for="priority">Niveau de priorité</label>
                <select class="form-control" id="priority" name="priority">
                    <?php foreach (['Faible','Moyen','Élevé','Urgent'] as $p): ?>
                        <option value="<?= $p ?>" <?= $old['priority'] === $p ? 'selected' : '' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px;">
            <div class="form-group">
                <label for="start_date">Date de début</label>
                <input class="form-control" type="date" id="start_date" name="start_date" value="<?= e($old['start_date']) ?>">
            </div>
            <div class="form-group">
                <label for="due_date">Date limite</label>
                <input class="form-control" type="date" id="due_date" name="due_date" value="<?= e($old['due_date']) ?>">
            </div>
            <div class="form-group">
                <label for="due_time">Heure</label>
                <input class="form-control" type="time" id="due_time" name="due_time" value="<?= e($old['due_time']) ?>">
            </div>
        </div>
        <div style="display:flex; gap:12px; margin-top:10px;">
            <button type="submit" class="btn btn-primary">💾 Enregistrer la tâche</button>
            <a href="dashboard.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
