<?php
/**
 * Model para gerenciar rádios customizadas
 */

class Radio {
    private $conn;
    private $table_name = "radios";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Buscar todas as rádios com paginação e filtros
     */
    public function getRadios($page = 1, $limit = 20, $filters = []) {
        try {
            $offset = ($page - 1) * $limit;
            
            // Condições WHERE
            $where_conditions = ["status = 'active'"];
            $params = [];

            // Filtro por país
            if (!empty($filters['country'])) {
                $where_conditions[] = "country LIKE :country";
                $params[':country'] = '%' . $filters['country'] . '%';
            }

            // Filtro por idioma
            if (!empty($filters['language'])) {
                $where_conditions[] = "language LIKE :language";
                $params[':language'] = '%' . $filters['language'] . '%';
            }

            // Filtro por gênero
            if (!empty($filters['genre'])) {
                $where_conditions[] = "JSON_CONTAINS(genres, :genre)";
                $params[':genre'] = '"' . $filters['genre'] . '"';
            }

            // Filtro por busca textual
            if (!empty($filters['search'])) {
                $where_conditions[] = "(radio_name LIKE :search_like OR brief_description LIKE :search_like OR detailed_description LIKE :search_like)";
                $params[':search_like'] = '%' . $filters['search'] . '%';
            }

            $where_clause = implode(' AND ', $where_conditions);

            // Contar total de registros
            $count_query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE " . $where_clause;
            $count_stmt = $this->conn->prepare($count_query);
            $count_stmt->execute($params);
            $total = $count_stmt->fetch()['total'];

            // Buscar rádios
            $query = "SELECT id, name, email, radio_name, stream_url, logo_url, brief_description, 
                      detailed_description, genres, country, language, website, whatsapp, facebook, 
                      instagram, twitter, total_clicks, created_at, updated_at
                      FROM " . $this->table_name . " 
                      WHERE " . $where_clause . " 
                      ORDER BY total_clicks DESC, created_at DESC 
                      LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $radios = $stmt->fetchAll();

            // Processar dados
            foreach ($radios as &$radio) {
                $radio['genres'] = json_decode($radio['genres'] ?? '[]', true) ?: [];
                $radio['total_clicks'] = (int)$radio['total_clicks'];
                $radio['id'] = (int)$radio['id'];
            }

            return [
                'success' => true,
                'data' => [
                    'radios' => $radios,
                    'pagination' => [
                        'page' => (int)$page,
                        'limit' => (int)$limit,
                        'total' => (int)$total,
                        'totalPages' => ceil($total / $limit)
                    ]
                ]
            ];

        } catch (Exception $e) {
            error_log("Error in getRadios: " . $e->getMessage());
            throw new Exception("Erro ao buscar rádios: " . $e->getMessage());
        }
    }

    /**
     * Buscar rádio por ID
     */
    public function getRadioById($id) {
        try {
            $query = "SELECT id, name, email, radio_name, stream_url, logo_url, brief_description, 
                      detailed_description, genres, country, language, website, whatsapp, facebook, 
                      instagram, twitter, total_clicks, created_at, updated_at
                      FROM " . $this->table_name . " 
                      WHERE id = :id AND status = 'active'";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $radio = $stmt->fetch();

            if (!$radio) {
                throw new Exception('Rádio não encontrada');
            }

            // Processar dados
            $radio['genres'] = json_decode($radio['genres'] ?? '[]', true) ?: [];
            $radio['total_clicks'] = (int)$radio['total_clicks'];
            $radio['id'] = (int)$radio['id'];

            return [
                'success' => true,
                'data' => $radio
            ];

        } catch (Exception $e) {
            error_log("Error in getRadioById: " . $e->getMessage());
            throw new Exception("Erro ao buscar rádio: " . $e->getMessage());
        }
    }

    /**
     * Criar nova rádio
     */
    public function createRadio($data) {
        try {
            // Verificar se já existe rádio com mesmo nome
            $check_query = "SELECT id FROM " . $this->table_name . " WHERE radio_name = :radio_name";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':radio_name', $data['radio_name']);
            $check_stmt->execute();

            if ($check_stmt->fetch()) {
                throw new Exception('Já existe uma rádio com este nome');
            }

            // Preparar dados
            $genres_json = json_encode($data['genres']);
            $logo_url = $data['logo_url'] ?? '';
            $detailed_description = $data['detailed_description'] ?? '';
            $website = $data['website'] ?? '';
            $whatsapp = $data['whatsapp'] ?? '';
            $facebook = $data['facebook'] ?? '';
            $instagram = $data['instagram'] ?? '';
            $twitter = $data['twitter'] ?? '';

            // Inserir rádio
            $query = "INSERT INTO " . $this->table_name . " (
                        name, email, radio_name, stream_url, logo_url, brief_description,
                        detailed_description, genres, country, language, website, whatsapp,
                        facebook, instagram, twitter, status
                      ) VALUES (
                        :name, :email, :radio_name, :stream_url, :logo_url, :brief_description,
                        :detailed_description, :genres, :country, :language, :website, :whatsapp,
                        :facebook, :instagram, :twitter, 'pending'
                      )";

            $stmt = $this->conn->prepare($query);

