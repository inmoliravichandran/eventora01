<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in', 'cart' => []]);
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT c.*, s.name, s.price, s.image_url, s.category 
        FROM cart c 
        JOIN services s ON c.service_id = s.id 
        WHERE c.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$cart = $stmt->fetchAll();

$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

echo json_encode(['success' => true, 'cart' => $cart, 'total' => $total]);
?>