<?php

require_once '../config/db.php';
require_once '../config/base.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $base_url . "/login.php");
    exit;
}

include '../includes/header.php';

// Fetch Totals
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$total_donors = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM donation_profiles")->fetchColumn();
$total_requests = $pdo->query("SELECT COUNT(*) FROM blood_requests")->fetchColumn();
$completed_donations = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status='Completed'")->fetchColumn();

$pending_institutions = $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('hospital', 'blood_bank') AND is_verified = FALSE")->fetchColumn();

?>

<div class="container">
    <h2>Admin Dashboard</h2>

    <div class="card-grid" style="margin-top: 2rem;">
        <div class="card">
            <h3><i class="fas fa-users"></i> Users</h3>
            <p>Total Users: <strong><?php echo $total_users; ?></strong></p>
            <a href="manage_users.php" class="btn btn-secondary mt-2">Manage Users</a>
        </div>

        <div class="card">
            <h3><i class="fas fa-heartbeat"></i> Donors</h3>
            <p>Active Donors: <strong><?php echo $total_donors; ?></strong></p>
            <a href="manage_users.php" class="btn btn-secondary mt-2">View Donors</a>
        </div>

        <div class="card">
            <h3><i class="fas fa-procedures"></i> Requests</h3>
            <p>Total Requests: <strong><?php echo $total_requests; ?></strong></p>
            <p>Completed: <strong><?php echo $completed_donations; ?></strong></p>
            <a href="manage_requests.php" class="btn btn-primary mt-2">Manage Requests</a>
        </div>

        <div class="card">
            <h3><i class="fas fa-chart-bar"></i> Statistics</h3>
            <p>View detailed reports</p>
            <a href="statistics.php" class="btn btn-secondary mt-2">View Stats</a>
        </div>

        <div class="card">
            <h3><i class="fas fa-building"></i> Institutions</h3>
            <p>Pending Verification: <strong><?php echo $pending_institutions; ?></strong></p>
            <a href="verify_institutions.php" class="btn btn-primary mt-2" style="background-color: #ff9800;">Verify
                Details</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>