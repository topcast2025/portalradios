<?php
/**
 * Radios API Endpoint
 * Gerencia operações CRUD para rádios customizadas
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

    // Incluir dependências
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../models/Radio.php';

    // Inicializar banco e model
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('Falha na conexão com o banco de dados');
    }
    
    $radio = new Radio($db);

    // Obter método e path
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path_parts = explode('/', trim($path, '/'));

    // Log da requisição
    error_log("API Request: $method $path");

    // Roteamento
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
                $page = max(1, (int)($_GET['page'] ?? 1));
                $limit = min(max(1, (int)($_GET['limit'] ?? 20)), 100); // Entre 1 e 100
                
                $filters = [
                    'country' => !empty($_GET['country']) ? trim($_GET['country']) : '',
                    'language' => !empty($_GET['language']) ? trim($_GET['language']) : '',
                    'genre' => !empty($_GET['genre']) ? trim($_GET['genre']) : '',
                    'search' => !empty($_GET['search']) ? trim($_GET['search']) : ''
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
                        $ip = $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'unknown';
                        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
                        
                        $result = $radio->registerClick($id, $ip, $userAgent, $referrer);
                        
                    } elseif ($path_parts[3] === 'report') {
                        // Report error
                        $input = json_decode(file_get_contents('php://input'), true);
                        
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new Exception('JSON inválido: ' . json_last_error_msg());
                        }
                        
                        $errorDescription = trim($input['errorDescription'] ?? '');
                        $userEmail = trim($input['userEmail'] ?? '');
                        $userIp = $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'unknown';
                        
                        if (empty($errorDescription)) {
                            throw new Exception('Descrição do erro é obrigatória');
                        }
                        
                        if (strlen($errorDescription) < 10) {
                            throw new Exception('Descrição do erro deve ter pelo menos 10 caracteres');
                        }
                        
                        if (!empty($userEmail) && !filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                            throw new Exception('Email inválido');
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
                    throw new Exception('JSON inválido: ' . json_last_error_msg());
                }
                
                // Validar campos obrigatórios
                $required_fields = [
                    'name' => 'Nome',
                    'email' => 'Email', 
                    'radio_name' => 'Nome da rádio',
                    'stream_url' => 'URL do stream',
                    'brief_description' => 'Descrição breve',
                    'genres' => 'Gêneros',
                    'country' => 'País',
                    'language' => 'Idioma'
                ];
                
                foreach ($required_fields as $field => $label) {
                    if (empty($input[$field])) {
                        throw new Exception("Campo '$label' é obrigatório");
                    }
                }
                
                // Validações específicas
                if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Email inválido');
                }
                
                if (!filter_var($input['stream_url'], FILTER_VALIDATE_URL)) {
                    throw new Exception('URL do stream inválida');
                }
                
                if (!str_starts_with($input['stream_url'], 'https://')) {
                    throw new Exception('URL do stream deve usar HTTPS');
                }
                
                if (!is_array($input['genres']) || empty($input['genres'])) {
                    throw new Exception('Deve selecionar pelo menos um gênero');
                }
                
                if (strlen($input['brief_description']) < 10) {
                    throw new Exception('Descrição breve deve ter pelo menos 10 caracteres');
                }
                
                if (strlen($input['brief_description']) > 500) {
                    throw new Exception('Descrição breve deve ter no máximo 500 caracteres');
                }
                
                // Validar URLs opcionais
                $optional_urls = ['website', 'facebook', 'instagram', 'twitter'];
                foreach ($optional_urls as $url_field) {
                    if (!empty($input[$url_field]) && !filter_var($input[$url_field], FILTER_VALIDATE_URL)) {
                        throw new Exception("URL do campo '$url_field' é inválida");
                    }
                }
                
                $result = $radio->createRadio($input);
            }
            break;

        case 'PUT':
            if (!isset($path_parts[2]) || !is_numeric($path_parts[2])) {
                throw new Exception('ID da rádio é obrigatório para atualização');
            }
            
            $id = (int)$path_parts[2];
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON inválido: ' . json_last_error_msg());
            }
            
            $result = $radio->updateRadio($id, $input);
            break;

        case 'DELETE':
            if (!isset($path_parts[2]) || !is_numeric($path_parts[2])) {
                throw new Exception('ID da rádio é obrigatório para exclusão');
            }
            
            $id = (int)$path_parts[2];
            $result = $radio->deleteRadio($id);
            break;

        default:
            throw new Exception('Método HTTP não permitido: ' . $method);
    }

    // Retornar resultado
    http_response_code(200);
    echo json_encode($result, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log("API Error in radios.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Determinar código de erro HTTP apropriado
    $http_code = 500;
    $error_message = $e->getMessage();
    
    if (strpos($error_message, 'não encontrado') !== false) {
        $http_code = 404;
    } elseif (strpos($error_message, 'obrigatório') !== false || 
              strpos($error_message, 'inválido') !== false ||
              strpos($error_message, 'JSON') !== false) {
        $http_code = 400;
    } elseif (strpos($error_message, 'não permitido') !== false) {
        $http_code = 405;
    }
    
    http_response_code($http_code);
    echo json_encode([
        'success' => false,
        'message' => $error_message,
        'error_code' => $http_code,
        'timestamp' => date('c'),
        'error_details' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]
    ], JSON_PRETTY_PRINT);
}
?>