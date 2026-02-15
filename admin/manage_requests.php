<?php

require_once '../config/db.php';
include '../includes/header.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id']) && isset($_POST['status'])) {
    $stmt = $pdo->prepare("UPDATE blood_requests SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['request_id']]);
}

// Check for Delete
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM blood_requests WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: manage_requests.php");
    exit;
}

// Fetch Requests
$stmt = $pdo->prepare("
    SELECT br.*, u.full_name, u.phone 
    FROM blood_requests br
    JOIN users u ON br.requester_id = u.id
    ORDER BY br.created_at DESC
");
$stmt->execute();
$requests = $stmt->fetchAll();
?>

<div class="container">
    <h2>Manage Blood Requests</h2>
    <div class="table-responsive mt-2">
        <table>
            <thead>
                <tr>
                    <th>Requester</th>
                    <th>Blood</th>
                    <th>Units</th>
                    <th>Hospital</th>
                    <th>Urgency</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($req['full_name']); ?><br>
                            <small><?php echo htmlspecialchars($req['phone']); ?></small>
                        </td>
                        <td><span class="badge" style="background:#d32f2f;"><?php echo $req['blood_group']; ?></span></td>
                        <td><?php echo $req['units_required']; ?></td>
                        <td>
                            <?php echo htmlspecialchars($req['hospital_name']); ?><br>
                            <small><?php echo htmlspecialchars($req['location']); ?></small>
                        </td>
                        <td>
                            <span class="badge <?php echo($req['urgency'] == 'Emergency') ? 'badge-urgent' : 'badge-normal'; ?>">
                                <?php echo $req['urgency']; ?>
                            </span>
                        </td>
                        <td>
                            <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                <select name="status" onchange="this.form.submit()" style="padding: 0.3rem; border-radius: 4px; border: 1px solid #ddd;">
                                    <option value="Pending" <?php if ($req['status'] == 'Pending')
        echo 'selected'; ?>>Pending</option>
                                    <option value="Accepted" <?php if ($req['status'] == 'Accepted')
        echo 'selected'; ?>>Accepted</option>
                                    <option value="Completed" <?php if ($req['status'] == 'Completed')
        echo 'selected'; ?>>Completed</option>
                                    <option value="Cancelled" <?php if ($req['status'] == 'Cancelled')
        echo 'selected'; ?>>Cancelled</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <a href="?delete_id=<?php echo $req['id']; ?>" class="btn btn-primary" style="padding: 0.2rem 0.5rem; font-size: 0.8rem; background: #c0392b;" onclick="return confirm('Delete this request?');">Delete</a>
                        </td>
                    </tr>
                <?php
endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
