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
    // Verificar arquivos necessários
    $required_files = [
        __DIR__ . '/../config/database.php',
        __DIR__ . '/../models/Radio.php'
    ];

    foreach ($required_files as $file) {
        if (!file_exists($file)) {
            throw new Exception("Arquivo necessário não encontrado: " . basename($file));
        }
    }

    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../models/Radio.php';

    $database = new Database();
    $db = $database->getConnection();
    $radio = new Radio($db);

    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path_parts = explode('/', trim($path, '/'));

    // Log da requisição
    error_log("API Request: $method $path");

    switch ($method) {
        case 'GET':
            if (isset($path_parts[2]) && is_numeric($path_parts[2])) {
                // Get radio by ID
                $id = (int)$path_parts[2];
                
                if (isset($path_parts[3]) && $path_parts[3] === 'statistics') {
                    // Get statistics
                    $result = $radio->getStatistics($id);
                } else {
                    // Get radio details
                    $result = $radio->getRadioById($id);
                }
            } else {
                // Get all radios with filters
                $page = (int)($_GET['page'] ?? 1);
                $limit = min((int)($_GET['limit'] ?? 20), 100); // Max 100
                $filters = [
                    'country' => $_GET['country'] ?? '',
                    'language' => $_GET['language'] ?? '',
                    'genre' => $_GET['genre'] ?? '',
                    'search' => $_GET['search'] ?? ''
                ];
                
                $result = $radio->getRadios($page, $limit, $filters);
            }
            break;

        case 'POST':
            if (isset($path_parts[2]) && is_numeric($path_parts[2])) {
                $id = (int)$path_parts[2];
                
                if (isset($path_parts[3])) {
                    if ($path_parts[3] === 'click') {
                        // Register click
                        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
                        
                        $result = $radio->registerClick($id, $ip, $userAgent, $referrer);
                    } elseif ($path_parts[3] === 'report') {
                        // Report error
                        $input = json_decode(file_get_contents('php://input'), true);
                        
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new Exception('JSON inválido');
                        }
                        
                        $errorDescription = trim($input['errorDescription'] ?? '');
                        $userEmail = trim($input['userEmail'] ?? '');
                        $userIp = $_SERVER['REMOTE_ADDR'] ?? '';
                        
                        if (empty($errorDescription)) {
                            throw new Exception('Descrição do erro é obrigatória');
                        }
                        
                        $result = $radio->reportError($id, $errorDescription, $userEmail, $userIp);
                    } else {
                        throw new Exception('Endpoint não encontrado');
                    }
                } else {
                    throw new Exception('Ação não especificada');
                }
            } else {
                // Create new radio
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('JSON inválido');
                }
                
                // Validate required fields
                $required_fields = ['name', 'email', 'radio_name', 'stream_url', 'brief_description', 'genres', 'country', 'language'];
                foreach ($required_fields as $field) {
                    if (empty($input[$field])) {
                        throw new Exception("Campo '$field' é obrigatório");
                    }
                }
                
                // Validate email
                if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Email inválido');
                }
                
                // Validate URL
                if (!filter_var($input['stream_url'], FILTER_VALIDATE_URL)) {
                    throw new Exception('URL do stream inválida');
                }
                
                if (!str_starts_with($input['stream_url'], 'https://')) {
                    throw new Exception('URL do stream deve usar HTTPS');
                }
                
                $result = $radio->createRadio($input);
            }
            break;

        default:
            throw new Exception('Método não permitido');
    }

    echo json_encode($result);

} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_details' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>