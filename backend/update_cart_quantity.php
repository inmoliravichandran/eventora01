<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service_id'] ?? 0;
    $quantity = intval($_POST['quantity'] ?? 1);
    $user_id = $_SESSION['user_id'];
    
    try {
        if ($quantity <= 0) {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND service_id = ?");
            $stmt->execute([$user_id, $service_id]);
            echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
        } else {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND service_id = ?");
            $stmt->execute([$quantity, $user_id, $service_id]);
            echo json_encode(['success' => true, 'message' => 'Quantity updated']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
