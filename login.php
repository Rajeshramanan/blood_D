<?php
require_once 'config/db.php';
require_once 'config/base.php';
session_start();

$error = '';

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
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    }
    else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];

            if ($user['role'] == 'admin') {
                header("Location: " . $base_url . "/admin/admin_dashboard.php");
            }
            else {
                header("Location: " . $base_url . "/user/dashboard.php");
            }
            exit;
        }
        else {
            $error = "Invalid email or password.";
        }
    }
}

include 'includes/header.php';
?>

<div class="form-container">
    <h2 class="text-center mb-2">Login</h2>
    <?php if ($error): ?>
        <p class="text-danger text-center"><?php echo $error; ?></p>
    <?php
endif; ?>
    
    <form action="" method="POST">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
    </form>
    <p class="text-center mt-2">Don't have an account? <a href="register.php">Register here</a></p>
</div>

<?php include 'includes/footer.php'; ?>
