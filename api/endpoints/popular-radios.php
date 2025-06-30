<?php
/**
 * Endpoint para rádios populares
 * Combina rádios mais votadas da API externa com rádios customizadas mais acessadas
 */

// Configurações de erro
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Headers CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $limit = min(max(1, (int)($_GET['limit'] ?? 50)), 100);
    $country = $_GET['country'] ?? '';
    
    $results = [
        'success' => true,
        'data' => [
            'popular_radios' => [],
            'custom_popular' => [],
            'external_popular' => [],
            'total' => 0
        ]
    ];

    // 1. Buscar rádios populares da API externa
    try {
        $external_url = 'https://de1.api.radio-browser.info/json/stations/topvote/' . $limit;
        if ($country) {
            $external_url = 'https://de1.api.radio-browser.info/json/stations/bycountry/' . urlencode($country);
        }
        
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
        
        $external_response = @file_get_contents($external_url, false, $context);
        
        if ($external_response !== false) {
            $external_data = json_decode($external_response, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($external_data)) {
                // Limitar e processar dados externos
                $external_data = array_slice($external_data, 0, $limit);
                
                // Adicionar flag de origem
                foreach ($external_data as &$station) {
                    $station['source'] = 'external';
                    $station['source_name'] = 'Radio-Browser';
                }
                
                $results['data']['external_popular'] = $external_data;
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching external popular radios: " . $e->getMessage());
    }

    // 2. Buscar rádios customizadas populares
    try {
        require_once __DIR__ . '/../config/database.php';
        require_once __DIR__ . '/../models/Radio.php';
        
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            $radio = new Radio($db);
            
            $filters = [];
            if ($country) {
                $filters['country'] = $country;
            }
            
            $custom_result = $radio->getRadios(1, $limit, $filters);
            
            if ($custom_result['success']) {
                // Adicionar flag de origem
                foreach ($custom_result['data']['radios'] as &$station) {
                    $station['source'] = 'custom';
                    $station['source_name'] = 'RadioWave';
                }
                
                $results['data']['custom_popular'] = $custom_result['data']['radios'];
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching custom popular radios: " . $e->getMessage());
    }

    // 3. Combinar e ordenar resultados
    $all_radios = array_merge(
        $results['data']['external_popular'],
        $results['data']['custom_popular']
    );

    // Ordenar por popularidade (votes para externas, total_clicks para customizadas)
    usort($all_radios, function($a, $b) {
        $score_a = $a['source'] === 'external' ? ($a['votes'] ?? 0) : ($a['total_clicks'] ?? 0);
        $score_b = $b['source'] === 'external' ? ($b['votes'] ?? 0) : ($b['total_clicks'] ?? 0);
        return $score_b - $score_a;
    });

    // Limitar resultado final
    $results['data']['popular_radios'] = array_slice($all_radios, 0, $limit);
    $results['data']['total'] = count($results['data']['popular_radios']);

    // 4. Estatísticas
    $results['data']['stats'] = [
        'external_count' => count($results['data']['external_popular']),
        'custom_count' => count($results['data']['custom_popular']),
        'total_combined' => count($results['data']['popular_radios']),
        'country_filter' => $country,
        'limit_applied' => $limit
    ];

    echo json_encode($results, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log("Popular radios error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar rádios populares',
        'error' => $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
?>