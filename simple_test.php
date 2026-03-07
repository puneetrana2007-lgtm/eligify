<?php
header('Content-Type: application/json');

// Simple direct test
echo json_encode([
    'test' => 'Starting ai_chat.php test',
    'timestamp' => date('Y-m-d H:i:s')
]);
exit;
?>
