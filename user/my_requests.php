<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/base.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "/login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM blood_requests WHERE requester_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$requests = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
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
                        <th>Accepted Donors</th>
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
                                <span
                                    class="badge <?php echo ($req['urgency'] == 'Emergency') ? 'badge-urgent' : 'badge-normal'; ?>">
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
                                $stmt2 = $pdo->prepare("
                                    SELECT u.full_name, u.phone 
                                    FROM request_responses rr
                                    JOIN users u ON rr.donor_id = u.id
                                    WHERE rr.request_id = ? AND rr.status = 'Accepted'
                                ");
                                $stmt2->execute([$req['id']]);
                                $accepted_donors = $stmt2->fetchAll();

                                if (count($accepted_donors) > 0) {
                                    echo "<ul style='margin:0; padding-left:20px; text-align:left;'>";
                                    foreach ($accepted_donors as $d) {
                                        echo "<li><strong>" . htmlspecialchars($d['full_name']) . "</strong>:<br><i class='fas fa-phone-alt'></i> " . htmlspecialchars($d['phone']) . "</li>";
                                    }
                                    echo "</ul>";
                                } else {
                                    echo "<span style='color: #888;'>Waiting for donors...</span>";
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($req['status'] == 'Pending'): ?>
                                    <form action="cancel_request.php" method="POST" onsubmit="return confirm('Are you sure?');">
                                        <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                        <button type="submit" class="btn btn-secondary"
                                            style="padding: 0.2rem 0.5rem; font-size: 0.8rem;">Cancel</button>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>