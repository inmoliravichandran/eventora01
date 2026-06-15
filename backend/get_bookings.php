<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT b.*, s.name, s.category, s.image_url 
        FROM bookings b 
        JOIN services s ON b.service_id = s.id 
        WHERE b.user_id = ? 
        ORDER BY b.booking_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

echo json_encode(['success' => true, 'bookings' => $bookings]);
?>