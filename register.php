<?php
include 'includes/db.php';
include 'includes/header.php';

$firstname = $lastname = $email = $phone = $password = $confirm_password = $gender = "";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $gender = trim($_POST['gender']);
    
    // Validation
    if (empty($firstname)) {
        $errors[] = "First name is required.";
    }
    if (empty($lastname)) {
        $errors[] = "Last name is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    }
    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    }
    if (!preg_match("/^[0-9]{11}$/", $phone)) {
        $errors[] = "Please enter a valid phone number (11 digits).";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if (empty($gender)) {
        $errors[] = "Gender is required.";
    }
    // If no errors, proceed to insert into database
    if (empty($errors)) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $errors[] = "Email already registered!";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Modified insert statement
            $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, phone, password, role, gender) 
            VALUES (:firstname, :lastname, :email, :phone, :password, 'user', :gender)");

            $stmt->bindParam(':firstname', $firstname);
            $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':gender', $gender);

            if ($stmt->execute()) {
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Registration Successful!',
                        text: 'Please login with your credentials',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                </script>";
                exit();
            } else {
                $errors[] = "Failed to register.";
            }
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
        min-height: 100vh;
    }

    .container {
        max-width: 450px;
        margin: 5vh auto;
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
        color: var(--secondary);
        font-weight: 600;
        text-align: center;
        position: relative;
        margin-bottom: 25px;
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
        width: 100%;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
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

    .alert {
        border-radius: 8px;
        border: none;
        background-color: #fff5f5;
        color: #c53030;
        border-left: 4px solid #fc8181;
    }
</style>

<!-- Update the form section -->
<div class="container">
    <h2>Create Account</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form action="register.php" method="POST">
        <div class="mb-3">
            <label for="firstname" class="form-label">First Name</label>
            <input type="text" class="form-control" id="firstname" name="firstname" 
                   value="<?php echo htmlspecialchars($firstname); ?>" required>
        </div>
        <div class="mb-3">
            <label for="lastname" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="lastname" name="lastname" 
                   value="<?php echo htmlspecialchars($lastname); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" 
                   value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="tel" class="form-control" id="phone" name="phone" 
                   pattern="[0-9]{11}" title="Please enter 11 digits phone number"
                   value="<?php echo htmlspecialchars($phone); ?>" required>
            <small class="text-muted">Format: 08012345678</small>
        </div>
        <div class="mb-3">
            <label class="form-label">Gender</label>
            <div class="d-flex">
                <div class="form-check me-4">
                    <input class="form-check-input" type="radio" name="gender" id="male" value="male" 
                        <?php echo ($gender === 'male') ? 'checked' : ''; ?> required>
                    <label class="form-check-label" for="male">
                        Male
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="gender" id="female" value="female"
                        <?php echo ($gender === 'female') ? 'checked' : ''; ?> required>
                    <label class="form-check-label" for="female">
                        Female
                    </label>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="password-field">
                <input type="password" class="form-control" id="password" name="password" required>
                <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
            </div>
        </div>
        <div class="mb-4">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <div class="password-field">
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password')"></i>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Create Account</button>
        <div class="form-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </form>
</div>

<script>
function togglePassword(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const toggleIcon = passwordField.nextElementSibling;
    
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
<?php include 'includes/whatsapp_float.php'; ?>
<?php include 'includes/footer.php'; ?>
