<?php
/**
 * Endpoint para listar gêneros/tags disponíveis
 * Combina gêneros das rádios customizadas e da API externa
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
    $limit = min(max(1, (int)($_GET['limit'] ?? 100)), 500);
    
    $results = [
        'success' => true,
        'data' => [
            'genres' => [],
            'total' => 0
        ]
    ];

    // 1. Buscar tags da API externa
    $external_genres = [];
    try {
        $external_url = 'https://de1.api.radio-browser.info/json/tags';
        
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
                foreach ($external_data as $tag) {
                    if (isset($tag['name']) && !empty($tag['name'])) {
                        $name = strtolower(trim($tag['name']));
                        if (!empty($name)) {
                            $external_genres[$name] = [
                                'name' => $tag['name'],
                                'stationcount' => $tag['stationcount'] ?? 0,
                                'source' => 'external'
                            ];
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching external genres: " . $e->getMessage());
    }

    // 2. Buscar gêneros das rádios customizadas
    $custom_genres = [];
    try {
        require_once __DIR__ . '/../config/database.php';
        
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            $query = "SELECT genres FROM radios WHERE status = 'active' AND genres IS NOT NULL";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $radios = $stmt->fetchAll();
            
            $genre_counts = [];
            
            foreach ($radios as $radio) {
                $genres = json_decode($radio['genres'], true);
                if (is_array($genres)) {
                    foreach ($genres as $genre) {
                        $genre = trim($genre);
                        if (!empty($genre)) {
                            $key = strtolower($genre);
                            if (!isset($genre_counts[$key])) {
                                $genre_counts[$key] = [
                                    'name' => $genre,
                                    'count' => 0
                                ];
                            }
                            $genre_counts[$key]['count']++;
                        }
                    }
                }
            }
            
            foreach ($genre_counts as $key => $data) {
                $custom_genres[$key] = [
                    'name' => $data['name'],
                    'stationcount' => $data['count'],
                    'source' => 'custom'
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching custom genres: " . $e->getMessage());
    }

    // 3. Combinar gêneros
    $all_genres = [];
    
    // Adicionar gêneros externos
    foreach ($external_genres as $key => $genre) {
        $all_genres[$key] = $genre;
    }
    
    // Adicionar/combinar gêneros customizados
    foreach ($custom_genres as $key => $genre) {
        if (isset($all_genres[$key])) {
            // Gênero já existe, somar contadores
            $all_genres[$key]['stationcount'] += $genre['stationcount'];
            $all_genres[$key]['custom_count'] = $genre['stationcount'];
            $all_genres[$key]['source'] = 'both';
        } else {
            // Novo gênero
            $all_genres[$key] = $genre;
        }
    }

    // 4. Filtrar gêneros com pelo menos 1 estação
    $all_genres = array_filter($all_genres, function($genre) {
        return $genre['stationcount'] > 0;
    });

    // 5. Ordenar por número de estações
    uasort($all_genres, function($a, $b) {
        return $b['stationcount'] - $a['stationcount'];
    });

    // 6. Limitar resultados
    $all_genres = array_slice($all_genres, 0, $limit, true);

    // 7. Converter para array indexado
    $results['data']['genres'] = array_values($all_genres);
    $results['data']['total'] = count($all_genres);

    // 8. Estatísticas
    $results['data']['stats'] = [
        'external_genres' => count($external_genres),
        'custom_genres' => count($custom_genres),
        'total_unique' => count($all_genres),
        'top_genres' => array_slice($results['data']['genres'], 0, 20)
    ];

    echo json_encode($results, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log("Genres endpoint error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar gêneros',
        'error' => $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
?>