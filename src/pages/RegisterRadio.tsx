import React, { useState } from 'react'
import { motion } from 'framer-motion'
import { Radio, Upload, Save, X, Plus, Trash2, AlertCircle } from 'lucide-react'
import { customRadioAPI } from '../services/customRadioApi'
import { RadioRegistrationData, GENRES, COUNTRIES, LANGUAGES } from '../types/customRadio'
import toast from 'react-hot-toast'

const RegisterRadio: React.FC = () => {
  const [formData, setFormData] = useState<RadioRegistrationData>({
    name: '',
    email: '',
    radio_name: '',
    stream_url: '',
    brief_description: '',
    detailed_description: '',
    genres: [],
    country: '',
    language: '',
    website: '',
    whatsapp: '',
    facebook: '',
    instagram: '',
    twitter: ''
  })

  const [logoFile, setLogoFile] = useState<File | null>(null)
  const [logoPreview, setLogoPreview] = useState<string>('')
  const [loading, setLoading] = useState(false)
  const [errors, setErrors] = useState<Record<string, string>>({})

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target
    setFormData(prev => ({ ...prev, [name]: value }))
    
    // Clear error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }))
    }
  }

  const handleGenreToggle = (genre: string) => {
    setFormData(prev => ({
      ...prev,
      genres: prev.genres.includes(genre)
        ? prev.genres.filter(g => g !== genre)
        : [...prev.genres, genre]
    }))
  }

  const handleLogoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (file) {
      if (file.size > 5 * 1024 * 1024) { // 5MB limit
        toast.error('A logo deve ter no máximo 5MB')
        return
      }

      if (!file.type.startsWith('image/')) {
        toast.error('Por favor, selecione uma imagem válida')
        return
      }

      setLogoFile(file)
      
      // Create preview
      const reader = new FileReader()
      reader.onload = (e) => {
        setLogoPreview(e.target?.result as string)
      }
      reader.readAsDataURL(file)
    }
  }

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {}

    if (!formData.name.trim()) newErrors.name = 'Nome é obrigatório'
    if (!formData.email.trim()) newErrors.email = 'Email é obrigatório'
    if (!/\S+@\S+\.\S+/.test(formData.email)) newErrors.email = 'Email inválido'
    if (!formData.radio_name.trim()) newErrors.radio_name = 'Nome da rádio é obrigatório'
    if (!formData.stream_url.trim()) newErrors.stream_url = 'URL do stream é obrigatória'
    if (!formData.stream_url.startsWith('https://')) newErrors.stream_url = 'URL deve começar com https://'
    if (!formData.brief_description.trim()) newErrors.brief_description = 'Descrição breve é obrigatória'
    if (formData.genres.length === 0) newErrors.genres = 'Selecione pelo menos um gênero'
    if (!formData.country) newErrors.country = 'País é obrigatório'
    if (!formData.language) newErrors.language = 'Idioma é obrigatório'

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    
    if (!validateForm()) {
      toast.error('Por favor, corrija os erros no formulário')
      return
    }

    try {
      setLoading(true)
      
      let logoUrl = ''
      
      // Upload logo if provided
      if (logoFile) {
        const uploadResult = await customRadioAPI.uploadLogo(logoFile)
        if (uploadResult.success && uploadResult.logoUrl) {
          logoUrl = uploadResult.logoUrl
        }
      }

      // Register radio
      const result = await customRadioAPI.registerRadio({
        ...formData,
        logo_url: logoUrl
      })

      if (result.success) {
        toast.success('Rádio cadastrada com sucesso!')
        // Reset form
        setFormData({
          name: '',
          email: '',
          radio_name: '',
          stream_url: '',
          brief_description: '',
          detailed_description: '',
          genres: [],
          country: '',
          language: '',
          website: '',
          whatsapp: '',
          facebook: '',
          instagram: '',
          twitter: ''
        })
        setLogoFile(null)
        setLogoPreview('')
      }
    } catch (error: any) {
      toast.error(error.message)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen pt-20 pb-12">
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="text-center mb-12">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
          >
            <div className="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-purple-600 to-pink-600 rounded-3xl mb-8 neon-glow">
              <Radio className="h-10 w-10 text-white" />
            </div>
            <h1 className="text-5xl font-bold text-white mb-6">
              Cadastrar <span className="text-gradient">Rádio</span>
            </h1>
            <p className="text-xl text-gray-400 max-w-3xl mx-auto">
              Adicione sua rádio ao nosso diretório e alcance milhares de ouvintes
            </p>
          </motion.div>
        </div>

        {/* Form */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6, delay: 0.2 }}
          className="card p-8"
        >
          <form onSubmit={handleSubmit} className="space-y-8">
            {/* Personal Information */}
            <div>
              <h3 className="text-2xl font-bold text-white mb-6">Informações Pessoais</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm font-medium text-gray-300 mb-3">
                    Nome *
                  </label>
                  <input
                    type="text"
                    name="name"
                    value={formData.name}
                    onChange={handleInputChange}
                    className={`w-full px-4 py-3 bg-slate-700/50 border rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400 ${
                      errors.name ? 'border-red-500' : 'border-slate-600/50'
                    }`}
                    placeholder="Seu nome completo"
                  />
                  {errors.name && (
                    <p className="mt-2 text-sm text-red-400 flex items-center">
                      <AlertCircle className="h-4 w-4 mr-1" />
                      {errors.name}
                    </p>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-300 mb-3">
                    Email *
                  </label>
                  <input
                    type="email"
                    name="email"
                    value={formData.email}
                    onChange={handleInputChange}
                    className={`w-full px-4 py-3 bg-slate-700/50 border rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400 ${
                      errors.email ? 'border-red-500' : 'border-slate-600/50'
                    }`}
                    placeholder="seu@email.com"
                  />
                  {errors.email && (
                    <p className="mt-2 text-sm text-red-400 flex items-center">
                      <AlertCircle className="h-4 w-4 mr-1" />
                      {errors.email}
                    </p>
                  )}
                </div>
              </div>
            </div>

            {/* Radio Information */}
            <div>
              <h3 className="text-2xl font-bold text-white mb-6">Informações da Rádio</h3>
              <div className="space-y-6">
                <div>
                  <label className="block text-sm font-medium text-gray-300 mb-3">
                    Nome da Rádio *
                  </label>
                  <input
                    type="text"
                    name="radio_name"
                    value={formData.radio_name}
                    onChange={handleInputChange}
                    className={`w-full px-4 py-3 bg-slate-700/50 border rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400 ${
                      errors.radio_name ? 'border-red-500' : 'border-slate-600/50'
                    }`}
                    placeholder="Nome da sua rádio"
                  />
                  {errors.radio_name && (
                    <p className="mt-2 text-sm text-red-400 flex items-center">
                      <AlertCircle className="h-4 w-4 mr-1" />
                      {errors.radio_name}
                    </p>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-300 mb-3">
                    Stream URL *
                  </label>
                  <input
                    type="url"
                    name="stream_url"
                    value={formData.stream_url}
                    onChange={handleInputChange}
                    className={`w-full px-4 py-3 bg-slate-700/50 border rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400 ${
                      errors.stream_url ? 'border-red-500' : 'border-slate-600/50'
                    }`}
                    placeholder="https://seulinkdestreaming:8888/stream"
                  />
                  {errors.stream_url && (
                    <p className="mt-2 text-sm text-red-400 flex items-center">
                      <AlertCircle className="h-4 w-4 mr-1" />
                      {errors.stream_url}
                    </p>
                  )}
                  <p className="mt-2 text-sm text-gray-400">
                    Utilize HTTPS para melhor compatibilidade
                  </p>
                </div>

                {/* Logo Upload */}
                <div>
                  <label className="block text-sm font-medium text-gray-300 mb-3">
                    Logo da Rádio
                  </label>
                  <div className="flex items-center space-x-6">
                    <div className="flex-shrink-0">
                      {logoPreview ? (
                        <img
                          src={logoPreview}
                          alt="Preview"
                          className="w-20 h-20 rounded-xl object-cover border-2 border-purple-500/30"
                        />
                      ) : (
                        <div className="w-20 h-20 bg-slate-700/50 rounded-xl flex items-center justify-center border-2 border-dashed border-slate-600/50">
                          <Upload className="h-8 w-8 text-gray-400" />
                        </div>
                      )}
                    </div>
                    <div className="flex-1">
                      <input
                        type="file"
                        accept="image/*"
                        onChange={handleLogoChange}
                        className="hidden"
                        id="logo-upload"
                      />
                      <label
                        htmlFor="logo-upload"
                        className="cursor-pointer inline-flex items-center px-6 py-3 bg-slate-700/50 hover:bg-slate-600/50 text-white rounded-xl transition-colors"
                      >
                        <Upload className="h-5 w-5 mr-2" />
                        {logoFile ? 'Alterar Logo' : 'Escolher Logo'}
                      </label>
                      <p className="mt-2 text-sm text-gray-400">
                        PNG, JPG até 5MB. Recomendado: 400x400px
                      </p>
                    </div>
                  </div>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-300 mb-3">
                    Descrição Breve *
                  </label>
                  <textarea
                    name="brief_description"
                    value={formData.brief_description}
                    onChange={handleInputChange}
                    rows={3}
                    className={`w-full px-4 py-3 bg-slate-700/50 border rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400 resize-none ${
                      errors.brief_description ? 'border-red-500' : 'border-slate-600/50'
                    }`}
                    placeholder="Descrição curta da sua rádio (máximo 200 caracteres)"
                    maxLength={200}
                  />
                  {errors.brief_description && (
                    <p className="mt-2 text-sm text-red-400 flex items-center">
                      <AlertCircle className="h-4 w-4 mr-1" />
                      {errors.brief_description}
                    </p>
                  )}
                  <p className="mt-2 text-sm text-gray-400">
                    {formData.brief_description.length}/200 caracteres
                  </p>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-300 mb-3">
                    Descrição Detalhada
                  </label>
                  <textarea
                    name="detailed_description"
                    value={formData.detailed_description}
                    onChange={handleInputChange}
                    rows={6}
                    className="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400 resize-none"
                    placeholder="Aproveite este espaço para adicionar informações de WhatsApp, site e muito mais..."
                  />
                  <p className="mt-2 text-sm text-gray-400">
                    Adicione informações detalhadas, links para redes sociais, WhatsApp, etc.
                  </p>
                </div>
              </div>
            </div>

            {/* Genres */}
            <div>
              <h3 className="text-2xl font-bold text-white mb-6">Gêneros *</h3>
              <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                {GENRES.map((genre) => (
                  <button
                    key={genre}
                    type="button"
                    onClick={() => handleGenreToggle(genre)}
                    className={`px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 ${
                      formData.genres.includes(genre)
                        ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white'
                        : 'bg-slate-700/50 text-gray-300 hover:bg-slate-600/50'
                    }`}
                  >
                    {genre}
                  </button>
                ))}
              </div>
              {errors.genres && (
                <p className="mt-3 text-sm text-red-400 flex items-center">
                  <AlertCircle className="h-4 w-4 mr-1" />
                  {errors.genres}
                </p>
              )}
            </div>

            {/* Location */}
            <div>
              <h3 className="text-2xl font-bold text-white mb-6">Localização</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm font-medium text-gray-300 mb-3">
                    País de Origem *
                  </label>
                  <select
                    name="country"
                    value={formData.country}
                    onChange={handleInputChange}
                    className={`w-full px-4 py-3 bg-slate-700/50 border rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white ${
                      errors.country ? 'border-red-500' : 'border-slate-600/50'
                    }`}
                  >
                    <option value="">Selecione o país</option>
                    {COUNTRIES.map((country) => (
                      <option key={country} value={country}>
                        {country}
                      </option>
                    ))}
                  </select>
                  {errors.country && (
                    <p className="mt-2 text-sm text-red-400 flex items-center">
                      <AlertCircle className="h-4 w-4 mr-1" />
                      {errors.country}
                    </p>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-300 mb-3">
                    Idioma da Rádio *
                  </label>
                  <select
                    name="language"
                    value={formData.language}
                    onChange={handleInputChange}
                    className={`w-full px-4 py-3 bg-slate-700/50 border rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white ${
                      errors.language ? 'border-red-500' : 'border-slate-600/50'
                    }`}
                  >
                    <option value="">Selecione o idioma</option>
                    {LANGUAGES.map((language) => (
                      <option key={language} value={language}>
                        {language}
                      </option>
                    ))}
                  </select>
                  {errors.language && (
                    <p className="mt-2 text-sm text-red-400 flex items-center">
                      <AlertCircle className="h-4 w-4 mr-1" />
                      {errors.language}
                    </p>
                  )}
                </div>
              </div>
            </div>

            {/* Social Media */}
            <div>
              <h3 className="text-2xl font-bold text-white mb-6">Redes Sociais (Opcional)</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm font-medium text-gray-300 mb-3">
                    Website
                  </label>
                  <input
                    type="url"
                    name="website"
                    value={formData.website}
                    onChange={handleInputChange}
                    className="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400"
                    placeholder="https://seusite.com"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-300 mb-3">
                    WhatsApp
                  </label>
                  <input
                    type="tel"
                    name="whatsapp"
                    value={formData.whatsapp}
                    onChange={handleInputChange}
                    className="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400"
                    placeholder="+55 11 99999-9999"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-300 mb-3">
                    Facebook
                  </label>
                  <input
                    type="url"
                    name="facebook"
                    value={formData.facebook}
                    onChange={handleInputChange}
                    className="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400"
                    placeholder="https://facebook.com/suaradio"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-300 mb-3">
                    Instagram
                  </label>
                  <input
                    type="url"
                    name="instagram"
                    value={formData.instagram}
                    onChange={handleInputChange}
                    className="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400"
                    placeholder="https://instagram.com/suaradio"
                  />
                </div>
              </div>
            </div>

            {/* Submit Button */}
            <div className="flex justify-end space-x-4 pt-8 border-t border-slate-700/50">
              <button
                type="button"
                className="px-8 py-3 bg-slate-700/50 hover:bg-slate-600/50 text-white rounded-xl transition-colors"
                onClick={() => window.history.back()}
              >
                Cancelar
              </button>
              <button
                type="submit"
                disabled={loading}
                className="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed font-semibold inline-flex items-center space-x-2"
              >
                {loading ? (
                  <>
                    <div className="spinner"></div>
                    <span>Cadastrando...</span>
                  </>
                ) : (
                  <>
                    <Save className="h-5 w-5" />
                    <span>Cadastrar Rádio</span>
                  </>
                )}
              </button>
            </div>
          </form>
        </motion.div>
      </div>
    </div>
  )
}

export default RegisterRadio