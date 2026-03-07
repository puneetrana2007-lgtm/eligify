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

echo "=== GEMINI API DIRECT TEST ===\n\n";

echo "1. API Key Check:\n";
echo "   Present: " . (!empty($apiKey) ? "YES" : "NO") . "\n";
echo "   Length: " . strlen($apiKey) . "\n";
echo "   Starts with: " . substr($apiKey, 0, 8) . "...\n\n";

echo "2. Building Request:\n";

$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . urlencode($apiKey);

echo "   API URL: " . str_replace($apiKey, 'KEY...', $apiUrl) . "\n\n";

$requestData = [
    'contents' => [
        [
            'parts' => [
                [
                    'text' => 'What is SSC CGL exam? Answer in 2 sentences.'
                ]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 200,
    ]
];

$jsonPayload = json_encode($requestData);
echo "   Payload size: " . strlen($jsonPayload) . " bytes\n\n";

echo "3. Sending Request via cURL:\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);     // For testing
curl_setopt($ch, CURLOPT_VERBOSE, true);

// Capture verbose output
$verbose = fopen('php://temp', 'r+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$curlErrno = curl_errno($ch);

rewind($verbose);
$verboseOutput = stream_get_contents($verbose);
fclose($verbose);

curl_close($ch);

echo "   HTTP Status: " . $httpCode . "\n";
echo "   cURL Error Code: " . $curlErrno . "\n";
if ($curlError) {
    echo "   cURL Error: " . $curlError . "\n";
}

echo "\n4. Response:\n";

if (!$response) {
    echo "   ❌ NO RESPONSE RECEIVED\n";
    if ($curlError) {
        echo "   Error: " . $curlError . "\n";
    }
} else {
    echo "   Response size: " . strlen($response) . " bytes\n";
    echo "   First 200 chars: " . substr($response, 0, 200) . "\n\n";
    
    $responseData = @json_decode($response, true);
    if ($responseData) {
        echo "   Parsed as JSON:\n";
        echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
        
        // Check for errors
        if (isset($responseData['error'])) {
            echo "   ⚠️ ERROR from API:\n";
            echo "   " . json_encode($responseData['error'], JSON_PRETTY_PRINT) . "\n";
        } elseif (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            echo "   ✅ SUCCESS! AI Response:\n";
            echo "   " . $responseData['candidates'][0]['content']['parts'][0]['text'] . "\n";
        } else {
            echo "   ⚠️ Unexpected response format\n";
        }
    } else {
        echo "   Response is not valid JSON\n";
        echo "   Raw: " . $response . "\n";
    }
}

echo "\n5. Verbose cURL Output:\n";
if ($verboseOutput) {
    echo $verboseOutput;
} else {
    echo "   (No verbose output captured)\n";
}

echo "\n=== END TEST ===\n";
?>
