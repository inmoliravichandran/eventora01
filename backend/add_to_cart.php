<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service_id'] ?? 0;
    $quantity = $_POST['quantity'] ?? 1;
    $event_date = $_POST['event_date'] ?? null;
    $guests = $_POST['guests'] ?? null;
    $special_requests = $_POST['special_requests'] ?? '';
    $user_id = $_SESSION['user_id'];
    
    // Check if item already exists
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND service_id = ?");
    $stmt->execute([$user_id, $service_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ?, event_date = ?, guests = ?, special_requests = ? WHERE user_id = ? AND service_id = ?");
        $stmt->execute([$quantity, $event_date, $guests, $special_requests, $user_id, $service_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, service_id, quantity, event_date, guests, special_requests) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $service_id, $quantity, $event_date, $guests, $special_requests]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Added to cart']);
}
?>