function togglePassword() {
    const passwordField = document.getElementById('password');
    const showPasswordBtn = document.querySelector('.show-password-btn');

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        showPasswordBtn.textContent = 'Hide Password';
    } else {
        passwordField.type = 'password';
        showPasswordBtn.textContent = 'Show Password';
    }
}