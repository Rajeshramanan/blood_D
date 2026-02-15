<?php

require_once '../config/db.php';
include '../includes/header.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $blood_group = $_POST['blood_group'];
    $last_donation_date = $_POST['last_donation_date'];
    $medical_eligible = isset($_POST['medical_eligible']) ? 1 : 0;

    // Calculate eligibility (must be > 90 days since last donation)
    $today = date('Y-m-d');
    $next_eligible = date('Y-m-d', strtotime($last_donation_date . ' + 90 days'));

    if ($next_eligible > $today) {
        $error = "You are not eligible to donate yet. Next eligible date: " . $next_eligible;
    }
    elseif (!$medical_eligible) {
        $error = "You must be medically eligible to donate.";
    }
    else {
        $stmt = $pdo->prepare("INSERT INTO donation_profiles (user_id, blood_group, last_donation_date, is_available, medical_eligible) VALUES (?, ?, ?, 1, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $blood_group, $last_donation_date, $medical_eligible])) {
            $message = "Thank you! Your donation profile has been updated and you are now available for donation.";
        }
        else {
            $error = "Database error. Please try again.";
        }
    }
}
?>

<div class="form-container">
    <h2 class="text-center mb-2">Donate Blood</h2>
    <?php if ($message): ?>
        <p class="text-center" style="color: green;"><?php echo $message; ?></p>
        <div class="text-center mt-2">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    <?php
else: ?>
        <?php if ($error): ?>
            <p class="text-danger text-center"><?php echo $error; ?></p>
        <?php
    endif; ?>
        
        <form action="" method="POST">
            <div class="form-group">
                <label>Blood Group</label>
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
                <label>Last Donation Date (Leave empty if first time)</label>
                <input type="date" name="last_donation_date" value="2000-01-01" required>
                <small>If first time, select a past date.</small>
            </div>
            
            <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="medical_eligible" id="med_check" style="width: auto;" required>
                <label for="med_check" style="margin: 0;">I confirm I am medically fit to donate.</label>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Register as Donor</button>
        </form>
    <?php
endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
