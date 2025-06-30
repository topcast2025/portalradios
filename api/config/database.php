<?php
/**
 * Configuração de conexão com banco de dados
 * RadioWave - Portal de Rádios Online
 */

// Configuração de headers CORS
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Error reporting para debug (desabilitar em produção)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Não mostrar erros na produção
ini_set('log_errors', 1);

/**
 * Classe para gerenciar conexão com banco de dados
 */
class Database {
    // Configurações do banco de dados
    private $host = 'localhost';
    private $db_name = 'soradios_radion';
    private $username = 'soradios_radion';
    private $password = 'Ant130915!';
    private $port = 3306;
    private $charset = 'utf8mb4';
    private $conn = null;

    /**
     * Estabelece conexão com o banco de dados
     * @return PDO|null
     */
    public function getConnection() {
        // Retorna conexão existente se já estabelecida
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            // Monta DSN (Data Source Name)
            $dsn = sprintf(
                "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                $this->host,
                $this->port,
                $this->db_name,
                $this->charset
            );
            
            // Opções de configuração do PDO
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}",
                PDO::ATTR_TIMEOUT => 30,
                PDO::ATTR_PERSISTENT => false
            ];

            // Estabelece conexão
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            // Log de sucesso
            error_log("Database connection established successfully");
            
        } catch(PDOException $exception) {
            // Log do erro
            error_log("Database connection error: " . $exception->getMessage());
            
            // Em caso de erro, retorna resposta JSON
            if (!headers_sent()) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro de conexão com o banco de dados',
                    'error' => $exception->getMessage(),
                    'code' => $exception->getCode()
                ]);
                exit();
            }
            
            return null;
        }

        return $this->conn;
    }

    /**
     * Testa a conexão com o banco de dados
     * @return bool
     */
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            if ($conn === null) {
                return false;
            }
            
            // Executa query simples para testar
            $stmt = $conn->query("SELECT 1 as test");
            $result = $stmt->fetch();
            
            return isset($result['test']) && $result['test'] == 1;
            
        } catch (Exception $e) {
            error_log("Database test connection failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica se as tabelas necessárias existem
     * @return array
     */
    public function checkTables() {
        try {
            $conn = $this->getConnection();
            if ($conn === null) {
                return ['success' => false, 'message' => 'Conexão não estabelecida'];
            }
            
            // Lista de tabelas necessárias
            $required_tables = [
                'radios',
                'radio_statistics', 
                'radio_clicks',
                'radio_error_reports',
                'file_uploads'
            ];
            
            // Verifica quais tabelas existem
            $stmt = $conn->query("SHOW TABLES");
            $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $missing_tables = array_diff($required_tables, $existing_tables);
            
            return [
                'success' => empty($missing_tables),
                'existing' => $existing_tables,
                'missing' => $missing_tables,
                'total_required' => count($required_tables),
                'total_existing' => count($existing_tables)
            ];
            
        } catch (Exception $e) {
            error_log("Database check tables failed: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Erro ao verificar tabelas: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Executa query de diagnóstico
     * @return array
     */
    public function getDiagnosticInfo() {
        try {
            $conn = $this->getConnection();
            if ($conn === null) {
                return ['success' => false, 'message' => 'Conexão não estabelecida'];
            }
            
            $info = [];
            
            // Versão do MySQL
            $stmt = $conn->query("SELECT VERSION() as version");
            $result = $stmt->fetch();
            $info['mysql_version'] = $result['version'];
            
            // Informações da conexão
            $info['connection_id'] = $conn->query("SELECT CONNECTION_ID() as id")->fetch()['id'];
            $info['current_user'] = $conn->query("SELECT USER() as user")->fetch()['user'];
            $info['current_database'] = $conn->query("SELECT DATABASE() as db")->fetch()['db'];
            
            // Verificar tabelas
            $tables_info = $this->checkTables();
            $info['tables'] = $tables_info;
            
            // Contar registros na tabela radios (se existir)
            if (in_array('radios', $tables_info['existing'] ?? [])) {
                $stmt = $conn->query("SELECT COUNT(*) as total FROM radios");
                $info['total_radios'] = $stmt->fetch()['total'];
            }
            
            return ['success' => true, 'data' => $info];
            
        } catch (Exception $e) {
            error_log("Database diagnostic failed: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Erro no diagnóstico: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Fecha a conexão
     */
    public function closeConnection() {
        $this->conn = null;
    }
}

// Se este arquivo for acessado diretamente, executa teste
if (basename($_SERVER['PHP_SELF']) === 'database.php') {
    header('Content-Type: application/json');
    
    try {
        $database = new Database();
        $diagnostic = $database->getDiagnosticInfo();
        
        echo json_encode([
            'success' => true,
            'message' => 'Teste de conexão executado',
            'timestamp' => date('c'),
            'diagnostic' => $diagnostic
        ], JSON_PRETTY_PRINT);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erro no teste de conexão',
            'error' => $e->getMessage(),
            'timestamp' => date('c')
        ], JSON_PRETTY_PRINT);
    }
}
?>