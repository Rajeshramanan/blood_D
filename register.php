<?php
require_once 'config/db.php';
require_once 'config/base.php';
session_start();
$error = '';
$success = '';
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: " . $base_url . "/admin/admin_dashboard.php");
    } else {
        header("Location: " . $base_url . "/user/dashboard.php");
    }
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $role = isset($_POST['role']) ? trim($_POST['role']) : 'user';
    $latitude = isset($_POST['latitude']) && $_POST['latitude'] !== '' ? $_POST['latitude'] : null;
    $longitude = isset($_POST['longitude']) && $_POST['longitude'] !== '' ? $_POST['longitude'] : null;
    $valid_roles = ['user', 'hospital', 'blood_bank'];
    if (!in_array($role, $valid_roles)) {
        $role = 'user';
    }
    if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->rowCount() > 0) {
                $error = "Email already registered.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, role, latitude, longitude) VALUES (:full_name, :email, :phone, :password, :role, :latitude, :longitude)");
                if (
                    $stmt->execute([
                        'full_name' => $full_name,
                        'email' => $email,
                        'phone' => $phone,
                        'password' => $hashed_password,
                        'role' => $role,
                        'latitude' => $latitude,
                        'longitude' => $longitude
                    ])
                ) {
                    $success = "Registration successful! You can now <a href='login.php'>login</a>.";
                    if ($role === 'hospital' || $role === 'blood_bank') {
                        $success .= " Note: Your account requires Admin verification before full access.";
                    }
                } else {
                    $error = "Something went wrong. Please try again.";
                }
            }
        } catch (PDOException $e) {
            $error = "Database connection error. Please configure your cloud database environment variables. Details: " . $e->getMessage();
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
        <div class="form-group mt-2">
            <label>Register As:</label>
            <div style="display: flex; align-items: center; gap: 15px; margin-top: 10px;">
                <div style="display: flex; align-items: center;">
                    <input type="radio" id="role_user" name="role" value="user" checked
                        style="width: auto; margin-right: 5px;">
                    <label for="role_user" style="margin-bottom: 0;">Donor/User</label>
                </div>
                <div style="display: flex; align-items: center;">
                    <input type="radio" id="role_hospital" name="role" value="hospital"
                        style="width: auto; margin-right: 5px;">
                    <label for="role_hospital" style="margin-bottom: 0;">Hospital</label>
                </div>
                <div style="display: flex; align-items: center;">
                    <input type="radio" id="role_bank" name="role" value="blood_bank"
                        style="width: auto; margin-right: 5px;">
                    <label for="role_bank" style="margin-bottom: 0;">Blood Bank</label>
                </div>
            </div>
        </div>
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">
        <button type="submit" class="btn btn-primary mt-2" style="width: 100%;">Register</button>
    </form>
    <p class="text-center mt-2">Already have an account? <a href="login.php">Login here</a></p>
</div>
<script>
    // Get geolocation immediately on load if available
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(functi on (position) {
            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;
        }, funct ion (error) {
            console.log("Geolocation error: " + error.message);
        });
    }
</script>
<?php include 'includes/footer.php'; ?>