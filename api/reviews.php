<?php
header("Content-Type: application/json");
require 'db.php';
require_once 'utils.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
    
    if ($product_id > 0) {
        $stmt = $conn->prepare("
            SELECT r.*, u.first_name, u.last_name 
            FROM agrishop_reviews r 
            JOIN agrishop_users u ON r.user_id = u.id 
            WHERE r.product_id = ? 
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$product_id]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(sanitize_output($reviews));
    } else {
        echo json_encode([]);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
    $product_id = isset($data['product_id']) ? intval($data['product_id']) : 0;
    $rating = isset($data['rating']) ? intval($data['rating']) : 5;
    $comment = isset($data['comment']) ? trim($data['comment']) : '';

    if ($user_id > 0 && $product_id > 0 && !empty($comment)) {
        try {
            // Check if user is the product owner (sellers cannot review their own products)
            $ownerCheck = $conn->prepare("SELECT user_id FROM products WHERE id = ?");
            $ownerCheck->execute([$product_id]);
            $product = $ownerCheck->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                http_response_code(404);
                echo json_encode(['error' => 'Product not found']);
                exit;
            }
            
            if ($product['user_id'] == $user_id) {
                http_response_code(403);
                echo json_encode(['error' => 'You cannot review your own product']);
                exit;
            }
            
            $stmt = $conn->prepare("INSERT INTO agrishop_reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $product_id, $rating, $comment]);
            echo json_encode(['success' => true, 'message' => 'Review posted via API']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
    }
}
?>


