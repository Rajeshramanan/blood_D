<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/base.php';
require_once __DIR__ . '/../includes/mail_helper.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "/login.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $blood_group = $_POST['blood_group'];
    $units = (int) $_POST['units'];
    $urgency = $_POST['urgency'];
    $hospital = trim($_POST['hospital']);
    $location = trim($_POST['location']);
    $contact_number = trim($_POST['contact_number']);

    if ($units < 1) {
        $error = "Units must be at least 1.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO blood_requests (requester_id, blood_group, units_required, urgency, hospital_name, location, contact_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");

        if ($stmt->execute([$_SESSION['user_id'], $blood_group, $units, $urgency, $hospital, $location, $contact_number])) {
            $request_id = $pdo->lastInsertId();

            // MATCHING ALGORITHM OVERRIDE: Notify ALL available donors globally
            // Fetch all donors with their emails regardless of blood group
            $stmt_donors = $pdo->prepare("SELECT dp.user_id, u.full_name, u.email 
                                          FROM donation_profiles dp 
                                          JOIN users u ON dp.user_id = u.id 
                                          WHERE dp.is_available = 1 
                                          AND dp.medical_eligible = 1 
                                          AND dp.user_id != ?");

            $stmt_donors->execute([$_SESSION['user_id']]);
            $donors = $stmt_donors->fetchAll();

            // Construct full URL for email link
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $domain = $_SERVER['HTTP_HOST'];
            $full_url = $protocol . "://" . $domain . $base_url;

            // Create request responses, send emails, and create in-app notifications
            $stmt_resp = $pdo->prepare("INSERT INTO request_responses (request_id, donor_id, status) VALUES (?, ?, 'Pending')");
            $stmt_notif = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'urgent')");

            foreach ($donors as $donor) {
                // 1. Create DB record for the blood request response
                $stmt_resp->execute([$request_id, $donor['user_id']]);

                // 2. Create In-App Notification Record
                $notif_msg = "URGENT: $hospital needs $units units of $blood_group blood. Please check your Pending Requests.";
                $stmt_notif->execute([$donor['user_id'], $notif_msg]);

                // 3. Send Email Notification
                $subject = "URGENT: Blood Request for " . $blood_group;
                $message_body = "
                    <p>Hello <strong>" . htmlspecialchars($donor['full_name']) . "</strong>,</p>
                    <p>There is an urgent request for blood in your area.</p>
                    <p>
                        <strong>Blood Group Needed:</strong> $blood_group<br>
                        <strong>Hospital:</strong> " . htmlspecialchars($hospital) . "<br>
                        <strong>Location/Address:</strong> " . htmlspecialchars($location) . "<br>
                        <strong>Contact Number:</strong> " . htmlspecialchars($contact_number) . "<br>
                        <strong>Urgency:</strong> <span class='highlight'>$urgency</span>
                    </p>
                    <p>Please log in to your account to accept or decline this request.</p>
                    <a href='$full_url/login.php' class='btn'>Login Now</a>
                ";

                send_notification_email($donor['email'], $subject, $message_body);
            }

            $success = "Request submitted successfully! " . count($donors) . " potential donors notified.";
        } else {
            $error = "Failed to submit request.";
        }
    }
}

include __DIR__ . '/../includes/header.php';
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

            <div class="form-group">
                <label>Contact/Phone Number</label>
                <input type="tel" name="contact_number" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Request</button>
        </form>
        <?php
    endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>