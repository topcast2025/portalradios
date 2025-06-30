<?php
/**
 * RadioWave - Portal de Rádios Online
 * Sistema completo funcionando apenas com PHP
 */

// Configurações de erro
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Headers de segurança
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Verificar status do sistema
$api_status = false;
$database_info = null;
$external_api_status = false;
$top_stations = [];

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

// Testar e carregar estações da API externa
try {
    $external_url = 'https://de1.api.radio-browser.info/json/stations/topvote/12';
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'RadioWave/2.0.0 (https://wave.soradios.online)',
            'method' => 'GET'
        ]
    ]);
    
    $response = @file_get_contents($external_url, false, $context);
    if ($response !== false) {
        $stations_data = json_decode($response, true);
        if ($stations_data && is_array($stations_data)) {
            $external_api_status = true;
            $top_stations = array_slice($stations_data, 0, 12);
        }
    }
} catch (Exception $e) {
    error_log("External API error: " . $e->getMessage());
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
    <meta name="description" content="Descubra e ouça mais de 30.000 rádios online gratuitas de todo o mundo!">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23a855f7' stroke-width='2'%3E%3Cpath d='M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'/%3E%3C/svg%3E">
    
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
        
        .radio-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .radio-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(147, 51, 234, 0.3);
        }
        
        .play-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(147, 51, 234, 0.9);
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            color: white;
            font-size: 24px;
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .radio-card:hover .play-btn {
            opacity: 1;
        }
        
        .audio-player {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(71, 85, 105, 0.5);
            padding: 20px;
            z-index: 1000;
            display: none;
        }
        
        .audio-player.active {
            display: block;
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
                        <div class="text-sm text-purple-400">30.000+ Rádios Gratuitas</div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="hidden md:flex space-x-6">
                    <a href="#home" class="text-gray-300 hover:text-white transition-colors">Início</a>
                    <a href="#radios" class="text-gray-300 hover:text-white transition-colors">Rádios</a>
                    <a href="#features" class="text-gray-300 hover:text-white transition-colors">Recursos</a>
                    <a href="debug.php" class="text-gray-300 hover:text-purple-400 transition-colors">Diagnóstico</a>
                    <a href="api/test-external-api.php" class="text-gray-300 hover:text-green-400 transition-colors">Teste API</a>
                </nav>
                
                <!-- Status -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center text-sm">
                        <span class="status-indicator <?php echo $external_api_status ? 'status-online' : 'status-offline'; ?> <?php echo !$external_api_status ? 'pulse' : ''; ?>"></span>
                        <span class="<?php echo $external_api_status ? 'text-green-400' : 'text-red-400'; ?>">
                            <?php echo $external_api_status ? 'API Online' : 'API Offline'; ?>
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
                    🎵 Mais de <strong>30.000 rádios online gratuitas</strong> de todo o mundo!
                    <br>🌍 Música, notícias, esportes e muito mais em um só lugar.
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
                                <?php echo $external_api_status ? count($top_stations) . ' estações carregadas' : '30.000+ estações'; ?>
                            </div>
                        </div>
                        
                        <!-- Database Status -->
                        <div class="p-4 bg-slate-700/30 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium">Banco de Dados</span>
                                <span class="status-indicator <?php echo $api_status ? 'status-online' : 'status-offline'; ?>"></span>
                            </div>
                            <div class="text-sm <?php echo $api_status ? 'text-green-400' : 'text-red-400'; ?>">
                                <?php echo $api_status ? 'MySQL Conectado' : 'Erro de Conexão'; ?>
                            </div>
                            <?php if ($database_info && isset($database_info['total_radios'])): ?>
                            <div class="text-xs text-gray-400 mt-1">
                                <?php echo $database_info['total_radios']; ?> rádios customizadas
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- API REST Status -->
                        <div class="p-4 bg-slate-700/30 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium">API REST</span>
                                <span class="status-indicator <?php echo file_exists('api/endpoints/health.php') ? 'status-online' : 'status-offline'; ?>"></span>
                            </div>
                            <div class="text-sm <?php echo file_exists('api/endpoints/health.php') ? 'text-green-400' : 'text-red-400'; ?>">
                                <?php echo file_exists('api/endpoints/health.php') ? 'Endpoints OK' : 'Não Encontrado'; ?>
                            </div>
                            <div class="text-xs text-gray-400 mt-1">
                                Sistema próprio
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
                    
                    <?php if ($external_api_status): ?>
                    <div class="mt-6 p-4 bg-green-500/20 border border-green-500/30 rounded-xl">
                        <h4 class="font-bold text-green-400 mb-2 flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Sistema Funcionando Perfeitamente!
                        </h4>
                        <ul class="text-sm text-green-300 space-y-1">
                            <li>✅ API externa conectada com sucesso</li>
                            <li>✅ <?php echo count($top_stations); ?> estações populares carregadas</li>
                            <li>✅ Sistema pronto para uso</li>
                        </ul>
                    </div>
                    <?php else: ?>
                    <div class="mt-6 p-4 bg-red-500/20 border border-red-500/30 rounded-xl">
                        <h4 class="font-bold text-red-400 mb-2 flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Problemas Detectados:
                        </h4>
                        <ul class="text-sm text-red-300 space-y-1">
                            <li>• API externa de rádios indisponível</li>
                            <li>• Verifique conexão com internet</li>
                            <li>• Tente recarregar a página</li>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <a href="#radios" class="btn-primary text-lg px-8 py-4 rounded-full font-semibold inline-flex items-center justify-center space-x-3 text-white no-underline">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12 7-12 6z"></path>
                        </svg>
                        <span>Ouvir Rádios Agora</span>
                    </a>
                    
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
                        <span>Diagnóstico</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- Radio Stations Section -->
        <?php if ($external_api_status && !empty($top_stations)): ?>
        <section id="radios" class="py-20 bg-slate-800/30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                        🎵 Estações <span class="text-gradient">Populares</span>
                    </h2>
                    <p class="text-xl text-gray-400 max-w-3xl mx-auto">
                        As rádios mais ouvidas do mundo - Clique para ouvir!
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <?php foreach ($top_stations as $index => $station): ?>
                    <div class="radio-card card p-6 relative overflow-hidden" onclick="playRadio('<?php echo htmlspecialchars($station['url_resolved'] ?: $station['url']); ?>', '<?php echo htmlspecialchars($station['name']); ?>', '<?php echo htmlspecialchars($station['country']); ?>')">
                        <!-- Station Image -->
                        <div class="relative mb-4">
                            <?php if (!empty($station['favicon'])): ?>
                            <img src="<?php echo htmlspecialchars($station['favicon']); ?>" 
                                 alt="<?php echo htmlspecialchars($station['name']); ?>"
                                 class="w-full h-32 object-cover rounded-xl"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <?php endif; ?>
                            <div class="w-full h-32 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center <?php echo !empty($station['favicon']) ? 'hidden' : ''; ?>">
                                <span class="text-white font-bold text-2xl">
                                    <?php echo strtoupper(substr($station['name'], 0, 2)); ?>
                                </span>
                            </div>
                            
                            <!-- Play Button Overlay -->
                            <button class="play-btn">
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Station Info -->
                        <div>
                            <h3 class="font-bold text-white text-lg mb-2 truncate">
                                <?php echo htmlspecialchars($station['name']); ?>
                            </h3>
                            
                            <div class="flex items-center space-x-3 mb-3 text-sm text-gray-400">
                                <div class="flex items-center space-x-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span><?php echo htmlspecialchars($station['country']); ?></span>
                                </div>
                                
                                <?php if (!empty($station['language'])): ?>
                                <div class="flex items-center space-x-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                                    </svg>
                                    <span><?php echo htmlspecialchars($station['language']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tags -->
                            <?php if (!empty($station['tags'])): ?>
                            <div class="flex flex-wrap gap-1 mb-3">
                                <?php 
                                $tags = array_slice(explode(',', $station['tags']), 0, 3);
                                foreach ($tags as $tag): 
                                    $tag = trim($tag);
                                    if (!empty($tag)):
                                ?>
                                <span class="px-2 py-1 bg-purple-500/20 text-purple-300 text-xs rounded-full border border-purple-500/30">
                                    <?php echo htmlspecialchars($tag); ?>
                                </span>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                            <?php endif; ?>

                            <!-- Stats -->
                            <div class="flex items-center justify-between text-sm text-gray-400">
                                <?php if ($station['votes'] > 0): ?>
                                <div class="flex items-center space-x-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                    <span><?php echo number_format($station['votes']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($station['bitrate'] > 0): ?>
                                <div class="flex items-center space-x-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 14.142M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                                    </svg>
                                    <span><?php echo $station['bitrate']; ?>kbps</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="text-center mt-12">
                    <a href="api/test-external-api.php" class="btn-primary text-lg px-8 py-4 rounded-full font-semibold inline-flex items-center justify-center space-x-3 text-white no-underline">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span>Ver Todas as 30.000+ Rádios</span>
                    </a>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Features Section -->
        <section id="features" class="py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                        Recursos do <span class="text-gradient">Sistema</span>
                    </h2>
                    <p class="text-xl text-gray-400 max-w-3xl mx-auto">
                        Uma plataforma completa para descobrir e ouvir rádios online
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- 30k+ Rádios -->
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

                    <!-- Busca Avançada -->
                    <div class="feature-card card p-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-2xl mb-6">
                            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-4">Busca Avançada</h3>
                        <p class="text-gray-400">
                            Encontre rádios por país, idioma, gênero musical ou nome da estação
                        </p>
                    </div>

                    <!-- Player Integrado -->
                    <div class="feature-card card p-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl mb-6">
                            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12 7-12 6z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-4">Player Integrado</h3>
                        <p class="text-gray-400">
                            Player de áudio moderno com controles de volume e informações da estação
                        </p>
                    </div>

                    <!-- Sem Anúncios -->
                    <div class="feature-card card p-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-2xl mb-6">
                            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-4">100% Gratuito</h3>
                        <p class="text-gray-400">
                            Sem anúncios intrusivos, sem assinaturas, sem taxas ocultas
                        </p>
                    </div>

                    <!-- Interface Moderna -->
                    <div class="feature-card card p-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-red-500 to-pink-500 rounded-2xl mb-6">
                            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-4">Interface Moderna</h3>
                        <p class="text-gray-400">
                            Design responsivo e intuitivo que funciona em todos os dispositivos
                        </p>
                    </div>

                    <!-- API Aberta -->
                    <div class="feature-card card p-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-2xl mb-6">
                            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-4">API Aberta</h3>
                        <p class="text-gray-400">
                            Integre facilmente com seus projetos usando nossa API REST
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Audio Player -->
    <div id="audioPlayer" class="audio-player">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <div>
                    <h4 id="currentStationName" class="font-bold text-white">Nenhuma rádio selecionada</h4>
                    <p id="currentStationCountry" class="text-sm text-gray-400">Clique em uma estação para ouvir</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <button id="playPauseBtn" onclick="togglePlayPause()" class="p-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-full text-white hover:from-purple-700 hover:to-pink-700 transition-all">
                    <svg id="playIcon" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                    <svg id="pauseIcon" class="h-6 w-6 hidden" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                    </svg>
                </button>

                <div class="flex items-center space-x-2">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 14.142M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                    </svg>
                    <input type="range" id="volumeSlider" min="0" max="1" step="0.1" value="0.7" class="w-24" onchange="setVolume(this.value)">
                </div>

                <button onclick="closePlayer()" class="p-2 text-gray-400 hover:text-white transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

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
                        Portal completo com mais de 30.000 rádios online gratuitas de todo o mundo.
                    </p>
                </div>

                <!-- Links -->
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4">Links Úteis</h3>
                    <ul class="space-y-2">
                        <li><a href="api/test-external-api.php" class="text-gray-400 hover:text-purple-400 transition-colors">Teste API Externa</a></li>
                        <li><a href="api/endpoints/health.php" class="text-gray-400 hover:text-purple-400 transition-colors">Health Check</a></li>
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
                        <li>API Externa: <span class="<?php echo $external_api_status ? 'text-green-400' : 'text-red-400'; ?>"><?php echo $external_api_status ? 'Online' : 'Offline'; ?></span></li>
                        <li>Estações: <span class="text-green-400"><?php echo $external_api_status ? count($top_stations) . ' carregadas' : '30.000+ disponíveis'; ?></span></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-800 mt-8 pt-8 text-center">
                <p class="text-gray-400">
                    © 2024 RadioWave. Portal de Rádios Online - Mais de 30.000 estações gratuitas de todo o mundo.
                </p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        let currentAudio = null;
        let isPlaying = false;

        function playRadio(url, name, country) {
            // Stop current audio if playing
            if (currentAudio) {
                currentAudio.pause();
                currentAudio = null;
            }

            // Update player info
            document.getElementById('currentStationName').textContent = name;
            document.getElementById('currentStationCountry').textContent = country;
            
            // Show player
            document.getElementById('audioPlayer').classList.add('active');

            // Create new audio
            currentAudio = new Audio(url);
            currentAudio.volume = document.getElementById('volumeSlider').value;
            
            // Play audio
            currentAudio.play().then(() => {
                isPlaying = true;
                updatePlayPauseButton();
            }).catch(error => {
                console.error('Error playing audio:', error);
                alert('Erro ao reproduzir esta rádio. Tente outra estação.');
            });

            // Handle audio events
            currentAudio.addEventListener('error', () => {
                alert('Erro ao carregar esta rádio. Tente outra estação.');
            });

            currentAudio.addEventListener('ended', () => {
                isPlaying = false;
                updatePlayPauseButton();
            });
        }

        function togglePlayPause() {
            if (!currentAudio) return;

            if (isPlaying) {
                currentAudio.pause();
                isPlaying = false;
            } else {
                currentAudio.play().then(() => {
                    isPlaying = true;
                }).catch(error => {
                    console.error('Error playing audio:', error);
                });
            }
            updatePlayPauseButton();
        }

        function updatePlayPauseButton() {
            const playIcon = document.getElementById('playIcon');
            const pauseIcon = document.getElementById('pauseIcon');
            
            if (isPlaying) {
                playIcon.classList.add('hidden');
                pauseIcon.classList.remove('hidden');
            } else {
                playIcon.classList.remove('hidden');
                pauseIcon.classList.add('hidden');
            }
        }

        function setVolume(value) {
            if (currentAudio) {
                currentAudio.volume = value;
            }
        }

        function closePlayer() {
            if (currentAudio) {
                currentAudio.pause();
                currentAudio = null;
            }
            isPlaying = false;
            updatePlayPauseButton();
            document.getElementById('audioPlayer').classList.remove('active');
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>