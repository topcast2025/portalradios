<?php
/**
 * Proxy para API externa de rádios (Radio-Browser)
 * API gratuita com milhares de estações de rádio
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

try {
    // URL base da API Radio-Browser (API gratuita com 30k+ estações)
    $radio_browser_api = 'https://de1.api.radio-browser.info/json';
    
    // Obter o path da requisição
    $request_uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($request_uri, PHP_URL_PATH);
    $query = $_SERVER['QUERY_STRING'] ?? '';
    
    // Remove /api/external-radios do path
    $external_path = str_replace('/api/external-radios', '', $path);
    
    // Se não há path específico, redirecionar para estações populares
    if (empty($external_path) || $external_path === '/') {
        $external_path = '/stations/topvote/50';
    }
    
    // Construir URL completa
    $url = $radio_browser_api . $external_path;
    if ($query) {
        $url .= '?' . $query;
    }
    
    // Log da requisição
    error_log("External Radio API Request: " . $url);
    
    // Configurar contexto para a requisição
    $context = stream_context_create([
        'http' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'header' => [
                'User-Agent: RadioWave/2.0.0 (https://wave.soradios.online)',
                'Accept: application/json',
                'Content-Type: application/json'
            ],
            'timeout' => 15,
            'ignore_errors' => true
        ]
    ]);
    
    // Fazer a requisição
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        throw new Exception('Erro ao conectar com a API externa de rádios');
    }
    
    // Verificar se a resposta é válida
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Resposta inválida da API externa');
    }
    
    // Processar e padronizar os dados se necessário
    if (is_array($data)) {
        foreach ($data as &$station) {
            // Garantir que campos essenciais existam
            if (!isset($station['stationuuid'])) {
                $station['stationuuid'] = $station['changeuuid'] ?? uniqid();
            }
            
            // Padronizar URLs
            if (isset($station['url_resolved']) && empty($station['url_resolved'])) {
                $station['url_resolved'] = $station['url'] ?? '';
            }
            
            // Garantir que favicon existe
            if (!isset($station['favicon']) || empty($station['favicon'])) {
                $station['favicon'] = '';
            }
            
            // Converter campos numéricos
            $station['votes'] = (int)($station['votes'] ?? 0);
            $station['clickcount'] = (int)($station['clickcount'] ?? 0);
            $station['bitrate'] = (int)($station['bitrate'] ?? 0);
        }
    }
    
    // Retornar resposta processada
    echo json_encode($data, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log("External Radio API Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 500,
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
?>