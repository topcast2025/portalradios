<?php
/**
 * Teste completo do sistema após instalação
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste do Sistema - RadioWave</title>
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
        .status-ok { color: #10b981; }
        .status-error { color: #ef4444; }
        .status-warning { color: #f59e0b; }
    </style>
</head>
<body class="text-white">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold mb-4">🧪 Teste Completo do Sistema</h1>
                <p class="text-xl text-gray-400">Verificação pós-instalação do RadioWave</p>
                <div class="mt-6">
                    <a href="../index.php" class="inline-block px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-semibold hover:from-purple-700 hover:to-pink-700 transition-all mr-4">
                        ← Voltar ao Sistema
                    </a>
                    <button onclick="runTests()" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-all">
                        🔄 Executar Testes
                    </button>
                </div>
            </div>

            <!-- Results Container -->
            <div id="results" class="space-y-8">
                <div class="card p-8 text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-500 mx-auto mb-4"></div>
                    <p class="text-gray-400">Clique em "Executar Testes" para iniciar a verificação</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function runTests() {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '<div class="card p-8 text-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-500 mx-auto mb-4"></div><p class="text-gray-400">Executando testes...</p></div>';

            try {
                // 1. Teste de verificação do banco
                const dbResponse = await fetch('verify-database.php');
                const dbData = await dbResponse.json();

                // 2. Teste da API Health
                const healthResponse = await fetch('../api/health');
                const healthData = await healthResponse.json();

                // 3. Teste da API de rádios
                const radiosResponse = await fetch('../api/radios');
                const radiosData = await radiosResponse.json();

                // Renderizar resultados
                renderResults({
                    database: dbData,
                    health: healthData,
                    radios: radiosData
                });

            } catch (error) {
                resultsDiv.innerHTML = `
                    <div class="card p-8">
                        <h2 class="text-2xl font-bold text-red-400 mb-4">❌ Erro nos Testes</h2>
                        <p class="text-gray-400">${error.message}</p>
                    </div>
                `;
            }
        }

        function renderResults(data) {
            const resultsDiv = document.getElementById('results');
            
            let html = '';

            // 1. Resultado do Banco de Dados
            html += `
                <div class="card p-8">
                    <h2 class="text-2xl font-bold text-white mb-6">🗄️ Banco de Dados</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold ${data.database.success ? 'text-green-400' : 'text-red-400'} mb-2">
                                ${data.database.success ? '✅' : '❌'}
                            </div>
                            <div class="text-gray-400">Conexão</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-400 mb-2">
                                ${data.database.summary?.tables_created || '0/0'}
                            </div>
                            <div class="text-gray-400">Tabelas</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-400 mb-2">
                                ${data.database.summary?.triggers_created || '0/0'}
                            </div>
                            <div class="text-gray-400">Triggers</div>
                        </div>
                    </div>
                    
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="font-bold text-white mb-3">Tabelas Criadas:</h3>
                            <div class="space-y-2">
            `;

            if (data.database.tables) {
                Object.entries(data.database.tables).forEach(([table, info]) => {
                    html += `
                        <div class="flex justify-between items-center p-2 bg-slate-700/30 rounded">
                            <span>${table}</span>
                            <span class="${info.exists ? 'status-ok' : 'status-error'}">
                                ${info.exists ? '✅' : '❌'} (${info.record_count || 0} registros)
                            </span>
                        </div>
                    `;
                });
            }

            html += `
                            </div>
                        </div>
                        <div>
                            <h3 class="font-bold text-white mb-3">Funcionalidades:</h3>
                            <div class="space-y-2">
            `;

            if (data.database.triggers) {
                Object.entries(data.database.triggers).forEach(([trigger, info]) => {
                    html += `
                        <div class="flex justify-between items-center p-2 bg-slate-700/30 rounded">
                            <span>${trigger}</span>
                            <span class="${info.exists ? 'status-ok' : 'status-error'}">
                                ${info.exists ? '✅' : '❌'}
                            </span>
                        </div>
                    `;
                });
            }

            html += `
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // 2. Resultado da API Health
            html += `
                <div class="card p-8">
                    <h2 class="text-2xl font-bold text-white mb-6">🩺 API Health Check</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold ${data.health.status === 'OK' ? 'text-green-400' : 'text-red-400'} mb-2">
                                ${data.health.status === 'OK' ? '✅' : '❌'}
                            </div>
                            <div class="text-gray-400">Status</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-400 mb-2">
                                ${data.health.php?.version || 'N/A'}
                            </div>
                            <div class="text-gray-400">PHP</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-400 mb-2">
                                ${data.health.database?.status === 'connected' ? '✅' : '❌'}
                            </div>
                            <div class="text-gray-400">Database</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-400 mb-2">
                                ${data.health.version || '1.0.0'}
                            </div>
                            <div class="text-gray-400">Versão</div>
                        </div>
                    </div>
                </div>
            `;

            // 3. Resultado da API de Rádios
            html += `
                <div class="card p-8">
                    <h2 class="text-2xl font-bold text-white mb-6">📻 API de Rádios</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold ${data.radios.success ? 'text-green-400' : 'text-red-400'} mb-2">
                                ${data.radios.success ? '✅' : '❌'}
                            </div>
                            <div class="text-gray-400">API Status</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-400 mb-2">
                                ${data.radios.data?.radios?.length || 0}
                            </div>
                            <div class="text-gray-400">Rádios</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-400 mb-2">
                                ${data.radios.data?.pagination?.total || 0}
                            </div>
                            <div class="text-gray-400">Total</div>
                        </div>
                    </div>
                    
                    ${data.radios.data?.radios?.length > 0 ? `
                        <div class="mt-6">
                            <h3 class="font-bold text-white mb-3">Rádios de Exemplo:</h3>
                            <div class="space-y-2">
                                ${data.radios.data.radios.slice(0, 3).map(radio => `
                                    <div class="flex justify-between items-center p-3 bg-slate-700/30 rounded">
                                        <div>
                                            <div class="font-medium text-white">${radio.radio_name}</div>
                                            <div class="text-sm text-gray-400">${radio.country} • ${radio.language}</div>
                                        </div>
                                        <div class="text-sm text-purple-400">
                                            ${radio.total_clicks} cliques
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;

            // 4. Resumo Final
            const allTestsPassed = data.database.success && 
                                 data.health.status === 'OK' && 
                                 data.radios.success;

            html += `
                <div class="card p-8 text-center">
                    <div class="w-16 h-16 ${allTestsPassed ? 'bg-green-500' : 'bg-red-500'} rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-2xl">${allTestsPassed ? '✅' : '❌'}</span>
                    </div>
                    <h2 class="text-3xl font-bold text-white mb-4">
                        ${allTestsPassed ? 'Sistema Funcionando Perfeitamente!' : 'Problemas Detectados'}
                    </h2>
                    <p class="text-gray-400 mb-8">
                        ${allTestsPassed ? 
                            'Todos os testes passaram. O RadioWave está pronto para uso!' : 
                            'Alguns testes falharam. Verifique os detalhes acima.'}
                    </p>
                    <div class="space-x-4">
                        <a href="../index.php" class="inline-block px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-semibold hover:from-purple-700 hover:to-pink-700 transition-all">
                            Acessar Sistema
                        </a>
                        <a href="../debug.php" class="inline-block px-8 py-3 bg-slate-700 text-white rounded-xl font-semibold hover:bg-slate-600 transition-all">
                            Diagnóstico Detalhado
                        </a>
                    </div>
                </div>
            `;

            resultsDiv.innerHTML = html;
        }

        // Executar testes automaticamente ao carregar a página
        window.addEventListener('load', () => {
            setTimeout(runTests, 1000);
        });
    </script>
</body>
</html>