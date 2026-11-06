<?php
require_once __DIR__ . '/includes/functions.php';
startSecureSession();

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$successMsg = $_GET['registered'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck($_POST['csrf_token'] ?? '')) {
        $error = "Session expirée, veuillez réessayer.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $error = "Veuillez remplir tous les champs.";
        } else {
            $stmt = getDB()->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = "Adresse e-mail ou mot de passe incorrect.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion — MY TODO</title>
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card fade-in">
        <div class="auth-logo">✅</div>
        <h1 class="auth-title">LOGIN</h1>
        <p class="auth-subtitle">Connectez-vous à votre espace MY TODO</p>

        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <?php if ($successMsg === '1'): ?><div class="alert alert-success">Inscription réussie ! Connectez-vous avec vos identifiants.</div><?php endif; ?>

        <form method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <div class="form-group">
                <label for="email">Adresse e-mail</label>
                <input class="form-control" type="email" id="email" name="email" placeholder="vous@exemple.com" value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="password-wrap">
                    <input class="form-control" type="password" id="password" name="password" placeholder="••••••••" required>
                    <button type="button" class="password-toggle" data-target="password">Afficher</button>
                </div>
            </div>
            <button type="submit" class="auth-btn">Se connecter</button>
        </form>

        <a class="auth-link-small" href="forgot_password.php">Mot de passe oublié ?</a>
        <div class="auth-footer">Vous n'avez pas de compte ? <a href="register.php">Inscrivez-vous</a></div>
    </div>
</div>
<script src="assets/js/script.js"></script>
</body>
</html>
