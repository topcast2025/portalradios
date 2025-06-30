<?php
/**
 * Teste rápido dos endpoints da API
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$results = [];

// Teste 1: Health Check
try {
    $health_url = "https://wave.soradios.online/api/health";
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'method' => 'GET'
        ]
    ]);
    
    $health_response = @file_get_contents($health_url, false, $context);
    if ($health_response) {
        $health_data = json_decode($health_response, true);
        $results['health'] = [
            'status' => 'success',
            'response' => $health_data
        ];
    } else {
        $results['health'] = [
            'status' => 'error',
            'message' => 'Falha na requisição'
        ];
    }
} catch (Exception $e) {
    $results['health'] = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Teste 2: Listar Rádios
try {
    $radios_url = "https://wave.soradios.online/api/radios";
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'method' => 'GET'
        ]
    ]);
    
    $radios_response = @file_get_contents($radios_url, false, $context);
    if ($radios_response) {
        $radios_data = json_decode($radios_response, true);
        $results['radios'] = [
            'status' => 'success',
            'response' => $radios_data
        ];
    } else {
        $results['radios'] = [
            'status' => 'error',
            'message' => 'Falha na requisição'
        ];
    }
} catch (Exception $e) {
    $results['radios'] = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Teste 3: Verificar estrutura local
$results['local_structure'] = [
    'database_config' => file_exists('config/database.php'),
    'radio_model' => file_exists('models/Radio.php'),
    'health_endpoint' => file_exists('endpoints/health.php'),
    'radios_endpoint' => file_exists('endpoints/radios.php'),
    'upload_endpoint' => file_exists('endpoints/upload.php')
];

// Teste 4: Conexão com banco local
try {
    if (file_exists('config/database.php')) {
        require_once 'config/database.php';
        $database = new Database();
        $connection_test = $database->testConnection();
        
        $results['database'] = [
            'status' => $connection_test ? 'success' : 'error',
            'connected' => $connection_test
        ];
        
        if ($connection_test) {
            $diagnostic = $database->getDiagnosticInfo();
            $results['database']['info'] = $diagnostic;
        }
    } else {
        $results['database'] = [
            'status' => 'error',
            'message' => 'Arquivo de configuração não encontrado'
        ];
    }
} catch (Exception $e) {
    $results['database'] = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

echo json_encode([
    'success' => true,
    'timestamp' => date('c'),
    'tests' => $results
], JSON_PRETTY_PRINT);
?>
```