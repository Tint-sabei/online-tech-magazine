<?php
session_start(); 
require_once '../classes/Database.php'; 

// --- SECURITY: ACCESS CONTROL (AUTHENTICATION) ---
// Verifies that a session exists to prevent unauthorized guest access.
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php'); 
    exit;
}

// --- SECURITY: ROLE SEPARATION (AUTHORIZATION) ---
// Restricts access to this folder exclusively to users with the 'administrator' role.
if ($_SESSION['role'] !== 'administrator') {
    // Prevents non-admin users (e.g., authors) from accessing the dashboard.
    header('Location: /index.php'); 
    exit;
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection(); 
} catch (PDOException $e) {
    die('Database connection failed.');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Administrator Control Panel</h1>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>. You have full access.</p>
    
    <ul>
        <li><a href="manage_users.php">Manage Users</a></li>
        <li><a href="view_messages.php">View Contact Messages</a></li>
    </ul>

    <p>This page proves separation of roles is working.</p>
</body>
</html>