<?php
require_once 'includes/header.php'; 
require_once 'classes/operatii_db.php'; 

// Check if the user is logged in (used for showing edit links)
$is_logged_in = isset($_SESSION['user_id']); 
$current_user_id = $_SESSION['user_id'] ?? null; // Securely get the user ID

$article_id = $_GET['id'] ?? null;
$article = null;

if (!$article_id || !is_numeric($article_id)) {
    // If no valid ID is passed, redirect to the list
    header("Location: index.php");
    exit;
}

try {
    // --- FIX: Use a single query to get the Article and Author Username ---
    // Since OperatiiDB::read cannot handle complex JOINs, we must perform the two reads carefully.
    
    // 1. Fetch Article Data
    $articles = OperatiiDB::read('articles', "WHERE article_id = {$article_id}");

    if (empty($articles)) {
        $article = null;
    } else {
        $article = $articles[0];
        
        // 2. Fetch the Author's Username separately
        $author_data = OperatiiDB::read('users', "WHERE user_id = {$article['author_id']}");
        
        // 3. Map the data securely
        $article['username'] = $author_data[0]['username'] ?? 'Unknown Author';
    }


} catch (Exception $e) {
    // Display a clean error page instead of dying with complex debug info
    echo "<div class='alert alert-danger mt-4'>Database Error loading article: " . htmlspecialchars($e->getMessage()) . "</div>";
    $article = null; // Set article to null to trigger the "not found" message
}
?>

<?php if (!$article): ?>
    <div class="alert alert-danger mt-4">Article not found or failed to load.</div>
    <a href="index.php" class="btn btn-primary">Go Home</a>
<?php else: ?>
    
    <div class="card my-4">
        <div class="card-header bg-dark text-white">
            <h1 class="h3 mb-0"><?= htmlspecialchars($article['title']) ?></h1>
        </div>
        <div class="card-body">
            <p class="text-muted small">
                Posted by: <strong><?= htmlspecialchars($article['username']) ?></strong> 
                on <?= date("F j, Y", strtotime($article['date_posted'])) ?>
            </p>
            
            <?php 
            // Show Edit/Delete links if the user is logged in AND is the author
            if ($is_logged_in && $current_user_id == $article['author_id']): ?>
                <div class="mb-3">
                    <a href="article_edit.php?id=<?= $article['article_id'] ?>" class="btn btn-sm btn-warning">Edit Article</a>
                    <a href="article_delete.php?id=<?= $article['article_id'] ?>" 
                       onclick="return confirm('Are you sure you want to delete this article?');"
                       class="btn btn-sm btn-danger">Delete Article</a>
                </div>
            <?php endif; ?>

            <hr>
            <div class="article-content">
                <!-- Use nl2br to format multiline content correctly -->
                <p><?= nl2br(htmlspecialchars($article['content'])) ?></p>
            </div>
        </div>
    </div>
    
    <a href="index.php" class="btn btn-secondary">← Back to All Articles</a>

<?php endif; ?>

<?php
require_once 'includes/footer.php'; 
?>
