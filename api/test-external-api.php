<?php
/**
 * Teste da API externa de rádios
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste API Externa - RadioWave</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .test-section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .btn { padding: 10px 20px; margin: 5px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Teste da API Externa de Rádios</h1>
        <p>Testando integração com Radio-Browser API (30.000+ estações gratuitas)</p>

        <?php
        // Teste 1: Estações Populares
        echo '<div class="test-section">';
        echo '<h2>1. Estações Populares (Top 10)</h2>';
        
        $url = 'https://de1.api.radio-browser.info/json/stations/topvote/10';
        $response = @file_get_contents($url, false, stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'RadioWave/2.0.0'
            ]
        ]));
        
        if ($response) {
            $data = json_decode($response, true);
            if ($data && is_array($data)) {
                echo '<p class="success">✅ API funcionando! Encontradas ' . count($data) . ' estações</p>';
                echo '<h3>Primeiras 3 estações:</h3>';
                foreach (array_slice($data, 0, 3) as $station) {
                    echo '<div style="border: 1px solid #eee; padding: 10px; margin: 5px 0; border-radius: 4px;">';
                    echo '<strong>' . htmlspecialchars($station['name']) . '</strong><br>';
                    echo 'País: ' . htmlspecialchars($station['country']) . '<br>';
                    echo 'Idioma: ' . htmlspecialchars($station['language']) . '<br>';
                    echo 'Votos: ' . $station['votes'] . '<br>';
                    echo 'URL: ' . htmlspecialchars($station['url']) . '<br>';
                    echo '</div>';
                }
            } else {
                echo '<p class="error">❌ Resposta inválida da API</p>';
            }
        } else {
            echo '<p class="error">❌ Falha ao conectar com a API</p>';
        }
        echo '</div>';

        // Teste 2: Busca por País
        echo '<div class="test-section">';
        echo '<h2>2. Rádios do Brasil</h2>';
        
        $url = 'https://de1.api.radio-browser.info/json/stations/bycountry/Brazil';
        $response = @file_get_contents($url, false, stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'RadioWave/2.0.0'
            ]
        ]));
        
        if ($response) {
            $data = json_decode($response, true);
            if ($data && is_array($data)) {
                echo '<p class="success">✅ Encontradas ' . count($data) . ' rádios brasileiras</p>';
                echo '<h3>Primeiras 3 rádios brasileiras:</h3>';
                foreach (array_slice($data, 0, 3) as $station) {
                    echo '<div style="border: 1px solid #eee; padding: 10px; margin: 5px 0; border-radius: 4px;">';
                    echo '<strong>' . htmlspecialchars($station['name']) . '</strong><br>';
                    echo 'Estado: ' . htmlspecialchars($station['state']) . '<br>';
                    echo 'Tags: ' . htmlspecialchars($station['tags']) . '<br>';
                    echo 'Bitrate: ' . $station['bitrate'] . 'kbps<br>';
                    echo '</div>';
                }
            } else {
                echo '<p class="error">❌ Resposta inválida da API</p>';
            }
        } else {
            echo '<p class="error">❌ Falha ao conectar com a API</p>';
        }
        echo '</div>';

        // Teste 3: Busca por Gênero
        echo '<div class="test-section">';
        echo '<h2>3. Rádios de Rock</h2>';
        
        $url = 'https://de1.api.radio-browser.info/json/stations/bytag/rock';
        $response = @file_get_contents($url, false, stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'RadioWave/2.0.0'
            ]
        ]));
        
        if ($response) {
            $data = json_decode($response, true);
            if ($data && is_array($data)) {
                echo '<p class="success">✅ Encontradas ' . count($data) . ' rádios de rock</p>';
                echo '<h3>Primeiras 3 rádios de rock:</h3>';
                foreach (array_slice($data, 0, 3) as $station) {
                    echo '<div style="border: 1px solid #eee; padding: 10px; margin: 5px 0; border-radius: 4px;">';
                    echo '<strong>' . htmlspecialchars($station['name']) . '</strong><br>';
                    echo 'País: ' . htmlspecialchars($station['country']) . '<br>';
                    echo 'Tags: ' . htmlspecialchars($station['tags']) . '<br>';
                    echo 'Cliques: ' . $station['clickcount'] . '<br>';
                    echo '</div>';
                }
            } else {
                echo '<p class="error">❌ Resposta inválida da API</p>';
            }
        } else {
            echo '<p class="error">❌ Falha ao conectar com a API</p>';
        }
        echo '</div>';

        // Teste 4: Teste da nossa API local
        echo '<div class="test-section">';
        echo '<h2>4. Teste da Nossa API Local</h2>';
        
        $local_url = '/api/external-radios/stations/topvote/5';
        $full_url = 'https://' . $_SERVER['HTTP_HOST'] . $local_url;
        
        echo '<p class="info">Testando: ' . $full_url . '</p>';
        
        $response = @file_get_contents($full_url, false, stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'RadioWave/2.0.0'
            ]
        ]));
        
        if ($response) {
            $data = json_decode($response, true);
            if ($data && is_array($data)) {
                echo '<p class="success">✅ Nossa API local funcionando! Retornou ' . count($data) . ' estações</p>';
            } else {
                echo '<p class="error">❌ Nossa API retornou dados inválidos</p>';
                echo '<pre>' . htmlspecialchars($response) . '</pre>';
            }
        } else {
            echo '<p class="error">❌ Falha ao acessar nossa API local</p>';
        }
        echo '</div>';

        // Informações da API
        echo '<div class="test-section">';
        echo '<h2>📊 Informações da Radio-Browser API</h2>';
        echo '<ul>';
        echo '<li><strong>Base URL:</strong> https://de1.api.radio-browser.info/json</li>';
        echo '<li><strong>Estações:</strong> 30.000+ rádios gratuitas</li>';
        echo '<li><strong>Países:</strong> 200+ países</li>';
        echo '<li><strong>Atualização:</strong> Tempo real</li>';
        echo '<li><strong>Limite:</strong> Sem limite de requisições</li>';
        echo '<li><strong>Documentação:</strong> <a href="https://api.radio-browser.info/" target="_blank">api.radio-browser.info</a></li>';
        echo '</ul>';
        echo '</div>';
        ?>

        <div class="test-section">
            <h2>🔗 Links Úteis</h2>
            <a href="../index.php" class="btn">🏠 Página Principal</a>
            <a href="../debug.php" class="btn">🔧 Diagnóstico</a>
            <a href="health" class="btn">🩺 Health Check</a>
            <a href="external-radios/stations/topvote/10" class="btn">📻 API Externa</a>
        </div>
    </div>
</body>
</html>