<?php
class Radio {
    private $conn;
    private $table_name = "radios";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all radios with pagination and filters
    public function getRadios($page = 1, $limit = 20, $filters = []) {
        try {
            $offset = ($page - 1) * $limit;
            
            $where_conditions = ["status = 'active'"];
            $params = [];

            if (!empty($filters['country'])) {
                $where_conditions[] = "country = :country";
                $params[':country'] = $filters['country'];
            }

            if (!empty($filters['language'])) {
                $where_conditions[] = "language = :language";
                $params[':language'] = $filters['language'];
            }

            if (!empty($filters['genre'])) {
                $where_conditions[] = "JSON_CONTAINS(genres, :genre)";
                $params[':genre'] = '"' . $filters['genre'] . '"';
            }

            if (!empty($filters['search'])) {
                $where_conditions[] = "(radio_name LIKE :search_like OR brief_description LIKE :search_like)";
                $params[':search_like'] = '%' . $filters['search'] . '%';
            }

            $where_clause = implode(' AND ', $where_conditions);

            // Get total count
            $count_query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE " . $where_clause;
            $count_stmt = $this->conn->prepare($count_query);
            $count_stmt->execute($params);
            $total = $count_stmt->fetch()['total'];

            // Get radios
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

            // Parse JSON genres
            foreach ($radios as &$radio) {
                $radio['genres'] = json_decode($radio['genres'] ?? '[]', true) ?: [];
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

    // Get radio by ID
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

            $radio['genres'] = json_decode($radio['genres'] ?? '[]', true) ?: [];

            return [
                'success' => true,
                'data' => $radio
            ];

        } catch (Exception $e) {
            error_log("Error in getRadioById: " . $e->getMessage());
            throw new Exception("Erro ao buscar rádio: " . $e->getMessage());
        }
    }

    // Create new radio
    public function createRadio($data) {
        try {
            // Check if radio name already exists
            $check_query = "SELECT id FROM " . $this->table_name . " WHERE radio_name = :radio_name";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':radio_name', $data['radio_name']);
            $check_stmt->execute();

            if ($check_stmt->fetch()) {
                throw new Exception('Já existe uma rádio com este nome');
            }

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
            $stmt->bindParam(':logo_url', $data['logo_url']);
            $stmt->bindParam(':brief_description', $data['brief_description']);
            $stmt->bindParam(':detailed_description', $data['detailed_description']);
            
            $genres_json = json_encode($data['genres']);
            $stmt->bindParam(':genres', $genres_json);
            
            $stmt->bindParam(':country', $data['country']);
            $stmt->bindParam(':language', $data['language']);
            $stmt->bindParam(':website', $data['website']);
            $stmt->bindParam(':whatsapp', $data['whatsapp']);
            $stmt->bindParam(':facebook', $data['facebook']);
            $stmt->bindParam(':instagram', $data['instagram']);
            $stmt->bindParam(':twitter', $data['twitter']);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Rádio cadastrada com sucesso! Aguarde aprovação.',
                    'data' => ['radioId' => $this->conn->lastInsertId()]
                ];
            }

            throw new Exception('Erro ao executar query de inserção');

        } catch (Exception $e) {
            error_log("Error in createRadio: " . $e->getMessage());
            throw new Exception("Erro ao cadastrar rádio: " . $e->getMessage());
        }
    }

    // Register click
    public function registerClick($id, $ip, $userAgent, $referrer) {
        try {
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
                'message' => 'Clique registrado'
            ];

        } catch (Exception $e) {
            error_log("Error in registerClick: " . $e->getMessage());
            throw new Exception("Erro ao registrar clique: " . $e->getMessage());
        }
    }

    // Get statistics
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

            return [
                'success' => true,
                'data' => $stmt->fetchAll()
            ];

        } catch (Exception $e) {
            error_log("Error in getStatistics: " . $e->getMessage());
            throw new Exception("Erro ao buscar estatísticas: " . $e->getMessage());
        }
    }

    // Report error
    public function reportError($id, $errorDescription, $userEmail, $userIp) {
        try {
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