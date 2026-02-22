<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/base.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Simulation of receiving an alert
// For testing, insert a fake alert if URL has ?simulate=1
if (isset($_GET['simulate']) && $_GET['simulate'] == 1) {
    if ($_SESSION['role'] === 'user') {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'urgent')");
        $stmt->execute([$user_id, "URGENT: A nearby hospital needs your blood group immediately!"]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'info')");
        $stmt->execute([$user_id, "INFO: System Notification Simulation"]);
    }
    header("Location: notifications.php");
    exit;
}

// Mark all as read when visited
$stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE");
$stmt->execute([$user_id]);

// Fetch alerts
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>My Notifications & Alerts</h2>
        <a href="?simulate=1" class="btn btn-secondary">Simulate Alert</a>
    </div>

    <div class="card" style="margin-top: 20px;">
        <?php if (count($notifications) > 0): ?>
            <ul style="list-style-type: none; padding: 0;">
                <?php foreach ($notifications as $note): ?>
                    <li
                        style="padding: 15px; border-bottom: 1px solid #ddd; <?php echo $note['type'] === 'urgent' ? 'background-color: #ffeeba; border-left: 5px solid #ff9800;' : ''; ?>">
                        <strong>
                            <?php echo strtoupper($note['type']); ?>:
                        </strong>
                        <?php echo htmlspecialchars($note['message']); ?>
                        <br>
                        <small style="color: #6c757d;">
                            <?php echo date('M d, Y h:i A', strtotime($note['created_at'])); ?>
                        </small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-center">You have no new notifications.</p>
        <?php endif; ?>
    </div>
    <div class="mt-2 text-center">
        <?php
        if ($_SESSION['role'] == 'hospital')
            $dash = 'hospital_dashboard.php';
        elseif ($_SESSION['role'] == 'blood_bank')
            $dash = 'blood_bank_dashboard.php';
        elseif ($_SESSION['role'] == 'admin')
            $dash = '../admin/admin_dashboard.php';
        else
            $dash = 'dashboard.php';
        ?>
        <a href="<?php echo $dash; ?>" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>