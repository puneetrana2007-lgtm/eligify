<?php
header('Content-Type: application/json');

// Load .env
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

$apiKey = GEMINI_API_KEY;

echo json_encode([
    '.env file' => file_exists(__DIR__ . '/.env') ? 'exists' : 'missing',
    'API Key Set' => !empty($apiKey) ? 'yes' : 'no',
    'API Key Length' => strlen($apiKey),
    'API Key Starts With' => substr($apiKey, 0, 8),
    'cURL Available' => function_exists('curl_init') ? 'yes' : 'no',
    'PHP Version' => phpversion(),
]);
?>
