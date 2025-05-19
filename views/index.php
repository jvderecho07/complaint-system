<?php
require_once '../includes/config.php';

if (User::isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = new User();
    if ($user->login($_POST['email'], $_POST['password'])) {
        if (User::isAdmin()) {
            header("Location: admin.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } else {
        set_flash_message('error', 'Invalid email or password.');
    }
}

$flash = get_flash_message();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Barangay Estanza Complaint System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <div class="card" style="@media max-width: 500px; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div class="card-header">
            <h2 style="text-align:center;">Brgy. 55 Estanza Complaint System</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                    <span class="show-password-btn" onclick="togglePassword('password')">Show Password</span>
                </div>

                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <div style="text-align:center; margin-top:15px;">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>
</div>
<script src="script.js"></script>
</body>
</html>