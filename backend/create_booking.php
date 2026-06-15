<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $_SESSION['user_id'];
    
    // Fetch user's cart items
    $stmt = $pdo->prepare("SELECT c.*, s.price FROM cart c JOIN services s ON c.service_id = s.id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    $cartItems = $stmt->fetchAll();
    
    if (count($cartItems) === 0) {
        echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
        exit;
    }
    
    $bookingIds = [];
    foreach ($cartItems as $item) {
        $event_date = $item['event_date'] ?: ($data['event_date'] ?? date('Y-m-d'));
        $guests = $item['guests'] ?: ($data['guests'] ?? 100);
        $total_price = $item['price'] * $item['quantity'];
        
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, service_id, event_date, guests, total_price, status) VALUES (?, ?, ?, ?, ?, 'confirmed')");
        $stmt->execute([$user_id, $item['service_id'], $event_date, $guests, $total_price]);
        $bookingIds[] = $pdo->lastInsertId();
    }
    
    // Clear cart after booking
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Booking confirmed', 
        'booking_ids' => $bookingIds
    ]);
}
?>