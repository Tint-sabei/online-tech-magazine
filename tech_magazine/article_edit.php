<?php
require_once 'includes/header.php'; 
require_once 'classes/operatii_db.php'; 

// Mandate authentication for this page
require_auth(); 

$article_id = $_GET['id'] ?? null;
$error = '';

if (!$article_id || !is_numeric($article_id)) {
    header("Location: index.php");
    exit;
}

// --- Fetch existing article data ---
try {
    $articles = OperatiiDB::read('articles', "WHERE article_id = {$article_id}");
    
    if (empty($articles)) {
        header("Location: index.php?status=notfound");
        exit;
    }
    $article = $articles[0]; // Get the single row
    
    // Authorization check: User can only edit their own article
    if ($article['author_id'] !== $_SESSION['user_id']) {
        header("Location: index.php?status=denied");
        exit;
    }

} catch (Exception $e) {
    die("Error fetching article: " . $e->getMessage());
}


// --- Handle Form Submission (UPDATE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    if (empty($title) || empty($content)) {
        $error = "Title and content cannot be empty.";
    } else {
        $data_to_update = [
            'title' => $title,
            'content' => $content
        ];
        
        $condition = "article_id = {$article_id}";

        try {
            OperatiiDB::update('articles', $data_to_update, $condition);
            header("Location: index.php?status=updated");
            exit;
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
    // Update the local $article variable with POST data on submission failure
    $article['title'] = $title;
    $article['content'] = $content;
}
?>

<h1>Edit Article: <?= htmlspecialchars($article['title']) ?></h1>

<?php if (isset($error) && $error != ''): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form action="article_edit.php?id=<?= $article_id ?>" method="POST">
    <div class="mb-3">
        <label for="title" class="form-label">Article Title</label>
        <input type="text" class="form-control" id="title" name="title" required value="<?= htmlspecialchars($article['title']) ?>">
    </div>
    <div class="mb-3">
        <label for="content" class="form-label">Content</label>
        <textarea class="form-control" id="content" name="content" rows="10" required><?= htmlspecialchars($article['content']) ?></textarea>
    </div>
    
    <button type="submit" class="btn btn-success">Save Changes</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php
require_once 'includes/footer.php'; 
?>