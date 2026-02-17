<?php
require_once 'config/db.php';
require_once 'config/base.php';
session_start();

$error = '';
$success = '';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: " . $base_url . "/admin/admin_dashboard.php");
    }
    else {
        header("Location: " . $base_url . "/user/dashboard.php");
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);

    if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
        $error = "Please fill in all fields.";
    }
    else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ($stmt->rowCount() > 0) {
            $error = "Email already registered.";
        }
        else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (:full_name, :email, :phone, :password, 'user')");

            if ($stmt->execute(['full_name' => $full_name, 'email' => $email, 'phone' => $phone, 'password' => $hashed_password])) {
                $success = "Registration successful! You can now <a href='login.php'>login</a>.";
            }
            else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="form-container">
    <h2 class="text-center mb-2">Register</h2>
    <?php if ($error): ?>
        <p class="text-danger text-center"><?php echo $error; ?></p>
    <?php
endif; ?>
    <?php if ($success): ?>
        <p class="text-center" style="color: green;"><?php echo $success; ?></p>
    <?php
endif; ?>
    
    <form action="" method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
    </form>
    <p class="text-center mt-2">Already have an account? <a href="login.php">Login here</a></p>
</div>

<?php include 'includes/footer.php'; ?>
