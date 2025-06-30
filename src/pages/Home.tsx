import React, { useState, useEffect } from 'react'
import { motion } from 'framer-motion'
import { Radio, TrendingUp, Globe, Music, Headphones, Star, Play, Zap, Users, Waves, Info } from 'lucide-react'
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
      description: 'Milhares de estações de todo o mundo',
      color: 'from-blue-500 to-cyan-500'
    },
    {
      icon: Music,
      title: 'Todos os Gêneros',
      description: 'Música, notícias, esportes e muito mais',
      color: 'from-purple-500 to-pink-500'
    },
    {
      icon: Headphones,
      title: 'Alta Qualidade',
      description: 'Streaming de áudio em alta definição',
      color: 'from-green-500 to-emerald-500'
    },
    {
      icon: Star,
      title: 'Favoritos',
      description: 'Salve suas estações preferidas',
      color: 'from-yellow-500 to-orange-500'
    }
  ]

  const stats = [
    { number: '50K+', label: 'Estações', icon: Radio },
    { number: '200+', label: 'Países', icon: Globe },
    { number: '24/7', label: 'Online', icon: Zap },
    { number: '1M+', label: 'Usuários', icon: Users }
  ]

  return (
    <div className="min-h-screen">
      {/* Particles Background */}
      <div className="fixed inset-0 overflow-hidden pointer-events-none">
        {[...Array(20)].map((_, i) => (
          <div
            key={i}
            className="particle bg-purple-500/20"
            style={{
              left: `${Math.random() * 100}%`,
              width: `${Math.random() * 4 + 2}px`,
              height: `${Math.random() * 4 + 2}px`,
              animationDelay: `${Math.random() * 8}s`,
              animationDuration: `${Math.random() * 4 + 6}s`
            }}
          />
        ))}
      </div>

      {/* Hero Section */}
      <section className="relative min-h-screen flex items-center justify-center overflow-hidden">
        <div className="absolute inset-0 hero-gradient opacity-10"></div>
        
        {/* Animated Background Elements */}
        <div className="absolute top-20 left-10 opacity-20">
          <div className="float">
            <Waves className="h-20 w-20 text-purple-500" />
          </div>
        </div>
        <div className="absolute top-40 right-20 opacity-20">
          <div className="float" style={{ animationDelay: '2s' }}>
            <Music className="h-16 w-16 text-pink-500" />
          </div>
        </div>
        <div className="absolute bottom-20 left-1/4 opacity-20">
          <div className="float" style={{ animationDelay: '4s' }}>
            <Headphones className="h-18 w-18 text-purple-500" />
          </div>
        </div>

        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32 text-center">
          <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
          >
            <div className="mb-8">
              <motion.div
                initial={{ scale: 0 }}
                animate={{ scale: 1 }}
                transition={{ duration: 0.8, delay: 0.2 }}
                className="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-r from-purple-600 to-pink-600 rounded-3xl mb-8 neon-glow"
              >
                <Waves className="h-12 w-12 text-white" />
              </motion.div>
            </div>

            <h1 className="text-6xl md:text-8xl font-bold mb-8">
              <span className="text-gradient">Radion</span>
            </h1>
            
            <p className="text-xl md:text-2xl text-gray-300 mb-12 max-w-4xl mx-auto leading-relaxed">
              Descubra e ouça milhares de rádios online de todo o mundo. 
              Sua música favorita está a um clique de distância.
            </p>
            
            <div className="flex flex-col sm:flex-row gap-6 justify-center">
              <Link
                to="/browse"
                className="btn-primary text-lg inline-flex items-center space-x-3"
              >
                <Play className="h-6 w-6" />
                <span>Começar a Ouvir</span>
              </Link>
              <Link
                to="/about"
                className="btn-secondary text-lg inline-flex items-center space-x-3"
              >
                <Info className="h-6 w-6" />
                <span>Saiba Mais</span>
              </Link>
            </div>
          </motion.div>
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-20 relative">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
            {stats.map((stat, index) => {
              const Icon = stat.icon
              return (
                <motion.div
                  key={index}
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ duration: 0.5, delay: index * 0.1 }}
                  className="text-center p-6 rounded-3xl bg-slate-800/30 backdrop-blur-xl border border-slate-700/50"
                >
                  <Icon className="h-8 w-8 mx-auto mb-4 text-purple-400" />
                  <div className="text-3xl md:text-4xl font-bold text-white mb-2">
                    {stat.number}
                  </div>
                  <div className="text-gray-400 font-medium">
                    {stat.label}
                  </div>
                </motion.div>
              )
            })}
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-4xl md:text-5xl font-bold text-white mb-6">
              Por que escolher o <span className="text-gradient">Radion</span>?
            </h2>
            <p className="text-xl text-gray-400 max-w-3xl mx-auto">
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
                  className="card p-8 text-center hover:scale-105 transition-all duration-500"
                >
                  <div className={`inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r ${feature.color} rounded-2xl mb-6`}>
                    <Icon className="h-8 w-8 text-white" />
                  </div>
                  <h3 className="text-xl font-semibold text-white mb-4">
                    {feature.title}
                  </h3>
                  <p className="text-gray-400">
                    {feature.description}
                  </p>
                </motion.div>
              )
            })}
          </div>
        </div>
      </section>

      {/* Top Stations Section */}
      <section className="py-20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between mb-12">
            <div>
              <h2 className="text-4xl font-bold text-white mb-4">
                <TrendingUp className="inline h-10 w-10 text-purple-400 mr-3" />
                Estações Populares
              </h2>
              <p className="text-xl text-gray-400">
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
            <div className="station-grid">
              {[...Array(6)].map((_, index) => (
                <div key={index} className="card p-8 animate-pulse">
                  <div className="flex items-center space-x-4 mb-6">
                    <div className="w-16 h-16 bg-slate-700 rounded-2xl"></div>
                    <div className="flex-1">
                      <div className="h-5 bg-slate-700 rounded mb-3"></div>
                      <div className="h-4 bg-slate-700 rounded w-2/3"></div>
                    </div>
                  </div>
                  <div className="space-y-3">
                    <div className="flex space-x-2">
                      <div className="h-6 w-16 bg-slate-700 rounded-full"></div>
                      <div className="h-6 w-20 bg-slate-700 rounded-full"></div>
                    </div>
                    <div className="h-4 bg-slate-700 rounded w-3/4"></div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="station-grid">
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
      <section className="py-20 relative">
        <div className="absolute inset-0 bg-gradient-to-r from-purple-600/20 to-pink-600/20"></div>
        <div className="relative max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
            className="card p-12"
          >
            <h2 className="text-4xl font-bold text-white mb-6">
              Pronto para começar?
            </h2>
            <p className="text-xl text-gray-300 mb-8">
              Junte-se a milhares de usuários que já descobriram suas rádios favoritas
            </p>
            <Link
              to="/browse"
              className="btn-primary text-lg inline-flex items-center space-x-3"
            >
              <Play className="h-6 w-6" />
              <span>Começar Agora</span>
            </Link>
          </motion.div>
        </div>
      </section>
    </div>
  )
}

export default Home