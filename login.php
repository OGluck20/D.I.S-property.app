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
            background-color: #f4f7fa; /* Light background for the entire page */
            font-family: 'Arial', sans-serif; /* Modern font */
            height: 75vh;
        }

        .container {
            max-width: 400px; /* Set a max width for the form */
            margin: 16vh auto; /* Center the form */
            padding: 30px; /* Padding around the form */
            background-color: white; /* White background for the form */
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
        }

        h2 {
            margin-bottom: 20px; /* Space below the heading */
            color: #333; /* Darker color for the heading */
        }

        .form-label {
            font-weight: bold; /* Bold labels */
        }

        .form-control {
            border-radius: 5px; /* Rounded corners for input fields */
            border: 1px solid #ddd; /* Light border */
            transition: border-color 0.3s; /* Smooth transition for border color */
        }

        .form-control:focus {
            border-color: #4CAF50; /* Green border on focus */
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.5); /* Light green shadow on focus */
        }

        .btn-primary {
            background-color: #4CAF50; /* Green background for the button */
            border: none; /* Remove border */
            border-radius: 5px; /* Rounded corners for the button */
            padding: 10px; /* Padding for the button */
            transition: background-color 0.3s; /* Smooth transition for hover */
        }

        .btn-primary:hover {
            background-color: #45a049; /* Darker green on hover */
        }

        .alert {
            margin-bottom: 20px; /* Space below the alert */
            border-radius: 5px; /* Rounded corners for alert */
        }
</style>
<body>
<div class="container">
    <h2 class="text-center">Login</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form action="login.php" method="POST" class="login-form">
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
</div>    
</body>

<?php include 'includes/footer.php'; ?>
