function togglePassword(inputId = 'password') {
    const passwordField = document.getElementById(inputId);
    const showPasswordBtn = document.querySelector(`[onclick="togglePassword('${inputId}')"] .show-password-text`);

    if (!passwordField) return;

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        if (showPasswordBtn) showPasswordBtn.textContent = 'Hide Password';
    } else {
        passwordField.type = 'password';
        if (showPasswordBtn) showPasswordBtn.textContent = 'Show Password';
    }
}