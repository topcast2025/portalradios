-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS soradios_radion;

-- Criação do usuário e concessão de privilégios
-- Substitua 'Ant130915!' pela senha desejada
CREATE USER IF NOT EXISTS 'soradios_radion'@'localhost' IDENTIFIED BY 'Ant130915!';
GRANT ALL PRIVILEGES ON soradios_radion.* TO 'soradios_radion'@'localhost';
FLUSH PRIVILEGES;

-- Seleciona o banco de dados
USE soradios_radion;

-- Tabela para armazenar as informações das rádios
CREATE TABLE IF NOT EXISTS radios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Nome do usuário que cadastrou',
    email VARCHAR(255) NOT NULL COMMENT 'Email do usuário que cadastrou',
    radio_name VARCHAR(255) NOT NULL COMMENT 'Nome da rádio',
    stream_url VARCHAR(2048) NOT NULL COMMENT 'URL do stream da rádio (HTTPS)',
    logo_url VARCHAR(2048) COMMENT 'URL da logo da rádio',
    brief_description TEXT NOT NULL COMMENT 'Descrição breve da rádio',
    detailed_description TEXT COMMENT 'Descrição detalhada da rádio (incluindo whatsapp, site, etc.)',
    genres JSON COMMENT 'Gêneros da rádio em formato JSON',
    country VARCHAR(255) NOT NULL COMMENT 'País de origem da rádio',
    language VARCHAR(255) NOT NULL COMMENT 'Idioma da rádio',
    website VARCHAR(2048) COMMENT 'Website da rádio',
    whatsapp VARCHAR(50) COMMENT 'WhatsApp da rádio',
    facebook VARCHAR(2048) COMMENT 'Facebook da rádio',
    instagram VARCHAR(2048) COMMENT 'Instagram da rádio',
    twitter VARCHAR(2048) COMMENT 'Twitter da rádio',
    status ENUM('active', 'pending', 'inactive') DEFAULT 'pending' COMMENT 'Status da rádio',
    total_clicks INT DEFAULT 0 COMMENT 'Total de cliques/acessos',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_country (country),
    INDEX idx_language (language),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    FULLTEXT idx_search (radio_name, brief_description, detailed_description)
);

-- Tabela para armazenar estatísticas quinzenais de acesso
CREATE TABLE IF NOT EXISTS radio_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    radio_id INT NOT NULL,
    access_count INT DEFAULT 0 COMMENT 'Número de acessos no período',
    period_start DATE NOT NULL COMMENT 'Data de início do período',
    period_end DATE NOT NULL COMMENT 'Data de fim do período',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (radio_id) REFERENCES radios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_period (radio_id, period_start, period_end),
    INDEX idx_radio_period (radio_id, period_start),
    INDEX idx_period_dates (period_start, period_end)
);

-- Tabela para armazenar cliques/acessos individuais
CREATE TABLE IF NOT EXISTS radio_clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    radio_id INT NOT NULL,
    ip_address VARCHAR(45) COMMENT 'IP do usuário (IPv4 ou IPv6)',
    user_agent TEXT COMMENT 'User agent do navegador',
    referrer VARCHAR(2048) COMMENT 'Página de origem',
    clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (radio_id) REFERENCES radios(id) ON DELETE CASCADE,
    INDEX idx_radio_date (radio_id, clicked_at),
    INDEX idx_ip_date (ip_address, clicked_at)
);

-- Tabela para armazenar relatórios de erro
CREATE TABLE IF NOT EXISTS radio_error_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    radio_id INT NOT NULL,
    error_description TEXT NOT NULL COMMENT 'Descrição do erro reportado',
    user_email VARCHAR(255) COMMENT 'Email do usuário que reportou (opcional)',
    user_ip VARCHAR(45) COMMENT 'IP do usuário que reportou',
    status ENUM('pending', 'resolved', 'ignored') DEFAULT 'pending' COMMENT 'Status do report',
    admin_notes TEXT COMMENT 'Notas do administrador',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL COMMENT 'Data de resolução do problema',
    
    FOREIGN KEY (radio_id) REFERENCES radios(id) ON DELETE CASCADE,
    INDEX idx_radio_status (radio_id, status),
    INDEX idx_status_date (status, created_at)
);

