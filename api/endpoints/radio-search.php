<?php
/**
 * Endpoint unificado para busca de rádios
 * Combina rádios customizadas com rádios da API externa
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
    // Incluir dependências
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../models/Radio.php';

    // Parâmetros de busca
    $search = $_GET['search'] ?? '';
    $country = $_GET['country'] ?? '';
    $language = $_GET['language'] ?? '';
    $genre = $_GET['genre'] ?? '';
    $limit = min(max(1, (int)($_GET['limit'] ?? 50)), 200);
    $source = $_GET['source'] ?? 'all'; // 'all', 'custom', 'external'

    $results = [
        'success' => true,
        'data' => [
            'custom_radios' => [],
            'external_radios' => [],
            'total_custom' => 0,
            'total_external' => 0,
            'search_params' => [
                'search' => $search,
                'country' => $country,
                'language' => $language,
                'genre' => $genre,
                'limit' => $limit,
                'source' => $source
            ]
        ]
    ];

    // 1. Buscar rádios customizadas (se solicitado)
    if ($source === 'all' || $source === 'custom') {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            if ($db) {
                $radio = new Radio($db);
                
                $filters = [
                    'country' => $country,
                    'language' => $language,
                    'genre' => $genre,
                    'search' => $search
                ];
                
                $custom_result = $radio->getRadios(1, $limit, $filters);
                
                if ($custom_result['success']) {
                    $results['data']['custom_radios'] = $custom_result['data']['radios'];
                    $results['data']['total_custom'] = $custom_result['data']['pagination']['total'];
                }
            }
        } catch (Exception $e) {
            error_log("Error fetching custom radios: " . $e->getMessage());
        }
    }

    // 2. Buscar rádios externas (se solicitado)
    if ($source === 'all' || $source === 'external') {
        try {
            $external_url = 'https://de1.api.radio-browser.info/json/stations/search';
            $params = [];
            
            if ($search) $params['name'] = $search;
            if ($country) $params['country'] = $country;
            if ($language) $params['language'] = $language;
            if ($genre) $params['tag'] = $genre;
            
            $params['limit'] = $limit;
            $params['hidebroken'] = 'true';
            $params['order'] = 'votes';
            $params['reverse'] = 'true';
            
            $query_string = http_build_query($params);
            $full_url = $external_url . '?' . $query_string;
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: RadioWave/2.0.0',
                        'Accept: application/json'
                    ],
                    'timeout' => 10
                ]
            ]);
            
            $external_response = @file_get_contents($full_url, false, $context);
            
            if ($external_response !== false) {
                $external_data = json_decode($external_response, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($external_data)) {
                    $results['data']['external_radios'] = $external_data;
                    $results['data']['total_external'] = count($external_data);
                }
            }
        } catch (Exception $e) {
            error_log("Error fetching external radios: " . $e->getMessage());
        }
    }

    // 3. Estatísticas gerais
    $results['data']['summary'] = [
        'total_results' => $results['data']['total_custom'] + $results['data']['total_external'],
        'custom_count' => $results['data']['total_custom'],
        'external_count' => $results['data']['total_external'],
        'search_performed' => !empty($search) || !empty($country) || !empty($language) || !empty($genre)
    ];

    echo json_encode($results, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log("Radio search error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro na busca de rádios',
        'error' => $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
?>