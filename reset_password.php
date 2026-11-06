<?php
require_once __DIR__ . '/includes/functions.php';
startSecureSession();

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$stmt = getDB()->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

$error = '';
if (!$user) {
    $error = "Ce lien de réinitialisation est invalide ou a expiré.";
}

if ($user && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck($_POST['csrf_token'] ?? '')) {
        $error = "Session expirée, veuillez réessayer.";
    } else {
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_new_password'] ?? '';
        if (strlen($new) < 6) {
            $error = "Le mot de passe doit contenir au moins 6 caractères.";
        } elseif ($new !== $confirm) {
            $error = "Les mots de passe ne correspondent pas.";
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $upd = getDB()->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            $upd->execute([$hash, $user['id']]);
            header('Location: login.php?registered=0');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Réinitialiser le mot de passe — MY TODO</title>
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card fade-in">
        <div class="auth-logo">🔑</div>
        <h1 class="auth-title">Nouveau mot de passe</h1>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <?php if ($user): ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="token" value="<?= e($token) ?>">
            <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input class="form-control" type="password" name="new_password" required>
            </div>
            <div class="form-group">
                <label>Confirmer le mot de passe</label>
                <input class="form-control" type="password" name="confirm_new_password" required>
            </div>
            <button type="submit" class="auth-btn">Réinitialiser</button>
        </form>
        <?php else: ?>
            <div class="auth-footer"><a href="forgot_password.php">Demander un nouveau lien</a></div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
