<?php
/**
 * Teste completo do sistema
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Teste Completo do Sistema RadioWave</h1>";

// Teste 1: Verificar PHP
echo "<h2>1. Verificação do PHP</h2>";
echo "Versão: " . PHP_VERSION . "<br>";
echo "SAPI: " . php_sapi_name() . "<br>";

if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
    echo "✅ PHP 8.1+ OK<br>";
} else {
    echo "❌ PHP precisa ser 8.1+<br>";
}

// Teste 2: Extensões
echo "<h2>2. Extensões PHP</h2>";
$extensions = ['pdo', 'pdo_mysql', 'gd', 'json', 'curl'];
foreach ($extensions as $ext) {
    echo extension_loaded($ext) ? "✅ $ext<br>" : "❌ $ext<br>";
}

// Teste 3: Arquivos
echo "<h2>3. Estrutura de Arquivos</h2>";
$files = [
    'api/config/database.php',
    'api/models/Radio.php', 
    'api/endpoints/health.php',
    'api/endpoints/radios.php',
    'uploads/',
    'uploads/logos/'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file<br>";
        if (is_dir($file)) {
            echo "&nbsp;&nbsp;&nbsp;Gravável: " . (is_writable($file) ? "✅" : "❌") . "<br>";
        }
    } else {
        echo "❌ $file<br>";
    }
}

// Teste 4: Banco de dados
echo "<h2>4. Banco de Dados</h2>";
try {
    require_once 'api/config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "✅ Conexão estabelecida<br>";
        
        // Testar tabelas
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "✅ Tabelas: " . implode(', ', $tables) . "<br>";
        
        // Testar dados
        if (in_array('radios', $tables)) {
            $stmt = $conn->query("SELECT COUNT(*) as total FROM radios");
            $result = $stmt->fetch();
            echo "✅ Registros na tabela radios: " . $result['total'] . "<br>";
        }
    } else {
        echo "❌ Falha na conexão<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

// Teste 5: APIs
echo "<h2>5. Teste de APIs</h2>";

// Health check
$health_url = "https://wave.soradios.online/api/health";
$health = @file_get_contents($health_url);
if ($health) {
    echo "✅ Health API funcionando<br>";
    $data = json_decode($health, true);
    if ($data && $data['status'] === 'OK') {
        echo "✅ Status: " . $data['status'] . "<br>";
    }
} else {
    echo "❌ Health API falhou<br>";
}

// Radios API
$radios_url = "https://wave.soradios.online/api/radios";
$radios = @file_get_contents($radios_url);
if ($radios) {
    echo "✅ Radios API funcionando<br>";
    $data = json_decode($radios, true);
    if ($data && isset($data['data']['radios'])) {
        echo "✅ Rádios encontradas: " . count($data['data']['radios']) . "<br>";
    }
} else {
    echo "❌ Radios API falhou<br>";
}

echo "<h2>6. Links Úteis</h2>";
echo '<a href="index.php">🏠 Página Principal</a><br>';
echo '<a href="debug.php">🔧 Diagnóstico Detalhado</a><br>';
echo '<a href="api/health">🩺 Health Check</a><br>';
echo '<a href="api/radios">📻 API Rádios</a><br>';

echo "<hr>";
echo "Teste executado em: " . date('d/m/Y H:i:s');
?>