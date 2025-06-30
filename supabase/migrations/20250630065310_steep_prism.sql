-- =====================================================
-- RadioWave - Portal de Rádios Online
-- Script de Criação Completa do Banco de Dados
-- Versão: 2.0.0
-- =====================================================

-- Configurações iniciais
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Configurar charset
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- =====================================================
-- 1. TABELA PRINCIPAL: RADIOS
-- =====================================================

CREATE TABLE IF NOT EXISTS `radios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nome do usuário que cadastrou',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Email do usuário que cadastrou',
  `radio_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nome da rádio',
  `stream_url` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL do stream da rádio (HTTPS)',
  `logo_url` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL da logo da rádio',
  `brief_description` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Descrição breve da rádio',
  `detailed_description` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descrição detalhada da rádio',
  `genres` json DEFAULT NULL COMMENT 'Gêneros da rádio em formato JSON',
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'País de origem da rádio',
  `language` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Idioma da rádio',
  `website` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Website da rádio',
  `whatsapp` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'WhatsApp da rádio',
  `facebook` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Facebook da rádio',
  `instagram` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Instagram da rádio',
  `twitter` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Twitter da rádio',
  `status` enum('active','pending','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'Status da rádio',
  `total_clicks` int(11) NOT NULL DEFAULT 0 COMMENT 'Total de cliques/acessos',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_radio_name` (`radio_name`),
  KEY `idx_country` (`country`),
  KEY `idx_language` (`language`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_total_clicks` (`total_clicks`),
  FULLTEXT KEY `idx_search` (`radio_name`,`brief_description`,`detailed_description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela principal das rádios cadastradas';

-- =====================================================
-- 2. TABELA DE ESTATÍSTICAS
-- =====================================================

CREATE TABLE IF NOT EXISTS `radio_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `radio_id` int(11) NOT NULL,
  `access_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Número de acessos no período',
  `period_start` date NOT NULL COMMENT 'Data de início do período',
  `period_end` date NOT NULL COMMENT 'Data de fim do período',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_period` (`radio_id`,`period_start`,`period_end`),
  KEY `idx_radio_period` (`radio_id`,`period_start`),
  KEY `idx_period_dates` (`period_start`,`period_end`),
  KEY `idx_access_count` (`access_count`),
  CONSTRAINT `fk_statistics_radio` FOREIGN KEY (`radio_id`) REFERENCES `radios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Estatísticas quinzenais de acesso às rádios';

-- =====================================================
-- 3. TABELA DE CLIQUES
-- =====================================================

CREATE TABLE IF NOT EXISTS `radio_clicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `radio_id` int(11) NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP do usuário (IPv4 ou IPv6)',
  `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'User agent do navegador',
  `referrer` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Página de origem',
  `clicked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_radio_date` (`radio_id`,`clicked_at`),
  KEY `idx_ip_date` (`ip_address`,`clicked_at`),
  KEY `idx_clicked_at` (`clicked_at`),
  CONSTRAINT `fk_clicks_radio` FOREIGN KEY (`radio_id`) REFERENCES `radios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log individual de cliques nas rádios';

-- =====================================================
-- 4. TABELA DE RELATÓRIOS DE ERRO
-- =====================================================

CREATE TABLE IF NOT EXISTS `radio_error_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `radio_id` int(11) NOT NULL,
  `error_description` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Descrição do erro reportado',
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email do usuário que reportou (opcional)',
  `user_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP do usuário que reportou',
  `status` enum('pending','resolved','ignored') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'Status do report',
  `admin_notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Notas do administrador',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL COMMENT 'Data de resolução do problema',
  PRIMARY KEY (`id`),
  KEY `idx_radio_status` (`radio_id`,`status`),
  KEY `idx_status_date` (`status`,`created_at`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_reports_radio` FOREIGN KEY (`radio_id`) REFERENCES `radios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relatórios de erro das rádios';

-- =====================================================
-- 5. TABELA DE UPLOADS
-- =====================================================

CREATE TABLE IF NOT EXISTS `file_uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int(11) NOT NULL COMMENT 'Tamanho do arquivo em bytes',
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `upload_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP de quem fez o upload',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_stored_filename` (`stored_filename`),
  KEY `idx_stored_filename` (`stored_filename`),
  KEY `idx_upload_date` (`created_at`),
  KEY `idx_file_size` (`file_size`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log de uploads de arquivos';

-- =====================================================
-- 6. TABELA DE CONFIGURAÇÕES DO SISTEMA
-- =====================================================

CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `setting_type` enum('string','integer','boolean','json') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configurações do sistema';

-- =====================================================
-- 7. TRIGGERS
-- =====================================================

-- Trigger para atualizar total_clicks quando um novo clique é registrado
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `update_radio_clicks_count` 
AFTER INSERT ON `radio_clicks`
FOR EACH ROW 
BEGIN
    UPDATE `radios` 
    SET `total_clicks` = `total_clicks` + 1 
    WHERE `id` = NEW.`radio_id`;
END$$
DELIMITER ;

-- Trigger para atualizar estatísticas quando um clique é registrado
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `update_statistics_on_click` 
AFTER INSERT ON `radio_clicks`
FOR EACH ROW 
BEGIN
    DECLARE period_start_date DATE;
    DECLARE period_end_date DATE;
    
    -- Calcular período quinzenal atual
    SET period_start_date = DATE_SUB(CURDATE(), INTERVAL 14 DAY);
    SET period_end_date = CURDATE();
    
    -- Inserir ou atualizar estatística do período atual
    INSERT INTO `radio_statistics` (`radio_id`, `access_count`, `period_start`, `period_end`)
    VALUES (NEW.`radio_id`, 1, period_start_date, period_end_date)
    ON DUPLICATE KEY UPDATE 
        `access_count` = `access_count` + 1,
        `last_updated` = CURRENT_TIMESTAMP;
END$$
DELIMITER ;

-- =====================================================
-- 8. STORED PROCEDURES
-- =====================================================

-- Procedure para calcular estatísticas quinzenais
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `CalculateFortnightlyStats`()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE radio_id_var INT;
    DECLARE period_start_var DATE;
    DECLARE period_end_var DATE;
    DECLARE access_count_var INT;
    
    -- Cursor para percorrer todas as rádios ativas
    DECLARE radio_cursor CURSOR FOR 
        SELECT `id` FROM `radios` WHERE `status` = 'active';
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Calcular o período quinzenal atual
    SET period_start_var = DATE_SUB(CURDATE(), INTERVAL 14 DAY);
    SET period_end_var = CURDATE();
    
    OPEN radio_cursor;
    
    radio_loop: LOOP
        FETCH radio_cursor INTO radio_id_var;
        IF done THEN
            LEAVE radio_loop;
        END IF;
        
        -- Contar os acessos no período para esta rádio
        SELECT COUNT(*) INTO access_count_var
        FROM `radio_clicks` 
        WHERE `radio_id` = radio_id_var 
        AND `clicked_at` >= period_start_var 
        AND `clicked_at` <= period_end_var;
        
        -- Inserir ou atualizar as estatísticas
        INSERT INTO `radio_statistics` (`radio_id`, `access_count`, `period_start`, `period_end`)
        VALUES (radio_id_var, access_count_var, period_start_var, period_end_var)
        ON DUPLICATE KEY UPDATE 
            `access_count` = access_count_var,
            `last_updated` = CURRENT_TIMESTAMP;
            
    END LOOP;
    
    CLOSE radio_cursor;
END$$
DELIMITER ;

-- Procedure para limpeza de dados antigos
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `CleanOldData`()
BEGIN
    -- Remover cliques mais antigos que 6 meses
    DELETE FROM `radio_clicks` 
    WHERE `clicked_at` < DATE_SUB(CURDATE(), INTERVAL 6 MONTH);
    
    -- Remover estatísticas mais antigas que 2 anos
    DELETE FROM `radio_statistics` 
    WHERE `period_start` < DATE_SUB(CURDATE(), INTERVAL 2 YEAR);
    
    -- Remover uploads órfãos (sem referência em rádios)
    DELETE FROM `file_uploads` 
    WHERE `created_at` < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    AND `stored_filename` NOT IN (
        SELECT SUBSTRING_INDEX(`logo_url`, '/', -1) 
        FROM `radios` 
        WHERE `logo_url` IS NOT NULL
    );
END$$
DELIMITER ;

-- =====================================================
-- 9. VIEWS ÚTEIS
-- =====================================================

-- View para estatísticas resumidas das rádios
CREATE OR REPLACE VIEW `radio_stats_summary` AS
SELECT 
    r.`id`,
    r.`radio_name`,
    r.`country`,
    r.`language`,
    r.`status`,
    r.`total_clicks`,
    r.`created_at`,
    COALESCE(SUM(rs.`access_count`), 0) as `total_period_access`,
    COUNT(rs.`id`) as `periods_tracked`,
    COALESCE(AVG(rs.`access_count`), 0) as `avg_period_access`,
    MAX(rs.`period_end`) as `last_tracked_period`
FROM `radios` r
LEFT JOIN `radio_statistics` rs ON r.`id` = rs.`radio_id`
GROUP BY r.`id`, r.`radio_name`, r.`country`, r.`language`, r.`status`, r.`total_clicks`, r.`created_at`;

-- View para rádios populares
CREATE OR REPLACE VIEW `popular_radios` AS
SELECT 
    r.*,
    COALESCE(recent_clicks.`recent_count`, 0) as `recent_clicks`
FROM `radios` r
LEFT JOIN (
    SELECT 
        `radio_id`,
        COUNT(*) as `recent_count`
    FROM `radio_clicks`
    WHERE `clicked_at` >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY `radio_id`
) recent_clicks ON r.`id` = recent_clicks.`radio_id`
WHERE r.`status` = 'active'
ORDER BY r.`total_clicks` DESC, recent_clicks.`recent_count` DESC;

-- =====================================================
-- 10. CONFIGURAÇÕES INICIAIS DO SISTEMA
-- =====================================================

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('site_name', 'RadioWave', 'string', 'Nome do site'),
('site_description', 'Portal de Rádios Online', 'string', 'Descrição do site'),
('max_upload_size', '5242880', 'integer', 'Tamanho máximo de upload em bytes (5MB)'),
('allowed_file_types', '["image/jpeg","image/png","image/gif","image/webp"]', 'json', 'Tipos de arquivo permitidos para upload'),
('auto_approve_radios', 'false', 'boolean', 'Aprovar rádios automaticamente'),
('stats_calculation_enabled', 'true', 'boolean', 'Habilitar cálculo automático de estatísticas'),
('cleanup_enabled', 'true', 'boolean', 'Habilitar limpeza automática de dados antigos'),
('maintenance_mode', 'false', 'boolean', 'Modo de manutenção'),
('api_rate_limit', '100', 'integer', 'Limite de requisições por IP por hora'),
('default_radio_status', 'pending', 'string', 'Status padrão para novas rádios')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- =====================================================
-- 11. DADOS DE EXEMPLO
-- =====================================================

-- Inserir rádio de exemplo
INSERT INTO `radios` (
    `name`, `email`, `radio_name`, `stream_url`, `logo_url`, 
    `brief_description`, `detailed_description`, `genres`, 
    `country`, `language`, `website`, `status`
) VALUES 
(
    'Administrador Sistema', 
    'admin@radiowave.com', 
    'RadioWave FM', 
    'https://stream.radiowave.com:8888/live', 
    NULL,
    'Rádio oficial do portal RadioWave com música variada 24 horas',
    'RadioWave FM é a rádio oficial do portal. Oferecemos uma programação diversificada com os melhores hits nacionais e internacionais, notícias e entretenimento. Sintonize conosco e desfrute da melhor experiência musical online!',
    '["Pop", "Rock", "MPB", "Eletrônica", "Hits"]',
    'Brasil',
    'portuguese',
    'https://radiowave.com',
    'active'
),
(
    'João Silva', 
    'joao@exemplo.com', 
    'Rádio Exemplo Rock', 
    'https://exemplo.com:8000/rock', 
    NULL,
    'A melhor rádio de rock nacional e internacional',
    'Rádio Exemplo Rock - Desde 2020 levando o melhor do rock para seus ouvidos. Rock clássico, nacional, internacional e muito mais. Entre em contato pelo WhatsApp: (11) 99999-9999',
    '["Rock", "Classic Rock", "Hard Rock", "Metal", "Rock Nacional"]',
    'Brasil',
    'portuguese',
    'https://exemplorock.com',
    'active'
),
(
    'Maria Santos', 
    'maria@sertanejo.com', 
    'Sertanejo Total FM', 
    'https://sertanejototal.com:8888/stream', 
    NULL,
    'Sertanejo universitário e raiz 24 horas no ar',
    'Sertanejo Total FM - A rádio que toca o melhor do sertanejo universitário e raiz. Acompanhe os maiores sucessos e novidades do mundo sertanejo. Acesse nosso site e redes sociais!',
    '["Sertanejo", "Sertanejo Universitário", "Música Brasileira", "Country"]',
    'Brasil',
    'portuguese',
    'https://sertanejototal.com',
    'active'
)
ON DUPLICATE KEY UPDATE `radio_name` = VALUES(`radio_name`);

-- Inserir alguns cliques de exemplo
INSERT INTO `radio_clicks` (`radio_id`, `ip_address`, `user_agent`, `referrer`) VALUES
(1, '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'https://radiowave.com'),
(1, '192.168.1.101', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)', 'https://google.com'),
(2, '192.168.1.102', 'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0', 'https://radiowave.com'),
(2, '192.168.1.103', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15', 'https://radiowave.com'),
(3, '192.168.1.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:88.0) Gecko/20100101 Firefox/88.0', 'https://radiowave.com');

-- Inserir estatísticas de exemplo
INSERT INTO `radio_statistics` (`radio_id`, `access_count`, `period_start`, `period_end`) VALUES
(1, 150, DATE_SUB(CURDATE(), INTERVAL 14 DAY), CURDATE()),
(1, 120, DATE_SUB(CURDATE(), INTERVAL 28 DAY), DATE_SUB(CURDATE(), INTERVAL 14 DAY)),
(1, 95, DATE_SUB(CURDATE(), INTERVAL 42 DAY), DATE_SUB(CURDATE(), INTERVAL 28 DAY)),
(2, 89, DATE_SUB(CURDATE(), INTERVAL 14 DAY), CURDATE()),
(2, 95, DATE_SUB(CURDATE(), INTERVAL 28 DAY), DATE_SUB(CURDATE(), INTERVAL 14 DAY)),
(3, 67, DATE_SUB(CURDATE(), INTERVAL 14 DAY), CURDATE()),
(3, 72, DATE_SUB(CURDATE(), INTERVAL 28 DAY), DATE_SUB(CURDATE(), INTERVAL 14 DAY))
ON DUPLICATE KEY UPDATE `access_count` = VALUES(`access_count`);

-- =====================================================
-- 12. EVENTOS AUTOMÁTICOS (se suportado)
-- =====================================================

-- Evento para calcular estatísticas automaticamente (executar a cada 15 dias)
-- Descomente se o MySQL tiver eventos habilitados
/*
SET GLOBAL event_scheduler = ON;

CREATE EVENT IF NOT EXISTS `calculate_stats_event`
ON SCHEDULE EVERY 15 DAY
STARTS CURRENT_TIMESTAMP
DO
    CALL CalculateFortnightlyStats();

CREATE EVENT IF NOT EXISTS `cleanup_old_data_event`
ON SCHEDULE EVERY 1 MONTH
STARTS CURRENT_TIMESTAMP
DO
    CALL CleanOldData();
*/

-- =====================================================
-- 13. ÍNDICES ADICIONAIS PARA PERFORMANCE
-- =====================================================

-- Índices compostos para consultas frequentes
ALTER TABLE `radios` ADD INDEX `idx_status_country` (`status`, `country`);
ALTER TABLE `radios` ADD INDEX `idx_status_language` (`status`, `language`);
ALTER TABLE `radios` ADD INDEX `idx_status_clicks` (`status`, `total_clicks`);

ALTER TABLE `radio_clicks` ADD INDEX `idx_radio_ip_date` (`radio_id`, `ip_address`, `clicked_at`);

ALTER TABLE `radio_statistics` ADD INDEX `idx_radio_access_period` (`radio_id`, `access_count`, `period_start`);

-- =====================================================
-- FINALIZAÇÃO
-- =====================================================

-- Atualizar contadores de cliques baseado nos dados existentes
UPDATE `radios` r SET `total_clicks` = (
    SELECT COUNT(*) FROM `radio_clicks` rc WHERE rc.`radio_id` = r.`id`
);

-- Commit das transações
COMMIT;

-- Restaurar configurações
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- =====================================================
-- SCRIPT CONCLUÍDO COM SUCESSO!
-- =====================================================
-- 
-- Este script criou:
-- ✅ 6 tabelas principais
-- ✅ 2 triggers automáticos  
-- ✅ 2 stored procedures
-- ✅ 2 views úteis
-- ✅ Configurações do sistema
-- ✅ Dados de exemplo
-- ✅ Índices otimizados
-- ✅ Relacionamentos e constraints
-- 
-- O sistema RadioWave está pronto para uso!
-- =====================================================