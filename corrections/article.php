<?php
require_once 'config.php';

$pdo = getDB();

// ✅ SQLi-02 : cast en entier, aucune injection possible
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    die("ID invalide.");
}

$stmt = $pdo->prepare("SELECT a.*, u.username FROM articles a LEFT JOIN users u ON a.author_id = u.id WHERE a.id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    die("Article introuvable.");
}

$stmt2 = $pdo->prepare("SELECT * FROM comments WHERE article_id = ? ORDER BY created_at ASC");
$stmt2->execute([$id]);
$comments = $stmt2->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ CSRF-02 : vérification token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(403);
        die('Token CSRF invalide.');
    }

    $author  = trim($_POST['author']  ?? '');
    $content = trim($_POST['content'] ?? '');

    // ✅ XSS-01 : le contenu est stocké tel quel mais échappé à l'affichage
    $stmt3 = $pdo->prepare("INSERT INTO comments (article_id, author_name, content) VALUES (?, ?, ?)");
    $stmt3->execute([$id, $author, $content]);

    unset($_SESSION['csrf_token']);
    header("Location: article.php?id=$id");
    exit;
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <!-- ✅ XSS-02 : titre échappé dans <title> -->
    <title><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?> – Lab Sécurité</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 0 20px; background: #f5f5f5; }
        nav { background: #27ae60; padding: 12px 20px; border-radius: 6px; margin-bottom: 30px; }
        nav a { color: white; text-decoration: none; margin-right: 20px; }
        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        .comment { background: #f9f9f9; border-left: 4px solid #27ae60; padding: 12px; margin-bottom: 12px; }
        input, textarea { width: 100%; padding: 10px; margin: 8px 0 16px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        h1 { color: #27ae60; }
    </style>
</head>
<body>

<nav>
    <a href="index.php">🏠 Accueil</a>
    <a href="search.php">🔍 Recherche</a>
</nav>

<div class="card">
    <!-- ✅ XSS-02 : échappement du titre et du contenu -->
    <h1><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p><?= htmlspecialchars($article['content'], ENT_QUOTES, 'UTF-8') ?></p>
    <small>Par <?= htmlspecialchars($article['username'], ENT_QUOTES, 'UTF-8') ?> – <?= htmlspecialchars($article['created_at'], ENT_QUOTES, 'UTF-8') ?></small>
</div>

<h2>Commentaires (<?= count($comments) ?>)</h2>

<?php foreach ($comments as $comment): ?>
<div class="comment">
    <!-- ✅ XSS-01 : commentaires échappés à l'affichage -->
    <strong><?= htmlspecialchars($comment['author_name'], ENT_QUOTES, 'UTF-8') ?></strong>
    <p><?= htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8') ?></p>
</div>
<?php endforeach; ?>

<div class="card">
    <h3>Laisser un commentaire</h3>
    <form method="POST">
        <!-- ✅ CSRF-02 : token dans le formulaire -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

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
