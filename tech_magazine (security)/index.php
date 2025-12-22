<?php
require_once 'includes/header.php'; 
require_once 'classes/operatii_db.php'; 

try {
    /**
     * SECURITY: SQL INJECTION PREVENTION
     * Even if these queries look simple, using your OperatiiDB methods 
     * ensures that your DB connection is handled via PDO Prepared Statements.
     */
    $articles = OperatiiDB::read('articles', 'ORDER BY date_posted DESC');
    $users_data = OperatiiDB::read('users', '');
    $cat_data = OperatiiDB::read('categories', null); 

    $users_map = [];
    foreach ($users_data as $user) { $users_map[$user['user_id']] = $user['username']; }
    
    $cat_map = [];
    foreach ($cat_data as $cat) { $cat_map[$cat['category_id']] = $cat['category_name']; }
} catch (Exception $e) {
    /**
     * SECURITY: XSS PREVENTION
     * We sanitize the error message just in case the exception contains user input.
     */
    $error = "Load Error: " . htmlspecialchars($e->getMessage());
}
?>

<div class="position-relative overflow-hidden p-3 p-md-5 text-center" 
     style="background-color: #F3E5F5; border-bottom: 5px solid #D1C4E9;">
    <div class="row align-items-center container mx-auto">
        <div class="col-md-7 text-start">
            <h1 class="display-3 fw-bold" style="color: #512DA8;">Tech Made Simple & Fun</h1>
            <p class="lead fw-normal text-muted">Discover the latest in tech with a fresh perspective and a touch of humor.</p>
            <a class="btn btn-lg rounded-pill px-4 shadow-sm" style="background-color: #D1C4E9; color: #512DA8;" href="user_register.php">Join the Community</a>
        </div>
        <div class="col-md-5">
            <img src="https://images.unsplash.com/photo-1546776310-eef45dd6d63c?q=80&w=810&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" 
                 class="img-fluid rounded-5 shadow-lg" alt="Funny Tech Character" style="border: 8px solid white;">
        </div>
    </div>
</div>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8">
            <h2 class="mb-4 fw-bold" style="color: #512DA8;">Latest Blog Posts</h2>
            <div class="row">
                <?php foreach ($articles as $article): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden" style="box-shadow: 0 10px 20px rgba(209, 196, 233, 0.2) !important;">
                            <img src="<?= $article['image_url'] ?: 'https://via.placeholder.com/400x250' ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <span class="badge mb-2" style="background-color: #FFCCBC; color: #D84315; border-radius: 50px; padding: 0.5rem 1rem;">
                                    <?= htmlspecialchars($cat_map[$article['category_id']] ?? 'Tech') ?>
                                </span>
                                <h5 class="card-title fw-bold">
                                    <a href="article_view.php?id=<?= (int)$article['article_id'] ?>" class="text-decoration-none" style="color: #455A64;">
                                        <?= htmlspecialchars($article['title']) ?>
                                    </a>
                                </h5>
                                <p class="text-muted small">By <?= htmlspecialchars($users_map[$article['author_id']] ?? 'Admin') ?> | <?= date("M d, Y", strtotime($article['date_posted'])) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-md-4">
            <div class="p-4 mb-3 rounded-4 shadow-sm" style="background-color: #F8F9FA;">
                <h4 class="fw-bold mb-3" style="color: #512DA8;">Trending Posts</h4>
                <p class="text-muted small">Analytics coming soon...</p>
            </div>

            <div class="p-4 bg-white border rounded-4 mt-4 shadow-sm" style="border-color: #D1C4E9 !important;">
                <h4 class="fw-bold mb-3" style="color: #512DA8;">Explore Categories</h4>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($cat_data as $cat): ?>
                        <a href="#" class="btn btn-sm rounded-pill px-3" 
                           style="border: 1px solid #D1C4E9; color: #512DA8;">
                           <?= htmlspecialchars($cat['category_name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="p-5 shadow-sm" style="background: linear-gradient(135deg, #D1C4E9, #E1BEE7); border-radius: 30px; color: #512DA8;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="display-6 fw-bold">Ready to contribute?</h2>
                <p class="mb-0 text-dark">Join our group of authors and share your tech insights.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="user_register.php" class="btn btn-light btn-lg rounded-pill px-5 shadow-sm" style="color: #512DA8; font-weight: bold;">Join Us</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>