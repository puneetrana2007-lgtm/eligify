<?php
// Test file to debug the AI chat setup
header('Content-Type: text/plain');

echo "=== ELIGIFY AI DEBUG TEST ===\n\n";

// Test 1: Check .env file
echo "1. Checking .env file:\n";
if (file_exists(__DIR__ . '/.env')) {
    echo "   ✓ .env file exists\n";
    $content = file_get_contents(__DIR__ . '/.env');
    echo "   Content: " . trim($content) . "\n";
} else {
    echo "   ✗ .env file NOT found\n";
}

echo "\n2. Checking config.php:\n";
if (file_exists(__DIR__ . '/config.php')) {
    echo "   ✓ config.php exists\n";
} else {
    echo "   ✗ config.php NOT found\n";
}

echo "\n3. Checking ai_chat.php:\n";
if (file_exists(__DIR__ . '/ai_chat.php')) {
    echo "   ✓ ai_chat.php exists\n";
} else {
    echo "   ✗ ai_chat.php NOT found\n";
}

echo "\n4. Loading .env and checking API key:\n";
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
            echo "   Loaded: $key = " . substr($value, 0, 10) . "...\n";
        }
    }
}

echo "\n5. Checking if GEMINI_API_KEY is set:\n";
if (defined('GEMINI_API_KEY')) {
    echo "   ✓ GEMINI_API_KEY defined\n";
    echo "   Value: " . substr(GEMINI_API_KEY, 0, 10) . "...\n";
    echo "   Length: " . strlen(GEMINI_API_KEY) . " chars\n";
} else {
    echo "   ✗ GEMINI_API_KEY NOT defined\n";
}

echo "\n6. Checking cURL availability:\n";
if (function_exists('curl_init')) {
    echo "   ✓ cURL is available\n";
} else {
    echo "   ✗ cURL is NOT available\n";
}

echo "\n7. Checking for errors in error_log:\n";
$error_log = '/Applications/XAMPP/xamppfiles/logs/apache_php.log';
if (file_exists($error_log)) {
    echo "   ✓ Error log exists\n";
    $tail = shell_exec("tail -n 10 " . escapeshellarg($error_log));
    if ($tail) {
        echo "   Last 10 lines:\n";
        echo $tail;
    }
} else {
    echo "   ✗ Error log not found\n";
}

echo "\n=== END DEBUG TEST ===\n";
?>
