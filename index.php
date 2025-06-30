<?php
/**
 * RadioWave - Portal de Rádios Online
 * Página principal do sistema
 */

// Configurações de erro para desenvolvimento
error_reporting(E_ALL);
ini_set('display_errors', 0); // Desabilitar em produção
ini_set('log_errors', 1);

// Headers de segurança
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Verificar se a API está funcionando
$api_status = false;
$database_info = null;
$external_api_status = false;

try {
    if (file_exists('api/config/database.php')) {
        require_once 'api/config/database.php';
        $database = new Database();
        $api_status = $database->testConnection();
        if ($api_status) {
            $diagnostic = $database->getDiagnosticInfo();
            $database_info = $diagnostic['success'] ? $diagnostic['data'] : null;
        }
    }
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
}

// Testar API externa
try {
    $external_test = @file_get_contents('https://de1.api.radio-browser.info/json/stations/topvote/1', false, stream_context_create([
        'http' => ['timeout' => 5, 'user_agent' => 'RadioWave/2.0.0']
    ]));
    $external_api_status = ($external_test !== false);
} catch (Exception $e) {
    error_log("External API test error: " . $e->getMessage());
}

// Verificar estrutura de diretórios
$uploads_writable = is_writable('uploads/');
$logos_writable = is_writable('uploads/logos/');

