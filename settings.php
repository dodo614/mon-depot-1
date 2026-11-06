<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$user = currentUser();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck($_POST['csrf_token'] ?? '')) {
        $errors[] = "Session expirée, veuillez réessayer.";
    } else {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_new_password'] ?? '';

        if (!password_verify($current, $user['password_hash'])) {
            $errors[] = "Le mot de passe actuel est incorrect.";
        }
        if (strlen($new) < 6) $errors[] = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
        if ($new !== $confirm) $errors[] = "Les mots de passe ne correspondent pas.";

        if (empty($errors)) {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = getDB()->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$hash, $user['id']]);
            $success = "Mot de passe mis à jour avec succès.";
        }
    }
}

$pageTitle = "Paramètres";
$activeMenu = "settings";
include __DIR__ . '/includes/header.php';
?>
<div class="card form-card fade-in">
    <h2 class="section-title">Changer le mot de passe</h2>
    <?php if ($errors): ?><div class="alert alert-error"><?php foreach($errors as $e2): ?>• <?= e($e2) ?><br><?php endforeach; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <div class="form-group">
            <label>Mot de passe actuel</label>
            <input class="form-control" type="password" name="current_password" required>
        </div>
        <div class="form-group">
            <label>Nouveau mot de passe</label>
            <input class="form-control" type="password" name="new_password" required>
        </div>
        <div class="form-group">
            <label>Confirmer le nouveau mot de passe</label>
            <input class="form-control" type="password" name="confirm_new_password" required>
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>

<div class="card form-card fade-in" style="margin-top:20px; border-left:4px solid #b91c1c;">
    <h2 class="section-title" style="color:#b91c1c;">Zone sensible</h2>
    <p style="color:#8a93a6; margin-bottom:14px; font-size:14px;">La suppression du compte est définitive et effacera toutes vos tâches.</p>
    <a href="delete_account.php" class="btn btn-danger js-confirm-delete">Supprimer mon compte</a>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
