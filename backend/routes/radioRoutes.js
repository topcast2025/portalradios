const express = require('express');
const { body, param, query, validationResult } = require('express-validator');
const radioController = require('../controllers/radioController');
const router = express.Router();

// Validation middleware
const handleValidationErrors = (req, res, next) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.status(400).json({
      success: false,
      message: 'Dados inválidos',
      errors: errors.array()
    });
  }
  next();
};

// Validation rules
const radioValidation = [
  body('name').trim().isLength({ min: 2, max: 255 }).withMessage('Nome deve ter entre 2 e 255 caracteres'),
  body('email').isEmail().normalizeEmail().withMessage('Email inválido'),
  body('radio_name').trim().isLength({ min: 2, max: 255 }).withMessage('Nome da rádio deve ter entre 2 e 255 caracteres'),
  body('stream_url').isURL({ protocols: ['https'] }).withMessage('URL do stream deve ser HTTPS válida'),
  body('brief_description').trim().isLength({ min: 10, max: 500 }).withMessage('Descrição breve deve ter entre 10 e 500 caracteres'),
  body('detailed_description').optional().trim().isLength({ max: 5000 }).withMessage('Descrição detalhada deve ter no máximo 5000 caracteres'),
  body('genres').isArray({ min: 1 }).withMessage('Deve selecionar pelo menos um gênero'),
  body('country').trim().isLength({ min: 2, max: 100 }).withMessage('País é obrigatório'),
  body('language').trim().isLength({ min: 2, max: 50 }).withMessage('Idioma é obrigatório'),
  body('website').optional().isURL().withMessage('Website deve ser uma URL válida'),
  body('whatsapp').optional().trim().isLength({ max: 50 }),
  body('facebook').optional().isURL().withMessage('Facebook deve ser uma URL válida'),
  body('instagram').optional().isURL().withMessage('Instagram deve ser uma URL válida'),
  body('twitter').optional().isURL().withMessage('Twitter deve ser uma URL válida')
];

const updateRadioValidation = [
  param('id').isInt({ min: 1 }).withMessage('ID inválido'),
  ...radioValidation.map(rule => rule.optional())
];

// Routes
router.get('/', [
  query('page').optional().isInt({ min: 1 }).withMessage('Página deve ser um número positivo'),
  query('limit').optional().isInt({ min: 1, max: 100 }).withMessage('Limite deve ser entre 1 e 100'),
  query('country').optional().trim(),
  query('language').optional().trim(),
  query('genre').optional().trim(),
  query('search').optional().trim()
], handleValidationErrors, radioController.getRadios);

router.get('/:id', [
  param('id').isInt({ min: 1 }).withMessage('ID inválido')
], handleValidationErrors, radioController.getRadioById);

router.post('/', radioValidation, handleValidationErrors, radioController.createRadio);

router.put('/:id', updateRadioValidation, handleValidationErrors, radioController.updateRadio);

router.delete('/:id', [
  param('id').isInt({ min: 1 }).withMessage('ID inválido')
], handleValidationErrors, radioController.deleteRadio);

router.post('/:id/click', [
  param('id').isInt({ min: 1 }).withMessage('ID inválido')
], handleValidationErrors, radioController.registerClick);

router.get('/:id/statistics', [
  param('id').isInt({ min: 1 }).withMessage('ID inválido')
], handleValidationErrors, radioController.getStatistics);

router.post('/:id/report', [
  param('id').isInt({ min: 1 }).withMessage('ID inválido'),
  body('errorDescription').trim().isLength({ min: 10, max: 1000 }).withMessage('Descrição do erro deve ter entre 10 e 1000 caracteres'),
  body('userEmail').optional().isEmail().normalizeEmail().withMessage('Email inválido')
], handleValidationErrors, radioController.reportError);

module.exports = router;