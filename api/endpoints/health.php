<?php
require_once '../config/database.php';

try {
    // Test database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Test query
    $stmt = $db->query("SELECT 1");
    $stmt->fetch();
    
    echo json_encode([
        'status' => 'OK',
        'timestamp' => date('c'),
        'version' => '1.0.0',
        'environment' => 'production',
        'database' => 'connected',
        'php_version' => PHP_VERSION
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'ERROR',
        'timestamp' => date('c'),
        'message' => 'Database connection failed',
        'php_version' => PHP_VERSION
    ]);
}
?>