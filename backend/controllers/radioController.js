const db = require('../config/database');

class RadioController {
  // Get all radios with pagination and filters
  async getRadios(req, res) {
    try {
      const page = parseInt(req.query.page) || 1;
      const limit = parseInt(req.query.limit) || 20;
      const offset = (page - 1) * limit;
      const { country, language, genre, search } = req.query;

      let whereConditions = ['status = "active"'];
      let queryParams = [];

      if (country) {
        whereConditions.push('country = ?');
        queryParams.push(country);
      }

      if (language) {
        whereConditions.push('language = ?');
        queryParams.push(language);
      }

      if (genre) {
        whereConditions.push('JSON_CONTAINS(genres, ?)');
        queryParams.push(`"${genre}"`);
      }

      if (search) {
        whereConditions.push('(MATCH(radio_name, brief_description, detailed_description) AGAINST(? IN NATURAL LANGUAGE MODE) OR radio_name LIKE ?)');
        queryParams.push(search, `%${search}%`);
      }

      const whereClause = whereConditions.length > 0 ? `WHERE ${whereConditions.join(' AND ')}` : '';

      // Get total count
      const [countResult] = await db.execute(
        `SELECT COUNT(*) as total FROM radios ${whereClause}`,
        queryParams
      );
      const total = countResult[0].total;

      // Get radios
      const [radios] = await db.execute(
        `SELECT id, name, email, radio_name, stream_url, logo_url, brief_description, 
         detailed_description, genres, country, language, website, whatsapp, facebook, 
         instagram, twitter, total_clicks, created_at, updated_at
         FROM radios ${whereClause} 
         ORDER BY total_clicks DESC, created_at DESC 
         LIMIT ? OFFSET ?`,
        [...queryParams, limit, offset]
      );

      // Parse JSON genres
      const processedRadios = radios.map(radio => ({
        ...radio,
        genres: JSON.parse(radio.genres || '[]')
      }));

      res.json({
        success: true,
        data: {
          radios: processedRadios,
          pagination: {
            page,
            limit,
            total,
            totalPages: Math.ceil(total / limit)
          }
        }
      });
    } catch (error) {
      console.error('Error getting radios:', error);
      res.status(500).json({
        success: false,
        message: 'Erro interno do servidor'
      });
    }
  }

  // Get radio by ID
  async getRadioById(req, res) {
    try {
      const { id } = req.params;

      const [radios] = await db.execute(
        `SELECT id, name, email, radio_name, stream_url, logo_url, brief_description, 
         detailed_description, genres, country, language, website, whatsapp, facebook, 
         instagram, twitter, total_clicks, created_at, updated_at
         FROM radios WHERE id = ? AND status = "active"`,
        [id]
      );

      if (radios.length === 0) {
        return res.status(404).json({
          success: false,
          message: 'Rádio não encontrada'
        });
      }

      const radio = {
        ...radios[0],
        genres: JSON.parse(radios[0].genres || '[]')
      };

      res.json({
        success: true,
        data: radio
      });
    } catch (error) {
      console.error('Error getting radio by ID:', error);
      res.status(500).json({
        success: false,
        message: 'Erro interno do servidor'
      });
    }
  }

  // Create new radio
  async createRadio(req, res) {
    try {
      const {
        name, email, radio_name, stream_url, logo_url, brief_description,
        detailed_description, genres, country, language, website, whatsapp,
        facebook, instagram, twitter
      } = req.body;

      // Check if radio name already exists
      const [existing] = await db.execute(
        'SELECT id FROM radios WHERE radio_name = ?',
        [radio_name]
      );

      if (existing.length > 0) {
        return res.status(400).json({
          success: false,
          message: 'Já existe uma rádio com este nome'
        });
      }

      const [result] = await db.execute(
        `INSERT INTO radios (
          name, email, radio_name, stream_url, logo_url, brief_description,
          detailed_description, genres, country, language, website, whatsapp,
          facebook, instagram, twitter, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')`,
        [
          name, email, radio_name, stream_url, logo_url, brief_description,
          detailed_description, JSON.stringify(genres), country, language,
          website, whatsapp, facebook, instagram, twitter
        ]
      );

      res.status(201).json({
        success: true,
        message: 'Rádio cadastrada com sucesso! Aguarde aprovação.',
        data: { radioId: result.insertId }
      });
    } catch (error) {
      console.error('Error creating radio:', error);
      res.status(500).json({
        success: false,
        message: 'Erro interno do servidor'
      });
    }
  }

