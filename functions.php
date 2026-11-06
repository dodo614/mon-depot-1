<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Calcule l'âge à partir d'une date de naissance (format Y-m-d)
 */
function calculateAge($birthdate) {
    $today = new DateTime();
    $bdate = new DateTime($birthdate);
    return $today->diff($bdate)->y;
}

/**
 * Détermine le thème à appliquer selon l'âge et le sexe déclarés.
 * - 15 à 18 ans inclus : indigo (filles et garçons)
 * - > 18 ans, homme : blue
 * - > 18 ans, femme : pink
 * Retourne null si l'accès doit être refusé (< 15 ans)
 */
function determineTheme($birthdate, $gender) {
    $age = calculateAge($birthdate);
    if ($age < 15) {
        return null; // accès restreint
    }
    if ($age >= 15 && $age <= 18) {
        return 'indigo';
    }
    return $gender === 'femme' ? 'pink' : 'blue';
}

/**
 * Formate une date (Y-m-d) en français, ex: "jeudi 5 novembre 2026"
 * (remplace strftime, dépréciée depuis PHP 8.1)
 */
function strftime_fr($date) {
    $jours = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
    $mois = ['', 'janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    $ts = strtotime($date);
    return $jours[date('w', $ts)] . ' ' . (int)date('j', $ts) . ' ' . $mois[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

function formatDateFr($date) {
    if (empty($date)) return '—';
    $mois = ['', 'jan.','fév.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'];
    $ts = strtotime($date);
    return (int)date('j', $ts) . ' ' . $mois[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

function priorityClass($priority) {
    $map = ['Faible' => 'badge-faible', 'Moyen' => 'badge-moyen', 'Élevé' => 'badge-eleve', 'Urgent' => 'badge-urgent'];
    return $map[$priority] ?? 'badge-moyen';
}

/**
 * Nettoie une chaîne pour affichage sécurisé
 */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Démarre la session si ce n'est pas déjà fait
 */
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

/**
 * Vérifie que l'utilisateur est connecté, sinon redirige vers login
 */
function requireLogin() {
    startSecureSession();
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Récupère l'utilisateur courant depuis la BDD
 */
function currentUser() {
    startSecureSession();
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = getDB()->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Génère / vérifie un jeton CSRF simple
 */
function csrfToken() {
    startSecureSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfCheck($token) {
    startSecureSession();
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}
