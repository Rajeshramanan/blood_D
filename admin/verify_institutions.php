<?php
require_once '../config/db.php';
require_once '../config/base.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $base_url . "/login.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $inst_id = (int) $_POST['user_id'];
    if ($_POST['action'] === 'approve') {
        $stmt = $pdo->prepare("UPDATE users SET is_verified = TRUE WHERE id = ?");
        if ($stmt->execute([$inst_id])) {
            $success = "Institution verified successfully.";
        } else {
            $error = "Failed to verify institution.";
        }
    } elseif ($_POST['action'] === 'reject') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$inst_id])) {
            $success = "Institution rejected and removed.";
        } else {
            $error = "Failed to reject institution.";
        }
    }
}

// Fetch pending institutions
$stmt = $pdo->query("SELECT * FROM users WHERE role IN ('hospital', 'blood_bank') AND is_verified = FALSE ORDER BY created_at DESC");
$pending_institutions = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Verify Institutions</h2>
        <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <?php if ($success): ?>
        <p class="text-center" style="color: green;">
            <?php echo $success; ?>
        </p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="text-center text-danger">
            <?php echo $error; ?>
        </p>
    <?php endif; ?>

    <div class="table-container mt-2">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Registered At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($pending_institutions) > 0): ?>
                    <?php foreach ($pending_institutions as $inst): ?>
                        <tr>
                            <td>
                                <?php echo $inst['id']; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($inst['full_name']); ?>
                            </td>
                            <td style="text-transform: capitalize;">
                                <?php echo str_replace('_', ' ', $inst['role']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($inst['email']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($inst['phone']); ?>
                            </td>
                            <td>
                                <?php echo date('M d, Y', strtotime($inst['created_at'])); ?>
                            </td>
                            <td>
                                <form action="" method="POST" style="display: inline-block;">
                                    <input type="hidden" name="user_id" value="<?php echo $inst['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-primary"
                                        style="padding: 5px 10px;">Approve</button>
                                    <button type="submit" name="action" value="reject" class="btn btn-danger"
                                        style="padding: 5px 10px; background-color: #dc3545;"
                                        onclick="return confirm('Are you sure you want to reject and delete this institution?');">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No pending institutions.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>