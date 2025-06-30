<?php
// Script de teste da API
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste da API</h1>";

// Teste 1: Health Check
echo "<h2>1. Health Check</h2>";
$health_url = "https://wave.soradios.online/api/health";
$health_response = @file_get_contents($health_url);

if ($health_response) {
    echo "✅ Health check funcionando<br>";
    echo "<pre>" . htmlspecialchars($health_response) . "</pre>";
} else {
    echo "❌ Health check falhou<br>";
    $error = error_get_last();
    if ($error) {
        echo "Erro: " . htmlspecialchars($error['message']) . "<br>";
    }
}

// Teste 2: Listar rádios
echo "<h2>2. Listar Rádios</h2>";
$radios_url = "https://wave.soradios.online/api/radios";
$radios_response = @file_get_contents($radios_url);

if ($radios_response) {
    echo "✅ Listagem de rádios funcionando<br>";
    $data = json_decode($radios_response, true);
    if ($data && isset($data['data']['radios'])) {
        echo "Total de rádios: " . count($data['data']['radios']) . "<br>";
    }
} else {
    echo "❌ Listagem de rádios falhou<br>";
}

// Teste 3: Verificar estrutura de arquivos
echo "<h2>3. Estrutura de Arquivos</h2>";
$files_to_check = [
    'api/config/database.php',
    'api/models/Radio.php',
    'api/endpoints/health.php',
    'api/endpoints/radios.php',
    'api/.htaccess',
    'uploads/',
    'uploads/logos/'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ $file<br>";
    } else {
        echo "❌ $file (não encontrado)<br>";
    }
}

echo "<h2>4. Informações do Servidor</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
?>