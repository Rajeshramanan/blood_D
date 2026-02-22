<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/base.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'blood_bank') {
    header("Location: " . $base_url . "/login.php");
    exit;
}

include __DIR__ . '/../includes/header.php';

$user_id = $_SESSION['user_id'];

// Get Stats for Blood Bank
$stmt = $pdo->prepare("SELECT SUM(units) FROM blood_inventory WHERE bank_id = ?");
$stmt->execute([$user_id]);
$total_units = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM blood_inventory WHERE bank_id = ? AND units > 0");
$stmt->execute([$user_id]);
$available_groups = $stmt->fetchColumn();

?>

<div class="container">
    <h2>Blood Bank Dashboard -
        <?php echo htmlspecialchars($_SESSION['full_name']); ?>
    </h2>

    <div class="card-grid" style="margin-top: 2rem;">
        <div class="card">
            <h3><i class="fas fa-cubes"></i> Inventory Manage</h3>
            <p>Update your available blood units for each blood group.</p>
            <p>Available Blood Groups: <strong>
                    <?php echo $available_groups; ?>
                </strong></p>
            <p>Total Units in Stock: <strong>
                    <?php echo $total_units; ?>
                </strong></p>
            <a href="inventory.php" class="btn btn-primary mt-2">Manage Inventory</a>
        </div>

        <div class="card">
            <h3><i class="fas fa-hand-holding-water"></i> Requests</h3>
            <p>View fulfilling history to requests.</p>
            <a href="my_requests.php" class="btn btn-secondary mt-2">View Requests</a>
        </div>

        <div class="card">
            <h3><i class="fas fa-warehouse"></i> Profile</h3>
            <p>Manage your blood bank's public information and location.</p>
            <a href="profile.php" class="btn btn-secondary mt-2">Manage Profile</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>