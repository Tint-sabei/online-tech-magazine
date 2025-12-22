<?php
// Force error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/header.php'; 
require_once 'classes/operatii_db.php'; 

// [RBAC] ROLE-BASED ACCESS CONTROL: Ensures only authenticated users (Authors/Admins) 
// can access the article creation interface.
require_auth(); 

$error = '';
$categories = [];

try {
    // [SQL INJECTION PREVENTION]: Using the secure read method to fetch categories.
    $categories = OperatiiDB::read('categories', null); 
} catch (Throwable $e) {
    $error = "Database Error: " . $e->getMessage();
}

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // [CSRF / XRSF PROTECTION]: Validating that the form was submitted from our site.
    // This prevents "Form Spoofing" where an external site tries to submit data on behalf of a logged-in user.
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
        $data_to_insert = [
            'title' => $title,
            'content' => $content,
            'image_url' => $image_url ?: null, 
            'category_id' => (int)$category_id,
            'author_id' => $_SESSION['user_id'] // [RBAC]: Correctly linking the resource to the authenticated user's ID.
        ];

        try {
            // [SQL INJECTION PREVENTION]: The 'create' method uses PDO Prepared Statements 
            // and named placeholders. The data in $data_to_insert is never concatenated directly 
            // into the SQL string, neutralizing any malicious SQL commands.
            $article_id = OperatiiDB::create('articles', $data_to_insert);
            if ($article_id) {
                header("Location: index.php?status=created");
                exit;
            }
        } catch (Exception $e) {
            $error = "Insertion Error: " . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <h1>Create New Article</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="article_create.php" method="POST">
        
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

        <div class="mb-3">
            <label for="title" class="form-label">Article Title</label>
            <input type="text" class="form-control" name="title" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="category_id" class="form-label">Category</label>
            <select class="form-select" id="category_id" name="category_id" required>
                <option value="">-- Select a Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['category_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="image_url" class="form-label">Cover Image URL</label>
            <input type="url" class="form-control" name="image_url" placeholder="https://example.com/tech-image.jpg" value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea class="form-control" name="content" rows="10" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Publish Article</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>