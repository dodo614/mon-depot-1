<?php
// Attendu : $user (tableau utilisateur) et $pageTitle, $activeMenu déjà définis
$theme = $user['theme'] ?? 'blue';
$initials = mb_strtoupper(mb_substr($user['first_name'] ?? '?', 0, 1) . mb_substr($user['last_name'] ?? '', 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'MY TODO') ?></title>
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/theme-<?= e($theme) ?>.css">
<link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">✅ MY TODO</div>
        <ul class="sidebar-nav">
            <li><a href="dashboard.php" class="<?= $activeMenu === 'dashboard' ? 'active' : '' ?>"><span class="sidebar-icon">🏠</span> Tableau de bord</a></li>
            <li><a href="tasks.php" class="<?= $activeMenu === 'tasks' ? 'active' : '' ?>"><span class="sidebar-icon">📋</span> Mes tâches</a></li>
            <li><a href="add_task.php" class="<?= $activeMenu === 'add' ? 'active' : '' ?>"><span class="sidebar-icon">➕</span> Ajouter une tâche</a></li>
            <li><a href="tasks.php?status=terminee" class="<?= $activeMenu === 'done' ? 'active' : '' ?>"><span class="sidebar-icon">✔️</span> Tâches terminées</a></li>
            <li><a href="tasks.php?status=en_attente" class="<?= $activeMenu === 'pending' ? 'active' : '' ?>"><span class="sidebar-icon">⏳</span> Tâches en attente</a></li>
            <li><a href="calendar.php" class="<?= $activeMenu === 'calendar' ? 'active' : '' ?>"><span class="sidebar-icon">📅</span> Calendrier</a></li>
            <li><a href="statistics.php" class="<?= $activeMenu === 'stats' ? 'active' : '' ?>"><span class="sidebar-icon">📊</span> Statistiques</a></li>
            <li><a href="profile.php" class="<?= $activeMenu === 'profile' ? 'active' : '' ?>"><span class="sidebar-icon">👤</span> Mon profil</a></li>
            <li><a href="settings.php" class="<?= $activeMenu === 'settings' ? 'active' : '' ?>"><span class="sidebar-icon">⚙️</span> Paramètres</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="logout.php"><span class="sidebar-icon">🚪</span> Déconnexion</a>
        </div>
    </aside>
    <main class="main-content">
        <div class="topbar">
            <div>
                <button type="button" class="btn btn-secondary topbar-toggle" style="margin-bottom:10px;">☰ Menu</button>
                <h1><?= e($pageTitle ?? '') ?></h1>
                <div class="date"><?= e(ucfirst(strftime_fr(date('Y-m-d')))) ?></div>
            </div>
            <div class="topbar-right">
                <div class="avatar"><?= e($initials) ?></div>
            </div>
        </div>
