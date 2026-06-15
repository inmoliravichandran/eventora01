<?php
$files = [
    'about.html',
    'admin-dashboard.html',
    'cart.html',
    'checkout.html',
    'contact.html',
    'index.html',
    'login.html',
    'privacy.html',
    'profile.html',
    'register.html',
    'service-details.html',
    'services.html'
];

$deleted = [];
$errors = [];

foreach ($files as $file) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            $deleted[] = $file;
        } else {
            $errors[] = "Failed to delete $file";
        }
    } else {
        $deleted[] = "$file (already gone)";
    }
}

// Self delete
@unlink(__FILE__);

header('Content-Type: application/json');
echo json_encode([
    'success' => empty($errors),
    'deleted' => $deleted,
    'errors' => $errors
]);
?>
