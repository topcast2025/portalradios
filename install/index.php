<?php
/**
 * Sistema de Instalação do RadioWave
 * Acesse: https://wave.soradios.online/install
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar se já foi instalado
$config_file = '../api/config/database.php';
$installed = file_exists($config_file) && file_exists('../.installed');

// Processar instalação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$installed) {
    try {
        $step = $_POST['step'] ?? 1;
        
        if ($step == 1) {
            // Verificar requisitos
            $_SESSION['install_step'] = 2;
        } elseif ($step == 2) {
            // Configurar banco de dados
            $db_config = [
                'host' => $_POST['db_host'] ?? 'localhost',
                'port' => $_POST['db_port'] ?? 3306,
                'database' => $_POST['db_name'] ?? 'soradios_radion',
                'username' => $_POST['db_user'] ?? 'soradios_radion',
                'password' => $_POST['db_pass'] ?? ''
            ];
            
            // Testar conexão
            $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['database']};charset=utf8mb4";
            $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
            
            // Salvar configuração na sessão
            $_SESSION['db_config'] = $db_config;
            $_SESSION['install_step'] = 3;
            
        } elseif ($step == 3) {
            // Criar estrutura do banco
            $db_config = $_SESSION['db_config'];
            $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['database']};charset=utf8mb4";
            $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
            
            // Executar SQL de criação das tabelas
            $sql_file = __DIR__ . '/database.sql';
            if (file_exists($sql_file)) {
                $sql = file_get_contents($sql_file);
                $pdo->exec($sql);
            }
            
            $_SESSION['install_step'] = 4;
            
        } elseif ($step == 4) {
            // Criar arquivos de configuração
            $db_config = $_SESSION['db_config'];
            
            // Criar arquivo de configuração do banco
            $config_content = generateDatabaseConfig($db_config);
            file_put_contents('../api/config/database.php', $config_content);
            
            // Criar diretórios necessários
            createDirectories();
            
            // Criar arquivo .htaccess principal
            createHtaccessFiles();
            
            // Marcar como instalado
            file_put_contents('../.installed', date('Y-m-d H:i:s'));
            
            $_SESSION['install_step'] = 5;
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$current_step = $_SESSION['install_step'] ?? 1;

function generateDatabaseConfig($config) {
    return '<?php
/**
 * Configuração de conexão com banco de dados
 * Gerado automaticamente pelo instalador
 */

