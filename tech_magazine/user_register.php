<?php
// CRITICAL: Start session for potential auto-login
session_start();

// Redirect logged-in users to the homepage
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'classes/Database.php'; 
require_once 'classes/operatii_db.php'; 

$error = '';
$success = '';

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = 'author'; // Default role for new users
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        try {
            // Check if user already exists (using email as unique identifier)
            $existing_user = OperatiiDB::read('users', "WHERE email = '{$email}'");
            
            if (!empty($existing_user)) {
                $error = "User with that email already exists.";
            } else {
                // 1. Generate the secure hash for storage
                // Use PASSWORD_DEFAULT for maximum security
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $user_data = [
                    'username' => $username,
                    'password_hash' => $password_hash,
                    'email' => $email,
                    'role' => $role,
                    'registration_date' => date('Y-m-d H:i:s')
                ];
                
                // 2. Insert the user into the database securely
                $new_user_id = OperatiiDB::create('users', $user_data);
                
                if ($new_user_id) {
                    $success = "Registration successful! You can now log in.";
                    
                    // OPTIONAL: Auto-login the user after registration
                    $_SESSION['user_id'] = $new_user_id;
                    $_SESSION['username'] = $username;
                    
                    // Redirect to show the user the home page now that they are logged in
                    header("Location: index.php?status=registered");
                    exit;
                } else {
                    $error = "Failed to create user account.";
                }
            }
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Tech Magazine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Register New User</h1>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form action="user_register.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Register</button>
            <a href="login.php" class="btn btn-secondary">Go to Login</a>
        </form>
    </div>
</body>
</html>