            // Bind parameters
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':radio_name', $data['radio_name']);
            $stmt->bindParam(':stream_url', $data['stream_url']);
            $stmt->bindParam(':logo_url', $logo_url);
            $stmt->bindParam(':brief_description', $data['brief_description']);
            $stmt->bindParam(':detailed_description', $detailed_description);
            $stmt->bindParam(':genres', $genres_json);
            $stmt->bindParam(':country', $data['country']);
            $stmt->bindParam(':language', $data['language']);
            $stmt->bindParam(':website', $website);
            $stmt->bindParam(':whatsapp', $whatsapp);
            $stmt->bindParam(':facebook', $facebook);
            $stmt->bindParam(':instagram', $instagram);
            $stmt->bindParam(':twitter', $twitter);

            if ($stmt->execute()) {
                $radioId = $this->conn->lastInsertId();
                
                return [
                    'success' => true,
                    'message' => 'Rádio cadastrada com sucesso! Aguarde aprovação.',
                    'data' => ['radioId' => (int)$radioId]
                ];
            }

            throw new Exception('Erro ao executar query de inserção');

        } catch (Exception $e) {
            error_log("Error in createRadio: " . $e->getMessage());
            throw new Exception("Erro ao cadastrar rádio: " . $e->getMessage());
        }
    }

    /**
     * Atualizar rádio
     */
    public function updateRadio($id, $data) {
        try {
            // Verificar se a rádio existe
            $check_query = "SELECT id FROM " . $this->table_name . " WHERE id = :id";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $check_stmt->execute();

            if (!$check_stmt->fetch()) {
                throw new Exception('Rádio não encontrada');
            }

            // Remover campos vazios
            $data = array_filter($data, function($value) {
                return $value !== null && $value !== '';
            });

            if (empty($data)) {
                throw new Exception('Nenhum dado para atualizar');
            }

            // Processar gêneros se fornecidos
            if (isset($data['genres']) && is_array($data['genres'])) {
                $data['genres'] = json_encode($data['genres']);
            }

            // Construir query de atualização
            $set_clauses = [];
            foreach ($data as $key => $value) {
                $set_clauses[] = "$key = :$key";
            }
            $set_clause = implode(', ', $set_clauses);

            $query = "UPDATE " . $this->table_name . " 
                      SET $set_clause, updated_at = CURRENT_TIMESTAMP 
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Rádio atualizada com sucesso'
                ];
            }

            throw new Exception('Erro ao executar query de atualização');

        } catch (Exception $e) {
            error_log("Error in updateRadio: " . $e->getMessage());
            throw new Exception("Erro ao atualizar rádio: " . $e->getMessage());
        }
    }

    /**
     * Deletar rádio (soft delete)
     */
    public function deleteRadio($id) {
        try {
            $query = "UPDATE " . $this->table_name . " SET status = 'inactive' WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Rádio removida com sucesso'
                ];
            }

            throw new Exception('Rádio não encontrada');

        } catch (Exception $e) {
            error_log("Error in deleteRadio: " . $e->getMessage());
            throw new Exception("Erro ao remover rádio: " . $e->getMessage());
        }
    }

    /**
     * Registrar clique/acesso
     */
    public function registerClick($id, $ip, $userAgent, $referrer) {
        try {
            // Verificar se a rádio existe
            $check_query = "SELECT id FROM " . $this->table_name . " WHERE id = :id AND status = 'active'";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $check_stmt->execute();

            if (!$check_stmt->fetch()) {
                throw new Exception('Rádio não encontrada');
            }

            // Registrar clique
            $query = "INSERT INTO radio_clicks (radio_id, ip_address, user_agent, referrer) 
                      VALUES (:radio_id, :ip_address, :user_agent, :referrer)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':radio_id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':ip_address', $ip);
            $stmt->bindParam(':user_agent', $userAgent);
            $stmt->bindParam(':referrer', $referrer);

            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Clique registrado com sucesso'
            ];

        } catch (Exception $e) {
            error_log("Error in registerClick: " . $e->getMessage());
            throw new Exception("Erro ao registrar clique: " . $e->getMessage());
        }
    }

    /**
     * Buscar estatísticas da rádio
     */
    public function getStatistics($id) {
        try {
            $query = "SELECT id, radio_id, access_count, period_start, period_end, last_updated
                      FROM radio_statistics 
                      WHERE radio_id = :radio_id 
                      ORDER BY period_start DESC 
                      LIMIT 20";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':radio_id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $statistics = $stmt->fetchAll();

            // Processar dados
            foreach ($statistics as &$stat) {
                $stat['id'] = (int)$stat['id'];
                $stat['radio_id'] = (int)$stat['radio_id'];
                $stat['access_count'] = (int)$stat['access_count'];
            }

            return [
                'success' => true,
                'data' => $statistics
            ];

        } catch (Exception $e) {
            error_log("Error in getStatistics: " . $e->getMessage());
            throw new Exception("Erro ao buscar estatísticas: " . $e->getMessage());
        }
    }

    /**
     * Reportar erro
     */
    public function reportError($id, $errorDescription, $userEmail, $userIp) {
        try {
            // Verificar se a rádio existe
            $check_query = "SELECT id FROM " . $this->table_name . " WHERE id = :id";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $check_stmt->execute();

            if (!$check_stmt->fetch()) {
                throw new Exception('Rádio não encontrada');
            }

            // Inserir relatório de erro
            $query = "INSERT INTO radio_error_reports (radio_id, error_description, user_email, user_ip) 
                      VALUES (:radio_id, :error_description, :user_email, :user_ip)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':radio_id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':error_description', $errorDescription);
            $stmt->bindParam(':user_email', $userEmail);
            $stmt->bindParam(':user_ip', $userIp);

            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Problema reportado com sucesso'
            ];

        } catch (Exception $e) {
            error_log("Error in reportError: " . $e->getMessage());
            throw new Exception("Erro ao reportar problema: " . $e->getMessage());
        }
    }
}
?>