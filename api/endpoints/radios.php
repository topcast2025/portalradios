<?php
require_once '../models/Radio.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $radio = new Radio($db);

    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path_parts = explode('/', trim($path, '/'));

    switch ($method) {
        case 'GET':
            if (isset($path_parts[2]) && is_numeric($path_parts[2])) {
                // Get radio by ID
                $id = $path_parts[2];
                
                if (isset($path_parts[3]) && $path_parts[3] === 'statistics') {
                    // Get statistics
                    $result = $radio->getStatistics($id);
                } else {
                    // Get radio details
                    $result = $radio->getRadioById($id);
                }
            } else {
                // Get all radios with filters
                $page = $_GET['page'] ?? 1;
                $limit = $_GET['limit'] ?? 20;
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
                $id = $path_parts[2];
                
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
                        $errorDescription = $input['errorDescription'] ?? '';
                        $userEmail = $input['userEmail'] ?? '';
                        $userIp = $_SERVER['REMOTE_ADDR'] ?? '';
                        
                        if (empty($errorDescription)) {
                            throw new Exception('Descrição do erro é obrigatória');
                        }
                        
                        $result = $radio->reportError($id, $errorDescription, $userEmail, $userIp);
                    }
                }
            } else {
                // Create new radio
                $input = json_decode(file_get_contents('php://input'), true);
                
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
                if (!filter_var($input['stream_url'], FILTER_VALIDATE_URL) || !str_starts_with($input['stream_url'], 'https://')) {
                    throw new Exception('URL do stream deve ser HTTPS válida');
                }
                
                $result = $radio->createRadio($input);
            }
            break;

        default:
            throw new Exception('Método não permitido');
    }

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>