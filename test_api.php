<?php
header('Content-Type: application/json');

// Load environment variables from .env file
if (file_exists(__DIR__ . '/.env')) {
    $env_lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            define(strtoupper($key), $value);
        }
    }
}

if (!defined('GEMINI_API_KEY')) {
    define('GEMINI_API_KEY', '');
}

echo json_encode([
    'test' => 'Gemini API Connection Test',
    'api_key_present' => !empty(GEMINI_API_KEY),
    'api_key_length' => strlen(GEMINI_API_KEY),
    'api_key_starts_with' => substr(GEMINI_API_KEY, 0, 10),
    'curl_available' => function_exists('curl_init'),
    'test_message' => 'Testing API connection...'
]);

if (empty(GEMINI_API_KEY)) {
    http_response_code(400);
    exit;
}

if (!function_exists('curl_init')) {
    http_response_code(500);
    exit;
}

// Test API call
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . GEMINI_API_KEY;

$requestData = [
    'contents' => [
        [
            'parts' => [
                [
                    'text' => 'Hello, this is a test message. Please respond briefly.'
                ]
            ]
        ]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo json_encode([
    'http_code' => $httpCode,
    'response_received' => !empty($response),
    'error' => $error,
    'response_preview' => substr($response, 0, 200)
]);
?>
