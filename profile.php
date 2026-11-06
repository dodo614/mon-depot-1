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
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($first_name === '' || $last_name === '') $errors[] = "Le nom et le prénom sont obligatoires.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Adresse e-mail invalide.";

        if (empty($errors) && $email !== $user['email']) {
            $chk = getDB()->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $chk->execute([$email, $user['id']]);
            if ($chk->fetch()) $errors[] = "Cette adresse e-mail est déjà utilisée par un autre compte.";
        }

        if (empty($errors)) {
            $stmt = getDB()->prepare("UPDATE users SET first_name=?, last_name=?, email=? WHERE id=?");
            $stmt->execute([$first_name, $last_name, $email, $user['id']]);
            $success = "Profil mis à jour avec succès.";
            $user = currentUser();
        }
    }
}

$pageTitle = "Mon profil";
$activeMenu = "profile";
include __DIR__ . '/includes/header.php';
$age = calculateAge($user['birthdate']);
?>
<div class="card form-card fade-in">
    <?php if ($errors): ?><div class="alert alert-error"><?php foreach($errors as $e2): ?>• <?= e($e2) ?><br><?php endforeach; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

    <div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
        <div class="avatar" style="width:64px; height:64px; font-size:22px;"><?= e(mb_strtoupper(mb_substr($user['first_name'],0,1).mb_substr($user['last_name'],0,1))) ?></div>
        <div>
            <strong style="font-size:18px;"><?= e($user['first_name'].' '.$user['last_name']) ?></strong><br>
            <span style="color:#8a93a6; font-size:14px;"><?= $age ?> ans · Thème <?= e(ucfirst($user['theme'])) ?></span>
        </div>
    </div>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
            <div class="form-group">
                <label>Prénom</label>
                <input class="form-control" type="text" name="first_name" value="<?= e($user['first_name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Nom</label>
                <input class="form-control" type="text" name="last_name" value="<?= e($user['last_name']) ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label>Adresse e-mail</label>
            <input class="form-control" type="email" name="email" value="<?= e($user['email']) ?>" required>
        </div>
        <div class="form-group">
            <label>Date de naissance</label>
            <input class="form-control" type="text" value="<?= e(formatDateFr($user['birthdate'])) ?>" disabled>
        </div>
        <div class="form-group">
            <label>Sexe</label>
            <input class="form-control" type="text" value="<?= e(ucfirst($user['gender'])) ?>" disabled>
        </div>
        <button type="submit" class="btn btn-primary">💾 Enregistrer les modifications</button>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
