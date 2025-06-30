import React, { useState } from 'react'
import { Search, Filter, X } from 'lucide-react'
import { SearchFilters } from '../types/radio'

interface SearchBarProps {
  onSearch: (filters: SearchFilters) => void
  loading?: boolean
}

const SearchBar: React.FC<SearchBarProps> = ({ onSearch, loading = false }) => {
  const [searchTerm, setSearchTerm] = useState('')
  const [showFilters, setShowFilters] = useState(false)
  const [filters, setFilters] = useState<SearchFilters>({})

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault()
    onSearch({
      ...filters,
      name: searchTerm || undefined
    })
  }

  const handleFilterChange = (key: keyof SearchFilters, value: string) => {
    const newFilters = {
      ...filters,
      [key]: value || undefined
    }
    setFilters(newFilters)
  }

  const clearFilters = () => {
    setFilters({})
    setSearchTerm('')
  }

  return (
    <div className="w-full max-w-4xl mx-auto">
      <form onSubmit={handleSearch} className="relative">
        <div className="flex">
          <div className="relative flex-1">
            <Search className="absolute left-6 top-1/2 transform -translate-y-1/2 text-gray-400 h-6 w-6" />
            <input
              type="text"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              placeholder="Buscar rádios por nome..."
              className="w-full pl-16 pr-6 py-5 bg-slate-800/50 backdrop-blur-xl rounded-l-3xl border border-slate-700/50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-lg text-white placeholder-gray-400"
            />
          </div>
          
          <button
            type="button"
            onClick={() => setShowFilters(!showFilters)}
            className={`px-8 py-5 border-t border-b border-slate-700/50 transition-all duration-300 ${
              showFilters 
                ? 'bg-purple-600 text-white' 
                : 'bg-slate-800/50 text-gray-400 hover:text-white hover:bg-slate-700/50'
            }`}
          >
            <Filter className="h-6 w-6" />
          </button>
          
          <button
            type="submit"
            disabled={loading}
            className="px-10 py-5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-r-3xl transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed font-semibold text-lg"
          >
            {loading ? (
              <div className="spinner"></div>
            ) : (
              'Buscar'
            )}
          </button>
        </div>
      </form>

      {/* Filters */}
      {showFilters && (
        <div className="mt-6 p-8 bg-slate-800/50 backdrop-blur-xl rounded-3xl border border-slate-700/50">
          <div className="flex items-center justify-between mb-6">
            <h3 className="text-lg font-semibold text-white">Filtros Avançados</h3>
            <button
              onClick={() => setShowFilters(false)}
              className="p-2 text-gray-400 hover:text-white transition-colors"
            >
              <X className="h-5 w-5" />
            </button>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label className="block text-sm font-medium text-gray-300 mb-3">
                País
              </label>
              <input
                type="text"
                value={filters.country || ''}
                onChange={(e) => handleFilterChange('country', e.target.value)}
                placeholder="Ex: Brazil"
                className="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-300 mb-3">
                Idioma
              </label>
              <input
                type="text"
                value={filters.language || ''}
                onChange={(e) => handleFilterChange('language', e.target.value)}
                placeholder="Ex: portuguese"
                className="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-300 mb-3">
                Categoria
              </label>
              <input
                type="text"
                value={filters.tag || ''}
                onChange={(e) => handleFilterChange('tag', e.target.value)}
                placeholder="Ex: music, news"
                className="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400"
              />
            </div>
          </div>
          
          <div className="mt-8 flex justify-end space-x-4">
            <button
              type="button"
              onClick={clearFilters}
              className="px-6 py-3 text-gray-400 hover:text-white transition-colors"
            >
              Limpar
            </button>
            <button
              type="button"
              onClick={() => onSearch({ ...filters, name: searchTerm || undefined })}
              className="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl transition-all duration-300 font-semibold"
            >
              Aplicar Filtros
            </button>
          </div>
        </div>
      )}
    </div>
  )
}

export default SearchBar