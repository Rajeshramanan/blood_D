<?php
require_once '../config/db.php';
require_once '../config/base.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: " . $base_url . "/login.php");
    exit;
}

include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Get Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM donation_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_donations = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM blood_requests WHERE requester_id = ? AND status != 'Completed' AND status != 'Cancelled'");
$stmt->execute([$user_id]);
$active_requests = $stmt->fetchColumn();

// Check next eligibility
$stmt = $pdo->prepare("SELECT last_donation_date FROM donation_profiles WHERE user_id = ? ORDER BY last_donation_date DESC LIMIT 1");
$stmt->execute([$user_id]);
$last_donation = $stmt->fetchColumn();

$eligible_date = "Now";
if ($last_donation) {
    $next_date = date('Y-m-d', strtotime($last_donation . ' + 90 days'));
    if ($next_date > date('Y-m-d')) {
        $eligible_date = $next_date;
    }
}
?>

<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
    
    <div class="card-grid" style="margin-top: 2rem;">
        <div class="card">
            <h3><i class="fas fa-hand-holding-heart"></i> Donations</h3>
            <p>Total Donations: <strong><?php echo $total_donations; ?></strong></p>
            <p>Next Eligible: <strong class="text-danger"><?php echo $eligible_date; ?></strong></p>
            <a href="donate.php" class="btn btn-primary mt-2">Donate Blood</a>
            <a href="my_donations.php" class="btn btn-secondary mt-2">History</a>
        </div>
        
        <div class="card">
            <h3><i class="fas fa-procedures"></i> Requests</h3>
            <p>Active Requests: <strong><?php echo $active_requests; ?></strong></p>
            <a href="request.php" class="btn btn-primary mt-2">Request Blood</a>
            <a href="my_requests.php" class="btn btn-secondary mt-2">My Requests</a>
        </div>
        
        <div class="card">
            <h3><i class="fas fa-user"></i> Profile</h3>
            <p>Update your personal information and password.</p>
            <a href="profile.php" class="btn btn-secondary mt-2">Manage Profile</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
