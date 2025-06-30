<?php
/**
 * Endpoint para listar países disponíveis
 * Combina países das rádios customizadas e da API externa
 */

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
    $results = [
        'success' => true,
        'data' => [
            'countries' => [],
            'total' => 0
        ]
    ];

    // 1. Buscar países da API externa
    $external_countries = [];
    try {
        $external_url = 'https://de1.api.radio-browser.info/json/countries';
        
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
                foreach ($external_data as $country) {
                    if (isset($country['name']) && !empty($country['name'])) {
                        $external_countries[$country['name']] = [
                            'name' => $country['name'],
                            'stationcount' => $country['stationcount'] ?? 0,
                            'source' => 'external'
                        ];
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching external countries: " . $e->getMessage());
    }

    // 2. Buscar países das rádios customizadas
    $custom_countries = [];
    try {
        require_once __DIR__ . '/../config/database.php';
        
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            $query = "SELECT country, COUNT(*) as stationcount 
                      FROM radios 
                      WHERE status = 'active' AND country IS NOT NULL AND country != ''
                      GROUP BY country 
                      ORDER BY stationcount DESC";
            
            $stmt = $db->prepare($query);
            $stmt->execute();
            $custom_data = $stmt->fetchAll();
            
            foreach ($custom_data as $country) {
                $custom_countries[$country['country']] = [
                    'name' => $country['country'],
                    'stationcount' => (int)$country['stationcount'],
                    'source' => 'custom'
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching custom countries: " . $e->getMessage());
    }

    // 3. Combinar países
    $all_countries = [];
    
    // Adicionar países externos
    foreach ($external_countries as $name => $country) {
        $all_countries[$name] = $country;
    }
    
    // Adicionar/combinar países customizados
    foreach ($custom_countries as $name => $country) {
        if (isset($all_countries[$name])) {
            // País já existe, somar contadores
            $all_countries[$name]['stationcount'] += $country['stationcount'];
            $all_countries[$name]['custom_count'] = $country['stationcount'];
            $all_countries[$name]['source'] = 'both';
        } else {
            // Novo país
            $all_countries[$name] = $country;
        }
    }

    // 4. Ordenar por número de estações
    uasort($all_countries, function($a, $b) {
        return $b['stationcount'] - $a['stationcount'];
    });

    // 5. Converter para array indexado
    $results['data']['countries'] = array_values($all_countries);
    $results['data']['total'] = count($all_countries);

    // 6. Estatísticas
    $results['data']['stats'] = [
        'external_countries' => count($external_countries),
        'custom_countries' => count($custom_countries),
        'total_unique' => count($all_countries),
        'top_countries' => array_slice($results['data']['countries'], 0, 10)
    ];

    echo json_encode($results, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log("Countries endpoint error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar países',
        'error' => $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
?>