<?php
require_once 'includes/header.php'; 
require_once 'classes/operatii_db.php'; 

require_auth(); 

$article_id = $_GET['id'] ?? null;
$error = '';
$categories = [];

if (!$article_id || !is_numeric($article_id)) {
    header("Location: index.php");
    exit;
}

try {
    // Fetch categories for the dropdown
    $categories = OperatiiDB::read('categories', null); 
    
    // [SQL INJECTION PREVENTION]: Parameterized fetch
    $articles = OperatiiDB::read('articles', "WHERE article_id = ?", [$article_id]);
    
    if (empty($articles)) {
        header("Location: index.php?status=notfound");
        exit;
    }
    $article = $articles[0]; 
    
    // [RBAC] ROLE SEPARATION
    $is_administrator = ($_SESSION['role'] ?? 'visitor') === 'administrator';
    $is_article_owner = (int)$article['author_id'] === (int)$_SESSION['user_id'];

    if (!$is_article_owner && !$is_administrator) {
        header("Location: index.php?status=denied");
        exit;
    }

} catch (Exception $e) {
    die("Error: " . htmlspecialchars($e->getMessage()));
}

// --- Handle Form Submission (UPDATE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // [CSRF PROTECTION]
    $submitted_token = $_POST['csrf_token'] ?? '';
    if (empty($submitted_token) || $submitted_token !== $_SESSION['csrf_token']) {
        die("Security Error: Invalid CSRF token.");
    }

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category_id = $_POST['category_id'] ?? '';
    $image_url = trim($_POST['image_url']);
    
    if (empty($title) || empty($content) || empty($category_id)) {
        $error = "Title, Category, and Content are required.";
    } else {
        $data_to_update = [
            'title' => $title,
            'content' => $content,
            'category_id' => (int)$category_id,
            'image_url' => $image_url ?: null
        ];
        
        try {
            // [SQL INJECTION PREVENTION]
            OperatiiDB::update('articles', $data_to_update, "article_id = " . (int)$article_id);
            header("Location: index.php?status=updated");
            exit;
        } catch (Exception $e) {
            $error = "Database Error: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<div class="container mt-4">
    <h1>Edit Article</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="article_edit.php?id=<?= (int)$article_id ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"> 

        <div class="mb-3">
            <label for="title" class="form-label">Article Title</label>
            <input type="text" class="form-control" name="title" required value="<?= htmlspecialchars($article['title']) ?>">
        </div>

        <div class="mb-3">
            <label for="category_id" class="form-label">Category</label>
            <select class="form-select" name="category_id" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>" <?= ($article['category_id'] == $cat['category_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="image_url" class="form-label">Cover Image URL</label>
            <input type="url" class="form-control" name="image_url" value="<?= htmlspecialchars($article['image_url'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea class="form-control" name="content" rows="10" required><?= htmlspecialchars($article['content']) ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-success">Save Changes</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>