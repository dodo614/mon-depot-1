<?php
require_once __DIR__ . '/includes/functions.php';
startSecureSession();
$message = '';
$error = '';
$resetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck($_POST['csrf_token'] ?? '')) {
        $error = "Session expirée, veuillez réessayer.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $stmt = getDB()->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Toujours afficher le même message, qu'un compte existe ou non (sécurité)
        $message = "Si un compte existe avec cette adresse, un lien de réinitialisation a été généré.";

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $upd = getDB()->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $upd->execute([$token, $expires, $user['id']]);
            // En local, sans serveur mail : on affiche le lien directement pour test
            $resetLink = "reset_password.php?token=" . $token;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mot de passe oublié — MY TODO</title>
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card fade-in">
        <div class="auth-logo">🔑</div>
        <h1 class="auth-title">Mot de passe oublié</h1>
        <p class="auth-subtitle">Entrez votre e-mail pour réinitialiser votre mot de passe</p>

        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
        <?php if ($resetLink): ?>
            <div class="alert alert-success">Mode local (pas d'envoi d'e-mail) : <a href="<?= e($resetLink) ?>" style="text-decoration:underline;">Cliquez ici pour réinitialiser</a></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <div class="form-group">
                <label for="email">Adresse e-mail</label>
                <input class="form-control" type="email" id="email" name="email" required autofocus>
            </div>
            <button type="submit" class="auth-btn">Envoyer le lien</button>
        </form>
        <div class="auth-footer"><a href="login.php">← Retour à la connexion</a></div>
    </div>
</div>
</body>
</html>
