<?php
require_once 'db.php';
require_once 'utils.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sql = "SELECT * FROM advisory_posts ORDER BY date_published DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $posts = $stmt->fetchAll();

    // Mapping for frontend
    $result = array_map(function($p) {
        $colors = [
            'Alert' => 'bg-red-100 text-red-800',
            'Guide' => 'bg-green-100 text-green-800',
            'Quality' => 'bg-blue-100 text-blue-800',
            'Market' => 'bg-purple-100 text-purple-800'
        ];
        
        $c = isset($colors[$p['type']]) ? $colors[$p['type']] : 'bg-gray-100 text-gray-800';

        // Helper to format date "X days ago" - simplified for now
        $date = new DateTime($p['date_published']);
        $now = new DateTime();
        $interval = $now->diff($date);
        $dateStr = $interval->format('%a days ago');
        if ($interval->days == 0) $dateStr = "Today";

        return [
            'id' => $p['id'],
            'title' => $p['title'],
            'titleRw' => $p['title_rw'],
            'type' => $p['type'],
            'date' => $dateStr,
            'color' => $c,
            'content' => $p['content'],
            'contentRw' => $p['content_rw'],
            'iconType' => $p['type'] // Frontend will map string to Lucide icon
        ];
    }, $posts);

    echo json_encode(sanitize_output($result));
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $title = $data['title'] ?? '';
    // Optional Kinyarwanda or fallback to English
    $title_rw = $data['title_rw'] ?? $title; 
    $type = $data['type'] ?? 'Guide';
    $content = $data['content'] ?? '';
    $content_rw = $data['content_rw'] ?? $content;

    if (empty($title) || empty($content)) {
        http_response_code(400); echo json_encode(['error' => 'Title and content are required']); exit;
    }

    $sql = "INSERT INTO advisory_posts (title, title_rw, type, content, content_rw) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$title, $title_rw, $type, $content, $content_rw])) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500); echo json_encode(['error' => 'Failed to post advisory']);
    }
}
?>
