<?php
require_once '../includes/config.php';

if (!User::isLoggedIn() || !User::isAdmin()) {
    header("Location: index.php");
    exit();
}

$complaint = new Complaint();
$user = new User();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token();

    // Handle delete action
    if (isset($_POST['delete_complaint'])) {
        $complaint_id = filter_input(INPUT_POST, 'complaint_id', FILTER_VALIDATE_INT);

        if ($complaint_id && $complaint->canEdit($complaint_id, $_SESSION['user_id'], $_SESSION['user_role'])) {
            $complaint->delete($complaint_id);
            set_flash_message('success', 'Complaint deleted successfully.');
        } else {
            set_flash_message('error', 'You do not have permission to delete this complaint.');
        }

        header("Location: admin.php");
        exit();
    }

    // Handle status update
    if (isset($_POST['update_status'])) {
        $complaint_id = filter_input(INPUT_POST, 'complaint_id', FILTER_VALIDATE_INT);
        $new_status = $_POST['status'];

        if ($complaint_id && in_array($new_status, ['pending', 'resolved'])) {
            $complaint->updateStatus($complaint_id, $new_status);
            set_flash_message('success', 'Complaint status updated successfully.');
        } else {
            set_flash_message('error', 'Invalid complaint ID or status.');
        }

        header("Location: admin.php");
        exit();
    }
}

$complaints = $complaint->getAll();
$users = $user->getAllUsers();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <div class="header-container">
        <h1>Admin Dashboard</h1>
        <div>
            <form method="POST" action="logout.php" style="display: inline;">
                <button type="submit" class="btn btn-primary">Logout</button>
            </form>
        </div>
    </div>

    <?php
    $flash = get_flash_message();
    if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>All Complaints</h2>
        </div>
        <div class="card-body">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Submitted By</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Image</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($complaints as $c): ?>
                        <tr>
                            <td><?= $c['complaint_id'] ?></td>
                            <td><?= htmlspecialchars($c['title']) ?></td>
                            <td class="description-cell" style="cursor:pointer; max-width: 200px;">
                                <span class="short-desc"><?= htmlspecialchars(strlen($c['description']) > 30 ? substr($c['description'], 0, 30) . '...' : $c['description']) ?></span>
                                <span class="full-desc" style="display:none;"><?= htmlspecialchars($c['description']) ?></span>
                                  <span class="toggle-icon" style="color: var(--primary); font-size:0.85rem;"> [Show More]</span>
                            </td>
                            <td><?= htmlspecialchars($c['first_name'].' '.$c['last_name']) ?></td>
                            <td><?= htmlspecialchars($c['email']) ?></td>
                            <td><?= htmlspecialchars($c['phone']) ?></td>
                            <td><?= $c['category'] ?></td>
                            <td>
                                <span class="status-badge status-<?= str_replace(' ', '-', $c['status']) ?>">
                                    <?= ucfirst($c['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($c['image_path'])): ?>
                                    <a href="<?= htmlspecialchars($c['image_path']) ?>" target="_blank">
                                        <img src="<?= htmlspecialchars($c['image_path']) ?>" alt="Complaint Image" class="complaint-image-preview">
                                    </a>
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($c['updated_at'])) ?></td>
                            <td style="white-space:nowrap;">
                                <!-- Status Edit Dropdown -->
                                <form method="POST" action="admin.php" style="display:inline; margin-right:10px;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="complaint_id" value="<?= $c['complaint_id'] ?>">
                                    <select name="status" class="form-control" style="width:auto; display:inline; padding:5px;">
                                        <option value="pending" <?= $c['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="resolved" <?= $c['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-sm btn-primary" style="padding:5px 10px;">
                                        Save
                                    </button>
                                </form>

                                <!-- Delete Button -->
                                <form method="POST" action="admin.php" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="complaint_id" value="<?= $c['complaint_id'] ?>">
                                    <button type="submit" name="delete_complaint" class="btn-delete btn-sm"
                                            onclick="return confirm('Are you sure?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-top:30px;">
        <div class="card-header">
            <h2>Registered Users</h2>
        </div>
        <div class="card-body">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['user_id'] ?></td>
                            <td><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= ucfirst($u['role']) ?></td>
                            <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="script.js"></script>
</body>
</html>