<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Define base URL to handle relative paths correctly
$base_url = 'http://localhost/blood_app';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donation Management System</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <a href="<?php echo $base_url; ?>/index.php" class="logo">
                <i class="fas fa-heartbeat"></i> <span>BDMS</span>
            </a>
            <ul class="nav-links">
                <li><a href="<?php echo $base_url; ?>/index.php">Home</a></li>
                <li><a href="<?php echo $base_url; ?>/about.php">About</a></li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="<?php echo $base_url; ?>/admin/admin_dashboard.php">Dashboard</a></li>
                    <?php
    else: ?>
                        <li><a href="<?php echo $base_url; ?>/user/dashboard.php">Dashboard</a></li>
                    <?php
    endif; ?>
                    <li><a href="<?php echo $base_url; ?>/logout.php" class="btn-nav btn-login">Logout</a></li>
                <?php
else: ?>
                    <li><a href="<?php echo $base_url; ?>/login.php" class="btn-nav btn-login">Login</a></li>
                    <li><a href="<?php echo $base_url; ?>/register.php" class="btn-nav btn-primary">Register</a></li>
                <?php
endif; ?>
            </ul>
        </nav>
    </header>
    <div class="main-content">
