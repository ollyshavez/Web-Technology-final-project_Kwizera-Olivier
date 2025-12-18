<?php
// Diagnostic Script - Test Database Connection
header('Content-Type: application/json');

// Database credentials
$host = 'localhost';
$db_name = 'webtech_2025A_kwizera_olivier';
$username = 'kwizera.olivier';
$password = '15101234';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo json_encode([
        'status' => 'SUCCESS',
        'message' => 'Database connection successful!',
        'database' => $db_name,
        'test_query' => 'Checking tables...'
    ]);
    
    // Test if agrishop_products table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'agrishop_products'");
    $exists = $stmt->rowCount() > 0;
    
    if ($exists) {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM agrishop_products");
        $count = $stmt->fetch()['count'];
        echo json_encode([
            'table_check' => 'Table agrishop_products EXISTS',
            'product_count' => $count
        ]);
    } else {
        echo json_encode([
            'table_check' => 'ERROR: Table agrishop_products DOES NOT EXIST'
        ]);
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'status' => 'ERROR',
        'message' => $e->getMessage()
    ]);
}
?>
