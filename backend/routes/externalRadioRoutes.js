const express = require('express');
const axios = require('axios');
const router = express.Router();

const RADIO_BROWSER_API = 'https://de1.api.radio-browser.info/json';

// Create axios instance for external API
const externalAPI = axios.create({
  baseURL: RADIO_BROWSER_API,
  timeout: 15000,
  headers: {
    'User-Agent': 'Radion/1.0.0'
  }
});

// Get top stations
router.get('/stations/topvote/:limit', async (req, res) => {
  try {
    const { limit } = req.params;
    const response = await externalAPI.get(`/stations/topvote/${limit}`);
    res.json(response.data);
  } catch (error) {
    console.error('Error fetching top stations:', error.message);
    res.status(500).json({ 
      success: false, 
      message: 'Erro ao carregar estações populares' 
    });
  }
});

// Search stations
router.get('/stations/search', async (req, res) => {
  try {
    const queryString = new URLSearchParams(req.query).toString();
    const response = await externalAPI.get(`/stations/search?${queryString}`);
    res.json(response.data);
  } catch (error) {
    console.error('Error searching stations:', error.message);
    res.status(500).json({ 
      success: false, 
      message: 'Erro na busca de estações' 
    });
  }
});

// Get stations by country
router.get('/stations/bycountry/:country', async (req, res) => {
  try {
    const { country } = req.params;
    const response = await externalAPI.get(`/stations/bycountry/${encodeURIComponent(country)}`);
    res.json(response.data);
  } catch (error) {
    console.error('Error fetching stations by country:', error.message);
    res.status(500).json({ 
      success: false, 
      message: 'Erro ao carregar estações por país' 
    });
  }
});

// Get stations by language
router.get('/stations/bylanguage/:language', async (req, res) => {
  try {
    const { language } = req.params;
    const response = await externalAPI.get(`/stations/bylanguage/${encodeURIComponent(language)}`);
    res.json(response.data);
  } catch (error) {
    console.error('Error fetching stations by language:', error.message);
    res.status(500).json({ 
      success: false, 
      message: 'Erro ao carregar estações por idioma' 
    });
  }
});

// Get stations by tag
router.get('/stations/bytag/:tag', async (req, res) => {
  try {
    const { tag } = req.params;
    const response = await externalAPI.get(`/stations/bytag/${encodeURIComponent(tag)}`);
    res.json(response.data);
  } catch (error) {
    console.error('Error fetching stations by tag:', error.message);
    res.status(500).json({ 
      success: false, 
      message: 'Erro ao carregar estações por categoria' 
    });
  }
});

// Get countries
router.get('/countries', async (req, res) => {
  try {
    const response = await externalAPI.get('/countries');
    res.json(response.data);
  } catch (error) {
    console.error('Error fetching countries:', error.message);
    res.status(500).json({ 
      success: false, 
      message: 'Erro ao carregar países' 
    });
  }
});

// Get languages
router.get('/languages', async (req, res) => {
  try {
    const response = await externalAPI.get('/languages');
    res.json(response.data);
  } catch (error) {
    console.error('Error fetching languages:', error.message);
    res.status(500).json({ 
      success: false, 
      message: 'Erro ao carregar idiomas' 
    });
  }
});

// Get tags
router.get('/tags', async (req, res) => {
  try {
    const response = await externalAPI.get('/tags');
    res.json(response.data);
  } catch (error) {
    console.error('Error fetching tags:', error.message);
    res.status(500).json({ 
      success: false, 
      message: 'Erro ao carregar categorias' 
    });
  }
});

// Register click
router.get('/url/:stationId', async (req, res) => {
  try {
    const { stationId } = req.params;
    const response = await externalAPI.get(`/url/${stationId}`);
    res.json(response.data);
  } catch (error) {
    console.error('Error registering click:', error.message);
    res.status(500).json({ 
      success: false, 
      message: 'Erro ao registrar clique' 
    });
  }
});

module.exports = router;