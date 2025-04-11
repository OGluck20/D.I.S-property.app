<?php

require_once 'includes/db.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$email = $password = "";
$errors = [];

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Use JavaScript for redirection instead of header()
    echo "<script>window.location.href = 'client_dashboard.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validation
    if (empty($email)) {
        $errors[] = "Email is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }
    
    // If no errors, proceed to check credentials
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['firstname'] = $user['firstname']; // Changed from username
                $_SESSION['lastname'] = $user['lastname']; // Added lastname
                $_SESSION['role'] = $user['role'];
                
                // Use JavaScript for redirection
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Welcome back, " . htmlspecialchars($user['firstname']) . "!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = 'client_dashboard.php';
                    });
                </script>";
                exit();
            } else {
                $errors[] = "Invalid email or password.";
            }
        } catch(PDOException $e) {
            $errors[] = "Login failed. Please try again.";
        }
    }
}
?>
<style>
    :root {
        --primary: #2ecc71;
        --primary-dark: #27ae60;
        --secondary: #34495e;
        --accent: #3498db;
        --background: #f9fafb;
        --text: #2c3e50;
        --shadow: rgba(0, 0, 0, 0.1);
    }

    body {
        background: linear-gradient(135deg, #f4f7fa 0%, #e8eef3 100%);
        font-family: 'Arial', sans-serif;
        height: 75vh;
    }

    .container {
        max-width: 400px;
        margin: 16vh auto;
        padding: 30px;
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .container:hover {
        transform: translateY(-5px);
    }

    h2 {
        margin-bottom: 25px;
        color: var(--secondary);
        font-weight: 600;
        text-align: center;
        position: relative;
    }

    h2:after {
        content: '';
        display: block;
        width: 50px;
        height: 3px;
        background: var(--primary);
        margin: 10px auto;
        border-radius: 3px;
    }

    .form-label {
        font-weight: 500;
        color: var(--text);
        margin-bottom: 8px;
    }

    .form-control {
        border-radius: 8px;
        border: 2px solid #eef2f7;
        padding: 12px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.1);
    }

    .password-field {
        position: relative;
    }

    .toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #999;
        transition: color 0.3s ease;
    }

    .toggle-password:hover {
        color: var(--primary);
    }

    .btn-primary {
        background-color: var(--primary);
        border: none;
        border-radius: 8px;
        padding: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
    }

    .alert {
        border-radius: 8px;
        border: none;
        background-color: #fff5f5;
        color: #c53030;
        border-left: 4px solid #fc8181;
    }

    .form-footer {
        margin-top: 20px;
        text-align: center;
        color: var(--text);
    }

    .form-footer a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .form-footer a:hover {
        color: var(--primary-dark);
    }
</style>

<body>
    <div class="container">
        <h2>Welcome Back</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form action="login.php" method="POST" class="login-form">
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="password-field">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
            <div class="mt-3 text-center">
                <a href="forgot_password.php" class="text-decoration-none">Forgot Password?</a>
            </div>
            <div class="form-footer">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </form>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
<?php include 'includes/whatsapp_float.php'; ?>
<?php include 'includes/footer.php'; ?>
