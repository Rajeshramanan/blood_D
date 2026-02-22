<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Define base URL to handle relative paths correctly
// Define base URL to handle relative paths correctly
require_once __DIR__ . '/../config/base.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donation Management System</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <script>
        // Check for saved theme in localStorage and apply it immediately to prevent FOUC
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
</head>

<body>
    <header>
        <nav class="navbar">
            <a href="<?php echo $base_url; ?>/index.php" class="logo">
                <i class="fas fa-heartbeat"></i> <span>BDMS</span>
            </a>

            <button class="hamburger" id="hamburger-menu">
                <i class="fas fa-bars"></i>
            </button>

            <ul class="nav-links" id="nav-menu">
                <li><a href="<?php echo $base_url; ?>/index.php">Home</a></li>
                <li><a href="<?php echo $base_url; ?>/about.php">About</a></li>
                <li>
                    <button id="theme-toggle" class="btn-nav" title="Toggle Dark Mode"
                        style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-color); padding: 0.5rem;">
                        <i class="fas fa-moon" id="theme-icon"></i>
                    </button>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="<?php echo $base_url; ?>/admin/admin_dashboard.php">Dashboard</a></li>
                    <?php elseif ($_SESSION['role'] === 'hospital'): ?>
                        <li><a href="<?php echo $base_url; ?>/user/hospital_dashboard.php">Dashboard</a></li>
                    <?php elseif ($_SESSION['role'] === 'blood_bank'): ?>
                        <li><a href="<?php echo $base_url; ?>/user/blood_bank_dashboard.php">Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $base_url; ?>/user/dashboard.php">Dashboard</a></li>
                    <?php endif; ?>

                    <li><a href="<?php echo $base_url; ?>/user/notifications.php" title="Notifications"><i
                                class="fas fa-bell"></i></a></li>
                    <li><a href="<?php echo $base_url; ?>/logout.php" class="btn-nav btn-login">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo $base_url; ?>/login.php" class="btn-nav btn-login">Login</a></li>
                    <li><a href="<?php echo $base_url; ?>/register.php" class="btn-nav btn-primary">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <div class="main-content">