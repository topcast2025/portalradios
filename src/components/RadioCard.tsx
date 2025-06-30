import React from 'react'
import { motion } from 'framer-motion'
import { Play, Pause, Heart, MapPin, Globe, Users } from 'lucide-react'
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
      transition={{ duration: 0.3, delay: index * 0.1 }}
      className={`radio-card ${isCurrentlyPlaying ? 'playing-animation glow' : ''}`}
      onClick={handlePlay}
    >
      <div className="flex items-center space-x-4">
        {/* Station Image/Icon */}
        <div className="flex-shrink-0 relative">
          {station.favicon ? (
            <img
              src={station.favicon}
              alt={station.name}
              className="w-16 h-16 rounded-xl object-cover"
              onError={(e) => {
                const target = e.target as HTMLImageElement
                target.style.display = 'none'
                target.nextElementSibling?.classList.remove('hidden')
              }}
            />
          ) : null}
          <div className={`w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl flex items-center justify-center ${station.favicon ? 'hidden' : ''}`}>
            <span className="text-white font-bold text-xl">
              {station.name.charAt(0).toUpperCase()}
            </span>
          </div>
          
          {/* Play/Pause Overlay */}
          <div className="absolute inset-0 bg-black/50 rounded-xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
            {isCurrentlyPlaying ? (
              <Pause className="h-6 w-6 text-white" />
            ) : (
              <Play className="h-6 w-6 text-white ml-0.5" />
            )}
          </div>
        </div>

        {/* Station Info */}
        <div className="flex-1 min-w-0">
          <h3 className="font-semibold text-gray-900 truncate group-hover:text-blue-600 transition-colors">
            {station.name}
          </h3>
          
          <div className="flex items-center space-x-4 mt-1 text-sm text-gray-500">
            {station.country && (
              <div className="flex items-center space-x-1">
                <MapPin className="h-3 w-3" />
                <span>{station.country}</span>
              </div>
            )}
            
            {station.language && (
              <div className="flex items-center space-x-1">
                <Globe className="h-3 w-3" />
                <span>{station.language}</span>
              </div>
            )}
            
            {station.votes > 0 && (
              <div className="flex items-center space-x-1">
                <Users className="h-3 w-3" />
                <span>{station.votes}</span>
              </div>
            )}
          </div>

          {/* Tags */}
          {station.tags && (
            <div className="flex flex-wrap gap-1 mt-2">
              {station.tags.split(',').slice(0, 3).map((tag, index) => (
                <span
                  key={index}
                  className="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full"
                >
                  {tag.trim()}
                </span>
              ))}
            </div>
          )}
        </div>

        {/* Favorite Button */}
        <button
          onClick={handleFavoriteToggle}
          className={`p-2 rounded-full transition-colors ${
            isFavorite(station.stationuuid)
              ? 'text-red-500 hover:text-red-600'
              : 'text-gray-400 hover:text-red-500'
          }`}
        >
          <Heart 
            className={`h-5 w-5 ${
              isFavorite(station.stationuuid) ? 'fill-current' : ''
            }`} 
          />
        </button>
      </div>

      {/* Audio Visualizer for playing station */}
      {isCurrentlyPlaying && (
        <div className="mt-4 flex justify-center">
          <div className="audio-visualizer">
            <div className="audio-bar w-1"></div>
            <div className="audio-bar w-1"></div>
            <div className="audio-bar w-1"></div>
            <div className="audio-bar w-1"></div>
            <div className="audio-bar w-1"></div>
          </div>
        </div>
      )}
    </motion.div>
  )
}

export default RadioCard