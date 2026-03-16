<?php
require_once 'config.php';

$pdo = getDB();

$articles = $pdo->query("SELECT a.*, u.username FROM articles a LEFT JOIN users u ON a.author_id = u.id ORDER BY a.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Blog Lab Sécurité PHP</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 0 20px; background: #f5f5f5; }
        nav { background: #c0392b; padding: 12px 20px; border-radius: 6px; margin-bottom: 30px; }
        nav a { color: white; text-decoration: none; margin-right: 20px; font-weight: bold; }
        nav a:hover { text-decoration: underline; }
        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        .card h2 a { color: #c0392b; text-decoration: none; }
        .meta { color: #888; font-size: 13px; margin-top: 8px; }
        h1 { color: #c0392b; }
    </style>
</head>
<body>

<nav>
    <a href="index.php">🏠 Accueil</a>
    <a href="login.php">🔐 Connexion</a>
    <a href="register.php">📝 Inscription</a>
    <a href="search.php">🔍 Recherche</a>
    <a href="contact.php">✉️ Contact</a>
    <a href="upload.php">📤 Upload</a>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="dashboard.php">👤 Mon compte</a>
        <a href="logout.php">🚪 Déconnexion</a>
    <?php endif; ?>
</nav>

<h1>Blog Lab Sécurité PHP</h1>

<?php foreach ($articles as $article): ?>
<div class="card">
    <h2><a href="article.php?id=<?= $article['id'] ?>"><?= $article['title'] ?></a></h2>
    <p><?= mb_substr($article['content'], 0, 150) ?>...</p>
    <div class="meta">
        Par <strong><?= $article['username'] ?></strong> –
        <?= $article['created_at'] ?>
    </div>
</div>
<?php endforeach; ?>

</body>
</html>
