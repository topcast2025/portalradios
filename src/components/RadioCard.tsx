import React from 'react'
import { motion } from 'framer-motion'
import { Play, Pause, Heart, MapPin, Globe, Users, Volume2 } from 'lucide-react'
import { RadioStation } from '../types/radio'
import { useRadio } from '../context/RadioContext'

interface RadioCardProps {
  station: RadioStation
  index?: number
}

const RadioCard: React.FC<RadioCardProps> = ({ station, index = 0 }) => {
  const { 
    currentStation, 
    isPlaying, 
    playStation, 
    pauseStation,
    addToFavorites,
    removeFromFavorites,
    isFavorite
  } = useRadio()

  const isCurrentStation = currentStation?.stationuuid === station.stationuuid
  const isCurrentlyPlaying = isCurrentStation && isPlaying

  const handlePlay = () => {
    if (isCurrentStation) {
      if (isPlaying) {
        pauseStation()
      } else {
        playStation(station)
      }
    } else {
      playStation(station)
    }
  }

  const handleFavoriteToggle = (e: React.MouseEvent) => {
    e.stopPropagation()
    if (isFavorite(station.stationuuid)) {
      removeFromFavorites(station.stationuuid)
    } else {
      addToFavorites(station)
    }
  }

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5, delay: index * 0.1 }}
      className={`radio-card group ${isCurrentlyPlaying ? 'playing-animation neon-glow' : ''}`}
      onClick={handlePlay}
    >
      <div className="relative z-10">
        {/* Station Header */}
        <div className="flex items-start justify-between mb-6">
          <div className="flex items-center space-x-4">
            {/* Station Image/Icon */}
            <div className="relative">
              {station.favicon ? (
                <img
                  src={station.favicon}
                  alt={station.name}
                  className="w-16 h-16 rounded-2xl object-cover border-2 border-purple-500/30"
                  onError={(e) => {
                    const target = e.target as HTMLImageElement
                    target.style.display = 'none'
                    target.nextElementSibling?.classList.remove('hidden')
                  }}
                />
              ) : null}
              <div className={`w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center border-2 border-purple-500/30 ${station.favicon ? 'hidden' : ''}`}>
                <span className="text-white font-bold text-xl">
                  {station.name.charAt(0).toUpperCase()}
                </span>
              </div>
              
              {/* Play/Pause Overlay */}
              <div className="absolute inset-0 bg-black/60 rounded-2xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                {isCurrentlyPlaying ? (
                  <Pause className="h-8 w-8 text-white" />
                ) : (
                  <Play className="h-8 w-8 text-white ml-1" />
                )}
              </div>
            </div>

            {/* Station Info */}
            <div className="flex-1 min-w-0">
              <h3 className="font-bold text-white text-lg truncate group-hover:text-purple-300 transition-colors">
                {station.name}
              </h3>
              
              <div className="flex items-center space-x-3 mt-2 text-sm text-gray-400">
                {station.country && (
                  <div className="flex items-center space-x-1">
                    <MapPin className="h-4 w-4" />
                    <span>{station.country}</span>
                  </div>
                )}
                
                {station.language && (
                  <div className="flex items-center space-x-1">
                    <Globe className="h-4 w-4" />
                    <span>{station.language}</span>
                  </div>
                )}
              </div>
            </div>
          </div>

          {/* Favorite Button */}
          <button
            onClick={handleFavoriteToggle}
            className={`p-3 rounded-full transition-all duration-300 ${
              isFavorite(station.stationuuid)
                ? 'text-pink-500 bg-pink-500/20 hover:bg-pink-500/30'
                : 'text-gray-400 hover:text-pink-500 hover:bg-pink-500/20'
            }`}
          >
            <Heart 
              className={`h-5 w-5 ${
                isFavorite(station.stationuuid) ? 'fill-current' : ''
              }`} 
            />
          </button>
        </div>

        {/* Station Details */}
        <div className="space-y-4">
          {/* Tags */}
          {station.tags && (
            <div className="flex flex-wrap gap-2">
              {station.tags.split(',').slice(0, 3).map((tag, index) => (
                <span
                  key={index}
                  className="px-3 py-1 bg-purple-500/20 text-purple-300 text-xs rounded-full border border-purple-500/30"
                >
                  {tag.trim()}
                </span>
              ))}
            </div>
          )}

          {/* Stats */}
          <div className="flex items-center justify-between text-sm text-gray-400">
            {station.votes > 0 && (
              <div className="flex items-center space-x-1">
                <Users className="h-4 w-4" />
                <span>{station.votes} votos</span>
              </div>
            )}
            
            {station.bitrate > 0 && (
              <div className="flex items-center space-x-1">
                <Volume2 className="h-4 w-4" />
                <span>{station.bitrate}kbps</span>
              </div>
            )}
          </div>

          {/* Audio Visualizer for playing station */}
          {isCurrentlyPlaying && (
            <div className="flex justify-center pt-4">
              <div className="audio-visualizer">
                <div className="audio-bar w-1"></div>
                <div className="audio-bar w-1"></div>
                <div className="audio-bar w-1"></div>
                <div className="audio-bar w-1"></div>
                <div className="audio-bar w-1"></div>
              </div>
            </div>
          )}
        </div>
      </div>
    </motion.div>
  )
}

export default RadioCard