import React, { createContext, useContext, useState, useRef, useEffect } from 'react'
import { RadioStation, RadioContextType } from '../types/radio'
import toast from 'react-hot-toast'

const RadioContext = createContext<RadioContextType | undefined>(undefined)

export const useRadio = () => {
  const context = useContext(RadioContext)
  if (!context) {
    throw new Error('useRadio must be used within a RadioProvider')
  }
  return context
}

export const RadioProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [currentStation, setCurrentStation] = useState<RadioStation | null>(null)
  const [isPlaying, setIsPlaying] = useState(false)
  const [volume, setVolumeState] = useState(0.7)
  const [favorites, setFavorites] = useState<RadioStation[]>([])
  const audioRef = useRef<HTMLAudioElement | null>(null)

  // Load favorites from localStorage
  useEffect(() => {
    const savedFavorites = localStorage.getItem('radioFavorites')
    if (savedFavorites) {
      setFavorites(JSON.parse(savedFavorites))
    }
  }, [])

  // Save favorites to localStorage
  useEffect(() => {
    localStorage.setItem('radioFavorites', JSON.stringify(favorites))
  }, [favorites])

  const playStation = async (station: RadioStation) => {
    try {
      if (audioRef.current) {
        audioRef.current.pause()
        audioRef.current = null
      }

      const audio = new Audio()
      audio.crossOrigin = 'anonymous'
      audio.volume = volume
      
      // Try the resolved URL first, then fallback to the original URL
      const urlToTry = station.url_resolved || station.url
      
      audio.src = urlToTry
      audioRef.current = audio

      audio.addEventListener('loadstart', () => {
        toast.loading(`Conectando à ${station.name}...`, { id: 'radio-loading' })
      })

      audio.addEventListener('canplay', () => {
        toast.dismiss('radio-loading')
        setCurrentStation(station)
        setIsPlaying(true)
        audio.play()
        toast.success(`Reproduzindo ${station.name}`)
      })

      audio.addEventListener('error', (e) => {
        toast.dismiss('radio-loading')
        console.error('Audio error:', e)
        toast.error(`Erro ao conectar com ${station.name}`)
        setIsPlaying(false)
      })

      audio.addEventListener('ended', () => {
        setIsPlaying(false)
      })

      audio.load()
    } catch (error) {
      console.error('Error playing station:', error)
      toast.error('Erro ao reproduzir a rádio')
      setIsPlaying(false)
    }
  }

  const pauseStation = () => {
    if (audioRef.current) {
      audioRef.current.pause()
      setIsPlaying(false)
      toast('Reprodução pausada', { icon: '⏸️' })
    }
  }

  const setVolume = (newVolume: number) => {
    setVolumeState(newVolume)
    if (audioRef.current) {
      audioRef.current.volume = newVolume
    }
  }

  const addToFavorites = (station: RadioStation) => {
    if (!isFavorite(station.stationuuid)) {
      setFavorites(prev => [...prev, station])
      toast.success(`${station.name} adicionada aos favoritos!`)
    }
  }

  const removeFromFavorites = (stationId: string) => {
    setFavorites(prev => prev.filter(station => station.stationuuid !== stationId))
    toast.success('Removida dos favoritos')
  }

  const isFavorite = (stationId: string) => {
    return favorites.some(station => station.stationuuid === stationId)
  }

  return (
    <RadioContext.Provider value={{
      currentStation,
      isPlaying,
      volume,
      favorites,
      playStation,
      pauseStation,
      setVolume,
      addToFavorites,
      removeFromFavorites,
      isFavorite
    }}>
      {children}
    </RadioContext.Provider>
  )
}