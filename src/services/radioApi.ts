import axios from 'axios'
import { RadioStation, SearchFilters } from '../types/radio'

const BASE_URL = 'https://de1.api.radio-browser.info/json'

class RadioAPI {
  private api = axios.create({
    baseURL: BASE_URL,
    timeout: 10000,
  })

  async getTopStations(limit: number = 50): Promise<RadioStation[]> {
    try {
      const response = await this.api.get(`/stations/topvote/${limit}`)
      return response.data
    } catch (error) {
      console.error('Error fetching top stations:', error)
      throw new Error('Falha ao carregar estações populares')
    }
  }

  async searchStations(filters: SearchFilters, limit: number = 100): Promise<RadioStation[]> {
    try {
      const params = new URLSearchParams()
      
      if (filters.name) params.append('name', filters.name)
      if (filters.country) params.append('country', filters.country)
      if (filters.language) params.append('language', filters.language)
      if (filters.tag) params.append('tag', filters.tag)
      
      params.append('limit', limit.toString())
      params.append('hidebroken', 'true')
      params.append('order', 'votes')
      params.append('reverse', 'true')

      const response = await this.api.get(`/stations/search?${params.toString()}`)
      return response.data
    } catch (error) {
      console.error('Error searching stations:', error)
      throw new Error('Falha na busca de estações')
    }
  }

  async getStationsByCountry(country: string, limit: number = 50): Promise<RadioStation[]> {
    try {
      const response = await this.api.get(`/stations/bycountry/${encodeURIComponent(country)}`)
      return response.data.slice(0, limit)
    } catch (error) {
      console.error('Error fetching stations by country:', error)
      throw new Error('Falha ao carregar estações por país')
    }
  }

  async getStationsByLanguage(language: string, limit: number = 50): Promise<RadioStation[]> {
    try {
      const response = await this.api.get(`/stations/bylanguage/${encodeURIComponent(language)}`)
      return response.data.slice(0, limit)
    } catch (error) {
      console.error('Error fetching stations by language:', error)
      throw new Error('Falha ao carregar estações por idioma')
    }
  }

  async getStationsByTag(tag: string, limit: number = 50): Promise<RadioStation[]> {
    try {
      const response = await this.api.get(`/stations/bytag/${encodeURIComponent(tag)}`)
      return response.data.slice(0, limit)
    } catch (error) {
      console.error('Error fetching stations by tag:', error)
      throw new Error('Falha ao carregar estações por categoria')
    }
  }

  async getCountries(): Promise<Array<{name: string, stationcount: number}>> {
    try {
      const response = await this.api.get('/countries')
      return response.data.sort((a: any, b: any) => b.stationcount - a.stationcount)
    } catch (error) {
      console.error('Error fetching countries:', error)
      return []
    }
  }

  async getLanguages(): Promise<Array<{name: string, stationcount: number}>> {
    try {
      const response = await this.api.get('/languages')
      return response.data.sort((a: any, b: any) => b.stationcount - a.stationcount)
    } catch (error) {
      console.error('Error fetching languages:', error)
      return []
    }
  }

  async getTags(): Promise<Array<{name: string, stationcount: number}>> {
    try {
      const response = await this.api.get('/tags')
      return response.data.sort((a: any, b: any) => b.stationcount - a.stationcount)
    } catch (error) {
      console.error('Error fetching tags:', error)
      return []
    }
  }

  async clickStation(stationId: string): Promise<void> {
    try {
      await this.api.get(`/url/${stationId}`)
    } catch (error) {
      console.error('Error registering click:', error)
    }
  }
}

export const radioAPI = new RadioAPI()