<?php
require_once 'db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Check if it's a file upload (Multipart) or JSON
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';

    // Handle JSON Update (Info)
    if (strpos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'User ID required']);
            exit;
        }

        // Handle Profile Picture Deletion
        if (!empty($data['delete_picture'])) {
            $id = $data['id'];
            
            // Get current picture path
            $stmt = $conn->prepare("SELECT profile_picture FROM agrishop_users WHERE id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetchColumn();

            if ($current) {
                // Delete file (convert DB path to FS path)
                // DB path: uploads/profiles/filename
                // FS path: ../uploads/profiles/filename (relative to api/)
                $fsPath = '../' . $current; 
                if (file_exists($fsPath)) {
                    unlink($fsPath);
                }
            }

            // Update DB
            $stmt = $conn->prepare("UPDATE agrishop_users SET profile_picture = NULL WHERE id = ?");
            if ($stmt->execute([$id])) {
                 // Fetch updated user
                $stmt = $conn->prepare("SELECT * FROM agrishop_users WHERE id = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                unset($user['password']);
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Deletion failed']);
            }
            exit;
        }

        $fields = [];
        $params = [];

        if (!empty($data['first_name'])) { $fields[] = "first_name = ?"; $params[] = $data['first_name']; }
        if (!empty($data['last_name'])) { $fields[] = "last_name = ?"; $params[] = $data['last_name']; }
        if (!empty($data['phone'])) { $fields[] = "phone = ?"; $params[] = $data['phone']; }
        if (isset($data['bio'])) { $fields[] = "bio = ?"; $params[] = $data['bio']; }
        if (isset($data['address'])) { $fields[] = "address = ?"; $params[] = $data['address']; }
        // Email update logic could go here (check unique)

        if (empty($fields)) {
            echo json_encode(['message' => 'No changes']);
            exit;
        }

        $params[] = $data['id'];
        $sql = "UPDATE agrishop_users SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute($params)) {
            // Fetch updated user to return
            $stmt = $conn->prepare("SELECT * FROM agrishop_users WHERE id = ?");
            $stmt->execute([$data['id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            unset($user['password']);
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Update failed']);
        }
        exit;
    } 
    
    // Handle File Upload (Profile Picture)
    if (isset($_FILES['profile_picture']) && isset($_POST['id'])) {
        $id = $_POST['id'];
        $file = $_FILES['profile_picture'];
        
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid file type']);
            exit;
        }

        // Create upload dir if not exists
        $uploadDir = '../uploads/profiles/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = 'user_' . $id . '_' . time() . '.' . $ext;
        $filepath = $uploadDir . $filename;
        $dbPath = 'uploads/profiles/' . $filename; // Path to save in DB

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $stmt = $conn->prepare("UPDATE agrishop_users SET profile_picture = ? WHERE id = ?");
            if ($stmt->execute([$dbPath, $id])) {
                // Return updated user
                $stmt = $conn->prepare("SELECT * FROM agrishop_users WHERE id = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                unset($user['password']);
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                echo json_encode(['error' => 'Database update failed']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'File save failed']);
        }
    }
}
?>

