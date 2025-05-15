<?php
require_once '../includes/config.php';
require_once '../classes/Complaint.php';
require_once '../classes/User.php';

if (!User::isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$complaint = new Complaint();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

$current_complaint = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validate_csrf_token();

    if (isset($_POST['submit_complaint'])) {
        $image_path = handleImageUpload('complaint_image');
        $complaint->create(
            $user_id,
            $_POST['title'],
            $_POST['description'],
            $_POST['category'],
            $image_path
        );
        set_flash_message('success', 'Complaint submitted successfully!');
        header("Location: complaints.php?created=1");
        exit();
    }

    if (isset($_POST['update_complaint'])) {
        $complaint_id = $_POST['complaint_id'];
        if ($complaint->canEdit($complaint_id, $user_id, $user_role)) {
            $image_path = handleImageUpload('complaint_image');
            $complaint->update(
                $complaint_id,
                $_POST['title'],
                $_POST['description'],
                $_POST['category'],
                $image_path
            );
            set_flash_message('success', 'Complaint updated successfully.');
            header("Location: complaints.php?updated=1&id=" . $complaint_id);
            exit();
        }
    }

    if (isset($_POST['delete_complaint'])) {
        $complaint_id = $_POST['complaint_id'];
        if ($complaint->canEdit($complaint_id, $user_id, $user_role)) {
            $complaint->delete($complaint_id);
            set_flash_message('success', 'Complaint deleted successfully.');
            header("Location: complaints.php");
            exit();
        }
    }
}

if (isset($_GET['id'])) {
    $current_complaint = $complaint->getById($_GET['id']);
    if (!$complaint->canEdit($current_complaint['complaint_id'], $user_id, $user_role)) {
        header("Location: dashboard.php");
        exit();
    }
}

function handleImageUpload($inputName) {
    if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        $fileType = mime_content_type($_FILES[$inputName]['tmp_name']);
        $fileSize = $_FILES[$inputName]['size'];

        if (!in_array($fileType, $allowedTypes)) {
            set_flash_message('error', 'Invalid file type. Only JPG, PNG, and GIF allowed.');
            return null;
        }

        if ($fileSize > $maxSize) {
            set_flash_message('error', 'File too large. Max size is 5MB.');
            return null;
        }

        $target_dir = "../uploads/";
        $filename = uniqid('img_', true) . '.' . strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES[$inputName]["tmp_name"], $target_file)) {
            return $target_file;
        } else {
            set_flash_message('error', 'Error uploading image.');
        }
    }
    return null;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= isset($current_complaint) ? 'Edit' : 'New' ?> Complaint</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <div class="header-container">
        <h1><?= isset($current_complaint) ? 'Edit Complaint' : 'Submit New Complaint' ?></h1>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <?php
    $flash = get_flash_message();
    if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <?php if ($current_complaint): ?>
                    <input type="hidden" name="complaint_id" value="<?= $current_complaint['complaint_id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" class="form-control" required value="<?= isset($current_complaint) ? htmlspecialchars($current_complaint['title']) : '' ?>">
                </div>

                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" class="form-control" required>
                        <option value="">Select Category</option>
                        <option value="Sanitation" <?= isset($current_complaint) && $current_complaint['category'] == 'Sanitation' ? 'selected' : '' ?>>Sanitation</option>
                        <option value="Noise" <?= isset($current_complaint) && $current_complaint['category'] == 'Noise' ? 'selected' : '' ?>>Noise</option>
                        <option value="Safety" <?= isset($current_complaint) && $current_complaint['category'] == 'Safety' ? 'selected' : '' ?>>Safety</option>
                        <option value="Infrastructure" <?= isset($current_complaint) && $current_complaint['category'] == 'Infrastructure' ? 'selected' : '' ?>>Infrastructure</option>
                        <option value="Other" <?= isset($current_complaint) && $current_complaint['category'] == 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" class="form-control" rows="5" required><?= isset($current_complaint) ? htmlspecialchars($current_complaint['description']) : '' ?></textarea>
                </div>

                <div class="form-group">
                    <label>Upload Image (Optional)</label>
                    <input type="file" name="complaint_image" accept="image/*">
                    <?php if (isset($current_complaint) && !empty($current_complaint['image_path'])): ?>
                        <div class="image-preview-container">
                            <p>Current Image:</p>
                            <img src="<?= $current_complaint['image_path'] ?>" class="image-preview">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <?php if (isset($current_complaint)): ?>
                        <button type="submit" name="update_complaint" class="btn btn-primary">Update Complaint</button>
                        <button type="submit" name="delete_complaint" class="btn-delete btn-sm" onclick="return confirm('Are you sure?')">Delete Complaint</button>
                    <?php else: ?>
                        <button type="submit" name="submit_complaint" class="btn btn-primary">Submit Complaint</button>
                    <?php endif; ?>
                    <a href="complaints.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>