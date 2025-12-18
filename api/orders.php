<?php
header("Content-Type: application/json");
require 'db.php';
require_once 'utils.php'; // For sanitization (if we output anything)

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $buyer_id = $data['user_id'] ?? 0;
    $cart = $data['cart'] ?? [];
    $phone = $data['phone'] ?? '';
    $address = $data['address'] ?? '';
    $instructions = $data['instructions'] ?? '';
    
    // New Fields
    $delivery_method = $data['delivery_method'] ?? 'door'; // door, pickup
    $payment_method = $data['payment_method'] ?? 'cod';    // cod, momo
    $pickup_station_id = $data['pickup_station_id'] ?? 0;

    if ($buyer_id == 0 || empty($cart)) {
        http_response_code(400); echo json_encode(['error' => 'Invalid order data']); exit;
    }

    try {
        $conn->beginTransaction();

        // 1. Group items by Seller
        $ordersBySeller = [];
        $delivery_fee_per_order = ($delivery_method === 'door') ? 1500 : 500; // Fee per shipment/seller

        foreach ($cart as $item) {
            $seller_id = $item['user_id'] ?? 0; 
            if ($seller_id == 0) continue; 
            
            if (!isset($ordersBySeller[$seller_id])) {
                $ordersBySeller[$seller_id] = [
                    'total' => 0, 
                    'items' => []
                ];
                // Add delivery fee for this seller's shipment
                // (Assuming separate delivery per seller)
                $ordersBySeller[$seller_id]['total'] += $delivery_fee_per_order;
            }
            $ordersBySeller[$seller_id]['items'][] = $item;
            $ordersBySeller[$seller_id]['total'] += ($item['price'] * ($item['qty'] ?? 1));
        }

        // 2. Create Order for each Seller
        $created_orders = [];
        
        foreach ($ordersBySeller as $seller_id => $orderData) {
            // Insert Order
            $stmt = $conn->prepare("INSERT INTO agrishop_orders (buyer_id, seller_id, total_amount, delivery_phone, delivery_address, delivery_instructions, status, delivery_method, payment_method, pickup_station_id) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)");
            $stmt->execute([$buyer_id, $seller_id, $orderData['total'], $phone, $address, $instructions, $delivery_method, $payment_method, $pickup_station_id]);
            $order_id = $conn->lastInsertId();
            $created_orders[] = $order_id;

            // Insert Items
            $stmtItem = $conn->prepare("INSERT INTO agrishop_order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($orderData['items'] as $item) {
                $stmtItem->execute([$order_id, $item['id'], ($item['qty'] ?? 1), $item['price']]);
            }

            // Initial History Log (COMMENTED OUT - table doesn't exist)
            // $stmtHist = $conn->prepare("INSERT INTO order_history (order_id, status, description) VALUES (?, ?, ?)");
            // $stmtHist->execute([$order_id, 'pending', 'Order placed successfully by customer.']);

            // Notify Seller (COMMENTED OUT - notifications table doesn't exist)
            // $msg = "New Order #$order_id received! Value: " . number_format($orderData['total']) . " RWF";
            // $stmtNotif = $conn->prepare("INSERT INTO notifications (user_id, message, type, related_id) VALUES (?, ?, 'info', ?)");
            // $stmtNotif->execute([$seller_id, $msg, $order_id]);
        }

        $conn->commit();
        echo json_encode(['success' => true, 'order_ids' => $created_orders]);

    } catch (Exception $e) {
        $conn->rollBack();
        http_response_code(500); echo json_encode(['error' => $e->getMessage()]);
    }

} elseif ($method === 'GET') {
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    $type = isset($_GET['type']) ? $_GET['type'] : 'purchases'; 
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

    if ($user_id == 0 && $order_id == 0) { echo json_encode([]); exit; }

    if ($order_id > 0) {
        // Single Order Detail + History
        $stmt = $conn->prepare("SELECT o.*, u.first_name, u.last_name, u.email, u.phone as seller_phone 
                                FROM agrishop_orders o 
                                LEFT JOIN agrishop_users u ON o.seller_id = u.id 
                                WHERE o.id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            // Get Items
            $stmtItems = $conn->prepare("SELECT oi.*, p.name, p.image_path, p.unit 
                                         FROM agrishop_order_items oi 
                                         JOIN agrishop_products p ON oi.product_id = p.id 
                                         WHERE oi.order_id = ?");
            $stmtItems->execute([$order_id]);
            $order['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            // Get History (COMMENTED OUT - table doesn't exist)
            // $stmtHist = $conn->prepare("SELECT * FROM order_history WHERE order_id = ? ORDER BY created_at ASC");
            // $stmtHist->execute([$order_id]);
            $order['history'] = []; // Empty array instead
            
            echo json_encode(sanitize_output($order));
        } else {
            http_response_code(404); echo json_encode(['error' => 'Not found']);
        }
        exit;
    }

    // List Views
    $sql = "";
    $params = [];

    if ($type === 'sales') {
        $sql = "SELECT o.*, u.first_name, u.last_name, u.email 
                FROM agrishop_orders o 
                LEFT JOIN agrishop_users u ON o.buyer_id = u.id 
                WHERE o.seller_id = ? 
                ORDER BY o.created_at DESC";
        $params = [$user_id];
    } else {
        $sql = "SELECT o.*, s.first_name as seller_first, s.last_name as seller_last 
                FROM agrishop_orders o 
                LEFT JOIN agrishop_users s ON o.seller_id = s.id
                WHERE o.buyer_id = ? 
                ORDER BY o.created_at DESC";
        $params = [$user_id];
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(sanitize_output($orders));

} elseif ($method === 'PUT') {
    // Update Status
    $data = json_decode(file_get_contents("php://input"), true);
    $order_id = $data['order_id'] ?? 0;
    $status = $data['status'] ?? '';
    $description = $data['description'] ?? 'Status updated by seller.';

    
    // Valid Order Statuses (updated to match database ENUM)
    $valid_statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'receipt_confirmed', 'completed', 'cancelled'];
    
    // Define valid status transitions (business logic)
    $valid_transitions = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['shipped', 'cancelled'],
        'shipped' => ['delivered', 'cancelled'],
        'delivered' => ['receipt_confirmed', 'completed', 'cancelled'],
        'receipt_confirmed' => ['completed'],
        'completed' => [], // Terminal state
        'cancelled' => [] // Terminal state
    ];


    if ($order_id && in_array($status, $valid_statuses)) {
        // Get Buyer, Seller ID and Current Status
        $stmt = $conn->prepare("SELECT buyer_id, seller_id, status FROM agrishop_orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();

        if ($order) {
            $current_status = $order['status'];
            
            // Dedup check: If status is same, just return success (idempotent)
            if ($current_status === $status) {
                 echo json_encode(['success' => true, 'message' => 'Status already up to date']);
                 exit;
            }
            
            // Validate status transition
            if (!in_array($status, $valid_transitions[$current_status] ?? [])) {
                http_response_code(400);
                echo json_encode([
                    'error' => 'Invalid status transition',
                    'detail' => "Cannot change from '$current_status' to '$status'"
                ]);
                exit;
            }


            $conn->beginTransaction();
            try {
                // Update Order Table
                $upd = $conn->prepare("UPDATE agrishop_orders SET status = ? WHERE id = ?");
                $upd->execute([$status, $order_id]);

                // Create History Record (COMMENTED OUT - table doesn't exist)
                // $hist = $conn->prepare("INSERT INTO order_history (order_id, status, description) VALUES (?, ?, ?)");
                // $hist->execute([$order_id, $status, $description]);

                // Determine who to notify
                // Attempt to identify current user from Session, defaulting to notifying Buyer if unknown
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $current_user = $_SESSION['user_id'] ?? 0;
                
                $target_id = $order['buyer_id']; // Default to Buyer
                if ($current_user == $order['buyer_id']) {
                    $target_id = $order['seller_id']; // If Buyer acts, notify Seller
                }

                // Notify Target (COMMENTED OUT - notifications table doesn't exist)
                // $msg = "Order #$order_id status updated to: " . strtoupper($status);
                // $notifType = ($status == 'delivered' || $status == 'completed') ? 'success' : 'info';
                // 
                // $notif = $conn->prepare("INSERT INTO notifications (user_id, message, type, related_id) VALUES (?, ?, ?, ?)");
                // $notif->execute([$target_id, $msg, $notifType, $order_id]);

                $conn->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $conn->rollBack();
                http_response_code(500); echo json_encode(['error' => $e->getMessage()]);
            }
        } else {
             http_response_code(404); echo json_encode(['error' => 'Order not found']);
        }
    } else {
        http_response_code(400); echo json_encode(['error' => 'Invalid status']);
    }
}




