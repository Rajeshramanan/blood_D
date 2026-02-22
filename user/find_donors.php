<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/base.php';
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'hospital' && $_SESSION['role'] !== 'admin')) {
    header("Location: " . $base_url . "/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];

$donors = [];
$search_performed = false;

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['search'])) {
    $search_performed = true;
    $bg = $_GET['blood_group'] ?? '';

    // Get current hospital's location
    $stmt = $pdo->prepare("SELECT latitude, longitude FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $hospital = $stmt->fetch();

    $lat = $hospital['latitude'] ?: '0';
    $lng = $hospital['longitude'] ?: '0';

    $query = "SELECT u.id, u.full_name, u.phone, u.latitude, u.longitude, d.blood_group, d.last_donation_date, 
              (6371 * acos(cos(radians(?)) * cos(radians(u.latitude)) * cos(radians(u.longitude) - radians(?)) + sin(radians(?)) * sin(radians(u.latitude)))) AS distance_km 
              FROM users u 
              JOIN donation_profiles d ON u.id = d.user_id 
              WHERE u.role = 'user' AND d.is_available = TRUE AND d.medical_eligible = TRUE ";

    $params = [$lat, $lng, $lat];

    if ($bg !== '') {
        $query .= "AND d.blood_group = ? ";
        $params[] = $bg;
    }

    $query .= "HAVING distance_km < 50 ORDER BY distance_km ASC LIMIT 50";

    // For local tests where coordinates might be missing, we just return them sorted purely or conditionally
    if ($lat == '0' && $lng == '0') {
        $query = "SELECT u.id, u.full_name, u.phone, u.latitude, u.longitude, d.blood_group, d.last_donation_date, 0 AS distance_km 
                  FROM users u 
                  JOIN donation_profiles d ON u.id = d.user_id 
                  WHERE u.role = 'user' AND d.is_available = TRUE AND d.medical_eligible = TRUE ";
        $params = [];
        if ($bg !== '') {
            $query .= "AND d.blood_group = ? ";
            $params[] = $bg;
        }
        $query .= "ORDER BY d.last_donation_date ASC LIMIT 50";
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $donors = $stmt->fetchAll();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Find Nearby Donors</h2>
        <a href="<?php echo $_SESSION['role'] === 'hospital' ? 'hospital_dashboard.php' : '../admin/admin_dashboard.php'; ?>"
            class="btn btn-secondary">Back</a>
    </div>

    <form action="" method="GET" class="form-container" style="max-width: 600px;">
        <div class="form-group">
            <label>Blood Group</label>
            <select name="blood_group" class="form-control" style="padding: 10px; width: 100%;" required>
                <option value="">Select Blood Group</option>
                <?php foreach ($blood_groups as $group): ?>
                    <option value="<?php echo $group; ?>" <?php echo (isset($_GET['blood_group']) && $_GET['blood_group'] == $group) ? 'selected' : ''; ?>>
                        <?php echo $group; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" name="search" class="btn btn-primary mt-2" style="width: 100%;">Search Donors (50km
            radius)</button>
    </form>

    <?php if ($search_performed): ?>
        <div class="table-container mt-2">
            <h3>Search Results</h3>
            <?php if (count($donors) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Blood Group</th>
                            <th>Phone</th>
                            <th>Distance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donors as $donor): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($donor['full_name']); ?>
                                </td>
                                <td><strong style="color: #dc3545;">
                                        <?php echo htmlspecialchars($donor['blood_group']); ?>
                                    </strong></td>
                                <td>
                                    <?php echo htmlspecialchars($donor['phone']); ?>
                                </td>
                                <td>
                                    <?php echo number_format($donor['distance_km'], 2); ?> km
                                </td>
                                <td>
                                    <!-- Link to simulated live tracking -->
                                    <a href="live_track.php?donor_id=<?php echo $donor['id']; ?>" class="btn btn-primary"
                                        style="padding: 5px 10px;">Live Track</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center mt-2">No eligible donors found for this blood group in your area.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>