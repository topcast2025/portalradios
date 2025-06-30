<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Verificar se o arquivo de configuração existe
    $config_file = __DIR__ . '/../config/database.php';
    if (!file_exists($config_file)) {
        throw new Exception('Arquivo de configuração não encontrado');
    }

    require_once $config_file;
    
    // Test database connection
    $database = new Database();
    
    if (!$database->testConnection()) {
        throw new Exception('Falha na conexão com o banco de dados');
    }
    
    $db = $database->getConnection();
    
    // Test query
    $stmt = $db->query("SELECT COUNT(*) as total FROM radios");
    $result = $stmt->fetch();
    
    echo json_encode([
        'status' => 'OK',
        'timestamp' => date('c'),
        'version' => '1.0.0',
        'environment' => 'production',
        'database' => 'connected',
        'php_version' => PHP_VERSION,
        'total_radios' => $result['total'] ?? 0,
        'server_info' => [
            'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
            'script_name' => $_SERVER['SCRIPT_NAME'] ?? ''
        ]
    ]);

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
            'line' => $e->getLine()
        ]
    ]);
}
?>