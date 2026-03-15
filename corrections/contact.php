<?php
require_once 'config.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ CSRF-04 : vérification du token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(403);
        die('Token CSRF invalide.');
    }

    $pdo     = getDB();
    $email   = trim($_POST['email']   ?? '');
    $message = trim($_POST['message'] ?? '');

    // ✅ VALIDATION-02 : validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (email, message) VALUES (?, ?)");
        $stmt->execute([$email, $message]);
        $success = "Message envoyé !";
        unset($_SESSION['csrf_token']);
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// ✅ IDOR-01 : accès au panel admin uniquement si connecté ET rôle admin
$messages = [];
if (isset($_GET['admin'])) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die('Accès interdit.');
    }
    $pdo      = getDB();
    $messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contact – Lab Sécurité</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 80px auto; padding: 0 20px; }
        h1 { color: #27ae60; }
        input, textarea { width: 100%; padding: 10px; margin: 8px 0 16px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .success { background: #e0ffe0; padding: 10px; border-radius: 4px; margin-bottom: 16px; }
        .error   { background: #ffe0e0; padding: 10px; border-radius: 4px; margin-bottom: 16px; color: #c0392b; }
        .admin-panel { background: #f0fff0; padding: 16px; border-radius: 4px; margin-top: 30px; }
        a { color: #27ae60; }
    </style>
</head>
<body>

<h1>✉️ Contact (corrigé)</h1>

<?php if ($success): ?>
    <div class="success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
    <!-- ✅ CSRF-04 : token dans le formulaire -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <label>Votre email</label>
    <input type="email" name="email" required>

    <label>Message</label>
    <textarea name="message" rows="5" required></textarea>

    <button type="submit">Envoyer</button>
</form>

<?php if (!empty($messages)): ?>
<div class="admin-panel">
    <h2>📬 Messages reçus (panel admin)</h2>
    <?php foreach ($messages as $msg): ?>
        <div style="border-bottom:1px solid #ddd; padding: 8px 0;">
            <!-- ✅ XSS-04 : échappement de l'email et du message -->
            <strong><?= htmlspecialchars($msg['email'], ENT_QUOTES, 'UTF-8') ?></strong><br>
            <?= htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8') ?>
            <small style="color:#888"> – <?= htmlspecialchars($msg['created_at'], ENT_QUOTES, 'UTF-8') ?></small>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

</body>
</html>
