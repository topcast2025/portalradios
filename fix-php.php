<?php
/**
 * Script de correção para problemas de PHP
 * Detecta e corrige problemas comuns de configuração
 */

header('Content-Type: text/html; charset=utf-8');

// Verificar se PHP está funcionando
$php_working = true;
$issues = [];
$fixes = [];

// 1. Verificar versão do PHP
if (version_compare(PHP_VERSION, '8.1.0', '<')) {
    $issues[] = 'PHP versão ' . PHP_VERSION . ' é muito antiga (requer 8.1+)';
}

// 2. Verificar extensões necessárias
$required_extensions = ['pdo', 'pdo_mysql', 'gd', 'json', 'curl', 'mbstring'];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $issues[] = "Extensão PHP '$ext' não está carregada";
    }
}

// 3. Verificar configurações do PHP
$php_configs = [
    'upload_max_filesize' => '5M',
    'post_max_size' => '5M',
    'max_execution_time' => 60,
    'memory_limit' => '256M'
];

foreach ($php_configs as $config => $recommended) {
    $current = ini_get($config);
    if ($config === 'memory_limit' || $config === 'upload_max_filesize' || $config === 'post_max_size') {
        $current_bytes = return_bytes($current);
        $recommended_bytes = return_bytes($recommended);
        if ($current_bytes < $recommended_bytes) {
            $issues[] = "$config está definido como $current (recomendado: $recommended)";
        }
    } elseif ($current < $recommended) {
        $issues[] = "$config está definido como $current (recomendado: $recommended)";
    }
}

// 4. Verificar permissões de arquivos
$directories_to_check = [
    'uploads' => 0777,
    'uploads/logos' => 0777,
    'api' => 0755,
    'api/config' => 0755,
    'api/models' => 0755,
    'api/endpoints' => 0755
];

foreach ($directories_to_check as $dir => $perm) {
    if (!is_dir($dir)) {
        $issues[] = "Diretório '$dir' não existe";
        $fixes[] = "mkdir('$dir', $perm, true);";
    } elseif (!is_writable($dir) && in_array($dir, ['uploads', 'uploads/logos'])) {
        $issues[] = "Diretório '$dir' não tem permissão de escrita";
        $fixes[] = "chmod('$dir', $perm);";
    }
}

// 5. Verificar arquivos de configuração
$config_files = [
    'api/config/database.php' => 'Configuração do banco de dados',
    'api/models/Radio.php' => 'Model Radio',
    'api/endpoints/health.php' => 'Endpoint health',
    'api/endpoints/radios.php' => 'Endpoint radios',
    'api/endpoints/upload.php' => 'Endpoint upload'
];

foreach ($config_files as $file => $desc) {
    if (!file_exists($file)) {
        $issues[] = "$desc não encontrado ($file)";
    }
}

// 6. Testar conexão com banco
$db_error = null;
try {
    if (file_exists('api/config/database.php')) {
        require_once 'api/config/database.php';
        $database = new Database();
        $conn = $database->getConnection();
        if (!$conn) {
            $db_error = 'Falha na conexão com o banco de dados';
        }
    } else {
        $db_error = 'Arquivo de configuração do banco não encontrado';
    }
} catch (Exception $e) {
    $db_error = 'Erro de banco: ' . $e->getMessage();
}

if ($db_error) {
    $issues[] = $db_error;
}

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correção de PHP - RadioWave</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            min-height: 100vh;
        }
        .card {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(71, 85, 105, 0.5);
            border-radius: 24px;
        }
    </style>
</head>
<body class="text-white">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold mb-4">🔧 Correção de Problemas PHP</h1>
                <p class="text-xl text-gray-400">Diagnóstico e correção automática</p>
                <div class="mt-6">
                    <a href="index.html" class="inline-block px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-semibold hover:from-purple-700 hover:to-pink-700 transition-all mr-4">
                        ← Voltar ao Início
                    </a>
                </div>
            </div>

            <!-- Status Geral -->
            <div class="card p-8 mb-8">
                <div class="text-center">
                    <?php if (empty($issues)): ?>
                    <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-green-400 mb-4">✅ PHP Funcionando Corretamente!</h2>
                    <p class="text-gray-400">Todos os requisitos estão atendidos.</p>
                    <?php else: ?>
                    <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-red-400 mb-4">❌ Problemas Detectados</h2>
                    <p class="text-gray-400">Encontramos <?php echo count($issues); ?> problema(s) que precisam ser corrigidos.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Informações do PHP -->
            <div class="card p-8 mb-8">
                <h3 class="text-2xl font-bold text-white mb-6">📋 Informações do PHP</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-bold text-white mb-3">Configuração Atual:</h4>
                        <ul class="space-y-2 text-sm">
                            <li><strong>Versão:</strong> <?php echo PHP_VERSION; ?></li>
                            <li><strong>SAPI:</strong> <?php echo php_sapi_name(); ?></li>
                            <li><strong>Memória:</strong> <?php echo ini_get('memory_limit'); ?></li>
                            <li><strong>Upload Max:</strong> <?php echo ini_get('upload_max_filesize'); ?></li>
                            <li><strong>Post Max:</strong> <?php echo ini_get('post_max_size'); ?></li>
                            <li><strong>Tempo Limite:</strong> <?php echo ini_get('max_execution_time'); ?>s</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold text-white mb-3">Extensões Carregadas:</h4>
                        <div class="space-y-1 text-sm">
                            <?php foreach ($required_extensions as $ext): ?>
                            <div class="flex justify-between">
                                <span><?php echo $ext; ?></span>
                                <span class="<?php echo extension_loaded($ext) ? 'text-green-400' : 'text-red-400'; ?>">
                                    <?php echo extension_loaded($ext) ? '✅' : '❌'; ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($issues)): ?>
            <!-- Problemas Encontrados -->
            <div class="card p-8 mb-8">
                <h3 class="text-2xl font-bold text-red-400 mb-6">⚠️ Problemas Encontrados</h3>
                <div class="space-y-3">
                    <?php foreach ($issues as $issue): ?>
                    <div class="p-4 bg-red-500/20 border border-red-500/30 rounded-xl">
                        <div class="flex items-start space-x-3">
                            <svg class="h-5 w-5 text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <span class="text-red-300"><?php echo htmlspecialchars($issue); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Soluções Recomendadas -->
            <div class="card p-8 mb-8">
                <h3 class="text-2xl font-bold text-blue-400 mb-6">💡 Soluções Recomendadas</h3>
                
                <div class="space-y-6">
                    <div class="p-6 bg-blue-500/20 border border-blue-500/30 rounded-xl">
                        <h4 class="font-bold text-blue-300 mb-3">1. Usar o Instalador Automático</h4>
                        <p class="text-gray-300 mb-4">A maneira mais fácil de corrigir todos os problemas é usar nosso instalador automático:</p>
                        <a href="install/" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-all">
                            🚀 Usar Instalador Automático
                        </a>
                    </div>

                    <div class="p-6 bg-green-500/20 border border-green-500/30 rounded-xl">
                        <h4 class="font-bold text-green-300 mb-3">2. Correção Manual</h4>
                        <p class="text-gray-300 mb-4">Se preferir corrigir manualmente, execute os seguintes comandos via SSH:</p>
                        <div class="bg-slate-900/50 p-4 rounded-xl overflow-x-auto">
                            <pre class="text-sm text-gray-300"><code># Criar diretórios necessários
