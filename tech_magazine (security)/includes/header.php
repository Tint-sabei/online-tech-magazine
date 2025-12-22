<?php
// Start the session only if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper variable for authentication status
$is_logged_in = isset($_SESSION['user_id']); 

// Function to check authentication
function require_auth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Tech Magazine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* White Navbar with Lavender Border */
        .navbar {
            padding: 1rem 0;
            background-color: #ffffff !important;
            border-bottom: 1px solid #D1C4E9;
        }
        
        .navbar-brand {
            color: #512DA8 !important;
            font-weight: 800;
        }

        .nav-link {
            font-weight: 500;
            color: #512DA8 !important;
            padding: 0.5rem 1rem !important;
        }

        /* Pill-style Button */
        .btn-vexon {
            background-color: #D1C4E9 !important;
            color: #512DA8 !important;
            border-radius: 50px;
            padding: 8px 24px;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-vexon:hover {
            background-color: #B39DDB !important;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="index.php">Online Tech Magazine</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
        <div class="navbar-nav ms-auto align-items-center">
            <a class="nav-link" href="index.php">Home</a>
            <a class="nav-link" href="contact_magazine/index.php">Contact</a>
          
            <?php if ($is_logged_in): ?>
                <a class="nav-link" href="article_create.php">Create Article</a>
                <div class="ms-lg-3 d-flex align-items-center">
                    <span class="text-muted small me-3">Hi, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
                    <a class="btn btn-vexon btn-sm" href="logout.php">Logout</a>
                </div>
            <?php else: ?>
                <a class="nav-link" href="login.php">Login</a>
                <a class="nav-link" href="user_register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
  </div>
</nav>

<main>