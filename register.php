<?php
require_once __DIR__ . '/includes/functions.php';
startSecureSession();

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$old = ['first_name' => '', 'last_name' => '', 'email' => '', 'birthdate' => '', 'gender' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck($_POST['csrf_token'] ?? '')) {
        $errors[] = "Session expirée, veuillez réessayer.";
    }

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $terms = isset($_POST['terms']);

    $old = compact('first_name', 'last_name', 'email', 'birthdate', 'gender');

    if ($first_name === '' || $last_name === '') $errors[] = "Le nom et le prénom sont obligatoires.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Adresse e-mail invalide.";
    if (strlen($password) < 6) $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    if ($password !== $confirm_password) $errors[] = "Les mots de passe ne correspondent pas.";
    if (!in_array($gender, ['homme', 'femme'])) $errors[] = "Veuillez sélectionner votre sexe.";
    if (!$terms) $errors[] = "Vous devez accepter les conditions d'utilisation.";

    $age = null;
    if (!empty($birthdate)) {
        $d = DateTime::createFromFormat('Y-m-d', $birthdate);
        if (!$d || $d > new DateTime()) {
            $errors[] = "Date de naissance invalide.";
        } else {
            $age = calculateAge($birthdate);
            if ($age < 15) {
                $errors[] = "L'inscription est réservée aux personnes de 15 ans et plus.";
            }
        }
    } else {
        $errors[] = "La date de naissance est obligatoire.";
    }

    if (empty($errors)) {
        $stmt = getDB()->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Cette adresse e-mail est déjà utilisée.";
        }
    }

    if (empty($errors)) {
        $theme = determineTheme($birthdate, $gender);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = getDB()->prepare(
            "INSERT INTO users (first_name, last_name, email, password_hash, birthdate, gender, theme) VALUES (?,?,?,?,?,?,?)"
        );
        $stmt->execute([$first_name, $last_name, $email, $hash, $birthdate, $gender, $theme]);
        header('Location: login.php?registered=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inscription — MY TODO</title>
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/register.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card fade-in">
        <div class="auth-logo">🌱</div>
        <h1 class="auth-title">Créer un compte</h1>
        <p class="auth-subtitle">Rejoignez MY TODO et organisez votre quotidien</p>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>• <?= e($err) ?><br><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="registerForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">Prénom</label>
                    <input class="form-control" type="text" id="first_name" name="first_name" value="<?= e($old['first_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Nom</label>
                    <input class="form-control" type="text" id="last_name" name="last_name" value="<?= e($old['last_name']) ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="email">Adresse e-mail</label>
                <input class="form-control" type="email" id="email" name="email" value="<?= e($old['email']) ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="password-wrap">
                        <input class="form-control" type="password" id="password" name="password" required>
                        <button type="button" class="password-toggle" data-target="password">Afficher</button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmer</label>
                    <div class="password-wrap">
                        <input class="form-control" type="password" id="confirm_password" name="confirm_password" required>
                        <button type="button" class="password-toggle" data-target="confirm_password">Afficher</button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="birthdate">Date de naissance</label>
                <input class="form-control" type="date" id="birthdate" name="birthdate" value="<?= e($old['birthdate']) ?>" required>
            </div>
            <div class="form-group">
                <label>Sexe</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="gender" value="femme" <?= ($old['gender'] === 'femme') ? 'checked' : '' ?> required> Femme
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="gender" value="homme" <?= ($old['gender'] === 'homme') ? 'checked' : '' ?> required> Homme
                    </label>
                </div>
            </div>
            <label class="checkbox-row">
                <input type="checkbox" name="terms" required>
                J'accepte les conditions d'utilisation et la politique de confidentialité de MY TODO.
            </label>
            <button type="submit" class="auth-btn">Créer mon compte</button>
        </form>
        <div class="auth-footer">Vous avez déjà un compte ? <a href="login.php">Connectez-vous</a></div>
    </div>
</div>
<script src="assets/js/script.js"></script>
</body>
</html>
