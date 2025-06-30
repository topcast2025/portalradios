<?php
// Script de diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnóstico do Sistema</h1>";

// Teste PHP
echo "<h2>1. Versão do PHP</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "PHP SAPI: " . php_sapi_name() . "<br>";

// Teste de extensões
echo "<h2>2. Extensões PHP</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'gd', 'json'];
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? '✅' : '❌';
    echo "$status $ext<br>";
}

// Teste de permissões
echo "<h2>3. Permissões de Arquivos</h2>";
$files_to_check = [
    '../uploads' => 'Pasta de uploads',
    '../uploads/logos' => 'Pasta de logos',
    'config/database.php' => 'Configuração do banco',
    'models/Radio.php' => 'Model Radio',
    'endpoints/health.php' => 'Endpoint health'
];

foreach ($files_to_check as $file => $desc) {
    if (file_exists($file)) {
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        echo "✅ $desc ($file) - Permissões: $perms<br>";
    } else {
        echo "❌ $desc ($file) - Não encontrado<br>";
    }
}

// Teste de conexão com banco
echo "<h2>4. Teste de Conexão com Banco</h2>";
try {
    $dsn = "mysql:host=localhost;port=3306;dbname=soradios_radion;charset=utf8mb4";
    $pdo = new PDO($dsn, 'soradios_radion', 'Ant130915!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexão com banco estabelecida<br>";
    
    // Teste de tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Tabelas encontradas: " . implode(', ', $tables) . "<br>";
    
} catch (Exception $e) {
    echo "❌ Erro de conexão: " . $e->getMessage() . "<br>";
}

// Teste de logs de erro
echo "<h2>5. Logs de Erro</h2>";
$error_log = ini_get('error_log');
echo "Log de erro: $error_log<br>";

if (file_exists($error_log)) {
    $errors = file_get_contents($error_log);
    $recent_errors = array_slice(explode("\n", $errors), -10);
    echo "<pre>" . implode("\n", $recent_errors) . "</pre>";
} else {
    echo "Arquivo de log não encontrado<br>";
}

echo "<h2>6. Variáveis de Ambiente</h2>";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "<br>";
?>