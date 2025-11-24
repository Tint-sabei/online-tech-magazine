<?php
// CRITICAL: Start the session immediately at the top
session_start();

// Redirect authenticated users away from the login page
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Include the Database class to establish PDO connection
require_once 'classes/Database.php'; 

$error = '';

// --- Process Login Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            // SELECT statement includes all necessary columns for verification and session setup
            $sql = "SELECT user_id, username, password_hash, email FROM users WHERE email = :email";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Apply TRIM() to the hash retrieved from the database to remove any hidden spaces
            $stored_hash = trim($user['password_hash'] ?? '');

            // 1. Verify if user exists and password matches hash (SECURE HASHING ONLY)
            if ($user && password_verify($password, $stored_hash)) { 
                
                // 2. Successful Login: Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                
                // 3. Redirect to the homepage
                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid email or password.";
            }

        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tech Magazine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">Login</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</body>
</html>