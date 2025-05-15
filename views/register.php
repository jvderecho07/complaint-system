<?php
require_once '../includes/config.php';

if (User::isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$flash = get_flash_message();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validate_csrf_token();

    $data = [
        'first_name' => trim($_POST['first_name']),
        'last_name' => trim($_POST['last_name']),
        'email' => trim($_POST['email']),
        'password' => $_POST['password'],
        'confirm_password' => $_POST['confirm_password'],
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? '')
    ];

    if (empty($data['first_name']) || empty($data['last_name'])) {
        set_flash_message('error', 'First name and last name are required.');
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        set_flash_message('error', 'Please enter a valid email address.');
    } elseif ($data['password'] !== $data['confirm_password']) {
        set_flash_message('error', 'Passwords do not match.');
    } else {
        $user = new User();
        if ($user->register($data)) {
            set_flash_message('success', 'Registration successful! Please log in.');
            header("Location: index.php");
            exit();
        } else {
            set_flash_message('error', 'Registration failed. Email may already exist.');
        }
    }

    // Redirect back to same page to show flash message
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Barangay Complaint System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <div class="card" style="@media max-width:600px; margin:30px auto;">
        <div class="card-header">
            <h2 style="margin:0;">Resident Registration</h2>
        </div>
        <div class="card-body">

            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-control">
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control">
                </div>

                <div class="form-group">
                    <label>Password * (min 6 characters)</label>
                    <input type="password" name="password" id="password" class="form-control" minlength="6" required>
                    <span class="show-password-btn" onclick="togglePassword()">Show Password</span>
                </div>

                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    <span class="show-password-btn" onclick="togglePassword()">Show Password</span>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Register</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="script.js"></script>
</body>
</html>