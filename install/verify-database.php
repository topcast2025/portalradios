<?php
/**
 * Script de verificação da estrutura do banco de dados
 * Executa após a instalação para validar se tudo foi criado corretamente
 */

header('Content-Type: application/json; charset=utf-8');

try {
    // Incluir configuração do banco
    require_once '../api/config/database.php';
    
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Falha na conexão com o banco de dados');
    }
    
    $results = [
        'success' => true,
        'timestamp' => date('c'),
        'database_info' => [],
        'tables' => [],
        'triggers' => [],
        'procedures' => [],
        'views' => [],
        'sample_data' => [],
        'indexes' => []
    ];
    
    // 1. Informações do banco
    $stmt = $conn->query("SELECT VERSION() as version, DATABASE() as database_name, USER() as user");
    $db_info = $stmt->fetch();
    $results['database_info'] = $db_info;
    
    // 2. Verificar tabelas
    $expected_tables = [
        'radios',
        'radio_statistics', 
        'radio_clicks',
        'radio_error_reports',
        'file_uploads',
        'system_settings'
    ];
    
    $stmt = $conn->query("SHOW TABLES");
    $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($expected_tables as $table) {
        $exists = in_array($table, $existing_tables);
        $count = 0;
        
        if ($exists) {
            $stmt = $conn->query("SELECT COUNT(*) as count FROM `$table`");
            $count = $stmt->fetch()['count'];
        }
        
        $results['tables'][$table] = [
            'exists' => $exists,
            'record_count' => $count
        ];
    }
    
    // 3. Verificar triggers
    $stmt = $conn->query("SHOW TRIGGERS");
    $triggers = $stmt->fetchAll();
    
    $expected_triggers = [
        'update_radio_clicks_count',
        'update_statistics_on_click'
    ];
    
    foreach ($expected_triggers as $trigger) {
        $exists = false;
        foreach ($triggers as $t) {
            if ($t['Trigger'] === $trigger) {
                $exists = true;
                break;
            }
        }
        $results['triggers'][$trigger] = ['exists' => $exists];
    }
    
    // 4. Verificar procedures
    $stmt = $conn->query("SHOW PROCEDURE STATUS WHERE Db = DATABASE()");
    $procedures = $stmt->fetchAll();
    
    $expected_procedures = [
        'CalculateFortnightlyStats',
        'CleanOldData'
    ];
    
    foreach ($expected_procedures as $procedure) {
        $exists = false;
        foreach ($procedures as $p) {
            if ($p['Name'] === $procedure) {
                $exists = true;
                break;
            }
        }
        $results['procedures'][$procedure] = ['exists' => $exists];
    }
    
    // 5. Verificar views
    $stmt = $conn->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
    $views = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $expected_views = [
        'radio_stats_summary',
        'popular_radios'
    ];
    
    foreach ($expected_views as $view) {
        $exists = in_array($view, $views);
        $results['views'][$view] = ['exists' => $exists];
    }
    
    // 6. Verificar dados de exemplo
    if (in_array('radios', $existing_tables)) {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM radios WHERE radio_name LIKE '%RadioWave%' OR radio_name LIKE '%Exemplo%'");
        $sample_radios = $stmt->fetch()['count'];
        $results['sample_data']['radios'] = $sample_radios;
    }
    
    if (in_array('system_settings', $existing_tables)) {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM system_settings");
        $settings_count = $stmt->fetch()['count'];
        $results['sample_data']['settings'] = $settings_count;
    }
    
    // 7. Verificar índices importantes
    $stmt = $conn->query("SHOW INDEX FROM radios WHERE Key_name != 'PRIMARY'");
    $indexes = $stmt->fetchAll();
    $results['indexes']['radios'] = count($indexes);
    
    // 8. Teste de funcionalidade básica
    try {
        // Testar inserção de clique
        $stmt = $conn->prepare("INSERT INTO radio_clicks (radio_id, ip_address, user_agent) VALUES (1, '127.0.0.1', 'Test Agent')");
        $stmt->execute();
        $click_id = $conn->lastInsertId();
        
        // Verificar se o trigger atualizou o total_clicks
        $stmt = $conn->query("SELECT total_clicks FROM radios WHERE id = 1");
        $total_clicks = $stmt->fetch()['total_clicks'];
        
        // Remover o clique de teste
        $stmt = $conn->prepare("DELETE FROM radio_clicks WHERE id = ?");
        $stmt->execute([$click_id]);
        
        $results['functionality_test'] = [
            'trigger_working' => $total_clicks > 0,
            'insert_test' => true,
            'delete_test' => true
        ];
        
    } catch (Exception $e) {
        $results['functionality_test'] = [
            'error' => $e->getMessage()
        ];
    }
    
    // 9. Resumo final
    $total_tables = count($expected_tables);
    $existing_tables_count = count(array_filter($results['tables'], function($t) { return $t['exists']; }));
    
    $total_triggers = count($expected_triggers);
    $existing_triggers_count = count(array_filter($results['triggers'], function($t) { return $t['exists']; }));
    
    $results['summary'] = [
        'tables_created' => "$existing_tables_count/$total_tables",
        'triggers_created' => "$existing_triggers_count/$total_triggers",
        'database_ready' => $existing_tables_count === $total_tables && $existing_triggers_count === $total_triggers
    ];
    
    echo json_encode($results, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
?>