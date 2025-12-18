<?php
include 'db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

if ($method === 'POST') {
    $action = $data['action'] ?? '';

    if ($action === 'register') {
        $first_name = trim($data['first_name'] ?? '');
        $last_name = trim($data['last_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $raw_password = $data['password'] ?? '';
        $role = $data['role'] ?? 'buyer';

        // Validation
        if (empty($first_name) || empty($last_name) || empty($email) || empty($raw_password)) {
            http_response_code(400); echo json_encode(['error' => 'All fields are required']); exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400); echo json_encode(['error' => 'Invalid email format']); exit;
        }
        if (strlen($raw_password) < 6) {
            http_response_code(400); echo json_encode(['error' => 'Password must be at least 6 characters']); exit;
        }
        if (!in_array($role, ['buyer', 'farmer', 'admin'])) {
            http_response_code(400); echo json_encode(['error' => 'Invalid role']); exit;
        }

        // Limit Admin to 1
        if ($role === 'admin') {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM agrishop_users WHERE role = 'admin'");
            $stmt->execute();
            if ($stmt->fetchColumn() >= 1) {
                http_response_code(400); 
                echo json_encode(['error' => 'Admin limit reached. Only 1 admin allowed.']); 
                exit;
            }
        }

        $password = password_hash($raw_password, PASSWORD_DEFAULT);

        // Check if email exists
        // NOTICE: Using $conn instead of $pdo to match db.php
        $stmt = $conn->prepare("SELECT id FROM agrishop_users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'Email already registered']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO agrishop_users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$first_name, $last_name, $email, $password, $role])) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Registration failed']);
        }
    } 

    elseif ($action === 'forgot_password') {
        $email = trim($data['email'] ?? '');
        if (empty($email)) {
            http_response_code(400); echo json_encode(['error' => 'Email is required']); exit;
        }

        // Check if user exists
        $stmt = $conn->prepare("SELECT id FROM agrishop_users WHERE email = ?");
        $stmt->execute([$email]);
        if (!$stmt->fetch()) {
            // Security: Don't reveal if user exists, but for this dev app we can just say sent.
            // Or typically we just say "If that email exists, we sent a link."
            // But let's return success regardless.
            echo json_encode(['success' => true, 'message' => 'Reset link sent (simulated)']); 
            exit;
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $conn->prepare("INSERT INTO agrishop_password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expires]);

        // SIMULATE EMAIL SENDING
        $link = "http://localhost/final project/reset_password.html?token=" . $token;
        echo json_encode(['success' => true, 'message' => 'Reset link sent', 'debug_link' => $link]);
    }
    elseif ($action === 'reset_password') {
        $token = $data['token'] ?? '';
        $new_password = $data['password'] ?? '';

        if (empty($token) || empty($new_password) || strlen($new_password) < 6) {
            http_response_code(400); echo json_encode(['error' => 'Invalid token or password too short']); exit;
        }

        // Validate Token
        $stmt = $conn->prepare("SELECT email FROM agrishop_password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            http_response_code(400); echo json_encode(['error' => 'Invalid or expired token']); exit;
        }

        $email = $row['email'];
        $pass_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // Update User Password
        $stmt = $conn->prepare("UPDATE agrishop_users SET password = ? WHERE email = ?");
        $stmt->execute([$pass_hash, $email]);

        // Delete used token
        $stmt = $conn->prepare("DELETE FROM agrishop_password_resets WHERE email = ?");
        $stmt->execute([$email]);

        echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
    }
    elseif ($action === 'login') {
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            http_response_code(400); echo json_encode(['error' => 'Email and Password are required']); exit;
        }

        $stmt = $conn->prepare("SELECT * FROM agrishop_users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Server-Side Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'];

            // Remove password from response
            unset($user['password']);
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid email or password']);
        }
    }
    elseif ($action === 'check_session') {
        if (isset($_SESSION['user_id'])) {
            $stmt = $conn->prepare("SELECT id, first_name, last_name, email, role, profile_picture FROM agrishop_users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                echo json_encode(['success' => false]);
            }
        } else {
            echo json_encode(['success' => false]);
        }
    }
    elseif ($action === 'logout') {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logged out']);
    }
}
?>


