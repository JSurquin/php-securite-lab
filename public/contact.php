<?php
require_once 'config.php';

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDB();

    $email   = $_POST['email']   ?? '';
    $message = $_POST['message'] ?? '';

    // ============================================================
    // FAILLE CSRF-04 : Pas de token CSRF
    // Un site malveillant peut envoyer des messages à l'insu de l'utilisateur
    // ============================================================

    // ============================================================
    // FAILLE VALIDATION-02 : Pas de validation de l'email
    // ============================================================
    $stmt = $pdo->prepare("INSERT INTO contact_messages (email, message) VALUES (?, ?)");
    $stmt->execute([$email, $message]);

    $success = "Message envoyé !";
}

// Récupération des messages pour les admins
// ============================================================
// FAILLE IDOR-01 : Accès aux messages sans vérification de rôle admin
// N'importe qui peut voir tous les messages en ajoutant ?admin=1
// ============================================================
$messages = [];
if (isset($_GET['admin'])) {  // ❌ FAILLE: pas de vérification d'authentification
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
    <!-- ❌ FAILLE CSRF-04: absence de token CSRF -->
    <label>Votre email</label>
    <input type="text" name="email" required>

    <label>Message</label>
    <textarea name="message" rows="5" required></textarea>

    <button type="submit">Envoyer</button>
</form>

<?php if (!empty($messages)): ?>
<div class="admin-panel">
    <h2>📬 Messages reçus (panel admin)</h2>
    <!-- ❌ FAILLE IDOR-01 : accessible sans authentification avec ?admin=1 -->
    <?php foreach ($messages as $msg): ?>
        <div style="border-bottom:1px solid #ddd; padding: 8px 0;">
            <strong><?= htmlspecialchars($msg['email']) ?></strong><br>
            <!-- ❌ FAILLE XSS-04 : message affiché sans échappement -->
            <?= $msg['message'] ?>
            <small style="color:#888"> – <?= $msg['created_at'] ?></small>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

</body>
</html>
