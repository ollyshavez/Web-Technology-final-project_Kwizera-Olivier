<?php
header("Content-Type: application/json");
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($method === 'GET') {
    if ($user_id > 0) {
        // Get user's wishlist
        try {
            $stmt = $conn->prepare("
                SELECT p.*, p.image_path as image, w.id as wishlist_id 
                FROM agrishop_products p 
                JOIN agrishop_wishlist w ON p.id = w.product_id 
                WHERE w.user_id = ?
            ");
            $stmt->execute([$user_id]);
            $wishlist = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Map image paths if needed (similar to products.php logic usually)
            // No mapping needed if p.* is correct
            // $wishlist = array_map(...);

            echo json_encode($wishlist);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        echo json_encode([]);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
    $product_id = isset($data['product_id']) ? intval($data['product_id']) : 0;
    $action = isset($data['action']) ? $data['action'] : 'add';

    if ($user_id > 0 && $product_id > 0) {
        try {
            if ($action === 'add') {
                $stmt = $conn->prepare("INSERT IGNORE INTO agrishop_wishlist (user_id, product_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $product_id]);
                echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
            } elseif ($action === 'remove') {
                $stmt = $conn->prepare("DELETE FROM agrishop_wishlist WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$user_id, $product_id]);
                echo json_encode(['success' => true, 'message' => 'Removed FROM agrishop_wishlist']);
            }
        } catch (PDOException $e) {
             http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid parameters']);
    }
}
?>

