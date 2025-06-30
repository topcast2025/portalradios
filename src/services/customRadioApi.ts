// API para rádios customizadas (backend PHP)
import axios from 'axios'
import { CustomRadio, RadioRegistrationData, RadioStatistics } from '../types/customRadio'

const API_BASE_URL = process.env.NODE_ENV === 'production' 
  ? 'https://wave.soradios.online/api' 
  : 'http://localhost/api'

class CustomRadioAPI {
  private api = axios.create({
    baseURL: API_BASE_URL,
    timeout: 10000,
  })

  // Registrar nova rádio
  async registerRadio(data: RadioRegistrationData): Promise<{ success: boolean; radioId?: number; message: string }> {
    try {
      const response = await this.api.post('/radios', data)
      return response.data
    } catch (error: any) {
      console.error('Error registering radio:', error)
      throw new Error(error.response?.data?.message || 'Erro ao cadastrar rádio')
    }
  }

  // Upload de logo
  async uploadLogo(file: File): Promise<{ success: boolean; logoUrl?: string; message: string }> {
    try {
      const formData = new FormData()
      formData.append('logo', file)

      const response = await this.api.post('/upload-logo', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })
      return response.data
    } catch (error: any) {
      console.error('Error uploading logo:', error)
      throw new Error(error.response?.data?.message || 'Erro ao fazer upload da logo')
    }
  }

  // Buscar rádios customizadas
  async getCustomRadios(page: number = 1, limit: number = 20): Promise<{ radios: CustomRadio[]; total: number }> {
    try {
      const response = await this.api.get(`/radios?page=${page}&limit=${limit}`)
      return response.data.data
    } catch (error) {
      console.error('Error fetching custom radios:', error)
      throw new Error('Erro ao carregar rádios customizadas')
    }
  }

  // Buscar rádio por ID
  async getRadioById(id: number): Promise<CustomRadio> {
    try {
      const response = await this.api.get(`/radios/${id}`)
      return response.data.data
    } catch (error) {
      console.error('Error fetching radio by ID:', error)
      throw new Error('Erro ao carregar detalhes da rádio')
    }
  }

  // Atualizar rádio
  async updateRadio(id: number, data: Partial<RadioRegistrationData>): Promise<{ success: boolean; message: string }> {
    try {
      const response = await this.api.put(`/radios/${id}`, data)
      return response.data
    } catch (error: any) {
      console.error('Error updating radio:', error)
      throw new Error(error.response?.data?.message || 'Erro ao atualizar rádio')
    }
  }

  // Deletar rádio
  async deleteRadio(id: number): Promise<{ success: boolean; message: string }> {
    try {
      const response = await this.api.delete(`/radios/${id}`)
      return response.data
    } catch (error: any) {
      console.error('Error deleting radio:', error)
      throw new Error(error.response?.data?.message || 'Erro ao deletar rádio')
    }
  }

  // Reportar erro em rádio
  async reportRadioError(radioId: number, errorDescription: string, userEmail?: string): Promise<{ success: boolean; message: string }> {
    try {
      const response = await this.api.post(`/radios/${radioId}/report`, {
        errorDescription,
        userEmail
      })
      return response.data
    } catch (error: any) {
      console.error('Error reporting radio error:', error)
      throw new Error(error.response?.data?.message || 'Erro ao reportar problema')
    }
  }

  // Registrar clique/acesso
  async registerClick(radioId: number): Promise<void> {
    try {
      await this.api.post(`/radios/${radioId}/click`)
    } catch (error) {
      console.error('Error registering click:', error)
    }
  }

  // Buscar estatísticas da rádio
  async getRadioStatistics(radioId: number): Promise<RadioStatistics[]> {
    try {
      const response = await this.api.get(`/radios/${radioId}/statistics`)
      return response.data.data
    } catch (error) {
      console.error('Error fetching radio statistics:', error)
      throw new Error('Erro ao carregar estatísticas')
    }
  }
}

export const customRadioAPI = new CustomRadioAPI()