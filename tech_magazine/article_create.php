<?php
require_once 'includes/header.php'; 
require_once 'classes/operatii_db.php'; 

// Mandate authentication for this page
require_auth(); 

$error = '';
$TEST_CATEGORY_ID = 1; // Assuming default category ID 1 exists

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    if (empty($title) || empty($content)) {
        $error = "Title and content cannot be empty.";
    } else {
        $data_to_insert = [
            'title' => $title,
            'content' => $content,
            'image_url' => null, 
            'category_id' => $TEST_CATEGORY_ID, 
            'author_id' => $_SESSION['user_id'] // CRITICAL: Link to the logged-in user
        ];

        try {
            // Using the validated OperatiiDB::create function
            $article_id = OperatiiDB::create('articles', $data_to_insert);
            if ($article_id) {
                // Redirect after success to display the new article
                header("Location: index.php?status=created");
                exit;
            } else {
                $error = "Failed to create article in the database.";
            }
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<h1>Create New Article</h1>

<?php if (isset($error) && $error != ''): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form action="article_create.php" method="POST">
    <div class="mb-3">
        <label for="title" class="form-label">Article Title</label>
        <input type="text" class="form-control" id="title" name="title" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label for="content" class="form-label">Content</label>
        <textarea class="form-control" id="content" name="content" rows="10" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
    </div>
    
    <button type="submit" class="btn btn-primary">Publish Article</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php
require_once 'includes/footer.php'; 
?>