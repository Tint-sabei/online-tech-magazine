<?php
require_once 'includes/header.php'; 
require_once 'classes/operatii_db.php'; 

/**
 * SECURITY: ROLE-BASED ACCESS CONTROL (RBAC)
 * Ensures the user is authenticated before any processing begins.
 */
require_auth(); 

// 1. Data Retrieval and Validation
$article_id = $_POST['article_id'] ?? null;
$current_user_id = $_SESSION['user_id'] ?? 0;
$current_user_role = $_SESSION['role'] ?? 'visitor';

/**
 * SECURITY: INPUT VALIDATION / HTTP REQUEST SPOOFING
 * Validates that the ID is numeric to prevent malformed requests.
 * Forces the request to be POST to prevent unauthorized GET-based deletions (Form Spoofing).
 */
if (!$article_id || !is_numeric($article_id)) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?status=error"); 
    exit;
}

/**
 * SECURITY: CSRF (XRSF) PROTECTION
 * Implements the Synchronizer Token Pattern by comparing the POST token 
 * to the secret token stored in the user's session.
 */
$submitted_token = $_POST['csrf_token'] ?? '';
$session_token = $_SESSION['csrf_token'] ?? '';

if (empty($submitted_token) || $submitted_token !== $session_token) {
    http_response_code(403); 
    die("Security Error: Invalid or missing CSRF token.");
}

// 3. SECURE DATA OPERATIONS
try {
    /**
     * SECURITY: SQL INJECTION PREVENTION (READ)
     * Uses PDO Prepared Statements with placeholders (?) to securely fetch the article.
     */
    $articles = OperatiiDB::read('articles', "WHERE article_id = ?", [$article_id]);

    if (empty($articles)) {
        header("Location: index.php?status=notfound");
        exit;
    }
    
    $article = $articles[0];
    
    /**
     * SECURITY: ROLE SEPARATION & AUTHORIZATION
     * Verifies if the user is an Administrator OR the specific owner of the article.
     * Prevents users from deleting other users' content by manipulating article IDs.
     */
    $is_administrator = ($current_user_role === 'administrator');
    $is_article_owner = ($article['author_id'] == $current_user_id);

    if (!$is_article_owner && !$is_administrator) {
        header("Location: index.php?status=denied");
        exit;
    }
    
    /**
     * SECURITY: SQL INJECTION PREVENTION (DELETE) & DATA INTEGRITY
     * Deletes related comments first to maintain relational integrity.
     * Uses parameterized delete methods to ensure the article ID is never executed as code.
     */
    OperatiiDB::delete('comments', 'article_id', $article_id); 
    OperatiiDB::delete('articles', 'article_id', $article_id);
    
    header("Location: index.php?status=deleted");
    exit;

} catch (Exception $e) {
    /**
     * SECURITY: XSS PROTECTION
     * Uses htmlspecialchars to safely output error messages, preventing script injection.
     */
    die("Deletion Error: " . htmlspecialchars($e->getMessage()));
}
?>