-- Tabela para armazenar logs de upload de arquivos
CREATE TABLE IF NOT EXISTS file_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_filename VARCHAR(255) NOT NULL,
    stored_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(2048) NOT NULL,
    file_size INT NOT NULL COMMENT 'Tamanho do arquivo em bytes',
    mime_type VARCHAR(100) NOT NULL,
    upload_ip VARCHAR(45) COMMENT 'IP de quem fez o upload',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_stored_filename (stored_filename),
    INDEX idx_upload_date (created_at)
);

-- Trigger para atualizar total_clicks quando um novo clique é registrado
DELIMITER //
CREATE TRIGGER update_radio_clicks_count 
AFTER INSERT ON radio_clicks
FOR EACH ROW
BEGIN
    UPDATE radios 
    SET total_clicks = total_clicks + 1 
    WHERE id = NEW.radio_id;
END//
DELIMITER ;

-- Procedure para calcular estatísticas quinzenais
DELIMITER //
CREATE PROCEDURE CalculateFortnightlyStats()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE radio_id_var INT;
    DECLARE period_start_var DATE;
    DECLARE period_end_var DATE;
    DECLARE access_count_var INT;
    
    -- Cursor para percorrer todas as rádios
    DECLARE radio_cursor CURSOR FOR 
        SELECT id FROM radios WHERE status = 'active';
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Calcula o período quinzenal atual
    SET period_start_var = DATE_SUB(CURDATE(), INTERVAL 14 DAY);
    SET period_end_var = CURDATE();
    
    OPEN radio_cursor;
    
    radio_loop: LOOP
        FETCH radio_cursor INTO radio_id_var;
        IF done THEN
            LEAVE radio_loop;
        END IF;
        
        -- Conta os acessos no período para esta rádio
        SELECT COUNT(*) INTO access_count_var
        FROM radio_clicks 
        WHERE radio_id = radio_id_var 
        AND clicked_at >= period_start_var 
        AND clicked_at <= period_end_var;
        
        -- Insere ou atualiza as estatísticas
        INSERT INTO radio_statistics (radio_id, access_count, period_start, period_end)
        VALUES (radio_id_var, access_count_var, period_start_var, period_end_var)
        ON DUPLICATE KEY UPDATE 
            access_count = access_count_var,
            last_updated = CURRENT_TIMESTAMP;
            
    END LOOP;
    
    CLOSE radio_cursor;
END//
DELIMITER ;

-- Event para executar o cálculo de estatísticas automaticamente a cada 15 dias
-- (Descomente se o MySQL tiver eventos habilitados)
/*
CREATE EVENT IF NOT EXISTS calculate_stats_event
ON SCHEDULE EVERY 15 DAY
STARTS CURRENT_TIMESTAMP
DO
    CALL CalculateFortnightlyStats();
*/

-- Inserir dados de exemplo (opcional)
INSERT INTO radios (
    name, email, radio_name, stream_url, brief_description, 
    detailed_description, genres, country, language, status
) VALUES 
(
    'João Silva', 
    'joao@exemplo.com', 
    'Rádio Exemplo FM', 
    'https://exemplo.com:8888/stream', 
    'A melhor rádio de música brasileira',
    'Rádio Exemplo FM - Tocando o melhor da música brasileira 24 horas por dia. Entre em contato pelo WhatsApp: (11) 99999-9999',
    '["Música Brasileira", "MPB", "Sertanejo", "Forró"]',
    'Brasil',
    'portuguese',
    'active'
),
(
    'Maria Santos', 
    'maria@exemplo.com', 
    'Rock Station', 
    'https://rockstation.com:8000/live', 
    'Rock clássico e moderno 24h',
    'Rock Station - A estação que toca o melhor do rock nacional e internacional. Visite nosso site: www.rockstation.com',
    '["Rock", "Classic Rock", "Hard Rock", "Metal"]',
    'Brasil',
    'portuguese',
    'active'
);

-- Inserir algumas estatísticas de exemplo
INSERT INTO radio_statistics (radio_id, access_count, period_start, period_end) VALUES
(1, 150, DATE_SUB(CURDATE(), INTERVAL 14 DAY), CURDATE()),
(1, 120, DATE_SUB(CURDATE(), INTERVAL 28 DAY), DATE_SUB(CURDATE(), INTERVAL 14 DAY)),
(2, 89, DATE_SUB(CURDATE(), INTERVAL 14 DAY), CURDATE()),
(2, 95, DATE_SUB(CURDATE(), INTERVAL 28 DAY), DATE_SUB(CURDATE(), INTERVAL 14 DAY));