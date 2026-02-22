<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/base.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $base_url . "/login.php");
    exit;
}
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: manage_users.php");
    exit;
}
$stmt = $pdo->query("SELECT id, full_name, email, phone, role, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC");
$users = $stmt->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <h2>Manage Users</h2>
    <div class="table-responsive mt-2">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <a href="?delete_id=<?php echo $user['id']; ?>" class="btn btn-primary" style="padding: 0.2rem 0.5rem; font-size: 0.8rem; background: #c0392b;" onclick="return confirm('Are you sure you want to delete this user? This will also remove their donations and requests.');">Delete</a>
                        </td>
                    </tr>
                <?php
endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
