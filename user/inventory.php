<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/base.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'blood_bank') {
    header("Location: " . $base_url . "/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

$blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_inventory'])) {
    try {
        $pdo->beginTransaction();

        foreach ($blood_groups as $bg) {
            $units = isset($_POST['units_' . md5($bg)]) ? (int) $_POST['units_' . md5($bg)] : 0;

            // Check if exists
            $stmt = $pdo->prepare("SELECT id FROM blood_inventory WHERE bank_id = ? AND blood_group = ?");
            $stmt->execute([$user_id, $bg]);

            if ($stmt->fetch()) {
                $upd = $pdo->prepare("UPDATE blood_inventory SET units = ?, last_updated = CURRENT_TIMESTAMP WHERE bank_id = ? AND blood_group = ?");
                $upd->execute([$units, $user_id, $bg]);
            } else {
                $ins = $pdo->prepare("INSERT INTO blood_inventory (bank_id, blood_group, units) VALUES (?, ?, ?)");
                $ins->execute([$user_id, $bg, $units]);
            }
        }

        $pdo->commit();
        $success = "Inventory updated successfully.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error updating inventory.";
    }
}

// Fetch current inventory
$stmt = $pdo->prepare("SELECT blood_group, units FROM blood_inventory WHERE bank_id = ?");
$stmt->execute([$user_id]);
$inventory = [];
while ($row = $stmt->fetch()) {
    $inventory[$row['blood_group']] = $row['units'];
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h2>Manage Blood Inventory</h2>

    <?php if ($success): ?>
        <p class="text-center" style="color: green;">
            <?php echo $success; ?>
        </p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="text-center text-danger">
            <?php echo $error; ?>
        </p>
    <?php endif; ?>

    <form action="" method="POST" class="form-container" style="max-width: 600px;">
        <p>Update the number of available units for each blood group:</p>

        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr>
                    <th style="text-align: left; padding-bottom: 10px;">Blood Group</th>
                    <th style="padding-bottom: 10px;">Available Units</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blood_groups as $bg):
                    $curr_units = isset($inventory[$bg]) ? $inventory[$bg] : 0;
                    ?>
                    <tr>
                        <td style="padding: 10px 0;"><strong>
                                <?php echo $bg; ?>
                            </strong></td>
                        <td style="padding: 10px 0; text-align: center;">
                            <input type="number" name="units_<?php echo md5($bg); ?>" value="<?php echo $curr_units; ?>"
                                min="0" class="form-control" style="width: 100px; display: inline-block;">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button type="submit" name="update_inventory" class="btn btn-primary mt-2" style="width: 100%;">Save
            Inventory</button>
        <a href="blood_bank_dashboard.php" class="btn btn-secondary mt-2"
            style="width: 100%; text-align: center; display: block; box-sizing: border-box;">Back to Dashboard</a>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>