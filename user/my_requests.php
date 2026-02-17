<?php

require_once '../config/db.php';
require_once '../config/base.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "/login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM blood_requests WHERE requester_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$requests = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container">
    <h2>My Blood Requests</h2>
    
    <?php if (count($requests) > 0): ?>
        <div class="table-responsive mt-2">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Blood Group</th>
                        <th>Units</th>
                        <th>Urgency</th>
                        <th>Status</th>
                        <th>Matched Donors</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($req['created_at'])); ?></td>
                            <td><span class="badge" style="background:#d32f2f;"><?php echo $req['blood_group']; ?></span></td>
                            <td><?php echo $req['units_required']; ?></td>
                            <td>
                                <span class="badge <?php echo($req['urgency'] == 'Emergency') ? 'badge-urgent' : 'badge-normal'; ?>">
                                    <?php echo $req['urgency']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($req['status']); ?>">
                                    <?php echo $req['status']; ?>
                                </span>
                            </td>
                            <td>
                                <?php
        $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM request_responses WHERE request_id = ? AND status = 'Accepted'");
        $stmt2->execute([$req['id']]);
        echo $stmt2->fetchColumn();
?>
                            </td>
                            <td>
                                <?php if ($req['status'] == 'Pending'): ?>
                                    <form action="cancel_request.php" method="POST" onsubmit="return confirm('Are you sure?');">
                                        <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                        <button type="submit" class="btn btn-secondary" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;">Cancel</button>
                                    </form>
                                <?php
        else: ?>
                                    -
                                <?php
        endif; ?>
                            </td>
                        </tr>
                    <?php
    endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php
else: ?>
        <p class="mt-2">No requests found. <a href="request.php">Request blood now.</a></p>
    <?php
endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
