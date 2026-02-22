<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/base.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "/login.php");
    exit;
}

if (!isset($_GET['donor_id'])) {
    echo "Invalid Donor ID.";
    exit;
}

$donor_id = (int) $_GET['donor_id'];
$requester_id = $_SESSION['user_id'];

// Get Requestor Location
$stmt = $pdo->prepare("SELECT latitude, longitude, full_name, role FROM users WHERE id = ?");
$stmt->execute([$requester_id]);
$requester = $stmt->fetch();

$req_lat = $requester['latitude'] ?: '0';
$req_lng = $requester['longitude'] ?: '0';

// Get Donor Location
$stmt = $pdo->prepare("SELECT u.full_name, u.phone, u.latitude, u.longitude, d.blood_group 
                       FROM users u 
                       JOIN donation_profiles d ON u.id = d.user_id 
                       WHERE u.id = ?");
$stmt->execute([$donor_id]);
$donor = $stmt->fetch();

if (!$donor) {
    echo "Donor not found or not eligible.";
    exit;
}

$don_lat = $donor['latitude'] ?: '0';
$don_lng = $donor['longitude'] ?: '0';

// Calculate initial distance 
function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371)
{
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
}

$distance = haversineGreatCircleDistance($req_lat, $req_lng, $don_lat, $don_lng);

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h2>Live Track Donor</h2>

    <div class="card" style="margin-top: 20px;">
        <h3>Tracking:
            <?php echo htmlspecialchars($donor['full_name']); ?>
        </h3>
        <p>Blood Group: <strong style="color: #dc3545;">
                <?php echo htmlspecialchars($donor['blood_group']); ?>
            </strong></p>
        <p>Contact:
            <?php echo htmlspecialchars($donor['phone']); ?>
        </p>
        <p>Current Estimated Distance: <strong id="distance-display">
                <?php echo number_format($distance, 2); ?> km
            </strong></p>

        <div id="map-simulation"
            style="width: 100%; height: 300px; background-color: #e9ecef; margin-top: 20px; border-radius: 8px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
            <div style="position: absolute; left: 10%; top: 50%; width: 20px; height: 20px; background-color: #007bff; border-radius: 50%;"
                title="You"></div>
            <div id="donor-dot"
                style="position: absolute; right: 10%; top: 50%; width: 20px; height: 20px; background-color: #dc3545; border-radius: 50%; transition: right 1s linear;"
                title="Donor"></div>
            <p style="color: #6c757d; font-weight: bold;">Simulated GPS Map View</p>
        </div>

        <button id="notify-btn" class="btn btn-primary mt-2" onclick="sendAlert()">Send Urgent Match Alert to
            Donor</button>
        <a href="<?php echo $_SESSION['role'] === 'hospital' ? 'find_donors.php' : 'dashboard.php'; ?>"
            class="btn btn-secondary mt-2">Back</a>
    </div>
</div>

<script>
    // Simulate Donor moving closer every 3 seconds for demonstration
    let currentDistance = <?php echo $distance; ?>;
    let rightPos = 10;

    setInterval(() => {
        if (currentDistance > 0.5) {
            currentDistance -= 0.5; // moves 0.5km closer
            document.getElementById('distance-display').innerText = currentDistance.toFixed(2) + ' km';

            // Move dot visually closer to center (left: 10% is requester, let's move right closer to 80%)
            rightPos += 2;
            if (rightPos > 80) rightPos = 80;
            document.getElementById('donor-dot').style.right = rightPos + '%';
        }
    }, 3000);

    function sendAlert() {
        alert("Push notification and SMS successfully sent to " + "<?php echo htmlspecialchars($donor['full_name']); ?>");
        document.getElementById('notify-btn').innerText = "Alert Sent!";
        document.getElementById('notify-btn').disabled = true;
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>