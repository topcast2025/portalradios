import React, { useState } from 'react'
import { Search, Filter } from 'lucide-react'
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

  return (
    <div className="w-full max-w-4xl mx-auto">
      <form onSubmit={handleSearch} className="relative">
        <div className="flex">
          <div className="relative flex-1">
            <Search className="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 h-5 w-5" />
            <input
              type="text"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              placeholder="Buscar rádios por nome..."
              className="w-full pl-12 pr-4 py-4 bg-white rounded-l-2xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg"
            />
          </div>
          
          <button
            type="button"
            onClick={() => setShowFilters(!showFilters)}
            className={`px-6 py-4 border-t border-b border-gray-200 transition-colors ${
              showFilters ? 'bg-blue-50 text-blue-600' : 'bg-white text-gray-600 hover:bg-gray-50'
            }`}
          >
            <Filter className="h-5 w-5" />
          </button>
          
          <button
            type="submit"
            disabled={loading}
            className="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-r-2xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-semibold"
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
        <div className="mt-4 p-6 bg-white rounded-2xl border border-gray-200 shadow-lg">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                País
              </label>
              <input
                type="text"
                value={filters.country || ''}
                onChange={(e) => handleFilterChange('country', e.target.value)}
                placeholder="Ex: Brazil"
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Idioma
              </label>
              <input
                type="text"
                value={filters.language || ''}
                onChange={(e) => handleFilterChange('language', e.target.value)}
                placeholder="Ex: portuguese"
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Categoria
              </label>
              <input
                type="text"
                value={filters.tag || ''}
                onChange={(e) => handleFilterChange('tag', e.target.value)}
                placeholder="Ex: music, news"
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>
          
          <div className="mt-4 flex justify-end space-x-3">
            <button
              type="button"
              onClick={() => {
                setFilters({})
                setSearchTerm('')
              }}
              className="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors"
            >
              Limpar
            </button>
            <button
              type="button"
              onClick={() => onSearch({ ...filters, name: searchTerm || undefined })}
              className="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
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