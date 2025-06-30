-- Criação das tabelas para o RadioWave
-- Executado automaticamente pelo instalador

-- Tabela para armazenar as informações das rádios
CREATE TABLE IF NOT EXISTS radios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Nome do usuário que cadastrou',
    email VARCHAR(255) NOT NULL COMMENT 'Email do usuário que cadastrou',
    radio_name VARCHAR(255) NOT NULL COMMENT 'Nome da rádio',
    stream_url VARCHAR(2048) NOT NULL COMMENT 'URL do stream da rádio (HTTPS)',
    logo_url VARCHAR(2048) COMMENT 'URL da logo da rádio',
    brief_description TEXT NOT NULL COMMENT 'Descrição breve da rádio',
    detailed_description TEXT COMMENT 'Descrição detalhada da rádio',
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
CREATE TRIGGER IF NOT EXISTS update_radio_clicks_count 
AFTER INSERT ON radio_clicks
FOR EACH ROW
BEGIN
    UPDATE radios 
    SET total_clicks = total_clicks + 1 
    WHERE id = NEW.radio_id;
END//
DELIMITER ;

-- Inserir dados de exemplo
INSERT IGNORE INTO radios (
    name, email, radio_name, stream_url, brief_description, 
    detailed_description, genres, country, language, status
) VALUES 
(
    'Administrador', 
    'admin@radiowave.com', 
    'RadioWave FM', 
    'https://exemplo.com:8888/stream', 
    'Rádio oficial do sistema RadioWave',
    'RadioWave FM - A rádio oficial do portal. Música variada 24 horas por dia.',
    '["Pop", "Rock", "MPB", "Eletrônica"]',
    'Brasil',
    'portuguese',
    'active'
);

-- Inserir estatísticas de exemplo
INSERT IGNORE INTO radio_statistics (radio_id, access_count, period_start, period_end) VALUES
(1, 150, DATE_SUB(CURDATE(), INTERVAL 14 DAY), CURDATE()),
(1, 120, DATE_SUB(CURDATE(), INTERVAL 28 DAY), DATE_SUB(CURDATE(), INTERVAL 14 DAY));
```