<?php

require_once '../config/db.php';
include '../includes/header.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $blood_group = $_POST['blood_group'];
    $units = (int)$_POST['units'];
    $urgency = $_POST['urgency'];
    $hospital = trim($_POST['hospital']);
    $location = trim($_POST['location']);

    if ($units < 1) {
        $error = "Units must be at least 1.";
    }
    else {
        $stmt = $pdo->prepare("INSERT INTO blood_requests (requester_id, blood_group, units_required, urgency, hospital_name, location, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");

        if ($stmt->execute([$_SESSION['user_id'], $blood_group, $units, $urgency, $hospital, $location])) {
            $request_id = $pdo->lastInsertId();

            // Find matching donors
            $stmt_donors = $pdo->prepare("SELECT user_id FROM donation_profiles WHERE blood_group = ? AND is_available = 1 AND medical_eligible = 1 AND user_id != ?");
            $stmt_donors->execute([$blood_group, $_SESSION['user_id']]);
            $donors = $stmt_donors->fetchAll();

            // Create request responses for matching donors
            $stmt_resp = $pdo->prepare("INSERT INTO request_responses (request_id, donor_id, status) VALUES (?, ?, 'Pending')");
            foreach ($donors as $donor) {
                $stmt_resp->execute([$request_id, $donor['user_id']]);
            }

            $success = "Request submitted successfully! " . count($donors) . " potential donors notified.";
        }
        else {
            $error = "Failed to submit request.";
        }
    }
}
?>

<div class="form-container">
    <h2 class="text-center mb-2">Request Blood</h2>
    <?php if ($success): ?>
        <p class="text-center" style="color: green;"><?php echo $success; ?></p>
        <div class="text-center mt-2">
            <a href="my_requests.php" class="btn btn-primary">View Request</a>
            <a href="dashboard.php" class="btn btn-secondary">Back</a>
        </div>
    <?php
else: ?>
        <?php if ($error): ?>
            <p class="text-danger text-center"><?php echo $error; ?></p>
        <?php
    endif; ?>
        
        <form action="" method="POST">
            <div class="form-group">
                <label>Blood Group Required</label>
                <select name="blood_group" required>
                    <option value="">Select Group</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Units Required</label>
                <input type="number" name="units" min="1" max="10" required>
            </div>
            
            <div class="form-group">
                <label>Urgency</label>
                <select name="urgency" required>
                    <option value="Normal">Normal</option>
                    <option value="Urgent">Urgent</option>
                    <option value="Emergency">Emergency</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Hospital Name</label>
                <input type="text" name="hospital" required>
            </div>
            
            <div class="form-group">
                <label>Location / Address</label>
                <textarea name="location" rows="3" required></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Request</button>
        </form>
    <?php
endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
