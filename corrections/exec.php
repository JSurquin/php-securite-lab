<?php
require_once 'config.php';

$output = '';
$host   = '';

if (isset($_GET['host'])) {
    $host = trim($_GET['host']);

    // ✅ CMD-01 : validation stricte — seule une adresse IP valide est acceptée
    if (!filter_var($host, FILTER_VALIDATE_IP)) {
        $output = "Erreur : adresse IP invalide.";
    } else {
        // ✅ CMD-01 : escapeshellarg() neutralise tout caractère dangereux
        exec('ping -c 1 ' . escapeshellarg($host), $output_lines);
        $output = implode("\n", $output_lines);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ping Tool – Lab Sécurité</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: 80px auto; padding: 0 20px; }
        h1 { color: #27ae60; }
        input { padding: 10px; width: 60%; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        pre { background: #1e1e1e; color: #0f0; padding: 16px; border-radius: 6px; overflow-x: auto; }
        a { color: #27ae60; }
    </style>
</head>
<body>

<h1>🖧 Outil Ping (corrigé)</h1>

<form method="GET">
    <input type="text" name="host" value="<?= htmlspecialchars($host, ENT_QUOTES, 'UTF-8') ?>" placeholder="ex: 127.0.0.1">
    <button type="submit">Ping</button>
</form>

<?php if ($output): ?>
    <pre><?= htmlspecialchars($output, ENT_QUOTES, 'UTF-8') ?></pre>
<?php endif; ?>

<p><a href="index.php">← Retour</a></p>
</body>
</html>
