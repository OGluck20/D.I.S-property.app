<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
include 'includes/header.php';
?>

<style>
    .container {
        max-width: 400px;
        margin: 16vh auto;
        padding: 30px;
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    }

    .btn-primary {
        background-color: #2ecc71;
        border: none;
        border-radius: 8px;
        padding: 12px;
        font-weight: 600;
    }
</style>

<div class="container">
    <h2 class="text-center mb-4">Reset Password</h2>
    <p class="text-center text-muted mb-4">Enter your email to reset your password</p>

    <div id="alertMessage" class="alert" style="display: none;"></div>

    <form id="forgotPasswordForm">
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="new_password" class="form-label">New Password</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100" id="submitBtn">Reset Password</button>
        <div class="text-center mt-3">
            <a href="login.php" class="text-decoration-none">Back to Login</a>
        </div>
    </form>
</div>

<script>
document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = document.getElementById('submitBtn');
    const alertDiv = document.getElementById('alertMessage');
    
    if (form.new_password.value !== form.confirm_password.value) {
        alertDiv.className = 'alert alert-danger';
        alertDiv.textContent = 'Passwords do not match';
        alertDiv.style.display = 'block';
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Processing...';
    
    fetch('handler/reset_password.php', {
        method: 'POST',
        body: new FormData(form)
    })
    .then(response => response.json())
    .then(data => {
        alertDiv.style.display = 'block';
        
        if (data.success) {
            alertDiv.className = 'alert alert-success';
            alertDiv.textContent = data.message;
            form.reset();
            
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 2000);
        } else {
            alertDiv.className = 'alert alert-danger';
            alertDiv.textContent = data.message;
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Reset Password';
        }
    })
    .catch(error => {
        alertDiv.style.display = 'block';
        alertDiv.className = 'alert alert-danger';
        alertDiv.textContent = 'An error occurred. Please try again.';
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Reset Password';
    });
});
</script>

<?php include 'includes/footer.php'; ?>