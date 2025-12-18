<?php
require_once 'db.php';
require_once 'utils.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        $exclude = isset($_GET['exclude']) ? intval($_GET['exclude']) : 0;
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        
        // Pagination Defaults
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
        $offset = ($page - 1) * $limit;

        if ($id > 0) {
            // Fetch Single Product
            $sql = "SELECT p.*, u.first_name as seller, u.bio as seller_bio, u.profile_picture as seller_image, u.address as seller_address 
                    FROM agrishop_products p 
                    JOIN agrishop_users u ON p.user_id = u.id 
                    WHERE p.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                // Formatting
                $product['image'] = $product['image_path'] ?? $product['image_type'];
                $product['price'] = (float)$product['price'];
                $product['certified'] = (bool)$product['is_certified'];
                $product['organic'] = (bool)$product['is_organic'];
                $product['seller'] = $product['seller'] ?? 'Farmer';
                $product['seller_bio'] = $product['seller_bio'] ?? 'No bio available.';
                $product['seller_image'] = $product['seller_image'] ?? null;
                $product['seller_address'] = $product['seller_address'] ?? null;
                
                // Add ownership flag for single product too
                $product['isOwner'] = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $product['user_id']);
                
                echo json_encode(sanitize_output($product));
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Product not found']);
            }
        } else {
            // Unified List Fetch (All, Category, User, Related)
            $where = [];
            $params = [];

            if (!empty($category) && $category !== 'All') {
                $where[] = "p.category = ?";
                $params[] = $category;
            }
            if ($exclude > 0) {
                $where[] = "p.id != ?";
                $params[] = $exclude;
            }
            if ($user_id > 0) {
                $where[] = "p.user_id = ?";
                $params[] = $user_id;
            }

            $whereSql = "";
            if (!empty($where)) {
                $whereSql = "WHERE " . implode(" AND ", $where);
            }

            // Total Count
            $countSql = "SELECT COUNT(*) FROM agrishop_products p $whereSql";
            $countStmt = $conn->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();
            $totalPages = ceil($total / $limit);

            // Fetch Items
            $sql = "SELECT p.*, u.first_name as seller 
                    FROM agrishop_products p 
                    JOIN agrishop_users u ON p.user_id = u.id 
                    $whereSql
                    ORDER BY p.created_at DESC 
                    LIMIT $limit OFFSET $offset";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Transform
            $items = array_map(function($p) {
                return [
                    'id' => $p['id'],
                    'name' => $p['name'],
                    'nameRw' => $p['name_rw'],
                    'price' => (float)$p['price'],
                    'unit' => $p['unit'],
                    'category' => $p['category'],
                    'location' => $p['location'],
                    'image' => $p['image_path'] ?? $p['image_type'],
                    'certified' => (bool)$p['is_certified'],
                    'organic' => (bool)$p['is_organic'],
                    'seller' => $p['seller'] ?? 'Farmer',
                    'user_id' => $p['user_id'], // Critical for order creation
                    'isOwner' => (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $p['user_id'])
                ];
            }, $products);
    
            echo json_encode(sanitize_output([
                'items' => $items,
                'meta' => [
                    'current_page' => $page,
                    'total_pages' => (int)$totalPages,
                    'total_items' => (int)$total,
                    'filter_category' => $category
                ]
            ]));
        }
        break;

    case 'POST':
        // Handle Multipart Form Data for both Create and Update
        
        // --- AUTH CHECK ---
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit;
        }
        $user_id = $_SESSION['user_id'];

        // Check if this is an Update (we pass update_id in form data)
        if (isset($_POST['update_id'])) {
            $id = $_POST['update_id'];

            // Security: Ensure User owns this product
            $chk = $conn->prepare("SELECT user_id FROM agrishop_products WHERE id = ?");
            $chk->execute([$id]);
            $p = $chk->fetch();
            if (!$p || $p['user_id'] != $user_id) {
                http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit;
            }

            $name = $_POST['name'] ?? '';
            $price = $_POST['price'] ?? '';
            $location = $_POST['location'] ?? 'Kigali';
            
            // Validation
            if (empty($name) || empty($price)) {
                http_response_code(400); echo json_encode(['error' => 'Name and Price required']); exit;
            }

            // Image Upload
            $imageSQL = "";
            $params = [$name, $price, $location, $id];

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/products/';
                if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $file = $_FILES['image'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $filename = 'prod_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                    if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                        $imagePath = 'uploads/products/' . $filename;
                        $imageSQL = ", image_path = ?";
                        // Insert image path into params before ID
                        // Params currently: name, price, location, id
                        // We need: name, price, location, image, id
                        array_splice($params, 3, 0, $imagePath);
                    }
                }
            }

            $sql = "UPDATE agrishop_products SET name = ?, price = ?, location = ? $imageSQL WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute($params)) {
                echo json_encode(['success' => true, 'message' => 'Product updated']);
            } else {
                http_response_code(500); echo json_encode(['error' => 'Update failed']);
            }
            exit; // Stop here for update
        }

        // --- CREATE NEW PRODUCT LOGIC (Existing) ---
        $name = $_POST['name'] ?? '';
        $price = $_POST['price'] ?? '';
        // user_id is already set from session at top of case

        if (empty($name) || empty($price)) {
            http_response_code(400);
            echo json_encode(['error' => 'Name and Price are required']);
            exit();
        }

        // Image Upload Logic
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/products/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $file = $_FILES['image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($ext, $allowed)) {
                $filename = 'prod_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $imagePath = 'uploads/products/' . $filename;
                }
            }
        }

        $sql = "INSERT INTO agrishop_products (user_id, name, name_rw, price, unit, category, location, image_type, image_path, is_certified, is_organic) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $user_id,
            $name,
            $_POST['nameRw'] ?? $name,
            $price,
            $_POST['unit'] ?? 'Kg',
            $_POST['category'] ?? 'General',
            $_POST['location'] ?? 'Kigali',
            'produce', // Keep legacy image_type for now or static
            $imagePath,
            isset($_POST['certified']) ? 1 : 0, // In multipart, booleans are strings "true"/"1" or not set
            isset($_POST['organic']) ? 1 : 0
        ]);

        echo json_encode(['message' => 'Product created', 'id' => $conn->lastInsertId()]);
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID required for update']);
            exit();
        }

        $fields = [];
        $params = [];

        if (isset($data->name)) {
            if (!preg_match("/^[a-zA-Z0-9\s]+$/", $data->name)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid product name format']);
                exit();
            }
            $fields[] = "name = ?";
            $params[] = $data->name;
        }

        if (isset($data->price)) {
            $fields[] = "price = ?";
            $params[] = $data->price;
        }

        // Add other fields as needed...

        if (empty($fields)) {
             echo json_encode(['message' => 'No changes provided']);
             exit();
        }

        $params[] = $data->id;
        $sql = "UPDATE agrishop_products SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['message' => 'Product updated']);
        break;

    case 'DELETE':
        // Clean code: separate logic
        if (!isset($_GET['id'])) {
            http_response_code(400); echo json_encode(['error' => 'ID required']); exit;
        }
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit;
        }
        
        $id = $_GET['id'];
        $user_id = $_SESSION['user_id'];

        // Verify Ownership
        $chk = $conn->prepare("SELECT user_id FROM agrishop_products WHERE id = ?");
        $chk->execute([$id]);
        $p = $chk->fetch();

        if (!$p || $p['user_id'] != $user_id) {
             http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit;
        }

        $stmt = $conn->prepare("DELETE FROM agrishop_products WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['message' => 'Product deleted']);
        break;
}
?>

