<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ CSRF-01 : vérification du token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(403);
        die('Token CSRF invalide.');
    }

    $pdo      = getDB();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // ✅ SQLi-01 : requête préparée, séparation données/code SQL
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✅ PASSWD-02 : vérification via password_verify()
    if ($user && password_verify($password, $user['password'])) {
        // ✅ SESSION-02 : régénération de l'ID après login (anti session fixation)
        session_regenerate_id(true);

        $_SESSION['user_id']       = $user['id'];
        $_SESSION['username']      = $user['username'];
        $_SESSION['role']          = $user['role'];
        $_SESSION['logged_at']     = time();
        $_SESSION['last_activity'] = time();

        unset($_SESSION['csrf_token']);

        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Identifiants invalides.";
    }
}

// Génération du token CSRF pour le formulaire
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion – Lab Sécurité</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 80px auto; padding: 0 20px; }
        h1 { color: #27ae60; }
        input { width: 100%; padding: 10px; margin: 8px 0 16px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #27ae60; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        .error { background: #ffe0e0; padding: 10px; border-radius: 4px; margin-bottom: 16px; color: #c0392b; }
        a { color: #27ae60; }
    </style>
</head>
<body>
<h1>🔐 Connexion (corrigée)</h1>

<?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
    <!-- ✅ CSRF-01 : token caché dans le formulaire -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <label>Nom d'utilisateur</label>
    <input type="text" name="username" required>

    <label>Mot de passe</label>
    <input type="password" name="password" required>

    <button type="submit">Se connecter</button>
</form>

<p><a href="register.php">Pas de compte ? S'inscrire</a></p>
</body>
</html>
