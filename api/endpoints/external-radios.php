<?php
/**
 * Proxy para API externa de rádios (Radio-Browser)
 * Fornece acesso a milhares de rádios online
 */

// Configurações de erro
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Headers CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// URL base da API Radio-Browser
$radio_browser_api = 'https://de1.api.radio-browser.info/json';

// Obter path da requisição
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$query = $_SERVER['QUERY_STRING'] ?? '';

// Remover /api/external-radios do path
$external_path = str_replace('/api/external-radios', '', $path);

// Construir URL completa
$url = $radio_browser_api . $external_path;
if ($query) {
    $url .= '?' . $query;
}

try {
    // Criar contexto para a requisição
    $context = stream_context_create([
        'http' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'header' => [
                'User-Agent: RadioWave/2.0.0',
                'Accept: application/json',
                'Content-Type: application/json'
            ],
            'timeout' => 15
        ]
    ]);

    // Fazer requisição
    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        throw new Exception('Erro ao conectar com a API externa');
    }

    // Verificar se é JSON válido
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Resposta inválida da API externa');
    }

    // Log da requisição bem-sucedida
    error_log("External API request successful: $url");

    // Retornar resposta
    echo $response;

} catch (Exception $e) {
    error_log("External API error: " . $e->getMessage() . " - URL: $url");
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao conectar com a API externa de rádios',
        'error' => $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
?>