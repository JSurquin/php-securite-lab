<?php
require_once 'config.php';

$results    = [];
$query_str  = '';

if (isset($_GET['q'])) {
    $pdo       = getDB();
    $query_str = $_GET['q'];

    // ✅ SQLi-03 : requête préparée avec LIKE et wildcards liés séparément
    $like = '%' . $query_str . '%';
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE title LIKE ? OR content LIKE ?");
    $stmt->execute([$like, $like]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche – Lab Sécurité</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 0 20px; background: #f5f5f5; }
        nav { background: #27ae60; padding: 12px 20px; border-radius: 6px; margin-bottom: 30px; }
        nav a { color: white; text-decoration: none; margin-right: 20px; }
        input { padding: 10px; width: 70%; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .card { background: white; border-radius: 8px; padding: 16px; margin-top: 16px; box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        h1 { color: #27ae60; }
    </style>
</head>
<body>

<nav>
    <a href="index.php">🏠 Accueil</a>
    <a href="search.php">🔍 Recherche</a>
</nav>

<h1>🔍 Recherche (corrigée)</h1>

<form method="GET">
    <!-- ✅ XSS-03 : valeur échappée dans l'attribut value -->
    <input type="text" name="q" value="<?= htmlspecialchars($query_str, ENT_QUOTES, 'UTF-8') ?>" placeholder="Rechercher...">
    <button type="submit">Rechercher</button>
</form>

<?php if ($query_str !== ''): ?>
    <!-- ✅ XSS-03 : terme de recherche échappé à l'affichage -->
    <p>Résultats pour : <strong><?= htmlspecialchars($query_str, ENT_QUOTES, 'UTF-8') ?></strong></p>

    <?php if (empty($results)): ?>
        <p>Aucun résultat.</p>
    <?php else: ?>
        <?php foreach ($results as $r): ?>
        <div class="card">
            <h3><a href="article.php?id=<?= (int) $r['id'] ?>"><?= htmlspecialchars($r['title'], ENT_QUOTES, 'UTF-8') ?></a></h3>
            <p><?= htmlspecialchars(mb_substr($r['content'], 0, 200), ENT_QUOTES, 'UTF-8') ?>...</p>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>
