<?php
// [Security] Load header to handle session start and consistent CSS styling
require_once 'includes/header.php'; 
require_once 'classes/operatii_db.php'; 

// Ensure a CSRF token exists for the session (essential for guest comments)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$is_logged_in = isset($_SESSION['user_id']); 
$current_user_id = $_SESSION['user_id'] ?? null; 
$current_user_role = $_SESSION['role'] ?? 'visitor';

$article_id = $_GET['id'] ?? null;
$article = null;
$comment_error = '';

// [Input Validation]: Basic sanitization to prevent Request Spoofing
if (!$article_id || !is_numeric($article_id)) {
    header("Location: index.php");
    exit;
}

// --- START: COMMENT HANDLER LOGIC (Supports Guest Comments) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_submit'])) {
    
    // [BOT PROTECTION]: Honeypot check. A bot will fill this, a human won't see it.
    if (!empty($_POST['website_url_verification'])) {
        die("Bot activity detected. Submission blocked.");
    }

    // [CSRF PROTECTION]: Validates the token for both guests and authors
    $submitted_token = $_POST['csrf_token'] ?? '';
    if (empty($submitted_token) || $submitted_token !== $_SESSION['csrf_token']) {
        die("Security Error: CSRF token validation failed.");
    }

    $article_id_post = (int)$_POST['article_id'];
    $comment_text = trim($_POST['comment_text'] ?? '');
    
    // Determine name: use session if logged in, otherwise use the guest input
    $author_name = $_SESSION['username'] ?? trim($_POST['author_name'] ?? '');

    if (empty($comment_text) || empty($author_name)) {
        $comment_error = "Both name and comment text are required.";
    } else {
        try {
            $data_to_create = [
                'article_id' => $article_id_post,
                'author_name' => $author_name,
                'comment_text' => $comment_text,
                'comment_date' => date('Y-m-d H:i:s'),
                'user_id' => $_SESSION['user_id'] ?? null // NULL for guest readers
            ];

            // [SQL INJECTION PREVENTION]: Parameterized PDO statement
            OperatiiDB::create('comments', $data_to_create);

            header("Location: article_view.php?id={$article_id_post}#comments-section");
            exit;

        } catch (Exception $e) {
            $comment_error = "Database Error: " . htmlspecialchars($e->getMessage());
        }
    }
}

// --- Fetch existing article data ---
try {
    // [SQL INJECTION PREVENTION]: Using placeholders (?) to protect reads
    $articles = OperatiiDB::read('articles', "WHERE article_id = ?", [$article_id]);

    if (!empty($articles)) {
        $article = $articles[0];
        
        // Securely fetch author and comment data using parameterized queries
        $author_data = OperatiiDB::read('users', "WHERE user_id = ?", [$article['author_id']]);
        $article['username'] = $author_data[0]['username'] ?? 'Unknown Author';

        $comments = OperatiiDB::read('comments', "WHERE article_id = ? ORDER BY comment_date ASC", [$article_id]);
        $comments_count = count($comments);
    }
} catch (Exception $e) {
    echo "<div class='container mt-4 alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    $article = null; 
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <?php if (!$article): ?>
                <div class="alert alert-danger">Article not found.</div>
            <?php else: ?>
                
                <article class="card border-0 shadow-sm rounded-5 overflow-hidden mb-5">
                    <?php if (!empty($article['image_url'])): ?>
                        <img src="<?= htmlspecialchars($article['image_url']) ?>" class="card-img-top" style="max-height: 400px; object-fit: cover;">
                    <?php endif; ?>

                    <div class="card-body p-4 p-md-5">
                        <h1 class="display-5 fw-bold mb-3" style="color: #512DA8;"><?= htmlspecialchars($article['title']) ?></h1>
                        
                        <p class="text-muted">
                            By <span class="fw-bold" style="color: #512DA8;"><?= htmlspecialchars($article['username']) ?></span> 
                            | <?= date("M d, Y", strtotime($article['date_posted'])) ?>
                        </p>

                        <?php 
                        // [RBAC]: UI check—Only Owners or Admins see management tools
                        $can_edit = $is_logged_in && ($current_user_id == $article['author_id'] || $current_user_role === 'administrator');
                        if ($can_edit): ?>
                            <div class="mb-4">
                                <a href="article_edit.php?id=<?= $article['article_id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Edit</a>
                                <form method="POST" action="article_delete.php" style="display:inline;">
                                    <input type="hidden" name="article_id" value="<?= $article['article_id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Delete</button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <div class="article-content lh-lg" style="font-size: 1.1rem; color: #455A64;">
                            <?= nl2br(htmlspecialchars($article['content'])) ?>
                        </div>
                    </div>
                </article>

                <div id="comments-section" class="mb-5">
                    <h3 class="fw-bold mb-4" style="color: #512DA8;">Comments (<?= $comments_count ?>)</h3>
                    <?php foreach ($comments as $comment): ?>
                        <div class="p-4 mb-3 rounded-4 bg-white border shadow-sm" style="border-color: #F3E5F5;">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold" style="color: #512DA8;"><?= htmlspecialchars($comment['author_name']) ?></span>
                                <small class="text-muted"><?= date("M d, Y", strtotime($comment['comment_date'])) ?></small>
                            </div>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($comment['comment_text'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card border-0 shadow-sm rounded-5 p-4 p-md-5" style="background-color: #F8F9FA;">
                    <h4 class="fw-bold mb-4" style="color: #512DA8;">Join the Discussion</h4>
                    
                    <?php if ($comment_error): ?>
                        <div class="alert alert-danger"><?= $comment_error ?></div>
                    <?php endif; ?>

                    <form action="article_view.php?id=<?= (int)$article_id ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="article_id" value="<?= (int)$article_id ?>">
                        
                        <div style="display:none;">
                            <input type="text" name="website_url_verification" value="">
                        </div>

                        <?php if (!$is_logged_in): ?>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Your Name</label>
                                <input type="text" class="form-control" name="author_name" required>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Your Comment</label>
                            <textarea class="form-control" name="comment_text" rows="4" required placeholder="What do you think?"></textarea>
                        </div>
                        <button type="submit" name="comment_submit" class="btn btn-primary rounded-pill px-5">Post Comment</button>
                    </form>
                </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>