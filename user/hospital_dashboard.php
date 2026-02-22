<?php
require_once '../config/db.php';
require_once '../config/base.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hospital') {
    header("Location: " . $base_url . "/login.php");
    exit;
}

include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Get Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM blood_requests WHERE requester_id = ?");
$stmt->execute([$user_id]);
$total_requests = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM blood_requests WHERE requester_id = ? AND status != 'Completed' AND status != 'Cancelled'");
$stmt->execute([$user_id]);
$active_requests = $stmt->fetchColumn();

?>

<div class="container">
    <h2>Hospital Dashboard -
        <?php echo htmlspecialchars($_SESSION['full_name']); ?>
    </h2>

    <div class="card-grid" style="margin-top: 2rem;">
        <div class="card">
            <h3><i class="fas fa-plus-circle"></i> Emergency Requests</h3>
            <p>Request blood for your patients safely and securely from our donor network.</p>
            <p>Active Requests: <strong>
                    <?php echo $active_requests; ?>
                </strong></p>
            <p>Total Requests: <strong>
                    <?php echo $total_requests; ?>
                </strong></p>
            <a href="request.php" class="btn btn-primary mt-2" style="background-color: #dc3545;">New Request</a>
            <a href="my_requests.php" class="btn btn-secondary mt-2">View History</a>
        </div>

        <div class="card">
            <h3><i class="fas fa-search-location"></i> Find Donors</h3>
            <p>Search for nearby eligible donors instantly.</p>
            <a href="find_donors.php" class="btn btn-primary mt-2">Search Donors</a>
        </div>

        <div class="card">
            <h3><i class="fas fa-hospital"></i> Profile & Settings</h3>
            <p>Manage hospital details and update coordinates to improve matching.</p>
            <a href="profile.php" class="btn btn-secondary mt-2">Manage Profile</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>