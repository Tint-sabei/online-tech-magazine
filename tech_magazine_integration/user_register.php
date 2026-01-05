<?php
/**
 * SECURITY: SESSION MANAGEMENT
 * Starts the session to handle auto-login after a successful registration.
 */
session_start();

// Redirect already logged-in users to the homepage.
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

    /**
     * SECURITY: AUTOMATED SUBMISSION PROTECTION (HONEYPOT)
     * Requirements: "Protection against automated transmission".
     * If a bot fills this hidden field, the submission is rejected.
     */
    if (!empty($_POST['middle_name_human_verify'])) {
        die("Automated submission detected. Access denied.");
    }

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = 'author'; // Default role for new authors
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required to join our team.";
    } elseif (strlen($password) < 6) {
        $error = "Security requirement: Password must be at least 6 characters.";
    } else {
        try {
            /**
             * SECURITY: SQL INJECTION PREVENTION (READ)
             * Using parameterized queries to check if the user exists.
             */
            $existing_user = OperatiiDB::read('users', "WHERE email = ?", [$email]);
            
            if (!empty($existing_user)) {
                $error = "This email is already registered with an author account.";
            } else {
                /**
                 * SECURITY: SECURE HASHING
                 * Uses PASSWORD_DEFAULT (Bcrypt) to ensure passwords are never stored in plain text.
                 */
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $user_data = [
                    'username' => $username,
                    'password_hash' => $password_hash,
                    'email' => $email,
                    'role' => $role,
                    'registration_date' => date('Y-m-d H:i:s')
                ];
                
                /**
                 * SECURITY: SQL INJECTION PREVENTION (CREATE)
                 * The create method uses Prepared Statements for safe data insertion.
                 */
                $new_user_id = OperatiiDB::create('users', $user_data);
                
                if ($new_user_id) {
                    // Success: Auto-login the user
                    $_SESSION['user_id'] = $new_user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    header("Location: index.php?status=registered");
                    exit;
                } else {
                    $error = "System error: Failed to create author account.";
                }
            }
        } catch (Exception $e) {
            $error = "Database Error: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Online Tech Magazine Authors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #F3E5F5; font-family: 'Segoe UI', sans-serif; }
        .register-card { border-radius: 25px; border: none; box-shadow: 0 10px 40px rgba(81, 45, 168, 0.15); background: white; }
        .btn-primary { background-color: #512DA8; border: none; border-radius: 50px; padding: 12px; font-weight: 600; }
        .btn-primary:hover { background-color: #311B92; }
        .brand-color { color: #512DA8; }
        .form-control { border-radius: 12px; border: 1px solid #D1C4E9; padding: 12px; }
        .form-control:focus { border-color: #512DA8; box-shadow: 0 0 0 0.2rem rgba(81, 45, 168, 0.1); }
    </style>
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="col-md-6 col-lg-5">
            <div class="card register-card p-5">
                <div class="text-center mb-4">
                    <h2 class="fw-bold brand-color">Online Tech Magazine</h2>
                    <h5 class="text-muted">Register as a New Author</h5>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger rounded-3 small"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form action="user_register.php" method="POST">
                    
                    <div style="display:none;">
                        <input type="text" name="middle_name_human_verify" value="">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">USERNAME</label>
                        <input type="text" class="form-control" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">EMAIL ADDRESS</label>
                        <input type="email" class="form-control" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">PASSWORD</label>
                        <input type="password" class="form-control" name="password" required placeholder="Min. 6 characters">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Create Author Account</button>
                        <a href="login.php" class="btn btn-link text-decoration-none small" style="color: #512DA8;">Already have an account? Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>