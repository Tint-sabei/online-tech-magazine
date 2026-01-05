<?php

require_once 'includes/header.php'; 
require_once 'classes/operatii_db.php'; 

$articles = [];
$cat_data = [];
$users_map = [];
$cat_map = [];

try {
    /**
     * SECURITY: SQL INJECTION PREVENTION
     * Even if these queries look simple, using your OperatiiDB methods 
     * ensures that your DB connection is handled via PDO Prepared Statements.
     */
    $articles = OperatiiDB::read('articles', 'ORDER BY date_posted DESC');
    $users_data = OperatiiDB::read('users', '');
    $cat_data = OperatiiDB::read('categories', null); 

    foreach ($users_data as $user) { 
        $users_map[$user['user_id']] = $user['username']; 
    }
    
    foreach ($cat_data as $cat) { 
        $cat_map[$cat['category_id']] = $cat['category_name']; 
    }
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
    <div class="row align-items-center container mx-auto text-start">
        <div class="col-md-7">
            <h1 class="display-3 fw-bold" style="color: #512DA8;">Tech Made Simple & Fun</h1>
            <p class="lead fw-normal text-muted">Discover the latest in tech with a fresh perspective and a touch of humor.</p>
            <a class="btn btn-lg rounded-pill px-4 shadow-sm" style="background-color: #D1C4E9; color: #512DA8; font-weight:bold;" href="user_register.php">Join the Community</a>
        </div>
        <div class="col-md-5">
            <img src="https://images.unsplash.com/photo-1546776310-eef45dd6d63c?auto=format&fit=crop&w=800&q=80" 
                 class="img-fluid rounded-5 shadow-lg" 
                 alt="Tech Illustration" 
                 style="border: 8px solid white; max-width: 100%; height: auto;">
        </div>
    </div>
</div>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8">
            <h2 class="mb-4 fw-bold" style="color: #512DA8;">Latest Articles</h2>
            <div class="row">
                <?php foreach ($articles as $article): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <img src="<?= $article['image_url'] ?: 'https://via.placeholder.com/400x250' ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <span class="badge mb-2">
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
            <div class="p-4 mb-4 rounded-4 shadow-sm border" style="background-color: #F3E5F5; border-color: #D1C4E9 !important;">
                <h5 class="fw-bold" style="color: #512DA8;"><i class="bi bi-chat-quote"></i> Tech Quote</h5>
                <?php
                    /**
                    * EXTERNAL DATA PARSING & MODELING
                    * This section fulfills the requirement to integrate external content without using frames/URLs.
                    * 1. DATA FETCHING: We consume the ZenQuotes API using file_get_contents.
                    * 2. DATA PARSING: The raw JSON response is parsed into a PHP associative array using json_decode.
                    * 3. DATA MODELING: We map specific external data points ('q' for quote, 'a' for author) 
                    * into the application's UI structure.
                    */
                    // 1. Fetch the data
                    $quote_json = @file_get_contents('https://zenquotes.io/api/random?t=' . time());
                    
                    if ($quote_json) {
                        $quote_data = json_decode($quote_json, true);

                        $content = $quote_data[0]['q'] ?? "The best way to predict the future is to invent it.";
                        $author  = $quote_data[0]['a'] ?? "Alan Kay";
                        
                        echo "<p class='small mb-2 text-dark italic'>\"" . htmlspecialchars($content) . "\"</p>";
                        echo "<footer class='blockquote-footer small' style='color: #512DA8;'>" . htmlspecialchars($author) . "</footer>";
                    } else {
                        echo "<p class='small mb-0'>Stay curious. Technology is always evolving.</p>";
                    }
                ?>
                
                <hr>
                <p class="x-small text-muted mb-0" style="font-size: 0.7rem;">Source: ZenQuotes API</p>
            </div>

            <div class="p-4 mb-3 rounded-4 shadow-sm" style="background-color: #F8F9FA;">
                <h4 class="fw-bold mb-3" style="color: #512DA8;">TRENDING NOW</h4>
                <div id="engagementChart" style="width: 100%; height: 280px;"></div>
            </div>

            <div class="p-4 bg-white border rounded-4 mt-4 shadow-sm text-center" style="border-color: #512DA8 !important;">
                <h5 class="fw-bold" style="color: #512DA8;">Magazine Report</h5>
                <p class="small text-muted">View Our Latest Insights</p>

                <?php 
                    $report_file = 'Tech_Magazine_Full_Report.pdf';
                    if (file_exists($report_file)): ?>
                        <a href="<?= $report_file ?>" download="Latest_Report.pdf" class="btn btn-peach-outline w-100 mb-2 fw-bold">
                            Download Latest PDF
                        </a>
                        
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'administrator'): ?>
                            <a href="delete_report.php" class="btn btn-sm text-danger text-decoration-none fw-bold" 
                            onclick="return confirm('Delete this report permanently?')">
                            <i class="bi bi-trash"></i> Delete Old Report
                            </a>
                        <?php endif; ?>
                <?php else: ?>
                    <div class='alert alert-light border small p-2 text-muted'>Coming Soon...</div>
                <?php endif; ?>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'administrator'): ?>
                    <hr>
                    <p class="small text-muted fw-bold">Admin Controls:</p>
                    <a href="generate_report.php" class="btn btn-vexon w-100">Generate New Report</a>
                <?php endif; ?>
            </div>
        </div> 
    </div> 
</div> 

<div class="container my-5">
    <div class="p-5 shadow-sm cta-block">
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

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="assets/js/render_charts.js"></script>

<?php require_once 'includes/footer.php'; ?>
