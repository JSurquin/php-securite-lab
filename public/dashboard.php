<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = getDB();

$viewId = $_GET['user_id'] ?? $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$viewId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

$pwdMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $newPassword = $_POST['new_password'];

    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$newPassword, $_SESSION['user_id']]);

    $pwdMsg = "Mot de passe mis à jour.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard – Lab Sécurité</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: 40px auto; padding: 0 20px; background: #f5f5f5; }
        nav { background: #c0392b; padding: 12px 20px; border-radius: 6px; margin-bottom: 30px; }
        nav a { color: white; text-decoration: none; margin-right: 20px; }
        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        input { width: 100%; padding: 10px; margin: 8px 0 16px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #c0392b; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .msg { background: #e0ffe0; padding: 10px; border-radius: 4px; margin-bottom: 16px; }
        h1,h2 { color: #c0392b; }
    </style>
</head>
<body>

<nav>
    <a href="index.php">🏠 Accueil</a>
    <a href="logout.php">🚪 Déconnexion</a>
</nav>

<h1>👤 Dashboard</h1>

<?php if ($pwdMsg): ?>
    <div class="msg"><?= $pwdMsg ?></div>
<?php endif; ?>

<div class="card">
    <h2>Profil consulté</h2>
    <?php if ($profile): ?>
        <p><strong>ID :</strong> <?= $profile['id'] ?></p>
        <p><strong>Nom :</strong> <?= $profile['username'] ?></p>
        <p><strong>Email :</strong> <?= $profile['email'] ?></p>
        <p><strong>Mot de passe :</strong> <?= $profile['password'] ?></p>
        <p><strong>Rôle :</strong> <?= $profile['role'] ?></p>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Changer mon mot de passe</h2>
    <form method="POST">
        <label>Nouveau mot de passe</label>
        <input type="password" name="new_password" required>
        <button type="submit">Mettre à jour</button>
    </form>
</div>

</body>
</html>
