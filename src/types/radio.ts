export interface RadioStation {
  changeuuid: string
  stationuuid: string
  name: string
  url: string
  url_resolved: string
  homepage: string
  favicon: string
  tags: string
  country: string
  countrycode: string
  state: string
  language: string
  languagecodes: string
  votes: number
  lastchangetime: string
  lastchangetime_iso8601: string
  codec: string
  bitrate: number
  hls: number
  lastcheckok: number
  lastchecktime: string
  lastchecktime_iso8601: string
  lastcheckoktime: string
  lastcheckoktime_iso8601: string
  lastlocalchecktime: string
  lastlocalchecktime_iso8601: string
  clicktimestamp: string
  clicktimestamp_iso8601: string
  clickcount: number
  clicktrend: number
  ssl_error: number
  geo_lat: number
  geo_long: number
  has_extended_info: boolean
}

export interface RadioContextType {
  currentStation: RadioStation | null
  isPlaying: boolean
  volume: number
  favorites: RadioStation[]
  playStation: (station: RadioStation) => void
  pauseStation: () => void
  setVolume: (volume: number) => void
  addToFavorites: (station: RadioStation) => void
  removeFromFavorites: (stationId: string) => void
  isFavorite: (stationId: string) => boolean
}

export interface SearchFilters {
  country?: string
  language?: string
  tag?: string
  name?: string
}