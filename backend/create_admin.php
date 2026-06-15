<?php
// Helper to create a seeded admin user.
// Usage (CLI): php backend/create_admin.php
// Usage (browser): http://localhost/Eventora-01/backend/create_admin.php

require_once 'config.php';

$adminEmail = 'admin@eventora.com';
$adminName = 'Administrator';
$adminPassword = 'Admin@123'; // change this after first login

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    $exists = $stmt->fetch();

    if ($exists) {
        echo json_encode(['success' => true, 'message' => 'Admin already exists', 'email' => $adminEmail]);
        exit;
    }

    $hashed = password_hash($adminPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$adminName, $adminEmail, $hashed, 'admin']);

    echo json_encode(['success' => true, 'message' => 'Admin created', 'email' => $adminEmail, 'password' => $adminPassword]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}

?>