// Headers CORS
if (!headers_sent()) {
    header(\'Content-Type: application/json; charset=utf-8\');
    header(\'Access-Control-Allow-Origin: *\');
    header(\'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS\');
    header(\'Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With\');
}

// Handle preflight requests
if ($_SERVER[\'REQUEST_METHOD\'] === \'OPTIONS\') {
    http_response_code(200);
    exit();
}

error_reporting(E_ALL);
ini_set(\'display_errors\', 0);
ini_set(\'log_errors\', 1);

class Database {
    private $host = \'' . $config['host'] . '\';
    private $db_name = \'' . $config['database'] . '\';
    private $username = \'' . $config['username'] . '\';
    private $password = \'' . $config['password'] . '\';
    private $port = ' . $config['port'] . ';
    private $charset = \'utf8mb4\';
    private $conn = null;

    public function getConnection() {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                $this->host,
                $this->port,
                $this->db_name,
                $this->charset
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}",
                PDO::ATTR_TIMEOUT => 30,
                PDO::ATTR_PERSISTENT => false
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            
            if (!headers_sent()) {
                http_response_code(500);
                echo json_encode([
                    \'success\' => false,
                    \'message\' => \'Erro de conexão com o banco de dados\',
                    \'error\' => $exception->getMessage()
                ]);
                exit();
            }
            
            return null;
        }

        return $this->conn;
    }

    public function testConnection() {
        try {
            $conn = $this->getConnection();
            if ($conn === null) {
                return false;
            }
            
            $stmt = $conn->query("SELECT 1 as test");
            $result = $stmt->fetch();
            
            return isset($result[\'test\']) && $result[\'test\'] == 1;
            
        } catch (Exception $e) {
            error_log("Database test connection failed: " . $e->getMessage());
            return false;
        }
    }

    public function getDiagnosticInfo() {
        try {
            $conn = $this->getConnection();
            if ($conn === null) {
                return [\'success\' => false, \'message\' => \'Conexão não estabelecida\'];
            }
            
            $info = [];
            
            $stmt = $conn->query("SELECT VERSION() as version");
            $result = $stmt->fetch();
            $info[\'mysql_version\'] = $result[\'version\'];
            
            $info[\'connection_id\'] = $conn->query("SELECT CONNECTION_ID() as id")->fetch()[\'id\'];
            $info[\'current_user\'] = $conn->query("SELECT USER() as user")->fetch()[\'user\'];
            $info[\'current_database\'] = $conn->query("SELECT DATABASE() as db")->fetch()[\'db\'];
            
            if ($conn->query("SHOW TABLES LIKE \'radios\'")->fetch()) {
                $stmt = $conn->query("SELECT COUNT(*) as total FROM radios");
                $info[\'total_radios\'] = $stmt->fetch()[\'total\'];
            }
            
            return [\'success\' => true, \'data\' => $info];
            
        } catch (Exception $e) {
            error_log("Database diagnostic failed: " . $e->getMessage());
            return [\'success\' => false, \'message\' => \'Erro no diagnóstico: \' . $e->getMessage()];
        }
    }

    public function closeConnection() {
        $this->conn = null;
    }
}
?>';
}

function createDirectories() {
    $dirs = [
        '../uploads',
        '../uploads/logos',
        '../api',
        '../api/config',
        '../api/models',
        '../api/endpoints'
    ];
    
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        chmod($dir, 0777);
    }
}

function createHtaccessFiles() {
    // .htaccess principal
    $main_htaccess = '# RadioWave - Configuração Apache
RewriteEngine On

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# Redirect API calls to api folder
RewriteRule ^api/(.*)$ api/$1 [L]

# Handle React Router (redirect all non-file requests to index.html)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} !^/api/
RewriteCond %{REQUEST_URI} !^/uploads/
RewriteCond %{REQUEST_URI} !\.(php|html|css|js|png|jpg|jpeg|gif|svg|ico)$
RewriteRule . /index.php [L]

# PHP Configuration
php_value upload_max_filesize 5M
php_value post_max_size 5M
php_value max_execution_time 60
php_value memory_limit 256M

# Gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/json
</IfModule>';

    file_put_contents('../.htaccess', $main_htaccess);

    // .htaccess da API
    $api_htaccess = 'RewriteEngine On

# CORS Headers
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"

# Handle preflight requests
RewriteCond %{REQUEST_METHOD} OPTIONS
RewriteRule ^(.*)$ - [R=200,L]

# API Routes
RewriteRule ^health/?$ endpoints/health.php [L,QSA]
RewriteRule ^radios/?$ endpoints/radios.php [L,QSA]
RewriteRule ^radios/([0-9]+)/?$ endpoints/radios.php [L,QSA]
RewriteRule ^radios/([0-9]+)/(click|statistics|report)/?$ endpoints/radios.php [L,QSA]
RewriteRule ^upload-logo/?$ endpoints/upload.php [L,QSA]

# Security
<FilesMatch "\.(php)$">
    Order allow,deny
    Allow from all
</FilesMatch>';

    file_put_contents('../api/.htaccess', $api_htaccess);

    // .htaccess do uploads
    $uploads_htaccess = '# Allow access to uploaded files
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Order allow,deny
    Allow from all
</FilesMatch>

# Deny access to PHP files
<FilesMatch "\.php$">
    Order deny,allow
    Deny from all
</FilesMatch>';

    file_put_contents('../uploads/.htaccess', $uploads_htaccess);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação do RadioWave</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            min-height: 100vh;
        }
        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .card {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(71, 85, 105, 0.5);
            border-radius: 24px;
        }
        .step-active { background: linear-gradient(135deg, #9333ea, #ec4899); }
        .step-completed { background: #10b981; }
        .step-pending { background: #374151; }
    </style>
</head>
<body class="text-white">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-purple-600 to-pink-600 rounded-3xl mb-8">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                    </svg>
                </div>
                <h1 class="text-5xl font-bold mb-4">
                    Instalação do <span class="text-gradient">RadioWave</span>
                </h1>
                <p class="text-xl text-gray-400">
                    Configure seu portal de rádios online em poucos passos
                </p>
            </div>

            <!-- Progress Steps -->
            <div class="mb-12">
                <div class="flex items-center justify-center space-x-4">
                    <?php
                    $steps = [
                        1 => 'Verificação',
                        2 => 'Banco de Dados', 
                        3 => 'Estrutura',
                        4 => 'Configuração',
                        5 => 'Concluído'
                    ];
                    
                    foreach ($steps as $num => $name) {
                        $class = 'step-pending';
                        if ($num < $current_step) $class = 'step-completed';
                        if ($num == $current_step) $class = 'step-active';
                        
                        echo '<div class="flex flex-col items-center">';
                        echo '<div class="w-12 h-12 rounded-full ' . $class . ' flex items-center justify-center text-white font-bold mb-2">' . $num . '</div>';
                        echo '<span class="text-sm text-gray-400">' . $name . '</span>';
                        echo '</div>';
                        
                        if ($num < count($steps)) {
                            echo '<div class="w-16 h-1 bg-gray-600 mt-6"></div>';
                        }
                    }
                    ?>
                </div>
            </div>

            <?php if ($installed): ?>
            <!-- Já Instalado -->
            <div class="card p-8 text-center">
                <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-white mb-4">Sistema Já Instalado!</h2>
                <p class="text-gray-400 mb-8">O RadioWave já foi configurado e está pronto para uso.</p>
                <div class="space-x-4">
                    <a href="../index.php" class="inline-block px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-semibold hover:from-purple-700 hover:to-pink-700 transition-all">
                        Acessar Sistema
                    </a>
                    <a href="../debug.php" class="inline-block px-8 py-3 bg-slate-700 text-white rounded-xl font-semibold hover:bg-slate-600 transition-all">
                        Diagnóstico
                    </a>
                </div>
            </div>

            <?php elseif ($current_step == 1): ?>
            <!-- Passo 1: Verificação de Requisitos -->
            <div class="card p-8">
                <h2 class="text-3xl font-bold text-white mb-6">Verificação de Requisitos</h2>
                
                <div class="space-y-4 mb-8">
                    <?php
                    $requirements = [
                        'PHP 8.1+' => version_compare(PHP_VERSION, '8.1.0', '>='),
                        'PDO Extension' => extension_loaded('pdo'),
                        'PDO MySQL' => extension_loaded('pdo_mysql'),
                        'GD Extension' => extension_loaded('gd'),
                        'JSON Extension' => extension_loaded('json'),
                        'Directory Writable' => is_writable('../')
                    ];
                    
                    $all_ok = true;
                    foreach ($requirements as $req => $status) {
                        $all_ok = $all_ok && $status;
                        echo '<div class="flex items-center justify-between p-4 bg-slate-700/30 rounded-xl">';
                        echo '<span>' . $req . '</span>';
                        echo '<span class="' . ($status ? 'text-green-400' : 'text-red-400') . '">';
                        echo $status ? '✅ OK' : '❌ Erro';
                        echo '</span></div>';
                    }
                    ?>
                </div>

                <?php if ($all_ok): ?>
                <form method="POST">
                    <input type="hidden" name="step" value="1">
                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-semibold hover:from-purple-700 hover:to-pink-700 transition-all">
                        Continuar Instalação
                    </button>
                </form>
                <?php else: ?>
                <div class="p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400">
                    <strong>⚠️ Requisitos não atendidos!</strong><br>
                    Corrija os problemas acima antes de continuar.
                </div>
                <?php endif; ?>
            </div>

            <?php elseif ($current_step == 2): ?>
            <!-- Passo 2: Configuração do Banco -->
            <div class="card p-8">
                <h2 class="text-3xl font-bold text-white mb-6">Configuração do Banco de Dados</h2>
                
                <?php if (isset($error)): ?>
                <div class="mb-6 p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400">
                    <strong>Erro:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <input type="hidden" name="step" value="2">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-3">Host do Banco</label>
                            <input type="text" name="db_host" value="localhost" required
                                   class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-3">Porta</label>
                            <input type="number" name="db_port" value="3306" required
                                   class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-3">Nome do Banco</label>
                        <input type="text" name="db_name" value="soradios_radion" required
                               class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-3">Usuário</label>
                            <input type="text" name="db_user" value="soradios_radion" required
                                   class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-3">Senha</label>
                            <input type="password" name="db_pass" value="Ant130915!" required
                                   class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-semibold hover:from-purple-700 hover:to-pink-700 transition-all">
                        Testar Conexão e Continuar
                    </button>
                </form>
            </div>

            <?php elseif ($current_step == 3): ?>
            <!-- Passo 3: Criação da Estrutura -->
            <div class="card p-8">
                <h2 class="text-3xl font-bold text-white mb-6">Criação da Estrutura do Banco</h2>
                <p class="text-gray-400 mb-8">Agora vamos criar as tabelas necessárias no banco de dados.</p>
                
                <form method="POST">
                    <input type="hidden" name="step" value="3">
                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-semibold hover:from-purple-700 hover:to-pink-700 transition-all">
                        Criar Estrutura do Banco
                    </button>
                </form>
            </div>

            <?php elseif ($current_step == 4): ?>
            <!-- Passo 4: Configuração Final -->
            <div class="card p-8">
                <h2 class="text-3xl font-bold text-white mb-6">Configuração Final</h2>
                <p class="text-gray-400 mb-8">Criando arquivos de configuração e definindo permissões.</p>
                
                <form method="POST">
                    <input type="hidden" name="step" value="4">
                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-semibold hover:from-purple-700 hover:to-pink-700 transition-all">
                        Finalizar Instalação
                    </button>
                </form>
            </div>

            <?php elseif ($current_step == 5): ?>
            <!-- Passo 5: Concluído -->
            <div class="card p-8 text-center">
                <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-white mb-4">Instalação Concluída!</h2>
                <p class="text-gray-400 mb-8">O RadioWave foi instalado com sucesso e está pronto para uso.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="p-4 bg-slate-700/30 rounded-xl">
                        <h3 class="font-bold text-white mb-2">Sistema Principal</h3>
                        <a href="../index.php" class="text-purple-400 hover:text-purple-300">Acessar</a>
                    </div>
                    <div class="p-4 bg-slate-700/30 rounded-xl">
                        <h3 class="font-bold text-white mb-2">API Health</h3>
                        <a href="../api/health" class="text-purple-400 hover:text-purple-300">Testar</a>
                    </div>
                    <div class="p-4 bg-slate-700/30 rounded-xl">
                        <h3 class="font-bold text-white mb-2">Diagnóstico</h3>
                        <a href="../debug.php" class="text-purple-400 hover:text-purple-300">Verificar</a>
                    </div>
                </div>

                <div class="space-x-4">
                    <a href="../index.php" class="inline-block px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-semibold hover:from-purple-700 hover:to-pink-700 transition-all">
                        Acessar RadioWave
                    </a>
                    <a href="../cadastro-radio.php" class="inline-block px-8 py-3 bg-slate-700 text-white rounded-xl font-semibold hover:bg-slate-600 transition-all">
                        Cadastrar Rádio
                    </a>
                </div>

                <div class="mt-8 p-4 bg-blue-500/20 border border-blue-500/30 rounded-xl text-blue-400">
                    <strong>💡 Próximos Passos:</strong><br>
                    1. Acesse o sistema principal<br>
                    2. Cadastre suas primeiras rádios<br>
                    3. Configure as permissões de upload se necessário<br>
                    4. Remova a pasta /install por segurança
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
```