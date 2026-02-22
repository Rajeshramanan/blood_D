<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/base.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "/login.php");
    exit;
}
$stmt = $pdo->prepare("SELECT * FROM donation_profiles WHERE user_id = ? ORDER BY last_donation_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$donations = $stmt->fetchAll();
$stmt2 = $pdo->prepare("
    SELECT r.blood_group, r.urgency, r.hospital_name, r.location, r.state, r.contact_number, rr.status, rr.responded_at, rr.request_id
    FROM request_responses rr
    JOIN blood_requests r ON rr.request_id = r.id
    WHERE rr.donor_id = ?
    ORDER BY rr.responded_at DESC
");
$stmt2->execute([$_SESSION['user_id']]);
$responses = $stmt2->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <h2>My Donations & Activity</h2>
    <div class="card-grid mt-2">
        <div class="card" style="text-align: left;">
            <h3>Donation History</h3>
            <?php if (count($donations) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Blood Group</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $don): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($don['last_donation_date'])); ?></td>
                                <td><?php echo $don['blood_group']; ?></td>
                                <td><?php echo $don['is_available'] ? 'Available' : 'Donated'; ?></td>
                            </tr>
                            <?php
                        endforeach; ?>
                    </tbody>
                </table>
                <?php
            else: ?>
                <p>No donation history yet.</p>
                <?php
            endif; ?>
        </div>
        <div class="card" style="text-align: left;">
            <h3>Response History</h3>
            <?php if (count($responses) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Hospital</th>
                            <th>Group</th>
                            <th>My Response</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($responses as $res): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($res['hospital_name']); ?></strong>
                                    <div style="font-size: 0.85rem; color: #555; margin-top: 5px;">
                                        <i class="fas fa-map-marker-alt" style="color: var(--primary-color);"></i>
                                        <?php echo htmlspecialchars($res['location']); ?><br>
                                        <i class="fas fa-map" style="color: var(--primary-color);"></i>
                                        <?php echo htmlspecialchars($res['state']); ?><br>
                                        <?php if ($res['status'] === 'Accepted'): ?>
                                            <i class="fas fa-phone-alt" style="color: var(--primary-color);"></i>
                                            <?php echo htmlspecialchars($res['contact_number']); ?>
                                        <?php else: ?>
                                            <span style="color: #888;"><i class="fas fa-lock"></i> Mobile hidden until
                                                accepted</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo $res['blood_group']; ?></td>
                                <td>
                                    <?php if ($res['status'] === 'Pending'): ?>
                                        <form action="respond_request.php" method="POST"
                                            style="display:inline-block; margin-right:5px;">
                                            <input type="hidden" name="request_id" value="<?php echo $res['request_id']; ?>">
                                            <input type="hidden" name="action" value="Accepted">
                                            <button type="submit" class="btn btn-primary"
                                                style="padding: 0.2rem 0.5rem; font-size: 0.8rem; background-color: #28a745;">Accept</button>
                                        </form>
                                        <form action="respond_request.php" method="POST" style="display:inline-block;">
                                            <input type="hidden" name="request_id" value="<?php echo $res['request_id']; ?>">
                                            <input type="hidden" name="action" value="Rejected">
                                            <button type="submit" class="btn btn-secondary"
                                                style="padding: 0.2rem 0.5rem; font-size: 0.8rem;">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <span
                                            class="badge badge-<?php echo strtolower($res['status']); ?>"><?php echo $res['status']; ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                        endforeach; ?>
                    </tbody>
                </table>
                <?php
            else: ?>
                <p>You haven't responded to any requests yet.</p>
                <?php
            endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>