<?php
header('Content-Type: application/json; charset=utf-8');

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

// Get the message from the request
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['message'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No message provided']);
    exit;
}

$userMessage = trim($data['message']);

if (empty($userMessage)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Empty message']);
    exit;
}

// Check if API key exists
if (empty(GEMINI_API_KEY)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'API Key Not Configured']);
    exit;
}

// Check if cURL is available
if (!function_exists('curl_init')) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'cURL not available']);
    exit;
}

// System prompt for the AI
$systemPrompt = <<<'PROMPT'
You are Eligify AI, a helpful assistant for government job seekers in India. You specialize in:
- Government job eligibility criteria
- Different types of exams (SSC, UPSC, Banking, Railway, etc.)
- Qualification requirements
- Application processes
- Job benefits and salary information
- Tips for exam preparation

Keep responses concise (2-3 sentences), friendly, and relevant to government jobs. Use emojis appropriately to make responses engaging.
If asked about unrelated topics, politely redirect to government jobs.
PROMPT;

try {
    // Prepare the request to Google Generative AI API
    $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . urlencode(GEMINI_API_KEY);
    
    $requestData = [
        'contents' => [
            [
                'parts' => [
                    [
                        'text' => $systemPrompt . "\n\nUser message: " . $userMessage
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
    
    // Use cURL to make the request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        throw new Exception('cURL error: ' . $curlError);
    }
    
    if (!$response) {
        throw new Exception('Empty response from API');
    }
    
    if ($httpCode !== 200) {
        $errorData = @json_decode($response, true);
        $errorMsg = isset($errorData['error']['message']) ? $errorData['error']['message'] : (isset($errorData['error']) ? json_encode($errorData['error']) : 'Unknown API error');
        throw new Exception('API error (' . $httpCode . '): ' . $errorMsg);
    }
    
    $responseData = json_decode($response, true);
    
    if (!$responseData) {
        throw new Exception('Invalid JSON response from API: ' . substr($response, 0, 200));
    }
    
    // Extract the text from the response
    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        $aiResponse = trim($responseData['candidates'][0]['content']['parts'][0]['text']);
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'response' => $aiResponse
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        throw new Exception('Unexpected API response format: ' . json_encode($responseData));
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'api_key_length' => strlen(GEMINI_API_KEY),
            'curl_available' => function_exists('curl_init'),
            'message_received' => $userMessage
        ]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
?>
