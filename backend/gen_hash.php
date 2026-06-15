<?php
// Secure placeholder
http_response_code(403);
echo json_encode(['error' => 'Forbidden']);
?>
