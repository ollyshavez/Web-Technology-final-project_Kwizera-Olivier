<?php
// Secure Session Start
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', // Current domain
    'secure' => false, // Set to true if HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CORS for Credentials
// Note: When allow-credentials is true, Origin cannot be *
$origin = $_SERVER['HTTP_ORIGIN'] ?? 'http://localhost';
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = 'localhost';
$db_name = 'webtech_2025A_kwizera_olivier'; // Production database
$username = 'kwizera.olivier'; // Production username
$password = '15101234'; // Production password

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

