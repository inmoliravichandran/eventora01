<?php
require_once 'config.php';

if (!empty($_SESSION['user_id'])) {
    echo json_encode([
        'success' => true,
        'authenticated' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['role'] ?? 'user'
        ]
    ]);
    exit;
}

echo json_encode(['success' => true, 'authenticated' => false]);
?>