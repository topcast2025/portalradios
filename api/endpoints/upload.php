<?php
require_once '../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    if (!isset($_FILES['logo'])) {
        throw new Exception('Nenhum arquivo enviado');
    }

    $file = $_FILES['logo'];
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erro no upload do arquivo');
    }

    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        throw new Exception('Arquivo muito grande. Tamanho máximo: 5MB');
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Tipo de arquivo não permitido. Use apenas imagens (JPEG, PNG, GIF, WebP)');
    }

    // Create upload directory
    $upload_dir = '../uploads/logos/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Erro ao salvar arquivo');
    }

    // Process image with GD (resize and optimize)
    $image_info = getimagesize($filepath);
    if ($image_info === false) {
        unlink($filepath);
        throw new Exception('Arquivo não é uma imagem válida');
    }

    // Create image resource
    switch ($image_info[2]) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($filepath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($filepath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($filepath);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($filepath);
            break;
        default:
            unlink($filepath);
            throw new Exception('Formato de imagem não suportado');
    }

    // Resize to 400x400
    $resized = imagecreatetruecolor(400, 400);
    imagecopyresampled($resized, $source, 0, 0, 0, 0, 400, 400, imagesx($source), imagesy($source));

    // Save as JPEG
    $final_filename = pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
    $final_filepath = $upload_dir . $final_filename;
    
    imagejpeg($resized, $final_filepath, 85);
    
    // Clean up
    imagedestroy($source);
    imagedestroy($resized);
    
    // Remove original if different
    if ($filepath !== $final_filepath) {
        unlink($filepath);
    }

    // Save upload record to database
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "INSERT INTO file_uploads (original_filename, stored_filename, file_path, file_size, mime_type, upload_ip) 
              VALUES (:original_filename, :stored_filename, :file_path, :file_size, :mime_type, :upload_ip)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':original_filename', $file['name']);
    $stmt->bindParam(':stored_filename', $final_filename);
    $stmt->bindParam(':file_path', $final_filepath);
    $stmt->bindValue(':file_size', filesize($final_filepath));
    $stmt->bindValue(':mime_type', 'image/jpeg');
    $stmt->bindValue(':upload_ip', $_SERVER['REMOTE_ADDR'] ?? '');
    $stmt->execute();

    // Return the URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $logoUrl = $protocol . '://' . $host . '/uploads/logos/' . $final_filename;

    echo json_encode([
        'success' => true,
        'message' => 'Logo enviada com sucesso',
        'data' => [
            'logoUrl' => $logoUrl,
            'filename' => $final_filename,
            'size' => filesize($final_filepath)
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>