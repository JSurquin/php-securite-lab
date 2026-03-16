<?php
require_once 'config.php';

$pdo = getDB();

$id = $_GET['id'];

$query = "SELECT a.*, u.username FROM articles a LEFT JOIN users u ON a.author_id = u.id WHERE a.id = $id";

$article = $pdo->query($query)->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    die("Article introuvable.");
}

$comments = $pdo->query("SELECT * FROM comments WHERE article_id = $id ORDER BY created_at ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author  = $_POST['author']  ?? '';
    $content = $_POST['content'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO comments (article_id, author_name, content) VALUES (?, ?, ?)");
    $stmt->execute([$id, $author, $content]);

    header("Location: article.php?id=$id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $article['title'] ?> – Lab Sécurité</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 0 20px; background: #f5f5f5; }
        nav { background: #c0392b; padding: 12px 20px; border-radius: 6px; margin-bottom: 30px; }
        nav a { color: white; text-decoration: none; margin-right: 20px; }
        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        .comment { background: #f9f9f9; border-left: 4px solid #c0392b; padding: 12px; margin-bottom: 12px; }
        input, textarea { width: 100%; padding: 10px; margin: 8px 0 16px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #c0392b; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        h1 { color: #c0392b; }
    </style>
</head>
<body>

<nav>
    <a href="index.php">🏠 Accueil</a>
    <a href="search.php">🔍 Recherche</a>
</nav>

<div class="card">
    <h1><?= $article['title'] ?></h1>
    <p><?= $article['content'] ?></p>
    <small>Par <?= $article['username'] ?> – <?= $article['created_at'] ?></small>
</div>

<h2>Commentaires (<?= count($comments) ?>)</h2>

<?php foreach ($comments as $comment): ?>
<div class="comment">
    <strong><?= $comment['author_name'] ?></strong>
    <p><?= $comment['content'] ?></p>
</div>
<?php endforeach; ?>

<div class="card">
    <h3>Laisser un commentaire</h3>
    <form method="POST">
        <label>Votre nom</label>
        <input type="text" name="author" required>

        <label>Commentaire</label>
        <textarea name="content" rows="4" required></textarea>

        <button type="submit">Publier</button>
    </form>
</div>

<p><a href="index.php">← Retour</a></p>

</body>
</html>
