<?php
require_once 'config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDB();

    $username = $_POST['username'] ?? '';
    $email    = $_POST['email']    ?? '';
    $password = $_POST['password'] ?? '';

    // ============================================================
    // FAILLE PASSWD-01 : Mot de passe stocké en CLAIR
    // Doit utiliser password_hash($password, PASSWORD_ARGON2ID)
    // ============================================================
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $password]);  // ❌ FAILLE: mot de passe en clair

    // ============================================================
    // FAILLE VALIDATION-01 : Aucune validation des entrées
    // - Pas de vérification de la longueur du mot de passe
    // - Pas de vérification du format de l'email
    // - Pas de vérification que l'utilisateur existe déjà
    // ============================================================

    $success = "Compte créé ! <a href='login.php'>Se connecter</a>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription – Lab Sécurité</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 80px auto; padding: 0 20px; }
        h1 { color: #c0392b; }
        input { width: 100%; padding: 10px; margin: 8px 0 16px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #c0392b; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        .success { background: #e0ffe0; padding: 10px; border-radius: 4px; margin-bottom: 16px; color: #27ae60; }
    </style>
</head>
<body>
<h1>📝 Inscription</h1>

<?php if ($success): ?>
    <div class="success"><?= $success ?></div>
<?php endif; ?>

<form method="POST">
    <label>Nom d'utilisateur</label>
    <input type="text" name="username" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Mot de passe</label>
    <input type="password" name="password" required>
    <!-- ❌ FAILLE: aucune règle de complexité -->

    <button type="submit">Créer mon compte</button>
</form>

</body>
</html>
