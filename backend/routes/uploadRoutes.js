const express = require('express');
const multer = require('multer');
const sharp = require('sharp');
const path = require('path');
const fs = require('fs').promises;
const { v4: uuidv4 } = require('uuid');
const db = require('../config/database');

const router = express.Router();

// Configure multer for file uploads
const storage = multer.memoryStorage();
const upload = multer({
  storage,
  limits: {
    fileSize: parseInt(process.env.MAX_FILE_SIZE) || 5 * 1024 * 1024, // 5MB
  },
  fileFilter: (req, file, cb) => {
    const allowedTypes = (process.env.ALLOWED_FILE_TYPES || 'image/jpeg,image/png,image/gif,image/webp').split(',');
    if (allowedTypes.includes(file.mimetype)) {
      cb(null, true);
    } else {
      cb(new Error('Tipo de arquivo não permitido'), false);
    }
  }
});

// Ensure upload directory exists
const uploadDir = process.env.UPLOAD_DIR || 'uploads';
const logoDir = path.join(uploadDir, 'logos');

async function ensureDirectoryExists(dir) {
  try {
    await fs.access(dir);
  } catch {
    await fs.mkdir(dir, { recursive: true });
  }
}

// Upload logo endpoint
router.post('/upload-logo', upload.single('logo'), async (req, res) => {
  try {
    if (!req.file) {
      return res.status(400).json({
        success: false,
        message: 'Nenhum arquivo enviado'
      });
    }

    await ensureDirectoryExists(logoDir);

    // Generate unique filename
    const fileExtension = path.extname(req.file.originalname);
    const filename = `${uuidv4()}${fileExtension}`;
    const filepath = path.join(logoDir, filename);

    // Process image with Sharp (resize and optimize)
    await sharp(req.file.buffer)
      .resize(400, 400, {
        fit: 'cover',
        position: 'center'
      })
      .jpeg({ quality: 85 })
      .toFile(filepath);

    // Get file stats
    const stats = await fs.stat(filepath);

    // Save upload record to database
    await db.execute(
      'INSERT INTO file_uploads (original_filename, stored_filename, file_path, file_size, mime_type, upload_ip) VALUES (?, ?, ?, ?, ?, ?)',
      [
        req.file.originalname,
        filename,
        filepath,
        stats.size,
        'image/jpeg', // Always JPEG after processing
        req.ip || req.connection.remoteAddress
      ]
    );

    // Return the URL
    const logoUrl = `${req.protocol}://${req.get('host')}/uploads/logos/${filename}`;

    res.json({
      success: true,
      message: 'Logo enviada com sucesso',
      data: {
        logoUrl,
        filename,
        size: stats.size
      }
    });

  } catch (error) {
    console.error('Error uploading logo:', error);
    
    if (error.message === 'Tipo de arquivo não permitido') {
      return res.status(400).json({
        success: false,
        message: 'Tipo de arquivo não permitido. Use apenas imagens (JPEG, PNG, GIF, WebP)'
      });
    }

    if (error.code === 'LIMIT_FILE_SIZE') {
      return res.status(400).json({
        success: false,
        message: 'Arquivo muito grande. Tamanho máximo: 5MB'
      });
    }

    res.status(500).json({
      success: false,
      message: 'Erro interno do servidor'
    });
  }
});

// Delete uploaded file endpoint
router.delete('/upload/:filename', async (req, res) => {
  try {
    const { filename } = req.params;
    
    // Validate filename (security)
    if (!/^[a-f0-9-]+\.(jpg|jpeg|png|gif|webp)$/i.test(filename)) {
      return res.status(400).json({
        success: false,
        message: 'Nome de arquivo inválido'
      });
    }

    const filepath = path.join(logoDir, filename);

    // Check if file exists in database
    const [files] = await db.execute(
      'SELECT id FROM file_uploads WHERE stored_filename = ?',
      [filename]
    );

    if (files.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Arquivo não encontrado'
      });
    }

    // Delete file from filesystem
    try {
      await fs.unlink(filepath);
    } catch (error) {
      console.warn('File not found on filesystem:', filepath);
    }

    // Remove from database
    await db.execute(
      'DELETE FROM file_uploads WHERE stored_filename = ?',
      [filename]
    );

    res.json({
      success: true,
      message: 'Arquivo removido com sucesso'
    });

  } catch (error) {
    console.error('Error deleting file:', error);
    res.status(500).json({
      success: false,
      message: 'Erro interno do servidor'
    });
  }
});

module.exports = router;