<?php
require_once 'config.php';

$category = $_GET['category'] ?? 'all';
$minPrice = $_GET['min_price'] ?? 0;
$maxPrice = $_GET['max_price'] ?? 9999999;

$sql = "SELECT * FROM services WHERE price BETWEEN ? AND ?";
$params = [$minPrice, $maxPrice];

if ($category !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

echo json_encode(['success' => true, 'services' => $services]);
?>