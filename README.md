# MY TODO — Application de gestion de tâches (PHP / MySQL)

## 📦 Contenu
Application complète et fonctionnelle : inscription, connexion, thèmes automatiques
(bleu/rose/indigo selon l'âge et le sexe déclarés), tableau de bord, gestion complète
des tâches (ajout, modification, suppression, statut), calendrier, statistiques, profil,
paramètres et mot de passe oublié.

## 🛠️ Installation avec Laragon (recommandé)

1. **Copier le dossier** `mytodo` dans `C:\laragon\www\` (ou l'équivalent de votre installation).
2. **Démarrer Laragon** (Apache + MySQL).
3. **Créer la base de données** :
   - Ouvrez phpMyAdmin (clic droit Laragon → MySQL → phpMyAdmin), ou HeidiSQL.
   - Importez le fichier `sql/mytodo.sql` (il crée la base `mytodo` et ses tables).
   - Ou en ligne de commande :
     ```
     mysql -u root -p < sql/mytodo.sql
     ```
4. **Configurer la connexion** si besoin dans `config/database.php`
   (par défaut : host `localhost`, user `root`, mot de passe vide — réglages standards de Laragon).
5. **Ouvrir l'application** dans votre navigateur :
   - `http://mytodo.test` (Laragon crée automatiquement ce domaine virtuel)
   - ou `http://localhost/mytodo/`

## 🛠️ Installation avec WAMP (votre environnement)

1. **Copier le dossier** `mytodo` dans `C:\wamp64\www\` (ou l'équivalent selon votre version de WAMP,
   parfois `C:\wamp\www\`).
2. **Démarrer WAMP** et attendre que l'icône soit **verte** (Apache + MySQL actifs).
   Si elle reste orange, cliquez dessus → vérifiez qu'aucun autre programme n'utilise déjà
   le port 80 (Apache) ou 3306 (MySQL), par exemple Skype ou un autre serveur.
3. **Créer la base de données** :
   - Clic gauche sur l'icône WAMP → phpMyAdmin (ou allez sur `http://localhost/phpmyadmin`).
   - Connectez-vous (utilisateur `root`, mot de passe vide par défaut sous WAMP).
   - Cliquez sur l'onglet **"Importer"**, choisissez le fichier `sql/mytodo.sql`, puis **"Exécuter"**.
   - Cela crée automatiquement la base `mytodo` avec ses tables `users` et `tasks`.
4. **Vérifier la configuration** dans `config/database.php` : les valeurs par défaut
   (`host: localhost`, `user: root`, `password: ''`) correspondent au réglage standard de WAMP,
   donc normalement **rien à changer**.
5. **Ouvrir l'application** dans votre navigateur :
   - `http://localhost/mytodo/`

### ⚠️ Problèmes fréquents avec WAMP
- **"Erreur de connexion à la base de données"** → vérifiez que l'icône WAMP est bien verte
  et que vous avez bien importé `sql/mytodo.sql`.
- **mbstring / pdo_mysql manquants** → ces extensions sont activées par défaut dans WAMP ;
  si ce n'est pas le cas, clic gauche sur WAMP → PHP → Extensions PHP → cochez `php_mbstring`
  et `php_pdo_mysql`, puis redémarrez les services.
- **Page blanche** → clic gauche sur WAMP → PHP → "php.ini" → repérez `display_errors` et
  mettez-le sur `On` temporairement pour voir le message d'erreur exact, ou consultez
  `C:\wamp64\logs\php_error.log`.

## 🛠️ Installation avec XAMPP / Laragon (alternative)

1. Copiez le dossier `mytodo` dans `htdocs` (XAMPP) ou `www` (Laragon).
2. Démarrez Apache et MySQL depuis le panneau de contrôle.
3. Importez `sql/mytodo.sql` via phpMyAdmin (`http://localhost/phpmyadmin`).
4. Accédez à `http://localhost/mytodo/` (ou `http://mytodo.test` avec Laragon).

## 📁 Structure du projet

```
mytodo/
├── config/database.php        → connexion PDO à MySQL
├── includes/
│   ├── functions.php          → fonctions utilitaires (thème, sécurité, dates)
│   ├── header.php              → en-tête + menu latéral (pages privées)
│   └── footer.php
├── assets/
│   ├── css/                    → base, login, register, thèmes (blue/pink/indigo), app
│   └── js/script.js
├── sql/mytodo.sql              → schéma de base de données
├── login.php / register.php / logout.php
├── forgot_password.php / reset_password.php
├── dashboard.php
├── add_task.php / edit_task.php / delete_task.php / toggle_task.php / task_success.php
├── tasks.php                   → liste, recherche, filtres, tri
├── calendar.php / statistics.php
├── profile.php / settings.php / delete_account.php
└── index.php                   → redirection selon état de connexion
```

## 🎨 Logique des thèmes

- **< 15 ans** : inscription refusée.
- **15 à 18 ans inclus** (fille ou garçon) : thème **indigo**.
- **> 18 ans, homme** : thème **bleu**.
- **> 18 ans, femme** : thème **rose**.

Le thème est calculé une seule fois à l'inscription (à partir de la date de naissance
et du sexe déclarés) et enregistré dans la base ; il reste donc identique aux
prochaines connexions.

## 🔒 Sécurité

- Mots de passe hachés avec `password_hash()` (jamais stockés en clair).
- Requêtes préparées PDO partout (protection contre les injections SQL).
- Jeton CSRF sur tous les formulaires.
- Session sécurisée (régénération d'ID à la connexion, cookie `HttpOnly`).
- Chaque utilisateur ne peut voir/modifier que ses propres tâches (`user_id` vérifié
  sur chaque requête).
- Toutes les pages privées sont protégées par `requireLogin()`.

## ✉️ Mot de passe oublié (mode local)

Comme il n'y a pas de serveur d'envoi d'e-mail en local, `forgot_password.php`
affiche directement le lien de réinitialisation à l'écran (pratique pour tester).
En production, il faudrait envoyer ce lien par e-mail (ex. avec PHPMailer).

## ➕ Pour aller plus loin

Le code est organisé simplement (un fichier par page, fonctions communes dans
`includes/functions.php`) pour que vous puissiez facilement ajouter des
fonctionnalités : pièces jointes, notifications, sous-tâches, partage de tâches, etc.
