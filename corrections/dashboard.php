<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = getDB();

// ✅ IDOR-02 : vérification que l'utilisateur accède à son propre profil
$requestedId = (int) ($_GET['user_id'] ?? $_SESSION['user_id']);
if ($requestedId !== (int) $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    die('Accès interdit.');
}

$stmt = $pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
$stmt->execute([$requestedId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

$pwdMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    // ✅ CSRF-05 : vérification du token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(403);
        die('Token CSRF invalide.');
    }

    $newPassword = $_POST['new_password'] ?? '';
    if (strlen($newPassword) < 8) {
        $pwdMsg = "Le mot de passe doit faire au moins 8 caractères.";
    } else {
        // ✅ PASSWD-02 : hachage avant stockage
        $hash = password_hash($newPassword, PASSWORD_ARGON2ID);
        $stmt2 = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt2->execute([$hash, $_SESSION['user_id']]);
        $pwdMsg = "Mot de passe mis à jour.";
        unset($_SESSION['csrf_token']);
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard – Lab Sécurité</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: 40px auto; padding: 0 20px; background: #f5f5f5; }
        nav { background: #27ae60; padding: 12px 20px; border-radius: 6px; margin-bottom: 30px; }
        nav a { color: white; text-decoration: none; margin-right: 20px; }
        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        input { width: 100%; padding: 10px; margin: 8px 0 16px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .msg { background: #e0ffe0; padding: 10px; border-radius: 4px; margin-bottom: 16px; }
        h1,h2 { color: #27ae60; }
    </style>
</head>
<body>

<nav>
    <a href="index.php">🏠 Accueil</a>
    <a href="logout.php">🚪 Déconnexion</a>
</nav>

<h1>👤 Dashboard (corrigé)</h1>

<?php if ($pwdMsg): ?>
    <div class="msg"><?= htmlspecialchars($pwdMsg) ?></div>
<?php endif; ?>

<div class="card">
    <h2>Profil</h2>
    <?php if ($profile): ?>
        <p><strong>ID :</strong> <?= (int) $profile['id'] ?></p>
        <p><strong>Nom :</strong> <?= htmlspecialchars($profile['username'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Rôle :</strong> <?= htmlspecialchars($profile['role'], ENT_QUOTES, 'UTF-8') ?></p>
        <!-- ✅ IDOR-02 : mot de passe non affiché, et on ne sélectionne pas la colonne password -->
    <?php endif; ?>
</div>

<div class="card">
    <h2>Changer mon mot de passe</h2>
    <form method="POST">
        <!-- ✅ CSRF-05 : token CSRF -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <label>Nouveau mot de passe (8 caractères min.)</label>
        <input type="password" name="new_password" required minlength="8">
        <button type="submit">Mettre à jour</button>
    </form>
</div>

</body>
</html>
