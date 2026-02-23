<?php
require_once __DIR__ . '/config/db.php';

try {
    $check = $pdo->query("SHOW COLUMNS FROM blood_requests LIKE 'state'");
    if ($check->rowCount() > 0) {
        echo "Column 'state' already exists. Nothing to do.\n";
    } else {
        $pdo->exec("ALTER TABLE blood_requests ADD COLUMN state VARCHAR(100) NOT NULL DEFAULT '' AFTER location;");
        echo "Success! Column 'state' added to blood_requests table.\n";
    }
} catch (\PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>