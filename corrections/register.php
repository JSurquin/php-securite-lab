<?php
require_once 'config.php';

$success = '';
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo      = getDB();
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    // ✅ VALIDATION-01 : validation des entrées
    if (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Le nom doit faire entre 3 et 50 caractères.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresse email invalide.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit faire au moins 8 caractères.";
    }

    if (empty($errors)) {
        // ✅ PASSWD-01 : hachage Argon2id avant stockage
        $hash = password_hash($password, PASSWORD_ARGON2ID);

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hash]);

        $success = "Compte créé ! <a href='login.php'>Se connecter</a>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription – Lab Sécurité</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 80px auto; padding: 0 20px; }
        h1 { color: #27ae60; }
        input { width: 100%; padding: 10px; margin: 8px 0 16px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #27ae60; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        .success { background: #e0ffe0; padding: 10px; border-radius: 4px; margin-bottom: 16px; color: #27ae60; }
        .error   { background: #ffe0e0; padding: 10px; border-radius: 4px; margin-bottom: 8px;  color: #c0392b; }
    </style>
</head>
<body>
<h1>📝 Inscription (corrigée)</h1>

<?php if ($success): ?>
    <div class="success"><?= $success ?></div>
<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <div class="error"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<form method="POST">
    <label>Nom d'utilisateur</label>
    <input type="text" name="username" required minlength="3" maxlength="50">

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Mot de passe (8 caractères min.)</label>
    <input type="password" name="password" required minlength="8">

    <button type="submit">Créer mon compte</button>
</form>
</body>
</html>
