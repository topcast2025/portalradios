import React, { useState, useEffect } from 'react'
import { motion } from 'framer-motion'
import { Search, Filter, Globe, Music, Mic, Radio } from 'lucide-react'
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
    { id: 'popular', name: 'Populares', icon: Radio },
    { id: 'music', name: 'Música', icon: Music },
    { id: 'news', name: 'Notícias', icon: Mic },
    { id: 'brazil', name: 'Brasil', icon: Globe },
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
    <div className="min-h-screen py-8">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="text-center mb-12">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
          >
            <h1 className="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
              Explorar Rádios
            </h1>
            <p className="text-xl text-gray-600 max-w-3xl mx-auto">
              Descubra milhares de estações de rádio de todo o mundo
            </p>
          </motion.div>
        </div>

        {/* Search Bar */}
        <div className="mb-12">
          <SearchBar onSearch={handleSearch} loading={loading} />
        </div>

        {/* Categories */}
        <div className="mb-8">
          <div className="flex flex-wrap gap-4 justify-center">
            {categories.map((category) => {
              const Icon = category.icon
              return (
                <button
                  key={category.id}
                  onClick={() => setActiveCategory(category.id)}
                  className={`flex items-center space-x-2 px-6 py-3 rounded-xl font-semibold transition-all duration-200 ${
                    activeCategory === category.id
                      ? 'bg-blue-600 text-white shadow-lg'
                      : 'bg-white text-gray-700 hover:bg-blue-50 hover:text-blue-600 border border-gray-200'
                  }`}
                >
                  <Icon className="h-5 w-5" />
                  <span>{category.name}</span>
                </button>
              )
            })}
          </div>
        </div>

        {/* Results */}
        <div className="mb-8">
          <div className="flex items-center justify-between">
            <h2 className="text-2xl font-bold text-gray-900">
              {activeCategory ? 
                categories.find(c => c.id === activeCategory)?.name || 'Resultados' : 
                'Resultados da Busca'
              }
            </h2>
            <span className="text-gray-500">
              {stations.length} estações encontradas
            </span>
          </div>
        </div>

        {/* Stations Grid */}
        {loading ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {[...Array(12)].map((_, index) => (
              <div key={index} className="card p-6 animate-pulse">
                <div className="flex items-center space-x-4">
                  <div className="w-16 h-16 bg-gray-300 rounded-xl"></div>
                  <div className="flex-1">
                    <div className="h-4 bg-gray-300 rounded mb-2"></div>
                    <div className="h-3 bg-gray-300 rounded w-2/3 mb-2"></div>
                    <div className="flex space-x-2">
                      <div className="h-6 w-16 bg-gray-300 rounded-full"></div>
                      <div className="h-6 w-20 bg-gray-300 rounded-full"></div>
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
            className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
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
          <div className="text-center py-16">
            <Search className="h-16 w-16 text-gray-400 mx-auto mb-4" />
            <h3 className="text-xl font-semibold text-gray-900 mb-2">
              Nenhuma estação encontrada
            </h3>
            <p className="text-gray-600">
              Tente ajustar seus filtros de busca ou explore outras categorias
            </p>
          </div>
        )}
      </div>
    </div>
  )
}

export default Browse