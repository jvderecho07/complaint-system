<?php
require_once '../includes/config.php';

if (!User::isLoggedIn()) {
    header("Location: index.php");
    exit();
}

if (User::isAdmin()) {
    header("Location: admin.php");
    exit();
}

$complaint = new Complaint();
$complaints = $complaint->getAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_complaint'])) {
    validate_csrf_token();
    $complaint_id = filter_input(INPUT_POST, 'complaint_id', FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'];

    if ($complaint_id && $complaint->canEdit($complaint_id, $user_id, $user_role)) {
        $complaint->delete($complaint_id);
        set_flash_message('success', 'Complaint deleted successfully.');
        header("Location: dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <div class="header-container">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
        <div >
            <form method="POST" action="logout.php" style="display: inline; ">
                <button type="submit" class="btn btn-primary">Logout</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="padding:12px 15px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2 style="margin:0; ">Your Complaints</h2>
                <a href="complaints.php?new=1" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New Complaint</a>
            </div>
        </div>
        <div class="card-body">
            <?php
            $flash = get_flash_message();
            if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>

            <?php if (empty($complaints)): ?>
                <p>You haven't submitted any complaints yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($complaints as $c): ?>
                        <tr>
                            <td><?= $c['complaint_id'] ?></td>
                            <td><?= htmlspecialchars($c['title']) ?></td>
                            <td><?= $c['category'] ?></td>
                            <td><span class="status-badge status-<?= str_replace(' ', '-', $c['status']) ?>"><?= $c['status'] ?></span></td>
                            <td><?= date('M d, Y', strtotime($c['updated_at'])) ?></td>
                            <td style="white-space:nowrap;">
                                <a href="complaints.php?id=<?= $c['complaint_id'] ?>" class="btn-edit btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="complaint_id" value="<?= $c['complaint_id'] ?>">
                                    <button type="submit" name="delete_complaint" class="btn-delete btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>