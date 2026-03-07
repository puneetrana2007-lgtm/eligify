<?php
// Load environment variables from .env file
if (file_exists(__DIR__ . '/.env')) {
    $env_lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) continue;
        
        // Parse KEY=VALUE format
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            
            define(strtoupper($key), $value);
        }
    }
}

// Fallback values if .env doesn't exist
if (!defined('GEMINI_API_KEY')) {
    define('GEMINI_API_KEY', '');
}

// API endpoint to securely provide the Gemini API key
header('Content-Type: application/json');

if ($_GET['action'] === 'get_api_key') {
    // Return the API key to frontend
    echo json_encode([
        'api_key' => GEMINI_API_KEY,
        'status' => empty(GEMINI_API_KEY) ? 'missing' : 'loaded'
    ]);
    exit;
}
?>
