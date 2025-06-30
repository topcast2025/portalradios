import React, { useState, useEffect } from 'react'
import { motion } from 'framer-motion'
import { Search, Filter, Globe, Music, Mic, Radio, Waves } from 'lucide-react'
import { radioAPI } from '../services/radioApi'
import { RadioStation, SearchFilters } from '../types/radio'
import RadioCard from '../components/RadioCard'
import SearchBar from '../components/SearchBar'
import toast from 'react-hot-toast'

const Browse: React.FC = () => {
  const [stations, setStations] = useState<RadioStation[]>([])
  const [loading, setLoading] = useState(false)
  const [activeCategory, setActiveCategory] = useState<string>('popular')

  const categories = [
    { id: 'popular', name: 'Populares', icon: Radio, color: 'from-purple-500 to-pink-500' },
    { id: 'music', name: 'Música', icon: Music, color: 'from-blue-500 to-cyan-500' },
    { id: 'news', name: 'Notícias', icon: Mic, color: 'from-green-500 to-emerald-500' },
    { id: 'brazil', name: 'Brasil', icon: Globe, color: 'from-yellow-500 to-orange-500' },
  ]

  useEffect(() => {
    loadStationsByCategory(activeCategory)
  }, [activeCategory])

  const loadStationsByCategory = async (category: string) => {
    try {
      setLoading(true)
      let stationsData: RadioStation[] = []

      switch (category) {
        case 'popular':
          stationsData = await radioAPI.getTopStations(50)
          break
        case 'music':
          stationsData = await radioAPI.getStationsByTag('music', 50)
          break
        case 'news':
          stationsData = await radioAPI.getStationsByTag('news', 50)
          break
        case 'brazil':
          stationsData = await radioAPI.getStationsByCountry('Brazil', 50)
          break
        default:
          stationsData = await radioAPI.getTopStations(50)
      }

      setStations(stationsData)
    } catch (error) {
      toast.error('Erro ao carregar estações')
      console.error(error)
    } finally {
      setLoading(false)
    }
  }

  const handleSearch = async (filters: SearchFilters) => {
    try {
      setLoading(true)
      const searchResults = await radioAPI.searchStations(filters, 100)
      setStations(searchResults)
      setActiveCategory('')
    } catch (error) {
      toast.error('Erro na busca')
      console.error(error)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen pt-20">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {/* Header */}
        <div className="text-center mb-16">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
          >
            <div className="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-purple-600 to-pink-600 rounded-3xl mb-8 neon-glow">
              <Waves className="h-10 w-10 text-white" />
            </div>
            <h1 className="text-5xl md:text-6xl font-bold text-white mb-6">
              Explorar <span className="text-gradient">Rádios</span>
            </h1>
            <p className="text-xl text-gray-400 max-w-3xl mx-auto">
              Descubra milhares de estações de rádio de todo o mundo
            </p>
          </motion.div>
        </div>

        {/* Search Bar */}
        <div className="mb-16">
          <SearchBar onSearch={handleSearch} loading={loading} />
        </div>

        {/* Categories */}
        <div className="mb-12">
          <div className="flex flex-wrap gap-4 justify-center">
            {categories.map((category) => {
              const Icon = category.icon
              return (
                <button
                  key={category.id}
                  onClick={() => setActiveCategory(category.id)}
                  className={`flex items-center space-x-3 px-8 py-4 rounded-2xl font-semibold transition-all duration-300 ${
                    activeCategory === category.id
                      ? `bg-gradient-to-r ${category.color} text-white shadow-lg neon-glow`
                      : 'bg-slate-800/50 text-gray-300 hover:text-white hover:bg-slate-700/50 border border-slate-700/50'
                  }`}
                >
                  <Icon className="h-6 w-6" />
                  <span>{category.name}</span>
                </button>
              )
            })}
          </div>
        </div>

        {/* Results */}
        <div className="mb-8">
          <div className="flex items-center justify-between">
            <h2 className="text-3xl font-bold text-white">
              {activeCategory ? 
                categories.find(c => c.id === activeCategory)?.name || 'Resultados' : 
                'Resultados da Busca'
              }
            </h2>
            <span className="text-gray-400 bg-slate-800/50 px-4 py-2 rounded-full">
              {stations.length} estações encontradas
            </span>
          </div>
        </div>

        {/* Stations Grid */}
        {loading ? (
          <div className="station-grid">
            {[...Array(12)].map((_, index) => (
              <div key={index} className="card p-8 animate-pulse">
                <div className="flex items-center space-x-4 mb-6">
                  <div className="w-16 h-16 bg-slate-700 rounded-2xl"></div>
                  <div className="flex-1">
                    <div className="h-5 bg-slate-700 rounded mb-3"></div>
                    <div className="h-4 bg-slate-700 rounded w-2/3 mb-3"></div>
                    <div className="flex space-x-2">
                      <div className="h-6 w-16 bg-slate-700 rounded-full"></div>
                      <div className="h-6 w-20 bg-slate-700 rounded-full"></div>
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : stations.length > 0 ? (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 0.5 }}
            className="station-grid"
          >
            {stations.map((station, index) => (
              <RadioCard
                key={station.stationuuid}
                station={station}
                index={index}
              />
            ))}
          </motion.div>
        ) : (
          <div className="text-center py-20">
            <div className="inline-flex items-center justify-center w-24 h-24 bg-slate-800/50 rounded-3xl mb-8">
              <Search className="h-12 w-12 text-gray-400" />
            </div>
            <h3 className="text-2xl font-semibold text-white mb-4">
              Nenhuma estação encontrada
            </h3>
            <p className="text-gray-400 mb-8">
              Tente ajustar seus filtros de busca ou explore outras categorias
            </p>
          </div>
        )}
      </div>
    </div>
  )
}

export default Browse