<?php

require_once '../config/db.php';
include '../includes/header.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$message = '';
$user_id = $_SESSION['user_id'];

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

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
    }
    else {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
        if ($stmt->execute([$full_name, $phone, $user_id])) {
            $message = "Profile updated successfully!";
            $_SESSION['full_name'] = $full_name;
        }
    }
    // Refresh user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}
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
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">Update Profile</button>
    </form>
    
    <div class="text-center mt-2">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
