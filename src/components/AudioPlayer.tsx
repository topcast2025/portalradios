import React from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { Play, Pause, Volume2, Heart, X } from 'lucide-react'
import { useRadio } from '../context/RadioContext'

const AudioPlayer: React.FC = () => {
  const { 
    currentStation, 
    isPlaying, 
    volume, 
    playStation, 
    pauseStation, 
    setVolume,
    addToFavorites,
    removeFromFavorites,
    isFavorite
  } = useRadio()

  if (!currentStation) return null

  const handleFavoriteToggle = () => {
    if (isFavorite(currentStation.stationuuid)) {
      removeFromFavorites(currentStation.stationuuid)
    } else {
      addToFavorites(currentStation)
    }
  }

  return (
    <AnimatePresence>
      <motion.div
        initial={{ y: 100, opacity: 0 }}
        animate={{ y: 0, opacity: 1 }}
        exit={{ y: 100, opacity: 0 }}
        className="fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-t border-gray-200 shadow-2xl"
      >
        <div className="max-w-7xl mx-auto px-4 py-4">
          <div className="flex items-center justify-between">
            {/* Station Info */}
            <div className="flex items-center space-x-4 flex-1 min-w-0">
              <div className="flex-shrink-0">
                {currentStation.favicon ? (
                  <img
                    src={currentStation.favicon}
                    alt={currentStation.name}
                    className="w-12 h-12 rounded-lg object-cover"
                    onError={(e) => {
                      const target = e.target as HTMLImageElement
                      target.style.display = 'none'
                    }}
                  />
                ) : (
                  <div className="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center">
                    <span className="text-white font-bold text-lg">
                      {currentStation.name.charAt(0).toUpperCase()}
                    </span>
                  </div>
                )}
              </div>

              <div className="min-w-0 flex-1">
                <h3 className="font-semibold text-gray-900 truncate">
                  {currentStation.name}
                </h3>
                <p className="text-sm text-gray-500 truncate">
                  {currentStation.country} • {currentStation.language}
                </p>
              </div>

              {/* Audio Visualizer */}
              {isPlaying && (
                <div className="audio-visualizer">
                  <div className="audio-bar w-1"></div>
                  <div className="audio-bar w-1"></div>
                  <div className="audio-bar w-1"></div>
                  <div className="audio-bar w-1"></div>
                  <div className="audio-bar w-1"></div>
                </div>
              )}
            </div>

            {/* Controls */}
            <div className="flex items-center space-x-4">
              {/* Favorite Button */}
              <button
                onClick={handleFavoriteToggle}
                className={`p-2 rounded-full transition-colors ${
                  isFavorite(currentStation.stationuuid)
                    ? 'text-red-500 hover:text-red-600'
                    : 'text-gray-400 hover:text-red-500'
                }`}
              >
                <Heart 
                  className={`h-5 w-5 ${
                    isFavorite(currentStation.stationuuid) ? 'fill-current' : ''
                  }`} 
                />
              </button>

              {/* Play/Pause Button */}
              <button
                onClick={isPlaying ? pauseStation : () => playStation(currentStation)}
                className="p-3 bg-blue-600 hover:bg-blue-700 text-white rounded-full transition-colors"
              >
                {isPlaying ? (
                  <Pause className="h-5 w-5" />
                ) : (
                  <Play className="h-5 w-5 ml-0.5" />
                )}
              </button>

              {/* Volume Control */}
              <div className="hidden sm:flex items-center space-x-2">
                <Volume2 className="h-4 w-4 text-gray-500" />
                <input
                  type="range"
                  min="0"
                  max="1"
                  step="0.1"
                  value={volume}
                  onChange={(e) => setVolume(parseFloat(e.target.value))}
                  className="w-20 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                />
              </div>
            </div>
          </div>
        </div>
      </motion.div>
    </AnimatePresence>
  )
}

export default AudioPlayer