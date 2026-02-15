<?php
require_once 'config/db.php';

$new_password = 'admin123';
$hashed = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = 'admin@bloodapp.com'");
if ($stmt->execute(['password' => $hashed])) {
    echo "<h1>Admin Password Reset Successfully</h1>";
    echo "<p>New Password: <strong>$new_password</strong></p>";
    echo "<p><a href='login.php'>Login Here</a></p>";

    // Also print the hash so we can update the schema file if needed
    echo "<p>New Hash (for db_schema.sql): $hashed</p>";
}
else {
    echo "Error updating password.";
}
?>
