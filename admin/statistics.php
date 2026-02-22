<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/base.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $base_url . "/login.php");
    exit;
}
$stmt = $pdo->query("SELECT blood_group, COUNT(*) as count FROM donation_profiles GROUP BY blood_group");
$blood_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$stmt = $pdo->query("SELECT urgency, COUNT(*) as count FROM blood_requests GROUP BY urgency");
$urgency_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$all_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
include __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <h2>System Statistics</h2>
    <div class="card-grid mt-2">
        <div class="card" style="text-align: left;">
            <h3>Donors by Blood Group</h3>
            <table>
                <thead>
                    <tr>
                        <th>Blood Group</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_groups as $group): ?>
                        <tr>
                            <td><strong><?php echo $group; ?></strong></td>
                            <td><?php echo isset($blood_stats[$group]) ? $blood_stats[$group] : 0; ?></td>
                        </tr>
                    <?php
endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card" style="text-align: left;">
            <h3>Requests by Urgency</h3>
            <table>
                <thead>
                    <tr>
                        <th>Urgency Level</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
$urgencies = ['Normal', 'Urgent', 'Emergency'];
foreach ($urgencies as $urgency):
?>
                        <tr>
                            <td><span class="badge badge-<?php echo strtolower($urgency) == 'emergency' ? 'urgent' : 'normal'; ?>"><?php echo $urgency; ?></span></td>
                            <td><?php echo isset($urgency_stats[$urgency]) ? $urgency_stats[$urgency] : 0; ?></td>
                        </tr>
                    <?php
endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
