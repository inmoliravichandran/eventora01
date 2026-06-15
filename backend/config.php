<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

session_start();

$host = '127.0.0.1';
$dbname = 'eventora_db';
$username = 'root';
$password = '';
$port = 3307; // IMPORTANT for your setup

try {
    // ✅ FIX: include port (this was your main issue)
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10
    ]);

    // optional success response for testing
    // echo json_encode(["success" => true, "message" => "DB Connected"]);

} catch (PDOException $e) {

    $errorMsg = $e->getMessage();

    // cleaner error message
    if (strpos($errorMsg, 'SQLSTATE[HY000]') !== false) {
        $errorMsg = 'Database connection failed. Please ensure:<br>
        1. MySQL is running in XAMPP<br>
        2. Database "eventora_db" exists in phpMyAdmin<br>
        3. MySQL port is 3307 (XAMPP setting)';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['ajax'])) {
        echo json_encode(['success' => false, 'message' => $errorMsg]);
    } else {
        echo $errorMsg;
    }
    exit;
}
?>