<?php
require_once 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array(strtolower($ext), $allowed)) {
        $destination = __DIR__ . '/uploads/' . $file['name'];

        move_uploaded_file($file['tmp_name'], $destination);
        $message = "✅ Fichier uploadé : <a href='uploads/{$file['name']}'>{$file['name']}</a>";
    } else {
        $message = "❌ Extension non autorisée.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Upload – Lab Sécurité</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 80px auto; padding: 0 20px; }
        h1 { color: #c0392b; }
        input[type=file] { margin: 16px 0; }
        button { background: #c0392b; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .msg { background: #e8f5e9; padding: 12px; border-radius: 4px; margin-top: 16px; }
        a { color: #c0392b; }
    </style>
</head>
<body>

<h1>📤 Upload de fichier</h1>

<?php if ($message): ?>
    <div class="msg"><?= $message ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <label>Sélectionner un fichier :</label><br>
    <input type="file" name="file" required>
    <br>
    <button type="submit">Uploader</button>
</form>

</body>
</html>