  // Update radio
  async updateRadio(req, res) {
    try {
      const { id } = req.params;
      const updateData = req.body;

      // Remove undefined values
      Object.keys(updateData).forEach(key => {
        if (updateData[key] === undefined) {
          delete updateData[key];
        }
      });

      if (Object.keys(updateData).length === 0) {
        return res.status(400).json({
          success: false,
          message: 'Nenhum dado para atualizar'
        });
      }

      // Convert genres to JSON if present
      if (updateData.genres) {
        updateData.genres = JSON.stringify(updateData.genres);
      }

      const setClause = Object.keys(updateData).map(key => `${key} = ?`).join(', ');
      const values = Object.values(updateData);

      const [result] = await db.execute(
        `UPDATE radios SET ${setClause}, updated_at = CURRENT_TIMESTAMP WHERE id = ?`,
        [...values, id]
      );

      if (result.affectedRows === 0) {
        return res.status(404).json({
          success: false,
          message: 'Rádio não encontrada'
        });
      }

      res.json({
        success: true,
        message: 'Rádio atualizada com sucesso'
      });
    } catch (error) {
      console.error('Error updating radio:', error);
      res.status(500).json({
        success: false,
        message: 'Erro interno do servidor'
      });
    }
  }

  // Delete radio
  async deleteRadio(req, res) {
    try {
      const { id } = req.params;

      const [result] = await db.execute(
        'UPDATE radios SET status = "inactive" WHERE id = ?',
        [id]
      );

      if (result.affectedRows === 0) {
        return res.status(404).json({
          success: false,
          message: 'Rádio não encontrada'
        });
      }

      res.json({
        success: true,
        message: 'Rádio removida com sucesso'
      });
    } catch (error) {
      console.error('Error deleting radio:', error);
      res.status(500).json({
        success: false,
        message: 'Erro interno do servidor'
      });
    }
  }

  // Register click/access
  async registerClick(req, res) {
    try {
      const { id } = req.params;
      const ip = req.ip || req.connection.remoteAddress;
      const userAgent = req.get('User-Agent');
      const referrer = req.get('Referrer');

      // Insert click record
      await db.execute(
        'INSERT INTO radio_clicks (radio_id, ip_address, user_agent, referrer) VALUES (?, ?, ?, ?)',
        [id, ip, userAgent, referrer]
      );

      res.json({
        success: true,
        message: 'Clique registrado'
      });
    } catch (error) {
      console.error('Error registering click:', error);
      res.status(500).json({
        success: false,
        message: 'Erro interno do servidor'
      });
    }
  }

  // Get radio statistics
  async getStatistics(req, res) {
    try {
      const { id } = req.params;

      const [statistics] = await db.execute(
        `SELECT id, radio_id, access_count, period_start, period_end, last_updated
         FROM radio_statistics 
         WHERE radio_id = ? 
         ORDER BY period_start DESC 
         LIMIT 20`,
        [id]
      );

      res.json({
        success: true,
        data: statistics
      });
    } catch (error) {
      console.error('Error getting statistics:', error);
      res.status(500).json({
        success: false,
        message: 'Erro interno do servidor'
      });
    }
  }

  // Report error
  async reportError(req, res) {
    try {
      const { id } = req.params;
      const { errorDescription, userEmail } = req.body;
      const ip = req.ip || req.connection.remoteAddress;

      await db.execute(
        'INSERT INTO radio_error_reports (radio_id, error_description, user_email, user_ip) VALUES (?, ?, ?, ?)',
        [id, errorDescription, userEmail, ip]
      );

      res.json({
        success: true,
        message: 'Problema reportado com sucesso'
      });
    } catch (error) {
      console.error('Error reporting error:', error);
      res.status(500).json({
        success: false,
        message: 'Erro interno do servidor'
      });
    }
  }
}

module.exports = new RadioController();