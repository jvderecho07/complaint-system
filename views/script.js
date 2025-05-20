// Toggle password visibility
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

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.description-cell').forEach(cell => {
        const shortDesc = cell.querySelector('.short-desc');
        const fullDesc = cell.querySelector('.full-desc');
        const toggleIcon = cell.querySelector('.toggle-icon');

        if (!shortDesc || !fullDesc || !toggleIcon) return;

        // Ensure correct initial state from CSS
        shortDesc.style.display = 'inline';
        fullDesc.style.display = 'none';

        cell.addEventListener('click', function () {
            if (shortDesc.style.display === 'none') {
                shortDesc.style.display = 'inline';
                fullDesc.style.display = 'none';
                toggleIcon.textContent = ' [Show More]';
            } else {
                shortDesc.style.display = 'none';
                fullDesc.style.display = 'inline';
                toggleIcon.textContent = ' [Show Less]';
            }
        });
    });
});