// Criar diretórios se não existirem
if (!is_dir('uploads/')) {
    @mkdir('uploads/', 0777, true);
}
if (!is_dir('uploads/logos/')) {
    @mkdir('uploads/logos/', 0777, true);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RadioWave - Portal de Rádios Online</title>
    <meta name="description" content="Descubra e ouça milhares de rádios online de todo o mundo. Música, notícias, esportes e muito mais!">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="public/radio-icon.svg">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom CSS -->
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
        
        .neon-glow {
            box-shadow: 0 0 20px rgba(147, 51, 234, 0.5), 0 0 40px rgba(147, 51, 234, 0.3);
        }
        
        .card {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(71, 85, 105, 0.5);
            border-radius: 24px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #9333ea, #ec4899);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #7c3aed, #db2777);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(147, 51, 234, 0.4);
        }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        .status-online { background-color: #10b981; }
        .status-offline { background-color: #ef4444; }
        .status-warning { background-color: #f59e0b; }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .pulse { animation: pulse 2s infinite; }
        
        .feature-card {
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(147, 51, 234, 0.2);
        }
    </style>
</head>
<body class="text-white">
    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-slate-900/80 backdrop-blur-xl border-b border-slate-700/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl neon-glow">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="text-2xl font-bold text-gradient">RadioWave</span>
                        <div class="text-sm text-purple-400">Online Radio Portal</div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="hidden md:flex space-x-6">
                    <a href="#home" class="text-gray-300 hover:text-white transition-colors">Início</a>
                    <a href="#features" class="text-gray-300 hover:text-white transition-colors">Recursos</a>
                    <a href="#api" class="text-gray-300 hover:text-white transition-colors">API</a>
                    <a href="debug.php" class="text-gray-300 hover:text-purple-400 transition-colors">Diagnóstico</a>
                    <a href="api/test-external-api.php" class="text-gray-300 hover:text-green-400 transition-colors">Teste API</a>
                </nav>
                
                <!-- Status -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center text-sm">
                        <span class="status-indicator <?php echo $api_status ? 'status-online' : 'status-offline'; ?> <?php echo !$api_status ? 'pulse' : ''; ?>"></span>
                        <span class="<?php echo $api_status ? 'text-green-400' : 'text-red-400'; ?>">
                            <?php echo $api_status ? 'Sistema Online' : 'Sistema Offline'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pt-20" id="home">
        <!-- Hero Section -->
        <section class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto text-center">
                <!-- Logo Icon -->
                <div class="mb-8">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-r from-purple-600 to-pink-600 rounded-3xl mb-8 neon-glow">
                        <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Title -->
                <h1 class="text-6xl md:text-8xl font-bold mb-8">
                    <span class="text-gradient">RadioWave</span>
                </h1>
                
                <!-- Subtitle -->
                <p class="text-xl md:text-2xl text-gray-300 mb-12 max-w-4xl mx-auto leading-relaxed">
                    Portal completo de rádios online com mais de 30.000 estações gratuitas de todo o mundo.
                    <br>Conectando você às melhores estações do planeta.
                </p>
                
                <!-- System Status Card -->
                <div class="card p-8 mb-12 max-w-4xl mx-auto">
                    <h3 class="text-2xl font-bold mb-6 flex items-center justify-center">
                        <svg class="h-6 w-6 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Status do Sistema
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Database Status -->
                        <div class="p-4 bg-slate-700/30 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium">Banco de Dados</span>
                                <span class="status-indicator <?php echo $api_status ? 'status-online' : 'status-offline'; ?>"></span>
                            </div>
                            <div class="text-sm <?php echo $api_status ? 'text-green-400' : 'text-red-400'; ?>">
                                <?php echo $api_status ? 'Conectado' : 'Erro de Conexão'; ?>
                            </div>
                            <?php if ($database_info && isset($database_info['total_radios'])): ?>
                            <div class="text-xs text-gray-400 mt-1">
                                <?php echo $database_info['total_radios']; ?> rádios cadastradas
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- API Status -->
                        <div class="p-4 bg-slate-700/30 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium">API REST</span>
                                <span class="status-indicator <?php echo file_exists('api/endpoints/health.php') ? 'status-online' : 'status-offline'; ?>"></span>
                            </div>
                            <div class="text-sm <?php echo file_exists('api/endpoints/health.php') ? 'text-green-400' : 'text-red-400'; ?>">
                                <?php echo file_exists('api/endpoints/health.php') ? 'Disponível' : 'Não Encontrado'; ?>
                            </div>
                            <div class="text-xs text-gray-400 mt-1">
                                Endpoints funcionais
                            </div>
                        </div>
                        
                        <!-- External API Status -->
                        <div class="p-4 bg-slate-700/30 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium">API Externa</span>
                                <span class="status-indicator <?php echo $external_api_status ? 'status-online' : 'status-offline'; ?>"></span>
                            </div>
                            <div class="text-sm <?php echo $external_api_status ? 'text-green-400' : 'text-red-400'; ?>">
                                <?php echo $external_api_status ? 'Radio-Browser OK' : 'Offline'; ?>
                            </div>
                            <div class="text-xs text-gray-400 mt-1">
                                30.000+ estações
                            </div>
                        </div>
                        
                        <!-- PHP Version -->
                        <div class="p-4 bg-slate-700/30 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium">PHP</span>
                                <span class="status-indicator <?php echo version_compare(PHP_VERSION, '8.1.0', '>=') ? 'status-online' : 'status-warning'; ?>"></span>
                            </div>
                            <div class="text-sm <?php echo version_compare(PHP_VERSION, '8.1.0', '>=') ? 'text-green-400' : 'text-yellow-400'; ?>">
                                <?php echo PHP_VERSION; ?>
                            </div>
                            <div class="text-xs text-gray-400 mt-1">
                                <?php echo version_compare(PHP_VERSION, '8.1.0', '>=') ? 'Compatível' : 'Requer 8.1+'; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!$api_status || !$uploads_writable || !$external_api_status): ?>
                    <div class="mt-6 p-4 bg-red-500/20 border border-red-500/30 rounded-xl">
                        <h4 class="font-bold text-red-400 mb-2 flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Problemas Detectados:
                        </h4>
                        <ul class="text-sm text-red-300 space-y-1">
                            <?php if (!$api_status): ?>
                            <li>• Erro na conexão com o banco de dados</li>
                            <li>• Verifique as credenciais em api/config/database.php</li>
                            <?php endif; ?>
                            <?php if (!$uploads_writable): ?>
                            <li>• Diretório uploads/ sem permissão de escrita</li>
                            <li>• Execute: chmod 777 uploads/ uploads/logos/</li>
                            <?php endif; ?>
                            <?php if (!$external_api_status): ?>
                            <li>• API externa de rádios indisponível</li>
                            <li>• Verifique conexão com internet</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <?php if ($api_status): ?>
                    <a href="api/endpoints/health.php" class="btn-primary text-lg px-8 py-4 rounded-full font-semibold inline-flex items-center justify-center space-x-3 text-white no-underline">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Testar API</span>
                    </a>
                    <?php endif; ?>
                    
                    <a href="api/test-external-api.php" class="px-8 py-4 bg-transparent border-2 border-green-500 hover:bg-green-500 text-green-500 hover:text-white rounded-full font-semibold transition-all duration-300 inline-flex items-center justify-center space-x-3 no-underline">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span>Testar 30k Rádios</span>
                    </a>
                    
                    <a href="debug.php" class="px-8 py-4 bg-transparent border-2 border-purple-500 hover:bg-purple-500 text-purple-500 hover:text-white rounded-full font-semibold transition-all duration-300 inline-flex items-center justify-center space-x-3 no-underline">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Diagnóstico Completo</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                        Recursos do <span class="text-gradient">Sistema</span>
                    </h2>
                    <p class="text-xl text-gray-400 max-w-3xl mx-auto">
                        Uma plataforma completa para gerenciar e descobrir rádios online
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- API Externa -->
                    <div class="feature-card card p-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-green-500 to-emerald-500 rounded-2xl mb-6">
                            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-4">30.000+ Rádios Gratuitas</h3>
                        <p class="text-gray-400">
                            Integração com Radio-Browser API oferecendo milhares de estações de rádio de todo o mundo
                        </p>
                    </div>

                    <!-- API REST -->
                    <div class="feature-card card p-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-2xl mb-6">
                            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-4">API REST Completa</h3>
                        <p class="text-gray-400">
                            Endpoints para CRUD de rádios, upload de logos, estatísticas e relatórios de erro
                        </p>
                    </div>

                    <!-- Database -->
                    <div class="feature-card card p-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl mb-6">
                            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-4">Banco MySQL</h3>
                        <p class="text-gray-400">
                            Estrutura robusta com tabelas para rádios, estatísticas, cliques e relatórios
                        </p>
                    </div>

                    <!-- Upload System -->
                    <div class="feature-card card p-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-2xl mb-6">
                            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-4">Sistema de Upload</h3>
                        <p class="text-gray-400">
                            Upload seguro de logos com processamento automático e redimensionamento
                        </p>
                    </div>

                    <!-- Security -->
                    <div class="feature-card card p-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-red-500 to-pink-500 rounded-2xl mb-6">
                            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-4">Segurança</h3>
                        <p class="text-gray-400">
                            Headers de segurança, validação de dados, sanitização e proteção contra ataques
                        </p>
                    </div>

                    <!-- Statistics -->
                    <div class="feature-card card p-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-2xl mb-6">
                            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-4">Estatísticas</h3>
                        <p class="text-gray-400">
                            Sistema automático de coleta de estatísticas quinzenais e relatórios detalhados
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- API Documentation -->
        <section id="api" class="py-20 bg-slate-800/30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-white mb-6">
                        Documentação da <span class="text-gradient">API</span>
                    </h2>
                    <p class="text-xl text-gray-400">
                        Endpoints disponíveis para integração
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Endpoints List -->
                    <div class="card p-8">
                        <h3 class="text-2xl font-bold text-white mb-6">Endpoints Principais</h3>
                        <div class="space-y-4">
                            <div class="p-4 bg-slate-700/30 rounded-xl">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-mono text-green-400">GET /api/health</span>
                                    <span class="text-xs bg-green-500/20 text-green-400 px-2 py-1 rounded">Status</span>
                                </div>
                                <p class="text-sm text-gray-400">Verificação de saúde do sistema</p>
                            </div>

                            <div class="p-4 bg-slate-700/30 rounded-xl">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-mono text-blue-400">GET /api/external-radios/stations/topvote/50</span>
                                    <span class="text-xs bg-blue-500/20 text-blue-400 px-2 py-1 rounded">Externa</span>
                                </div>
                                <p class="text-sm text-gray-400">Top 50 estações mais votadas</p>
                            </div>

                            <div class="p-4 bg-slate-700/30 rounded-xl">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-mono text-purple-400">GET /api/external-radios/stations/bycountry/Brazil</span>
                                    <span class="text-xs bg-purple-500/20 text-purple-400 px-2 py-1 rounded">Externa</span>
                                </div>
                                <p class="text-sm text-gray-400">Rádios por país</p>
                            </div>

                            <div class="p-4 bg-slate-700/30 rounded-xl">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-mono text-orange-400">POST /api/radios</span>
                                    <span class="text-xs bg-orange-500/20 text-orange-400 px-2 py-1 rounded">Criar</span>
                                </div>
                                <p class="text-sm text-gray-400">Cadastrar nova rádio customizada</p>
                            </div>
                        </div>
                    </div>

                    <!-- Example Response -->
                    <div class="card p-8">
                        <h3 class="text-2xl font-bold text-white mb-6">Exemplo de Resposta</h3>
                        <div class="bg-slate-900/50 p-4 rounded-xl overflow-x-auto">
                            <pre class="text-sm text-gray-300"><code>[
  {
    "changeuuid": "abc123",
    "stationuuid": "def456", 
    "name": "Rádio Exemplo FM",
    "url": "https://exemplo.com/stream",
    "homepage": "https://exemplo.com",
    "favicon": "https://exemplo.com/logo.png",
    "tags": "music,pop,rock",
    "country": "Brazil",
    "language": "portuguese",
    "votes": 150,
    "clickcount": 1250,
    "bitrate": 128
  }
]</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- System Info -->
        <section class="py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-white mb-4">Informações do Sistema</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- PHP Version -->
                    <div class="card p-6 text-center">
                        <div class="text-3xl font-bold text-purple-400 mb-2">
                            <?php echo PHP_VERSION; ?>
                        </div>
                        <div class="text-gray-400">PHP Version</div>
                        <div class="text-xs text-gray-500 mt-1">
                            <?php echo php_sapi_name(); ?>
                        </div>
                    </div>
                    
                    <!-- Server -->
                    <div class="card p-6 text-center">
                        <div class="text-3xl font-bold text-pink-400 mb-2">
                            <?php 
                            $server = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
                            echo explode('/', $server)[0];
                            ?>
                        </div>
                        <div class="text-gray-400">Web Server</div>
                        <div class="text-xs text-gray-500 mt-1">
                            <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>
                        </div>
                    </div>
                    
                    <!-- Database -->
                    <div class="card p-6 text-center">
                        <div class="text-3xl font-bold text-blue-400 mb-2">
                            MySQL
                        </div>
                        <div class="text-gray-400">Database</div>
                        <div class="text-xs text-gray-500 mt-1">
                            <?php echo $database_info['mysql_version'] ?? 'N/A'; ?>
                        </div>
                    </div>
                    
                    <!-- External API -->
                    <div class="card p-6 text-center">
                        <div class="text-3xl font-bold <?php echo $external_api_status ? 'text-green-400' : 'text-red-400'; ?> mb-2">
                            30K+
                        </div>
                        <div class="text-gray-400">Rádios Externas</div>
                        <div class="text-xs text-gray-500 mt-1">
                            Radio-Browser API
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 border-t border-slate-800 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Logo and Description -->
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="p-2 bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-gradient">RadioWave</span>
                    </div>
                    <p class="text-gray-400 mb-4">
                        Portal completo de rádios online com mais de 30.000 estações gratuitas de todo o mundo.
                    </p>
                </div>

                <!-- Links -->
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4">Links Úteis</h3>
                    <ul class="space-y-2">
                        <li><a href="api/endpoints/health.php" class="text-gray-400 hover:text-purple-400 transition-colors">Health Check</a></li>
                        <li><a href="api/test-external-api.php" class="text-gray-400 hover:text-purple-400 transition-colors">Teste API Externa</a></li>
                        <li><a href="debug.php" class="text-gray-400 hover:text-purple-400 transition-colors">Diagnóstico</a></li>
                        <li><a href="cadastro-radio.php" class="text-gray-400 hover:text-purple-400 transition-colors">Cadastrar Rádio</a></li>
                    </ul>
                </div>

                <!-- System Info -->
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4">Sistema</h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li>PHP <?php echo PHP_VERSION; ?></li>
                        <li>MySQL <?php echo $database_info['mysql_version'] ?? 'N/A'; ?></li>
                        <li><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown Server'; ?></li>
                        <li>Status: <span class="<?php echo $api_status ? 'text-green-400' : 'text-red-400'; ?>"><?php echo $api_status ? 'Online' : 'Offline'; ?></span></li>
                        <li>API Externa: <span class="<?php echo $external_api_status ? 'text-green-400' : 'text-red-400'; ?>"><?php echo $external_api_status ? 'Online' : 'Offline'; ?></span></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-800 mt-8 pt-8 text-center">
                <p class="text-gray-400">
                    © 2024 RadioWave. Portal de Rádios Online - Sistema completo em PHP com 30.000+ estações gratuitas.
                </p>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" 
            class="fixed bottom-8 right-8 p-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-110 neon-glow">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>
</body>
</html>