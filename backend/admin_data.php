<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$role = $_SESSION['role'] ?? null;
$adminEmail = $_SESSION['user_email'] ?? '';
$isAdmin = false;

if ($role === 'admin') {
    $isAdmin = true;
} elseif (empty($role) && in_array($adminEmail, ['admin@eventora.com', 'admin@example.com'])) {
    $isAdmin = true;
}

if (!$isAdmin) {
    echo json_encode(['success' => false, 'message' => 'Admin access only']);
    exit;
}

try {
    $usersCount = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $bookingsCount = $pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
    $servicesCount = $pdo->query('SELECT COUNT(*) FROM services')->fetchColumn();
    $pendingBookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
    $totalRevenue = $pdo->query('SELECT IFNULL(SUM(total_price), 0) FROM bookings')->fetchColumn();
    $recentBookings = $pdo->query(
        'SELECT b.id, b.booking_date, b.status, u.name AS user_name, s.name AS service_name, b.total_price FROM bookings b LEFT JOIN users u ON b.user_id = u.id LEFT JOIN services s ON b.service_id = s.id ORDER BY b.booking_date DESC LIMIT 6'
    )->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => [
            'usersCount' => (int) $usersCount,
            'bookingsCount' => (int) $bookingsCount,
            'servicesCount' => (int) $servicesCount,
            'pendingBookings' => (int) $pendingBookings,
            'totalRevenue' => number_format((float) $totalRevenue, 2, '.', ''),
            'recentBookings' => $recentBookings
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Unable to load admin data: ' . $e->getMessage()]);
}
