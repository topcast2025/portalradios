import React, { useState, useEffect } from 'react'
import { useParams, Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { 
  Play, Pause, Heart, Share2, Edit, Flag, 
  Globe, MapPin, Calendar, BarChart3, 
  ExternalLink, MessageCircle, Users,
  TrendingUp, Clock, Radio as RadioIcon
} from 'lucide-react'
import { customRadioAPI } from '../services/customRadioApi'
import { CustomRadio, RadioStatistics } from '../types/customRadio'
import { useRadio } from '../context/RadioContext'
import toast from 'react-hot-toast'

const RadioDetails: React.FC = () => {
  const { id } = useParams<{ id: string }>()
  const [radio, setRadio] = useState<CustomRadio | null>(null)
  const [statistics, setStatistics] = useState<RadioStatistics[]>([])
  const [loading, setLoading] = useState(true)
  const [showReportModal, setShowReportModal] = useState(false)
  const [reportDescription, setReportDescription] = useState('')
  const [reportEmail, setReportEmail] = useState('')

  const { 
    currentStation, 
    isPlaying, 
    playStation, 
    pauseStation,
    addToFavorites,
    removeFromFavorites,
    isFavorite
  } = useRadio()

  useEffect(() => {
    if (id) {
      loadRadioDetails(parseInt(id))
    }
  }, [id])

  const loadRadioDetails = async (radioId: number) => {
    try {
      setLoading(true)
      const [radioData, statsData] = await Promise.all([
        customRadioAPI.getRadioById(radioId),
        customRadioAPI.getRadioStatistics(radioId)
      ])
      setRadio(radioData)
      setStatistics(statsData)
    } catch (error: any) {
      toast.error(error.message)
    } finally {
      setLoading(false)
    }
  }

  const handlePlay = async () => {
    if (!radio) return

    // Register click
    await customRadioAPI.registerClick(radio.id)

    // Convert to RadioStation format for player
    const stationData = {
      stationuuid: `custom-${radio.id}`,
      name: radio.radio_name,
      url: radio.stream_url,
      url_resolved: radio.stream_url,
      homepage: radio.website || '',
      favicon: radio.logo_url || '',
      tags: radio.genres.join(','),
      country: radio.country,
      countrycode: '',
      state: '',
      language: radio.language,
      languagecodes: '',
      votes: radio.total_clicks,
      lastchangetime: radio.created_at,
      lastchangetime_iso8601: radio.created_at,
      codec: '',
      bitrate: 0,
      hls: 0,
      lastcheckok: 1,
      lastchecktime: '',
      lastchecktime_iso8601: '',
      lastcheckoktime: '',
      lastcheckoktime_iso8601: '',
      lastlocalchecktime: '',
      lastlocalchecktime_iso8601: '',
      clicktimestamp: '',
      clicktimestamp_iso8601: '',
      clickcount: radio.total_clicks,
      clicktrend: 0,
      ssl_error: 0,
      geo_lat: 0,
      geo_long: 0,
      has_extended_info: false,
      changeuuid: ''
    }

    const isCurrentStation = currentStation?.stationuuid === stationData.stationuuid
    
    if (isCurrentStation) {
      if (isPlaying) {
        pauseStation()
      } else {
        playStation(stationData)
      }
    } else {
      playStation(stationData)
    }
  }

  const handleFavoriteToggle = () => {
    if (!radio) return

    const stationData = {
      stationuuid: `custom-${radio.id}`,
      name: radio.radio_name,
      url: radio.stream_url,
      url_resolved: radio.stream_url,
      homepage: radio.website || '',
      favicon: radio.logo_url || '',
      tags: radio.genres.join(','),
      country: radio.country,
      countrycode: '',
      state: '',
      language: radio.language,
      languagecodes: '',
      votes: radio.total_clicks,
      lastchangetime: radio.created_at,
      lastchangetime_iso8601: radio.created_at,
      codec: '',
      bitrate: 0,
      hls: 0,
      lastcheckok: 1,
      lastchecktime: '',
      lastchecktime_iso8601: '',
      lastcheckoktime: '',
      lastcheckoktime_iso8601: '',
      lastlocalchecktime: '',
      lastlocalchecktime_iso8601: '',
      clicktimestamp: '',
      clicktimestamp_iso8601: '',
      clickcount: radio.total_clicks,
      clicktrend: 0,
      ssl_error: 0,
      geo_lat: 0,
      geo_long: 0,
      has_extended_info: false,
      changeuuid: ''
    }

    if (isFavorite(stationData.stationuuid)) {
      removeFromFavorites(stationData.stationuuid)
    } else {
      addToFavorites(stationData)
    }
  }

  const handleReportError = async () => {
    if (!radio || !reportDescription.trim()) {
      toast.error('Por favor, descreva o problema encontrado')
      return
    }

    try {
      await customRadioAPI.reportRadioError(radio.id, reportDescription, reportEmail)
      toast.success('Problema reportado com sucesso!')
      setShowReportModal(false)
      setReportDescription('')
      setReportEmail('')
    } catch (error: any) {
      toast.error(error.message)
    }
  }

  const handleShare = () => {
    if (navigator.share) {
      navigator.share({
        title: radio?.radio_name,
        text: radio?.brief_description,
        url: window.location.href
      })
    } else {
      navigator.clipboard.writeText(window.location.href)
      toast.success('Link copiado para a área de transferência!')
    }
  }

  if (loading) {
    return (
      <div className="min-h-screen pt-20 flex items-center justify-center">
        <div className="text-center">
          <div className="spinner mx-auto mb-4"></div>
          <p className="text-gray-400">Carregando detalhes da rádio...</p>
        </div>
      </div>
    )
  }

  if (!radio) {
    return (
      <div className="min-h-screen pt-20 flex items-center justify-center">
        <div className="text-center">
          <RadioIcon className="h-16 w-16 text-gray-400 mx-auto mb-4" />
          <h2 className="text-2xl font-bold text-white mb-2">Rádio não encontrada</h2>
          <p className="text-gray-400 mb-8">A rádio que você procura não existe ou foi removida.</p>
          <Link to="/browse" className="btn-primary">
            Explorar Rádios
          </Link>
        </div>
      </div>
    )
  }

  const isCurrentStation = currentStation?.stationuuid === `custom-${radio.id}`
  const isCurrentlyPlaying = isCurrentStation && isPlaying

  // Calculate total access from statistics
  const totalAccess = statistics.reduce((sum, stat) => sum + stat.access_count, 0)

  return (
    <div className="min-h-screen pt-20">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {/* Header */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          className="card p-8 mb-8"
        >
          <div className="flex flex-col lg:flex-row items-start lg:items-center space-y-6 lg:space-y-0 lg:space-x-8">
            {/* Logo */}
            <div className="flex-shrink-0">
              {radio.logo_url ? (
                <img
                  src={radio.logo_url}
                  alt={radio.radio_name}
                  className="w-32 h-32 rounded-3xl object-cover border-4 border-purple-500/30"
                />
              ) : (
                <div className="w-32 h-32 bg-gradient-to-br from-purple-500 to-pink-500 rounded-3xl flex items-center justify-center border-4 border-purple-500/30">
                  <span className="text-white font-bold text-4xl">
                    {radio.radio_name.charAt(0).toUpperCase()}
                  </span>
                </div>
              )}
            </div>

            {/* Info */}
            <div className="flex-1 min-w-0">
              <h1 className="text-4xl font-bold text-white mb-4">
                {radio.radio_name}
              </h1>
              <p className="text-xl text-gray-300 mb-6">
                {radio.brief_description}
              </p>

              <div className="flex flex-wrap items-center gap-4 mb-6">
                <div className="flex items-center space-x-2 text-gray-400">
                  <MapPin className="h-5 w-5" />
                  <span>{radio.country}</span>
                </div>
                <div className="flex items-center space-x-2 text-gray-400">
                  <Globe className="h-5 w-5" />
                  <span>{radio.language}</span>
                </div>
                <div className="flex items-center space-x-2 text-gray-400">
                  <Calendar className="h-5 w-5" />
                  <span>Desde {new Date(radio.created_at).toLocaleDateString('pt-BR')}</span>
                </div>
                <div className="flex items-center space-x-2 text-gray-400">
                  <Users className="h-5 w-5" />
                  <span>{radio.total_clicks} acessos</span>
                </div>
              </div>

              {/* Genres */}
              <div className="flex flex-wrap gap-2 mb-6">
                {radio.genres.map((genre, index) => (
                  <span
                    key={index}
                    className="px-3 py-1 bg-purple-500/20 text-purple-300 text-sm rounded-full border border-purple-500/30"
                  >
                    {genre}
                  </span>
                ))}
              </div>

              {/* Actions */}
              <div className="flex flex-wrap gap-4">
                <button
                  onClick={handlePlay}
                  className={`inline-flex items-center space-x-3 px-8 py-4 rounded-2xl font-semibold transition-all duration-300 ${
                    isCurrentlyPlaying
                      ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white neon-glow'
                      : 'bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white'
                  }`}
                >
                  {isCurrentlyPlaying ? (
                    <Pause className="h-6 w-6" />
                  ) : (
                    <Play className="h-6 w-6" />
                  )}
                  <span>{isCurrentlyPlaying ? 'Pausar' : 'Ouvir Agora'}</span>
                </button>

                <button
                  onClick={handleFavoriteToggle}
                  className={`p-4 rounded-2xl transition-all duration-300 ${
                    isFavorite(`custom-${radio.id}`)
                      ? 'bg-pink-500/20 text-pink-500 border border-pink-500/30'
                      : 'bg-slate-700/50 text-gray-400 hover:text-pink-500 hover:bg-pink-500/20 border border-slate-600/50'
                  }`}
                >
                  <Heart className={`h-6 w-6 ${isFavorite(`custom-${radio.id}`) ? 'fill-current' : ''}`} />
                </button>

                <button
                  onClick={handleShare}
                  className="p-4 bg-slate-700/50 hover:bg-slate-600/50 text-gray-400 hover:text-white rounded-2xl transition-colors border border-slate-600/50"
                >
                  <Share2 className="h-6 w-6" />
                </button>

                <button
                  onClick={() => setShowReportModal(true)}
                  className="p-4 bg-slate-700/50 hover:bg-red-500/20 text-gray-400 hover:text-red-500 rounded-2xl transition-colors border border-slate-600/50"
                >
                  <Flag className="h-6 w-6" />
                </button>
              </div>
            </div>
          </div>
        </motion.div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Main Content */}
          <div className="lg:col-span-2 space-y-8">
            {/* Description */}
            {radio.detailed_description && (
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: 0.1 }}
                className="card p-8"
              >
                <h3 className="text-2xl font-bold text-white mb-6">Sobre a Rádio</h3>
                <div className="text-gray-300 whitespace-pre-wrap leading-relaxed">
                  {radio.detailed_description}
                </div>
              </motion.div>
            )}

            {/* Statistics */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.2 }}
              className="card p-8"
            >
              <h3 className="text-2xl font-bold text-white mb-6 flex items-center">
                <BarChart3 className="h-6 w-6 mr-3 text-purple-400" />
                Estatísticas de Acesso
              </h3>

              {statistics.length > 0 ? (
                <div className="space-y-6">
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div className="text-center p-6 bg-slate-700/30 rounded-2xl">
                      <div className="text-3xl font-bold text-purple-400 mb-2">
                        {totalAccess}
                      </div>
                      <div className="text-gray-400">Total de Acessos</div>
                    </div>
                    <div className="text-center p-6 bg-slate-700/30 rounded-2xl">
                      <div className="text-3xl font-bold text-pink-400 mb-2">
                        {statistics.length}
                      </div>
                      <div className="text-gray-400">Períodos Registrados</div>
                    </div>
                    <div className="text-center p-6 bg-slate-700/30 rounded-2xl">
                      <div className="text-3xl font-bold text-blue-400 mb-2">
                        {Math.round(totalAccess / statistics.length) || 0}
                      </div>
                      <div className="text-gray-400">Média por Período</div>
                    </div>
                  </div>

                  <div className="space-y-4">
                    <h4 className="text-lg font-semibold text-white">Histórico Quinzenal</h4>
                    {statistics.slice(0, 10).map((stat, index) => (
                      <div key={stat.id} className="flex items-center justify-between p-4 bg-slate-700/30 rounded-xl">
                        <div className="flex items-center space-x-3">
                          <Clock className="h-5 w-5 text-gray-400" />
                          <div>
                            <div className="text-white font-medium">
                              {new Date(stat.period_start).toLocaleDateString('pt-BR')} - {new Date(stat.period_end).toLocaleDateString('pt-BR')}
                            </div>
                            <div className="text-sm text-gray-400">
                              Atualizado em {new Date(stat.last_updated).toLocaleDateString('pt-BR')}
                            </div>
                          </div>
                        </div>
                        <div className="flex items-center space-x-2">
                          <TrendingUp className="h-5 w-5 text-green-400" />
                          <span className="text-xl font-bold text-white">{stat.access_count}</span>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              ) : (
                <div className="text-center py-12">
                  <BarChart3 className="h-16 w-16 text-gray-400 mx-auto mb-4" />
                  <p className="text-gray-400">Nenhuma estatística disponível ainda</p>
                </div>
              )}
            </motion.div>
          </div>

          {/* Sidebar */}
          <div className="space-y-8">
            {/* Contact Info */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.3 }}
              className="card p-8"
            >
              <h3 className="text-2xl font-bold text-white mb-6">Contato</h3>
              <div className="space-y-4">
                <div className="flex items-center space-x-3 text-gray-300">
                  <MessageCircle className="h-5 w-5 text-purple-400" />
                  <span>{radio.email}</span>
                </div>

                {radio.website && (
                  <a
                    href={radio.website}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="flex items-center space-x-3 text-purple-400 hover:text-purple-300 transition-colors"
                  >
                    <ExternalLink className="h-5 w-5" />
                    <span>Website</span>
                  </a>
                )}

                {radio.whatsapp && (
                  <a
                    href={`https://wa.me/${radio.whatsapp.replace(/\D/g, '')}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="flex items-center space-x-3 text-green-400 hover:text-green-300 transition-colors"
                  >
                    <MessageCircle className="h-5 w-5" />
                    <span>WhatsApp</span>
                  </a>
                )}
              </div>
            </motion.div>

            {/* Social Media */}
            {(radio.facebook || radio.instagram || radio.twitter) && (
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: 0.4 }}
                className="card p-8"
              >
                <h3 className="text-2xl font-bold text-white mb-6">Redes Sociais</h3>
                <div className="space-y-4">
                  {radio.facebook && (
                    <a
                      href={radio.facebook}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="flex items-center space-x-3 text-blue-400 hover:text-blue-300 transition-colors"
                    >
                      <ExternalLink className="h-5 w-5" />
                      <span>Facebook</span>
                    </a>
                  )}

                  {radio.instagram && (
                    <a
                      href={radio.instagram}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="flex items-center space-x-3 text-pink-400 hover:text-pink-300 transition-colors"
                    >
                      <ExternalLink className="h-5 w-5" />
                      <span>Instagram</span>
                    </a>
                  )}

                  {radio.twitter && (
                    <a
                      href={radio.twitter}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="flex items-center space-x-3 text-blue-400 hover:text-blue-300 transition-colors"
                    >
                      <ExternalLink className="h-5 w-5" />
                      <span>Twitter</span>
                    </a>
                  )}
                </div>
              </motion.div>
            )}

            {/* Actions */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.5 }}
              className="card p-8"
            >
              <h3 className="text-2xl font-bold text-white mb-6">Ações</h3>
              <div className="space-y-4">
                <Link
                  to={`/edit-radio/${radio.id}`}
                  className="w-full inline-flex items-center justify-center space-x-2 px-6 py-3 bg-slate-700/50 hover:bg-slate-600/50 text-white rounded-xl transition-colors"
                >
                  <Edit className="h-5 w-5" />
                  <span>Editar Rádio</span>
                </Link>

                <button
                  onClick={() => setShowReportModal(true)}
                  className="w-full inline-flex items-center justify-center space-x-2 px-6 py-3 bg-red-500/20 hover:bg-red-500/30 text-red-400 hover:text-red-300 rounded-xl transition-colors border border-red-500/30"
                >
                  <Flag className="h-5 w-5" />
                  <span>Reportar Problema</span>
                </button>
              </div>
            </motion.div>
          </div>
        </div>
      </div>

      {/* Report Modal */}
      {showReportModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <motion.div
            initial={{ opacity: 0, scale: 0.9 }}
            animate={{ opacity: 1, scale: 1 }}
            className="bg-slate-800 rounded-3xl p-8 max-w-md w-full"
          >
            <h3 className="text-2xl font-bold text-white mb-6">Reportar Problema</h3>
            
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-300 mb-2">
                  Descreva o problema *
                </label>
                <textarea
                  value={reportDescription}
                  onChange={(e) => setReportDescription(e.target.value)}
                  rows={4}
                  className="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400 resize-none"
                  placeholder="Descreva o problema encontrado com esta rádio..."
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-300 mb-2">
                  Seu email (opcional)
                </label>
                <input
                  type="email"
                  value={reportEmail}
                  onChange={(e) => setReportEmail(e.target.value)}
                  className="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400"
                  placeholder="seu@email.com"
                />
              </div>
            </div>

            <div className="flex space-x-4 mt-8">
              <button
                onClick={() => setShowReportModal(false)}
                className="flex-1 px-6 py-3 bg-slate-700/50 hover:bg-slate-600/50 text-white rounded-xl transition-colors"
              >
                Cancelar
              </button>
              <button
                onClick={handleReportError}
                className="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors"
              >
                Reportar
              </button>
            </div>
          </motion.div>
        </div>
      )}
    </div>
  )
}

export default RadioDetails