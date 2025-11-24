<?php
require_once 'includes/header.php'; 
require_once 'classes/operatii_db.php'; 

// Mandate authentication for this page
require_auth(); 

$article_id = $_GET['id'] ?? null;

if (!$article_id || !is_numeric($article_id)) {
    header("Location: index.php");
    exit;
}

try {
    // 1. Authorization check (User can only delete their own article)
    $article_check = OperatiiDB::read('articles', "WHERE article_id = {$article_id}");
    
    if (empty($article_check) || $article_check[0]['author_id'] !== $_SESSION['user_id']) {
        header("Location: index.php?status=denied");
        exit;
    }
    
    // 2. Perform the DELETE operation
    $condition = "article_id = {$article_id}";
    OperatiiDB::delete('articles', $condition);
    
    // 3. Redirect back to the homepage with a success message
    header("Location: index.php?status=deleted");
    exit;
} catch (Exception $e) {
    // Handle deletion error
    die("Deletion Error: " . $e->getMessage());
}
?>