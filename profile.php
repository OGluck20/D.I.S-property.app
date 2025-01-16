<?php
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT email, username FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
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
        --gradient: linear-gradient(135deg, var(--primary), var(--accent));
    }
    .profile-container {
        max-width: 600px;
        margin: 4rem auto;
        padding: 2rem;
        background: var(--background);
        border-radius: 15px;
        box-shadow: 0 4px 6px var(--shadow);
    }

    .profile-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        background: var(--primary);
        border-radius: 50%;
        margin: 0 auto 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
    }

    .profile-section {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        margin-bottom: 1.5rem;
    }

    .profile-section h3 {
        color: var(--primary);
        margin-bottom: 1.5rem;
        font-size: 1.2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--text);
        font-weight: 500;
    }

    .form-group input {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #eee;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .form-group input:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.1);
    }

    .btn-save {
        background: var(--primary);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        cursor: pointer;
        width: 100%;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-save:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar">
            <i class="fas fa-user"></i>
        </div>
        <h2>My Profile</h2>
    </div>

    <div class="profile-section">
        <h3>Account Information</h3>
        <form action="update_profile.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" 
                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <button type="submit" class="btn-save">Update Profile</button>
        </form>
    </div>

    <div class="profile-section">
        <h3>Change Password</h3>
        <form id="changePasswordForm" action="change_password.php" method="POST">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn-save">Change Password</button>
        </form>
    </div>
</div>

<script>
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('handler/change_password.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000
                }).then(() => {
                    this.reset();
                });
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>