mkdir -p uploads/logos
chmod 777 uploads uploads/logos

# Definir permissões corretas
chmod 755 api api/config api/models api/endpoints
chmod 644 api/config/database.php
chmod 644 api/models/Radio.php
chmod 644 api/endpoints/*.php

# Verificar configuração do PHP
php -v
php -m | grep -E "(pdo|mysql|gd|json|curl)"</code></pre>
                        </div>
                    </div>

                    <?php if (!empty($fixes)): ?>
                    <div class="p-6 bg-yellow-500/20 border border-yellow-500/30 rounded-xl">
                        <h4 class="font-bold text-yellow-300 mb-3">3. Correções Automáticas Disponíveis</h4>
                        <p class="text-gray-300 mb-4">Clique no botão abaixo para tentar corrigir automaticamente alguns problemas:</p>
                        <button onclick="applyFixes()" class="inline-block px-6 py-3 bg-yellow-600 hover:bg-yellow-700 text-white rounded-xl font-semibold transition-all">
                            🔧 Aplicar Correções
                        </button>
                        <div id="fix-results" class="mt-4 hidden"></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Próximos Passos -->
            <div class="card p-8">
                <h3 class="text-2xl font-bold text-white mb-6">🎯 Próximos Passos</h3>
                <div class="space-y-4">
                    <?php if (empty($issues)): ?>
                    <div class="p-4 bg-green-500/20 border border-green-500/30 rounded-xl">
                        <h4 class="font-bold text-green-300 mb-2">Sistema Pronto!</h4>
                        <p class="text-gray-300 mb-4">Seu sistema está funcionando corretamente. Você pode:</p>
                        <div class="space-x-4">
                            <a href="index.html" class="inline-block px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition-all">
                                Acessar Sistema
                            </a>
                            <a href="api/health" class="inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-all">
                                Testar API
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="p-4 bg-red-500/20 border border-red-500/30 rounded-xl">
                        <h4 class="font-bold text-red-300 mb-2">Correção Necessária</h4>
                        <p class="text-gray-300 mb-4">Corrija os problemas acima e depois:</p>
                        <div class="space-x-4">
                            <button onclick="location.reload()" class="inline-block px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold transition-all">
                                🔄 Verificar Novamente
                            </button>
                            <a href="install/" class="inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-all">
                                🚀 Usar Instalador
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function applyFixes() {
            const button = event.target;
            const resultsDiv = document.getElementById('fix-results');
            
            button.disabled = true;
            button.textContent = '🔧 Aplicando...';
            
            try {
                const response = await fetch('fix-php.php?action=apply_fixes', {
                    method: 'POST'
                });
                
                const result = await response.text();
                
                resultsDiv.className = 'mt-4 p-4 bg-green-500/20 border border-green-500/30 rounded-xl';
                resultsDiv.innerHTML = `
                    <h5 class="font-bold text-green-300 mb-2">Correções Aplicadas:</h5>
                    <p class="text-gray-300">Algumas correções foram aplicadas. Recarregue a página para verificar.</p>
                    <button onclick="location.reload()" class="mt-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition-all">
                        🔄 Recarregar Página
                    </button>
                `;
                
            } catch (error) {
                resultsDiv.className = 'mt-4 p-4 bg-red-500/20 border border-red-500/30 rounded-xl';
                resultsDiv.innerHTML = `
                    <h5 class="font-bold text-red-300 mb-2">Erro:</h5>
                    <p class="text-gray-300">${error.message}</p>
                `;
            }
            
            resultsDiv.classList.remove('hidden');
            button.disabled = false;
            button.textContent = '🔧 Aplicar Correções';
        }
    </script>
</body>
</html>

<?php
// Aplicar correções se solicitado
if (isset($_GET['action']) && $_GET['action'] === 'apply_fixes' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Criar diretórios necessários
        $dirs = ['uploads', 'uploads/logos'];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            chmod($dir, 0777);
        }
        
        echo json_encode(['success' => true, 'message' => 'Correções aplicadas']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>