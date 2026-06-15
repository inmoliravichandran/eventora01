<?php
// Simple installer to import backend/event.sql into MySQL (XAMPP)
// Usage (browser): http://localhost/Eventora-01/backend/install_db.php
// Usage (CLI): php backend/install_db.php

// Do not require config.php here because it attempts to connect to the
// (possibly non-existent) database using PDO. This installer must be able
// to create the database before any PDO connection is attempted.

// Dynamically parse connection params from config.php to keep them in sync
$host = '127.0.0.1';
$username = 'root';
$password = '';
$port = 3306;

$configFile = __DIR__ . '/config.php';
if (file_exists($configFile)) {
    $configContent = file_get_contents($configFile);
    if ($configContent !== false) {
        if (preg_match('/\$host\s*=\s*[\'"]([^\'"]+)[\'"]/i', $configContent, $matches)) {
            $host = $matches[1];
        }
        if (preg_match('/\$username\s*=\s*[\'"]([^\'"]+)[\'"]/i', $configContent, $matches)) {
            $username = $matches[1];
        }
        if (preg_match('/\$password\s*=\s*[\'"]([^\'"]*)[\'"]/i', $configContent, $matches)) {
            $password = $matches[1];
        }
        if (preg_match('/\$port\s*=\s*(\d+)/i', $configContent, $matches)) {
            $port = intval($matches[1]);
        }
    }
}

$sqlFile = __DIR__ . '/event.sql';
if (!file_exists($sqlFile)) {
    echo json_encode(['success' => false, 'message' => 'SQL file not found: ' . $sqlFile]);
    exit;
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    echo json_encode(['success' => false, 'message' => 'Unable to read SQL file']);
    exit;
}

$mysqli = new mysqli($host, $username, $password, '', $port);
if ($mysqli->connect_errno) {
    echo json_encode(['success' => false, 'message' => 'MySQL connection failed: ' . $mysqli->connect_error . '. Make sure MySQL is running in XAMPP.']);
    exit;
}

// Increase timeout
$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
$mysqli->set_charset('utf8');

// Run multi-query
if ($mysqli->multi_query($sql)) {
    $counter = 0;
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
        $counter++;
    } while ($mysqli->more_results() && $mysqli->next_result());

    echo json_encode(['success' => true, 'message' => 'SQL imported successfully', 'statements_processed' => $counter]);
} else {
    echo json_encode(['success' => false, 'message' => 'Import failed: ' . $mysqli->error]);
}

$mysqli->close();
