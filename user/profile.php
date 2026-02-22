<?php

require_once '../config/db.php';
require_once '../config/base.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "/login.php");
    exit;
}

$message = '';
$user_id = $_SESSION['user_id'];

// Fetch current user data
$stmt = $pdo->prepare("SELECT u.*, d.is_available, d.medical_eligible 
                       FROM users u 
                       LEFT JOIN donation_profiles d ON u.id = d.user_id 
                       WHERE u.id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Ensure user_rewards exists
$stmt = $pdo->prepare("SELECT * FROM user_rewards WHERE user_id = ?");
$stmt->execute([$user_id]);
$rewards = $stmt->fetch();

if (!$rewards && $_SESSION['role'] === 'user') {
    $pdo->prepare("INSERT INTO user_rewards (user_id) VALUES (?)")->execute([$user_id]);
    $stmt->execute([$user_id]);
    $rewards = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, password = ? WHERE id = ?");
        if ($stmt->execute([$full_name, $phone, $hashed, $user_id])) {
            $message = "Profile updated successfully!";
            $_SESSION['full_name'] = $full_name;
        }
    } else {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
        if ($stmt->execute([$full_name, $phone, $user_id])) {
            $message = "Profile updated successfully!";
            $_SESSION['full_name'] = $full_name;
        }
    }

    // Update availability if donor
    if ($_SESSION['role'] === 'user' && isset($_POST['is_available'])) {
        $is_avail = $_POST['is_available'] == '1' ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE donation_profiles SET is_available = ? WHERE user_id = ?");
        $stmt->execute([$is_avail, $user_id]);
    }

    // Refresh user data
    $stmt = $pdo->prepare("SELECT u.*, d.is_available, d.medical_eligible FROM users u LEFT JOIN donation_profiles d ON u.id = d.user_id WHERE u.id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}

include '../includes/header.php';
?>

<div class="form-container">
    <h2 class="text-center mb-2">My Profile</h2>
    <?php if ($message): ?>
        <p class="text-center" style="color: green;"><?php echo $message; ?></p>
        <?php
    endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
        </div>

        <div class="form-group">
            <label>Email (Cannot be changed)</label>
            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
        </div>

        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
        </div>

        <div class="form-group">
            <label>New Password (Leave blank to keep current)</label>
            <input type="password" name="password" placeholder="********">
        </div>

        <?php if ($_SESSION['role'] === 'user'): ?>
            <div class="form-group mt-2">
                <label>Available to Donate?</label><br>
                <select name="is_available" class="form-control" style="width: 100%;">
                    <option value="1" <?php echo (isset($user['is_available']) && $user['is_available'] == 1) ? 'selected' : ''; ?>>Yes, I am available</option>
                    <option value="0" <?php echo (isset($user['is_available']) && $user['is_available'] == 0) ? 'selected' : ''; ?>>No, temporarily unavailable</option>
                </select>
                <small class="text-muted">Turn this off if you recently donated or are medically ineligible right
                    now.</small>
            </div>

            <div class="card mt-2 p-3 text-center" style="background-color: #f8f9fa;">
                <h4>🏆 Gamification Stats</h4>
                <p>Life Saver Badge: <strong
                        style="color: #ffc107;"><?php echo htmlspecialchars($rewards['badge'] ?? 'Starter'); ?></strong></p>
                <p>Points: <strong><?php echo $rewards['points'] ?? 0; ?></strong></p>
                <p>Donation Streak: <strong><?php echo $rewards['donation_streak'] ?? 0; ?> 🔥</strong></p>
                <div style="width: 100%; background-color: #e9ecef; border-radius: 5px; height: 10px; margin-top: 10px;">
                    <?php $progress = min(100, ($rewards['points'] ?? 0)); ?>
                    <div
                        style="width: <?php echo $progress; ?>%; background-color: #28a745; height: 100%; border-radius: 5px;">
                    </div>
                </div>
                <p style="font-size: 12px; margin-top: 5px;">Reach 100 points for the next rank!</p>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary mt-3" style="width: 100%;">Update Profile</button>
    </form>

    <div class="text-center mt-2">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>