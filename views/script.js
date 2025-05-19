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

// Expand/Collapse Description Toggle
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.description-cell').forEach(cell => {
        const shortDesc = cell.querySelector('.short-desc');
        const fullDesc = cell.querySelector('.full-desc');
        const toggleIcon = cell.querySelector('.toggle-icon');

        // Skip if any element is missing
        if (!shortDesc || !fullDesc || !toggleIcon) return;

        cell.addEventListener('click', function () {
            if (shortDesc.style.display === 'none') {
                // Show short, hide full
                shortDesc.style.display = '';
                fullDesc.style.display = 'none';
                toggleIcon.textContent = ' [Show More]';
            } else {
                // Hide short, show full
                shortDesc.style.display = 'none';
                fullDesc.style.display = '';
                toggleIcon.textContent = ' [Show Less]';
            }
        });
    });
});