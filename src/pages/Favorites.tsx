import React from 'react'
import { motion } from 'framer-motion'
import { Heart, Music } from 'lucide-react'
import { Link } from 'react-router-dom'
import { useRadio } from '../context/RadioContext'
import RadioCard from '../components/RadioCard'

const Favorites: React.FC = () => {
  const { favorites } = useRadio()

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
              <Heart className="inline h-12 w-12 text-red-500 mr-3" />
              Suas Favoritas
            </h1>
            <p className="text-xl text-gray-600 max-w-3xl mx-auto">
              Suas estações de rádio favoritas em um só lugar
            </p>
          </motion.div>
        </div>

        {/* Favorites Count */}
        {favorites.length > 0 && (
          <div className="mb-8">
            <div className="flex items-center justify-between">
              <h2 className="text-2xl font-bold text-gray-900">
                Minhas Estações
              </h2>
              <span className="text-gray-500">
                {favorites.length} {favorites.length === 1 ? 'favorita' : 'favoritas'}
              </span>
            </div>
          </div>
        )}

        {/* Favorites Grid */}
        {favorites.length > 0 ? (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 0.5 }}
            className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
          >
            {favorites.map((station, index) => (
              <RadioCard
                key={station.stationuuid}
                station={station}
                index={index}
              />
            ))}
          </motion.div>
        ) : (
          <div className="text-center py-16">
            <div className="mb-8">
              <div className="inline-flex items-center justify-center w-24 h-24 bg-gray-100 rounded-full mb-4">
                <Music className="h-12 w-12 text-gray-400" />
              </div>
            </div>
            <h3 className="text-2xl font-semibold text-gray-900 mb-4">
              Nenhuma favorita ainda
            </h3>
            <p className="text-gray-600 mb-8 max-w-md mx-auto">
              Comece a explorar e adicione suas estações favoritas clicando no ícone de coração
            </p>
            <Link
              to="/browse"
              className="btn-primary"
            >
              Explorar Rádios
            </Link>
          </div>
        )}
      </div>
    </div>
  )
}

export default Favorites