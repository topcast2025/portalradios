import React, { useState, useEffect } from 'react'
import { motion } from 'framer-motion'
import { Radio, TrendingUp, Globe, Music, Headphones, Star } from 'lucide-react'
import { Link } from 'react-router-dom'
import { radioAPI } from '../services/radioApi'
import { RadioStation } from '../types/radio'
import RadioCard from '../components/RadioCard'
import toast from 'react-hot-toast'

const Home: React.FC = () => {
  const [topStations, setTopStations] = useState<RadioStation[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    loadTopStations()
  }, [])

  const loadTopStations = async () => {
    try {
      setLoading(true)
      const stations = await radioAPI.getTopStations(12)
      setTopStations(stations)
    } catch (error) {
      toast.error('Erro ao carregar estações populares')
      console.error(error)
    } finally {
      setLoading(false)
    }
  }

  const features = [
    {
      icon: Globe,
      title: 'Rádios Globais',
      description: 'Milhares de estações de todo o mundo'
    },
    {
      icon: Music,
      title: 'Todos os Gêneros',
      description: 'Música, notícias, esportes e muito mais'
    },
    {
      icon: Headphones,
      title: 'Alta Qualidade',
      description: 'Streaming de áudio em alta definição'
    },
    {
      icon: Star,
      title: 'Favoritos',
      description: 'Salve suas estações preferidas'
    }
  ]

  return (
    <div className="min-h-screen">
      {/* Hero Section */}
      <section className="relative overflow-hidden bg-gradient-to-br from-blue-600 via-purple-600 to-blue-800">
        <div className="absolute inset-0 bg-black/20"></div>
        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
          <div className="text-center">
            <motion.div
              initial={{ opacity: 0, y: 30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8 }}
            >
              <h1 className="text-5xl md:text-7xl font-bold text-white mb-6">
                Radio<span className="text-yellow-400">Wave</span>
              </h1>
              <p className="text-xl md:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
                Descubra e ouça milhares de rádios online de todo o mundo. 
                Sua música favorita está a um clique de distância.
              </p>
              <div className="flex flex-col sm:flex-row gap-4 justify-center">
                <Link
                  to="/browse"
                  className="btn-primary text-lg px-8 py-4"
                >
                  Explorar Rádios
                </Link>
                <Link
                  to="/about"
                  className="btn-secondary text-lg px-8 py-4 bg-white/10 border-white/30 text-white hover:bg-white/20"
                >
                  Saiba Mais
                </Link>
              </div>
            </motion.div>
          </div>
        </div>

        {/* Floating Elements */}
        <div className="absolute top-20 left-10 opacity-20">
          <div className="float">
            <Radio className="h-16 w-16 text-white" />
          </div>
        </div>
        <div className="absolute top-40 right-20 opacity-20">
          <div className="float" style={{ animationDelay: '2s' }}>
            <Music className="h-12 w-12 text-white" />
          </div>
        </div>
        <div className="absolute bottom-20 left-1/4 opacity-20">
          <div className="float" style={{ animationDelay: '4s' }}>
            <Headphones className="h-14 w-14 text-white" />
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-4xl font-bold text-gray-900 mb-4">
              Por que escolher o RadioWave?
            </h2>
            <p className="text-xl text-gray-600 max-w-3xl mx-auto">
              A melhor experiência de rádio online com recursos incríveis
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {features.map((feature, index) => {
              const Icon = feature.icon
              return (
                <motion.div
                  key={index}
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ duration: 0.5, delay: index * 0.1 }}
                  className="text-center p-6 rounded-2xl hover:shadow-lg transition-shadow"
                >
                  <div className="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-500 rounded-2xl mb-4">
                    <Icon className="h-8 w-8 text-white" />
                  </div>
                  <h3 className="text-xl font-semibold text-gray-900 mb-2">
                    {feature.title}
                  </h3>
                  <p className="text-gray-600">
                    {feature.description}
                  </p>
                </motion.div>
              )
            })}
          </div>
        </div>
      </section>

      {/* Top Stations Section */}
      <section className="py-20 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between mb-12">
            <div>
              <h2 className="text-4xl font-bold text-gray-900 mb-4">
                <TrendingUp className="inline h-10 w-10 text-blue-600 mr-3" />
                Estações Populares
              </h2>
              <p className="text-xl text-gray-600">
                As rádios mais ouvidas pelos nossos usuários
              </p>
            </div>
            <Link
              to="/browse"
              className="btn-primary"
            >
              Ver Todas
            </Link>
          </div>

          {loading ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {[...Array(6)].map((_, index) => (
                <div key={index} className="card p-6 animate-pulse">
                  <div className="flex items-center space-x-4">
                    <div className="w-16 h-16 bg-gray-300 rounded-xl"></div>
                    <div className="flex-1">
                      <div className="h-4 bg-gray-300 rounded mb-2"></div>
                      <div className="h-3 bg-gray-300 rounded w-2/3"></div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {topStations.map((station, index) => (
                <RadioCard
                  key={station.stationuuid}
                  station={station}
                  index={index}
                />
              ))}
            </div>
          )}
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-20 bg-gradient-to-r from-blue-600 to-purple-600">
        <div className="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
          >
            <h2 className="text-4xl font-bold text-white mb-6">
              Pronto para começar?
            </h2>
            <p className="text-xl text-blue-100 mb-8">
              Junte-se a milhares de usuários que já descobriram suas rádios favoritas
            </p>
            <Link
              to="/browse"
              className="btn-primary bg-white text-blue-600 hover:bg-gray-100 text-lg px-8 py-4"
            >
              Começar Agora
            </Link>
          </motion.div>
        </div>
      </section>
    </div>
  )
}

export default Home