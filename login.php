<?php

include 'includes/db.php';
include 'includes/header.php';

$email = $password = "";
$errors = [];

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
        // Prepare the statement with named parameters
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = :email");
        
        // Bind the email parameter
        $stmt->bindParam(':email', $email);
        
        // Execute the statement
        $stmt->execute();

        // Check if a record is found
        if ($stmt->rowCount() === 1) {
            // Fetch the user data
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Credentials are correct, start a session
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Invalid email or password.";
            }
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}
?>
<style>
    .container{
        padding: 30px 20px;
        height: 82vh;
    }
</style>
<div class="container">
    <h2>Login</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>
         <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
