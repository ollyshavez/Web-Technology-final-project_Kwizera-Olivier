<?php
require_once 'db.php';

header('Content-Type: application/json');


// Admin Role Check - Enforce server-side authorization
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden: Admin access required']);
    exit;
}


$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    try {
        if ($action === 'list_users') {
            $stmt = $conn->prepare("SELECT id, first_name, last_name, email, role, created_at FROM agrishop_users ORDER BY created_at DESC");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['users' => $users]);
            exit;
        }

        if ($action === 'list_products') {
        $stmt = $conn->prepare("
            SELECT p.id, p.name, p.price, p.category, p.created_at, u.first_name, u.last_name 
            FROM products p 
            JOIN agrishop_users u ON p.user_id = u.id 
            ORDER BY p.created_at DESC
        ");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['products' => $products]);
        exit;
    }

    if ($action === 'get_stats') {
            $stats = [];
            
            // Count Users
            $stats['total_users'] = $conn->query("SELECT COUNT(*) FROM agrishop_users")->fetchColumn();
            $stats['farmers'] = $conn->query("SELECT COUNT(*) FROM agrishop_users WHERE role='farmer'")->fetchColumn();
            $stats['buyers'] = $conn->query("SELECT COUNT(*) FROM agrishop_users WHERE role='buyer'")->fetchColumn();
            
            // Count Products
            $stats['total_products'] = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
            
            // Count Advisory (handle case if table missing)
            try {
                $stats['total_advisory'] = $conn->query("SELECT COUNT(*) FROM advisory")->fetchColumn();
            } catch (Exception $e) { $stats['total_advisory'] = 0; }

            echo json_encode($stats);
            exit;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    // Handle specific admin actions like deleting users or content
    $data = json_decode(file_get_contents("php://input"), true);
    $action = $data['action'] ?? '';

    if ($action === 'delete_user') {
        $id = $data['id'] ?? 0;
        // Prevent deleting self (would need session id check)
        
        // Prevent deleting self or other admins
        $check = $conn->prepare("SELECT role FROM agrishop_users WHERE id = ?");
        $check->execute([$id]);
        $targetRole = $check->fetchColumn();

        if ($targetRole === 'admin') {
            http_response_code(403); 
            echo json_encode(['error' => 'Cannot delete admin users']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM agrishop_users WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500); echo json_encode(['error' => 'Delete failed']);
        }
    }

    if ($action === 'delete_product') {
        $id = $data['id'] ?? 0;
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500); echo json_encode(['error' => 'Delete failed']);
        }
    }
}
?>


