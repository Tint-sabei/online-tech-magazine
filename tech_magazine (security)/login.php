<?php
/**
 * SECURITY: SESSION MANAGEMENT
 * Start the session at the very top to handle authentication states and CSRF tokens.
 */
session_start();

// Redirect already authenticated users to prevent unnecessary login attempts.
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once ('classes/Database.php'); 

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    /**
     * SECURITY: AUTOMATED SUBMISSION PROTECTION (HONEYPOT)
     * Requirement: Protection against bots. If this hidden field is filled, 
     * it indicates an automated script rather than a human user.
     */
    if (!empty($_POST['username_field_hidden'])) {
        die("Bot activity detected.");
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            /**
             * SECURITY: SQL INJECTION PREVENTION
             * Using named placeholders (:email) and Prepared Statements ensures 
             * the user input is never interpreted as executable SQL code.
             */
            $sql = "SELECT user_id, username, password_hash, email, role FROM users WHERE email = :email";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $stored_hash = trim($user['password_hash'] ?? '');

            /**
             * SECURITY: SECURE HASHING & AUTHENTICATION
             * We use password_verify() to check the password against the Bcrypt hash.
             * This prevents plain-text password exposure in the database.
             */
            if ($user && password_verify($password, $stored_hash)) { 
                
                // [RBAC] ROLE SEPARATION: Store the user's role in the session 
                // to enable permission checks (Admin vs Author) across the site.
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Generate a CSRF token upon successful login for subsequent state-changing actions.
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                header("Location: index.php");
                exit;
            } else {
                // [Security] Generic error message to prevent "User Enumeration" attacks.
                $error = "Invalid email or password.";
            }

        } catch (PDOException $e) {
            /**
             * SECURITY: XSS PREVENTION
             * Sanitizing database error output to prevent script injection via error messages.
             */
            $error = "Database error: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Tech Magazine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #F3E5F5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .login-card { border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(81, 45, 168, 0.1); background: white; }
        .btn-primary { background-color: #512DA8; border: none; border-radius: 50px; padding: 10px 30px; }
        .btn-primary:hover { background-color: #311B92; }
        .form-control { border-radius: 12px; padding: 12px; border: 1px solid #D1C4E9; }
        .form-control:focus { box-shadow: 0 0 0 0.25rem rgba(81, 45, 168, 0.1); border-color: #512DA8; }
        .brand-text { color: #512DA8; font-weight: bold; }
    </style>
</head>

<body>
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="col-md-5">
            <div class="card login-card p-5">
                <div class="text-center mb-4">
                    <h2 class="brand-text">Online Tech Magazine</h2>
                    <p class="text-muted">Welcome back! Please login.</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger rounded-4"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div style="display:none;">
                        <input type="text" name="username_field_hidden" value="">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Sign In</button>
                    </div>
                </form>
                
                <div class="mt-4 text-center">
                    <p class="small text-muted">Don't have an account? <a href="user_register.php" style="color: #512DA8;">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>