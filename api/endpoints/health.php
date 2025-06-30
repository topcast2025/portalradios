<?php
/**
 * Health Check Endpoint
 * Verifica o status geral do sistema
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
    // Verificar se o arquivo de configuração existe
    $config_file = __DIR__ . '/../config/database.php';
    if (!file_exists($config_file)) {
        throw new Exception('Arquivo de configuração do banco não encontrado');
    }

    // Incluir configuração do banco
    require_once $config_file;
    
    // Testar conexão com banco
    $database = new Database();
    $connection_test = $database->testConnection();
    
    if (!$connection_test) {
        throw new Exception('Falha na conexão com o banco de dados');
    }
    
    // Obter informações diagnósticas
    $diagnostic = $database->getDiagnosticInfo();
    
    // Verificar estrutura de diretórios
    $upload_dir = __DIR__ . '/../../uploads';
    $logos_dir = __DIR__ . '/../../uploads/logos';
    
    $directories_status = [
        'uploads' => [
            'exists' => is_dir($upload_dir),
            'writable' => is_writable($upload_dir)
        ],
        'logos' => [
            'exists' => is_dir($logos_dir),
            'writable' => is_writable($logos_dir)
        ]
    ];

    // Resposta de sucesso
    $response = [
        'status' => 'OK',
        'timestamp' => date('c'),
        'version' => '2.0.0',
        'environment' => 'production',
        'database' => [
            'status' => 'connected',
            'info' => $diagnostic['success'] ? $diagnostic['data'] : null
        ],
        'php' => [
            'version' => PHP_VERSION,
            'sapi' => php_sapi_name(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time')
        ],
        'server' => [
            'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
            'script_name' => $_SERVER['SCRIPT_NAME'] ?? ''
        ],
        'directories' => $directories_status,
        'extensions' => [
            'pdo' => extension_loaded('pdo'),
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'gd' => extension_loaded('gd'),
            'json' => extension_loaded('json'),
            'curl' => extension_loaded('curl'),
            'mbstring' => extension_loaded('mbstring'),
            'openssl' => extension_loaded('openssl')
        ]
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log("Health check error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'status' => 'ERROR',
        'timestamp' => date('c'),
        'message' => $e->getMessage(),
        'php_version' => PHP_VERSION,
        'error_details' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ], JSON_PRETTY_PRINT);
}
?>