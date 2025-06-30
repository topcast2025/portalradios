<?php
/**
 * Script de diagnóstico completo do sistema
 */

// Configurações de erro
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico do Sistema - RadioWave</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            color: #f8fafc;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            background: linear-gradient(135deg, #1e293b, #334155);
            border-radius: 12px;
        }
        .section {
            background: #1e293b;
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #334155;
        }
        .section h2 {
            color: #a855f7;
            margin-top: 0;
            border-bottom: 2px solid #a855f7;
            padding-bottom: 10px;
        }
        .status-ok { color: #10b981; }
        .status-error { color: #ef4444; }
        .status-warning { color: #f59e0b; }
        .code-block {
            background: #0f172a;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #334155;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .card {
            background: #334155;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #475569;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #9333ea, #ec4899);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 5px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(147, 51, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Diagnóstico do Sistema RadioWave</h1>
            <p>Análise completa do ambiente e configurações</p>
            <div>
                <a href="index.php" class="btn">← Voltar ao Início</a>
                <a href="api/endpoints/health.php" class="btn">Testar API</a>
            </div>
        </div>

        <!-- 1. Informações do PHP -->
        <div class="section">
            <h2>1. Informações do PHP</h2>
            <div class="grid">
                <div class="card">
                    <strong>Versão do PHP:</strong><br>
                    <span class="<?php echo version_compare(PHP_VERSION, '8.1.0', '>=') ? 'status-ok' : 'status-error'; ?>">
                        <?php echo PHP_VERSION; ?>
                    </span>
                    <?php if (version_compare(PHP_VERSION, '8.1.0', '<')): ?>
                        <br><small class="status-error">⚠️ Requer PHP 8.1 ou superior</small>
                    <?php endif; ?>
                </div>
                <div class="card">
                    <strong>SAPI:</strong><br>
                    <?php echo php_sapi_name(); ?>
                </div>
                <div class="card">
                    <strong>Memória Limite:</strong><br>
                    <?php echo ini_get('memory_limit'); ?>
                </div>
                <div class="card">
                    <strong>Tempo Limite:</strong><br>
                    <?php echo ini_get('max_execution_time'); ?>s
                </div>
            </div>
        </div>

        <!-- 2. Extensões PHP -->
        <div class="section">
            <h2>2. Extensões PHP Necessárias</h2>
            <div class="grid">
                <?php
                $required_extensions = [
                    'pdo' => 'PDO (PHP Data Objects)',
                    'pdo_mysql' => 'PDO MySQL Driver',
                    'gd' => 'GD (Processamento de Imagens)',
                    'json' => 'JSON',
                    'curl' => 'cURL',
                    'mbstring' => 'Multibyte String',
                    'openssl' => 'OpenSSL'
                ];
                
                foreach ($required_extensions as $ext => $desc) {
                    $loaded = extension_loaded($ext);
                    echo '<div class="card">';
                    echo '<strong>' . $desc . ':</strong><br>';
                    echo '<span class="' . ($loaded ? 'status-ok' : 'status-error') . '">';
                    echo $loaded ? '✅ Carregada' : '❌ Não encontrada';
                    echo '</span></div>';
                }
                ?>
            </div>
        </div>

        <!-- 3. Estrutura de Arquivos -->
        <div class="section">
            <h2>3. Estrutura de Arquivos</h2>
            <?php
            $files_to_check = [
                'api/config/database.php' => 'Configuração do banco',
                'api/models/Radio.php' => 'Model Radio',
                'api/endpoints/health.php' => 'Endpoint health',
                'api/endpoints/radios.php' => 'Endpoint radios',
                'api/endpoints/upload.php' => 'Endpoint upload',
                'api/.htaccess' => 'Configuração Apache API',
                '.htaccess' => 'Configuração Apache raiz',
                'uploads/' => 'Diretório de uploads',
                'uploads/logos/' => 'Diretório de logos'
            ];
            
            echo '<div class="grid">';
            foreach ($files_to_check as $file => $desc) {
                $exists = file_exists($file);
                $writable = is_writable($file);
                
                echo '<div class="card">';
                echo '<strong>' . $desc . ':</strong><br>';
                echo '<span class="' . ($exists ? 'status-ok' : 'status-error') . '">';
                echo $exists ? '✅ Existe' : '❌ Não encontrado';
                echo '</span>';
                
                if ($exists && is_dir($file)) {
                    echo '<br><small class="' . ($writable ? 'status-ok' : 'status-warning') . '">';
                    echo $writable ? 'Gravável' : 'Somente leitura';
                    echo '</small>';
                }
                
                if ($exists && !is_dir($file)) {
                    $perms = substr(sprintf('%o', fileperms($file)), -4);
                    echo '<br><small>Permissões: ' . $perms . '</small>';
                }
                echo '</div>';
            }
            echo '</div>';
            ?>
        </div>

        <!-- 4. Teste de Conexão com Banco -->
        <div class="section">
            <h2>4. Teste de Conexão com Banco de Dados</h2>
            <?php
            try {
                if (file_exists('api/config/database.php')) {
                    require_once 'api/config/database.php';
                    
                    echo '<div class="card">';
                    echo '<strong>Arquivo de configuração:</strong> <span class="status-ok">✅ Encontrado</span><br>';
                    
                    $database = new Database();
                    $conn = $database->getConnection();
                    
                    if ($conn) {
                        echo '<strong>Conexão:</strong> <span class="status-ok">✅ Estabelecida</span><br>';
                        
                        // Testar tabelas
                        $stmt = $conn->query("SHOW TABLES");
                        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        echo '<strong>Tabelas encontradas:</strong><br>';
                        if (count($tables) > 0) {
                            echo '<span class="status-ok">✅ ' . count($tables) . ' tabelas: ' . implode(', ', $tables) . '</span>';
                        } else {
                            echo '<span class="status-warning">⚠️ Nenhuma tabela encontrada</span>';
                        }
                        
                        // Testar tabela radios especificamente
                        if (in_array('radios', $tables)) {
                            $stmt = $conn->query("SELECT COUNT(*) as total FROM radios");
                            $result = $stmt->fetch();
                            echo '<br><strong>Registros na tabela radios:</strong> <span class="status-ok">' . $result['total'] . '</span>';
                        }
                    } else {
                        echo '<strong>Conexão:</strong> <span class="status-error">❌ Falhou</span>';
                    }
                    echo '</div>';
                } else {
                    echo '<div class="card">';
                    echo '<span class="status-error">❌ Arquivo de configuração não encontrado</span>';
                    echo '</div>';
                }
            } catch (Exception $e) {
                echo '<div class="card">';
                echo '<strong>Erro de conexão:</strong><br>';
                echo '<span class="status-error">❌ ' . htmlspecialchars($e->getMessage()) . '</span>';
                echo '</div>';
            }
            ?>
        </div>

        <!-- 5. Configurações do Servidor -->
        <div class="section">
            <h2>5. Configurações do Servidor</h2>
            <div class="grid">
                <div class="card">
                    <strong>Servidor Web:</strong><br>
                    <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido'; ?>
                </div>
                <div class="card">
                    <strong>Document Root:</strong><br>
                    <small><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'N/A'; ?></small>
                </div>
                <div class="card">
                    <strong>Script Name:</strong><br>
                    <small><?php echo $_SERVER['SCRIPT_NAME'] ?? 'N/A'; ?></small>
                </div>
                <div class="card">
                    <strong>Request URI:</strong><br>
                    <small><?php echo $_SERVER['REQUEST_URI'] ?? 'N/A'; ?></small>
                </div>
            </div>
        </div>

        <!-- 6. Teste de APIs -->
        <div class="section">
            <h2>6. Teste de Endpoints da API</h2>
            <div class="grid">
                <?php
                $endpoints = [
                    'api/endpoints/health.php' => 'Health Check',
                    'api/endpoints/radios.php' => 'Listagem de Rádios',
                    'api/endpoints/upload.php' => 'Upload de Arquivos'
                ];
                
                foreach ($endpoints as $endpoint => $name) {
                    echo '<div class="card">';
                    echo '<strong>' . $name . ':</strong><br>';
                    
                    if (file_exists($endpoint)) {
                        echo '<span class="status-ok">✅ Arquivo existe</span><br>';
                        echo '<a href="' . $endpoint . '" target="_blank" class="btn" style="font-size: 12px; padding: 5px 10px;">Testar</a>';
                    } else {
                        echo '<span class="status-error">❌ Arquivo não encontrado</span>';
                    }
                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <!-- 7. Logs de Erro -->
        <div class="section">
            <h2>7. Logs de Erro</h2>
            <?php
            $error_log = ini_get('error_log');
            echo '<div class="card">';
            echo '<strong>Arquivo de log:</strong> ' . ($error_log ?: 'Não configurado') . '<br>';
            
            if ($error_log && file_exists($error_log)) {
                echo '<strong>Status:</strong> <span class="status-ok">✅ Existe</span><br>';
                
                $errors = file_get_contents($error_log);
                if ($errors) {
                    $recent_errors = array_slice(explode("\n", $errors), -10);
                    echo '<strong>Últimos erros:</strong><br>';
                    echo '<div class="code-block">';
                    echo htmlspecialchars(implode("\n", array_filter($recent_errors)));
                    echo '</div>';
                } else {
                    echo '<span class="status-ok">✅ Nenhum erro recente</span>';
                }
            } else {
                echo '<strong>Status:</strong> <span class="status-warning">⚠️ Não encontrado</span>';
            }
            echo '</div>';
            ?>
        </div>

        <!-- 8. Recomendações -->
        <div class="section">
            <h2>8. Recomendações</h2>
            <div class="card">
                <h3>Para corrigir problemas comuns:</h3>
                <ol>
                    <li><strong>Erro 500:</strong> Verifique os logs de erro do servidor</li>
                    <li><strong>Banco de dados:</strong> Confirme as credenciais em <code>api/config/database.php</code></li>
                    <li><strong>Permissões:</strong> Defina 755 para diretórios e 644 para arquivos</li>
                    <li><strong>Upload:</strong> Certifique-se que o diretório <code>uploads/</code> tem permissão 777</li>
                    <li><strong>PHP:</strong> Use PHP 8.1 ou superior</li>
                </ol>
                
                <h3>Comandos úteis (via SSH):</h3>
                <div class="code-block">
# Definir permissões corretas
chmod -R 755 .
chmod -R 777 uploads/
chmod 644 *.php
chmod 644 api/*.php
chmod 644 api/*/*.php

# Verificar logs
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log
</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="section" style="text-align: center;">
            <p>Diagnóstico executado em: <?php echo date('d/m/Y H:i:s'); ?></p>
            <a href="index.php" class="btn">← Voltar ao Início</a>
        </div>
    </div>
</body>
</html>