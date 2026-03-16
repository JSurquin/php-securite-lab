<?php
require_once 'config.php';

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDB();

    $email   = $_POST['email']   ?? '';
    $message = $_POST['message'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO contact_messages (email, message) VALUES (?, ?)");
    $stmt->execute([$email, $message]);

    $success = "Message envoyé !";
}

$messages = [];
if (isset($_GET['admin'])) {
    $pdo = getDB();
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
        h1 { color: #c0392b; }
        input, textarea { width: 100%; padding: 10px; margin: 8px 0 16px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #c0392b; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .success { background: #e0ffe0; padding: 10px; border-radius: 4px; margin-bottom: 16px; }
        .admin-panel { background: #fff3cd; padding: 16px; border-radius: 4px; margin-top: 30px; }
        a { color: #c0392b; }
    </style>
</head>
<body>

<h1>✉️ Contact</h1>

<?php if ($success): ?>
    <div class="success"><?= $success ?></div>
<?php endif; ?>

<form method="POST">
    <label>Votre email</label>
    <input type="text" name="email" required>

    <label>Message</label>
    <textarea name="message" rows="5" required></textarea>

    <button type="submit">Envoyer</button>
</form>

<?php if (!empty($messages)): ?>
<div class="admin-panel">
    <h2>📬 Messages reçus (panel admin)</h2>
    <?php foreach ($messages as $msg): ?>
        <div style="border-bottom:1px solid #ddd; padding: 8px 0;">
            <strong><?= htmlspecialchars($msg['email']) ?></strong><br>
            <?= $msg['message'] ?>
            <small style="color:#888"> – <?= $msg['created_at'] ?></small>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

</body>
</html>
