<?php
header("Content-Type: application/json");
require 'db.php';
require_once 'utils.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    
    if ($user_id > 0) {
        $stmt = $conn->prepare("SELECT * FROM agrishop_notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(sanitize_output($notifs));
    } else {
        echo json_encode([]);
    }

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'] ?? 0;
    // Mark specific as read OR all as read for user
    $user_id = $data['user_id'] ?? 0;

    if ($id) {
        $stmt = $conn->prepare("UPDATE agrishop_notifications SET is_read = 1 WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($user_id) {
        $stmt = $conn->prepare("UPDATE agrishop_notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }
    echo json_encode(['success' => true]);
}
?>

