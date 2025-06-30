import React from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { Play, Pause, Volume2, Heart, X, SkipBack, SkipForward } from 'lucide-react'
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
        className="fixed bottom-0 left-0 right-0 z-50 bg-slate-900/95 backdrop-blur-xl border-t border-slate-700/50"
      >
        <div className="max-w-7xl mx-auto px-4 py-6">
          <div className="flex items-center justify-between">
            {/* Station Info */}
            <div className="flex items-center space-x-4 flex-1 min-w-0">
              <div className="flex-shrink-0">
                {currentStation.favicon ? (
                  <img
                    src={currentStation.favicon}
                    alt={currentStation.name}
                    className="w-16 h-16 rounded-2xl object-cover border-2 border-purple-500/30"
                    onError={(e) => {
                      const target = e.target as HTMLImageElement
                      target.style.display = 'none'
                    }}
                  />
                ) : (
                  <div className="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center border-2 border-purple-500/30">
                    <span className="text-white font-bold text-lg">
                      {currentStation.name.charAt(0).toUpperCase()}
                    </span>
                  </div>
                )}
              </div>

              <div className="min-w-0 flex-1">
                <h3 className="font-bold text-white text-lg truncate">
                  {currentStation.name}
                </h3>
                <p className="text-sm text-gray-400 truncate">
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
            <div className="flex items-center space-x-6">
              {/* Favorite Button */}
              <button
                onClick={handleFavoriteToggle}
                className={`p-3 rounded-full transition-all duration-300 ${
                  isFavorite(currentStation.stationuuid)
                    ? 'text-pink-500 bg-pink-500/20 hover:bg-pink-500/30'
                    : 'text-gray-400 hover:text-pink-500 hover:bg-pink-500/20'
                }`}
              >
                <Heart 
                  className={`h-6 w-6 ${
                    isFavorite(currentStation.stationuuid) ? 'fill-current' : ''
                  }`} 
                />
              </button>

              {/* Previous Button */}
              <button className="p-3 text-gray-400 hover:text-white transition-colors">
                <SkipBack className="h-6 w-6" />
              </button>

              {/* Play/Pause Button */}
              <button
                onClick={isPlaying ? pauseStation : () => playStation(currentStation)}
                className="p-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-full transition-all duration-300 transform hover:scale-110 neon-glow"
              >
                {isPlaying ? (
                  <Pause className="h-6 w-6" />
                ) : (
                  <Play className="h-6 w-6 ml-0.5" />
                )}
              </button>

              {/* Next Button */}
              <button className="p-3 text-gray-400 hover:text-white transition-colors">
                <SkipForward className="h-6 w-6" />
              </button>

              {/* Volume Control */}
              <div className="hidden sm:flex items-center space-x-3">
                <Volume2 className="h-5 w-5 text-gray-400" />
                <input
                  type="range"
                  min="0"
                  max="1"
                  step="0.1"
                  value={volume}
                  onChange={(e) => setVolume(parseFloat(e.target.value))}
                  className="w-24 h-2 slider"
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