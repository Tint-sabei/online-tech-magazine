<?php
// CRITICAL: Includes header to start session and provide layout
require_once 'includes/header.php'; 
require_once 'classes/operatii_db.php'; 

// Fetch all articles
try {
    // 1. Fetch raw articles data
    $articles = OperatiiDB::read('articles', 'ORDER BY date_posted DESC');
    
    // 2. Fetch users to map article author_id to a username for display
    $users_data = OperatiiDB::read('users', '');
    $users_map = [];
    foreach ($users_data as $user) {
        $users_map[$user['user_id']] = $user['username'];
    }

} catch (Exception $e) {
    $error = "Could not load articles: " . $e->getMessage();
    $articles = [];
}
?>

    <div class="row">
        <div class="col-12">
            <h1>Latest Tech Articles</h1>
            
            <?php 
            // Display system messages after CRUD operations
            if (isset($_GET['status'])): ?>
                <?php $msg = ''; $class = 'success';
                if ($_GET['status'] == 'created') $msg = 'Article created successfully!';
                if ($_GET['status'] == 'updated') $msg = 'Article updated successfully!';
                if ($_GET['status'] == 'deleted') $msg = 'Article deleted successfully.';
                if ($_GET['status'] == 'denied') { $msg = 'Access denied. You can only edit your own articles.'; $class = 'warning'; }
                ?>
                <div class="alert alert-<?= $class ?>"><?= $msg ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if (empty($articles)): ?>
                <div class="alert alert-info">No articles found. Time to create one!</div>
            <?php endif; ?>

            <?php foreach ($articles as $article): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="article_view.php?id=<?= $article['article_id'] ?>" class="text-decoration-none">
                                <?= htmlspecialchars($article['title']) ?>
                            </a>
                        </h5>   
                        <p class="card-text text-muted small">
                            Posted by: <strong><?= htmlspecialchars($users_map[$article['author_id']] ?? 'Unknown') ?><strong> on <?= date("F j, Y", strtotime($article['date_posted'])) ?>
                        </p>
                        <p class="card-text">
                            <?= substr(htmlspecialchars($article['content']), 0, 300) ?>... 
                        </p>
                        
                        <!-- Action links visible only if logged in and if the user is the author -->
                        <?php if ($is_logged_in && $_SESSION['user_id'] == $article['author_id']): ?>
                        <a href="article_edit.php?id=<?= $article['article_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="article_delete.php?id=<?= $article['article_id'] ?>" 
                           onclick="return confirm('Are you sure you want to delete this article?');"
                           class="btn btn-sm btn-danger">Delete</a>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php
require_once 'includes/footer.php'; 
?>
