<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/base.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "/login.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id']) && isset($_POST['action'])) {
    $request_id = (int) $_POST['request_id'];
    $action = $_POST['action'];
    $donor_id = $_SESSION['user_id'];
    if (in_array($action, ['Accepted', 'Rejected'])) {
        try {
            $stmt = $pdo->prepare("UPDATE request_responses SET status = ?, responded_at = NOW() WHERE request_id = ? AND donor_id = ?");
            $stmt->execute([$action, $request_id, $donor_id]);
            if ($action === 'Accepted') {
                $stmtCheck = $pdo->prepare("SELECT units_required FROM blood_requests WHERE id = ?");
                $stmtCheck->execute([$request_id]);
                $required_units = $stmtCheck->fetchColumn();
                $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM request_responses WHERE request_id = ? AND status = 'Accepted'");
                $stmtCount->execute([$request_id]);
                $accepted_count = $stmtCount->fetchColumn();
                if ($accepted_count >= $required_units) {
                    $stmtComplete = $pdo->prepare("UPDATE blood_requests SET status = 'Completed' WHERE id = ?");
                    $stmtComplete->execute([$request_id]);
                }
            }
        } catch (PDOException $e) {
        }
    }
}
header("Location: my_donations.php");
exit;
?>