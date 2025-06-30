<?php
// Proxy para API externa de rádios
$radio_browser_api = 'https://de1.api.radio-browser.info/json';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$query = $_SERVER['QUERY_STRING'] ?? '';

// Remove /api/external-radios from path
$external_path = str_replace('/api/external-radios', '', $path);

// Build full URL
$url = $radio_browser_api . $external_path;
if ($query) {
    $url .= '?' . $query;
}

// Create context for the request
$context = stream_context_create([
    'http' => [
        'method' => $_SERVER['REQUEST_METHOD'],
        'header' => [
            'User-Agent: Radion/1.0.0',
            'Accept: application/json'
        ],
        'timeout' => 15
    ]
]);

// Make request
$response = file_get_contents($url, false, $context);

if ($response === false) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao conectar com a API externa'
    ]);
    exit;
}

// Forward response
header('Content-Type: application/json');
echo $response;
?>