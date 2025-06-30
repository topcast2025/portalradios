<?php
/**
 * Script de limpeza pós-instalação
 * Remove arquivos temporários e configura permissões finais
 */

header('Content-Type: application/json; charset=utf-8');

try {
    $results = [
        'success' => true,
        'timestamp' => date('c'),
        'actions' => []
    ];
    
    // 1. Verificar se a instalação foi concluída
    if (!file_exists('../.installed')) {
        throw new Exception('Sistema não foi instalado ainda');
    }
    
    // 2. Configurar permissões corretas
    $directories = [
        '../uploads' => 0777,
        '../uploads/logos' => 0777,
        '../api' => 0755,
        '../api/config' => 0755,
        '../api/models' => 0755,
        '../api/endpoints' => 0755
    ];
    
    foreach ($directories as $dir => $perm) {
        if (is_dir($dir)) {
            chmod($dir, $perm);
            $results['actions'][] = "Permissão $perm definida para $dir";
        }
    }
    
    // 3. Configurar permissões de arquivos
    $files = [
        '../api/config/database.php' => 0644,
        '../api/models/Radio.php' => 0644,
        '../api/endpoints/health.php' => 0644,
        '../api/endpoints/radios.php' => 0644,
        '../api/endpoints/upload.php' => 0644,
        '../.htaccess' => 0644,
        '../api/.htaccess' => 0644,
        '../uploads/.htaccess' => 0644
    ];
    
    foreach ($files as $file => $perm) {
        if (file_exists($file)) {
            chmod($file, $perm);
            $results['actions'][] = "Permissão $perm definida para $file";
        }
    }
    
    // 4. Criar arquivo de versão
    $version_info = [
        'version' => '2.0.0',
        'installed_at' => date('c'),
        'php_version' => PHP_VERSION,
        'mysql_version' => 'Unknown'
    ];
    
    // Tentar obter versão do MySQL
    try {
        require_once '../api/config/database.php';
        $database = new Database();
        $conn = $database->getConnection();
        if ($conn) {
            $stmt = $conn->query("SELECT VERSION() as version");
            $mysql_info = $stmt->fetch();
            $version_info['mysql_version'] = $mysql_info['version'];
        }
    } catch (Exception $e) {
        // Ignorar erro
    }
    
    file_put_contents('../version.json', json_encode($version_info, JSON_PRETTY_PRINT));
    $results['actions'][] = 'Arquivo de versão criado';
    
    // 5. Criar arquivo de configuração de segurança
    $security_config = '<?php
// Configurações de segurança do RadioWave
// Gerado automaticamente em ' . date('Y-m-d H:i:s') . '

// Bloquear acesso direto
if (!defined("RADIOWAVE_SYSTEM")) {
    http_response_code(403);
    exit("Acesso negado");
}

// Configurações de segurança
define("RADIOWAVE_VERSION", "2.0.0");
define("RADIOWAVE_INSTALLED", true);
define("RADIOWAVE_INSTALL_DATE", "' . date('c') . '");

// Headers de segurança padrão
function radiowave_security_headers() {
    if (!headers_sent()) {
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
    }
}

// Validação de entrada
function radiowave_sanitize_input($input) {
    if (is_array($input)) {
        return array_map("radiowave_sanitize_input", $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, "UTF-8");
}

// Verificar se é requisição AJAX
function radiowave_is_ajax() {
    return !empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && 
           strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest";
}
?>';
    
    file_put_contents('../security-config.php', $security_config);
    $results['actions'][] = 'Configuração de segurança criada';
    
    // 6. Otimizar banco de dados
    try {
        require_once '../api/config/database.php';
        $database = new Database();
        $conn = $database->getConnection();
        
        if ($conn) {
            // Otimizar tabelas
            $tables = ['radios', 'radio_statistics', 'radio_clicks', 'radio_error_reports', 'file_uploads', 'system_settings'];
            foreach ($tables as $table) {
                $conn->exec("OPTIMIZE TABLE `$table`");
            }
            $results['actions'][] = 'Tabelas do banco otimizadas';
            
            // Atualizar estatísticas
            $conn->exec("ANALYZE TABLE `radios`, `radio_statistics`, `radio_clicks`");
            $results['actions'][] = 'Estatísticas do banco atualizadas';
        }
    } catch (Exception $e) {
        $results['actions'][] = 'Aviso: Não foi possível otimizar o banco - ' . $e->getMessage();
    }
    
    // 7. Criar arquivo de backup da configuração
    $backup_dir = '../backups';
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    $backup_file = $backup_dir . '/config-backup-' . date('Y-m-d-H-i-s') . '.json';
    $backup_data = [
        'timestamp' => date('c'),
        'version' => '2.0.0',
        'php_version' => PHP_VERSION,
        'installation_completed' => true,
        'directories_created' => array_keys($directories),
        'files_configured' => array_keys($files)
    ];
    
    file_put_contents($backup_file, json_encode($backup_data, JSON_PRETTY_PRINT));
    $results['actions'][] = 'Backup da configuração criado';
    
    // 8. Marcar limpeza como concluída
    file_put_contents('../.cleanup-completed', date('c'));
    $results['actions'][] = 'Limpeza marcada como concluída';
    
    $results['message'] = 'Limpeza pós-instalação concluída com sucesso';
    
    echo json_encode($results, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
?>