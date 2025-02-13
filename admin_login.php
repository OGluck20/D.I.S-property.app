<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Case-insensitive username check
    $stmt = $conn->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?) AND role = 'admin'");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    // Add validation before accessing array elements
    if ($admin !== false) {
        error_log("Stored hash: " . $admin['password']);
        $passwordMatch = password_verify($password, $admin['password']);
        error_log("Verification: " . ($passwordMatch ? 'Match' : 'No Match'));
    } else {
        error_log("No admin found with username: " . $username);
        $admin = null; // Explicitly set to null
    }

    if ($admin && $passwordMatch) {
        // Set admin session variables
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['username'] = $admin['username'];
        $_SESSION['role'] = 'admin';
        $_SESSION['admin_logged_in'] = true;

        // Use absolute path for redirect
        header('Location: /D.I.S-property.app/admin.php');
        exit();
    } else {
        $error = "Invalid admin credentials";
    }

    // Add debug output
    error_log("Login attempt - Username: " . $_POST['username']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - D.I.S</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .btn-login {
            background: #2ecc71;
            border: none;
            width: 100%;
        }
        .btn-login:hover {
            background: #27ae60;
        }
    </style>
</head>
<body>
    <div class="login-form">
        <div class="login-header">
            <h2>Admin Login</h2>
        </div>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-login text-white">Login</button>
        </form>
    </div>
</body>
</html>