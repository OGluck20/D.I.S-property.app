// Example: Simple form validation for registration
document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.querySelector('form[action="register.php"]');
    if(registerForm) {
        registerForm.addEventListener('submit', (e) => {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            if(password !== confirmPassword) {
                e.preventDefault();
                alert("Passwords do not match.");
            }
        });
    }
});
