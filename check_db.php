<?php
require_once __DIR__ . '/config/db.php';
$stmt = $pdo->query("SHOW COLUMNS FROM blood_requests");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "COLUMNS IN BLOOD_REQUESTS TABLE:\n";
foreach ($columns as $col) {
    echo "- " . $col['Field'] . "\n";
}
?>