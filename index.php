<?php
/**
 * RadioWave - Portal de Rádios Online
 * Página principal do sistema
 */

// Configurações de erro para desenvolvimento
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Headers de segurança
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Verificar se a API está funcionando
$api_status = false;
try {
    if (file_exists('api/config/database.php')) {
        require_once 'api/config/database.php';
        $database = new Database();
        $api_status = $database->testConnection();
    }
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
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
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .pulse { animation: pulse 2s infinite; }
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
                        <div class="text-sm text-purple-400">Online Radio</div>
                    </div>
                </div>
                
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
    <main class="pt-20">
        <!-- Hero Section -->
        <section class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center">
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
                    Descubra e ouça milhares de rádios online de todo o mundo. 
                    Sua música favorita está a um clique de distância.
                </p>
                
                <!-- System Status -->
                <div class="card p-8 mb-12 max-w-2xl mx-auto">
                    <h3 class="text-2xl font-bold mb-6">Status do Sistema</h3>
                    
                    <div class="space-y-4">
                        <!-- Database Status -->
                        <div class="flex items-center justify-between p-4 bg-slate-700/30 rounded-xl">
                            <div class="flex items-center">
                                <span class="status-indicator <?php echo $api_status ? 'status-online' : 'status-offline'; ?>"></span>
                                <span class="font-medium">Banco de Dados</span>
                            </div>
                            <span class="<?php echo $api_status ? 'text-green-400' : 'text-red-400'; ?>">
                                <?php echo $api_status ? 'Conectado' : 'Erro de Conexão'; ?>
                            </span>
                        </div>
                        
                        <!-- API Status -->
                        <div class="flex items-center justify-between p-4 bg-slate-700/30 rounded-xl">
                            <div class="flex items-center">
                                <span class="status-indicator <?php echo file_exists('api/endpoints/health.php') ? 'status-online' : 'status-offline'; ?>"></span>
                                <span class="font-medium">API Endpoints</span>
                            </div>
                            <span class="<?php echo file_exists('api/endpoints/health.php') ? 'text-green-400' : 'text-red-400'; ?>">
                                <?php echo file_exists('api/endpoints/health.php') ? 'Disponível' : 'Não Encontrado'; ?>
                            </span>
                        </div>
                        
                        <!-- Upload Directory -->
                        <div class="flex items-center justify-between p-4 bg-slate-700/30 rounded-xl">
                            <div class="flex items-center">
                                <span class="status-indicator <?php echo is_writable('uploads/') ? 'status-online' : 'status-offline'; ?>"></span>
                                <span class="font-medium">Diretório de Upload</span>
                            </div>
                            <span class="<?php echo is_writable('uploads/') ? 'text-green-400' : 'text-red-400'; ?>">
                                <?php echo is_writable('uploads/') ? 'Gravável' : 'Sem Permissão'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if (!$api_status): ?>
                    <div class="mt-6 p-4 bg-red-500/20 border border-red-500/30 rounded-xl">
                        <h4 class="font-bold text-red-400 mb-2">Problemas Detectados:</h4>
                        <ul class="text-sm text-red-300 space-y-1">
                            <?php if (!$api_status): ?>
                            <li>• Erro na conexão com o banco de dados</li>
                            <li>• Verifique as credenciais em api/config/database.php</li>
                            <?php endif; ?>
                            <?php if (!is_writable('uploads/')): ?>
                            <li>• Diretório uploads/ sem permissão de escrita</li>
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
                    
                    <a href="debug.php" class="px-8 py-4 bg-transparent border-2 border-purple-500 hover:bg-purple-500 text-purple-500 hover:text-white rounded-full font-semibold transition-all duration-300 inline-flex items-center justify-center space-x-3 no-underline">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Diagnóstico</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- System Info -->
        <section class="py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- PHP Info -->
                    <div class="card p-6 text-center">
                        <div class="text-3xl font-bold text-purple-400 mb-2">
                            <?php echo PHP_VERSION; ?>
                        </div>
                        <div class="text-gray-400">Versão do PHP</div>
                    </div>
                    
                    <!-- Server Info -->
                    <div class="card p-6 text-center">
                        <div class="text-3xl font-bold text-pink-400 mb-2">
                            <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>
                        </div>
                        <div class="text-gray-400">Servidor Web</div>
                    </div>
                    
                    <!-- Database Info -->
                    <div class="card p-6 text-center">
                        <div class="text-3xl font-bold text-blue-400 mb-2">
                            MySQL
                        </div>
                        <div class="text-gray-400">Banco de Dados</div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 border-t border-slate-800 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="flex items-center justify-center space-x-3 mb-4">
                <div class="p-2 bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <span class="text-xl font-bold text-gradient">RadioWave</span>
            </div>
            <p class="text-gray-400">
                © 2024 RadioWave. Portal de Rádios Online.
            </p>
        </div>
    </footer>
</body>
</html>