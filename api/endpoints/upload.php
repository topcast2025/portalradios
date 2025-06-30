<?php
/**
 * Upload de arquivos (logos)
 */

// Configurações de erro
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Verificar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido. Use POST.');
    }

    // Verificar se arquivo foi enviado
    if (!isset($_FILES['logo'])) {
        throw new Exception('Nenhum arquivo enviado. Use o campo "logo".');
    }

    $file = $_FILES['logo'];
    
    // Verificar erros de upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'Arquivo muito grande (limite do servidor)',
            UPLOAD_ERR_FORM_SIZE => 'Arquivo muito grande (limite do formulário)',
            UPLOAD_ERR_PARTIAL => 'Upload incompleto',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo enviado',
            UPLOAD_ERR_NO_TMP_DIR => 'Diretório temporário não encontrado',
            UPLOAD_ERR_CANT_WRITE => 'Erro de escrita no disco',
            UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
        ];
        
        $error_msg = $error_messages[$file['error']] ?? 'Erro desconhecido no upload';
        throw new Exception($error_msg);
    }

    // Verificar tamanho (5MB máximo)
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        throw new Exception('Arquivo muito grande. Tamanho máximo: 5MB');
    }

    // Verificar tipo de arquivo
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        throw new Exception('Tipo de arquivo não permitido. Use apenas: JPEG, PNG, GIF, WebP');
    }

    // Verificar se é realmente uma imagem
    $image_info = getimagesize($file['tmp_name']);
    if ($image_info === false) {
        throw new Exception('Arquivo não é uma imagem válida');
    }

    // Criar diretório de upload se não existir
    $upload_dir = __DIR__ . '/../../uploads/logos/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            throw new Exception('Erro ao criar diretório de upload');
        }
    }

    // Verificar permissões de escrita
    if (!is_writable($upload_dir)) {
        throw new Exception('Diretório de upload sem permissão de escrita');
    }

    // Gerar nome único para o arquivo
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('logo_', true) . '.' . strtolower($extension);
    $filepath = $upload_dir . $filename;

    // Processar imagem com GD
    try {
        // Criar resource da imagem baseado no tipo
        switch ($image_info[2]) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($file['tmp_name']);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($file['tmp_name']);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($file['tmp_name']);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($file['tmp_name']);
                break;
            default:
                throw new Exception('Formato de imagem não suportado');
        }

        if (!$source) {
            throw new Exception('Erro ao processar imagem');
        }

        // Redimensionar para 400x400 mantendo proporção
        $original_width = imagesx($source);
        $original_height = imagesy($source);
        $target_size = 400;

        // Calcular dimensões mantendo proporção
        if ($original_width > $original_height) {
            $new_width = $target_size;
            $new_height = ($original_height * $target_size) / $original_width;
        } else {
            $new_height = $target_size;
            $new_width = ($original_width * $target_size) / $original_height;
        }

        // Criar imagem redimensionada
        $resized = imagecreatetruecolor($target_size, $target_size);
        
        // Fundo transparente para PNG
        if ($image_info[2] == IMAGETYPE_PNG) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefill($resized, 0, 0, $transparent);
        } else {
            // Fundo branco para outros formatos
            $white = imagecolorallocate($resized, 255, 255, 255);
            imagefill($resized, 0, 0, $white);
        }

        // Centralizar imagem
        $x = ($target_size - $new_width) / 2;
        $y = ($target_size - $new_height) / 2;

        // Redimensionar e centralizar
        imagecopyresampled(
            $resized, $source,
            $x, $y, 0, 0,
            $new_width, $new_height,
            $original_width, $original_height
        );

        // Salvar como JPEG com qualidade 85
        $final_filename = pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
        $final_filepath = $upload_dir . $final_filename;
        
        if (!imagejpeg($resized, $final_filepath, 85)) {
            throw new Exception('Erro ao salvar imagem processada');
        }

        // Limpar recursos
        imagedestroy($source);
        imagedestroy($resized);

    } catch (Exception $e) {
        throw new Exception('Erro no processamento da imagem: ' . $e->getMessage());
    }

    // Salvar registro no banco de dados
    try {
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            $query = "INSERT INTO file_uploads (original_filename, stored_filename, file_path, file_size, mime_type, upload_ip) 
                      VALUES (:original_filename, :stored_filename, :file_path, :file_size, :mime_type, :upload_ip)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':original_filename', $file['name']);
            $stmt->bindParam(':stored_filename', $final_filename);
            $stmt->bindParam(':file_path', $final_filepath);
            $stmt->bindValue(':file_size', filesize($final_filepath));
            $stmt->bindValue(':mime_type', 'image/jpeg');
            $stmt->bindValue(':upload_ip', $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'unknown');
            $stmt->execute();
        }
    } catch (Exception $e) {
        error_log("Database error in upload: " . $e->getMessage());
        // Não falhar o upload por erro de banco
    }

    // Gerar URL da imagem
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $logoUrl = $protocol . '://' . $host . '/uploads/logos/' . $final_filename;

    // Resposta de sucesso
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Logo enviada com sucesso',
        'data' => [
            'logoUrl' => $logoUrl,
            'filename' => $final_filename,
            'size' => filesize($final_filepath),
            'dimensions' => '400x400'
        ]
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log("Upload error: " . $e->getMessage());
    
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
?>