<?php

require_once '../config/db.php';
include '../includes/header.php';
require_once '../includes/mail_helper.php';


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

            // MATCHING ALGORITHM: Find compatible donors
            $compatibility = [
                'A+' => ['A+', 'A-', 'O+', 'O-'],
                'A-' => ['A-', 'O-'],
                'B+' => ['B+', 'B-', 'O+', 'O-'],
                'B-' => ['B-', 'O-'],
                'AB+' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
                'AB-' => ['A-', 'B-', 'AB-', 'O-'],
                'O+' => ['O+', 'O-'],
                'O-' => ['O-']
            ];

            $compatible_groups = $compatibility[$blood_group] ?? [$blood_group];
            $placeholders = implode(',', array_fill(0, count($compatible_groups), '?'));

            // Fetch donors with their emails
            $stmt_donors = $pdo->prepare("SELECT dp.user_id, u.full_name, u.email 
                                          FROM donation_profiles dp 
                                          JOIN users u ON dp.user_id = u.id 
                                          WHERE dp.blood_group IN ($placeholders) 
                                          AND dp.is_available = 1 
                                          AND dp.medical_eligible = 1 
                                          AND dp.user_id != ?");

            $params = array_merge($compatible_groups, [$_SESSION['user_id']]);
            $stmt_donors->execute($params);
            $donors = $stmt_donors->fetchAll();

            // Construct full URL for email link
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $domain = $_SERVER['HTTP_HOST'];
            $full_url = $protocol . "://" . $domain . $base_url;

            // Create request responses and send emails
            $stmt_resp = $pdo->prepare("INSERT INTO request_responses (request_id, donor_id, status) VALUES (?, ?, 'Pending')");

            foreach ($donors as $donor) {
                // 1. Create DB record
                $stmt_resp->execute([$request_id, $donor['user_id']]);

                // 2. Send Email Notification
                $subject = "URGENT: Blood Request for " . $blood_group;
                $message_body = "
                    <p>Hello <strong>" . htmlspecialchars($donor['full_name']) . "</strong>,</p>
                    <p>There is an urgent request for blood that matches your blood group.</p>
                    <p>
                        <strong>Blood Group Needed:</strong> $blood_group<br>
                        <strong>Hospital:</strong> " . htmlspecialchars($hospital) . "<br>
                        <strong>Location:</strong> " . htmlspecialchars($location) . "<br>
                        <strong>Urgency:</strong> <span class='highlight'>$urgency</span>
                    </p>
                    <p>Please log in to your account to accept or decline this request.</p>
                    <a href='$full_url/login.php' class='btn'>Login Now</a>
                ";

                send_notification_email($donor['email'], $subject, $message_body);
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
