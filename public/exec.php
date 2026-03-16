<?php
require_once 'config.php';

$output = '';
$host   = '';

if (isset($_GET['host'])) {
    $host = $_GET['host'];

    exec("ping -c 1 " . $host, $output_lines);
    $output = implode("\n", $output_lines);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ping Tool – Lab Sécurité</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: 80px auto; padding: 0 20px; }
        h1 { color: #c0392b; }
        input { padding: 10px; width: 60%; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #c0392b; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        pre { background: #1e1e1e; color: #0f0; padding: 16px; border-radius: 6px; overflow-x: auto; }
        a { color: #c0392b; }
    </style>
</head>
<body>

<h1>🖧 Outil Ping</h1>

<form method="GET">
    <input type="text" name="host" value="<?= htmlspecialchars($host) ?>" placeholder="ex: 127.0.0.1">
    <button type="submit">Ping</button>
</form>

<?php if ($output): ?>
    <pre><?= htmlspecialchars($output) ?></pre>
<?php endif; ?>

<p><a href="index.php">← Retour</a></p>

</body>
</html>
