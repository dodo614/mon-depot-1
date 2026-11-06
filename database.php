<?php
// ===== CONFIGURATION BASE DE DONNÉES =====
// Adapte ces valeurs si besoin (par défaut réglé pour Laragon : user root, mdp vide)
define('DB_HOST', 'localhost');
define('DB_NAME', 'mytodo');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données. Vérifiez que MySQL est démarré et que la base 'mytodo' existe (importez sql/mytodo.sql). Détail : " . $e->getMessage());
        }
    }
    return $pdo;
}
