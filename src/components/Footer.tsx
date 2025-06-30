import React from 'react'
import { Radio, Heart, Github, Twitter, Mail } from 'lucide-react'

const Footer: React.FC = () => {
  return (
    <footer className="bg-slate-900 text-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          {/* Logo and Description */}
          <div className="col-span-1 md:col-span-2">
            <div className="flex items-center space-x-2 mb-4">
              <div className="p-2 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl">
                <Radio className="h-6 w-6 text-white" />
              </div>
              <span className="text-xl font-bold">RadioWave</span>
            </div>
            <p className="text-gray-300 mb-6 max-w-md">
              Descubra e ouça milhares de rádios online de todo o mundo. 
              Música, notícias, esportes e muito mais, tudo em um só lugar.
            </p>
            <div className="flex space-x-4">
              <a href="#" className="text-gray-400 hover:text-white transition-colors">
                <Github className="h-5 w-5" />
              </a>
              <a href="#" className="text-gray-400 hover:text-white transition-colors">
                <Twitter className="h-5 w-5" />
              </a>
              <a href="#" className="text-gray-400 hover:text-white transition-colors">
                <Mail className="h-5 w-5" />
              </a>
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="text-lg font-semibold mb-4">Links Rápidos</h3>
            <ul className="space-y-2">
              <li><a href="/" className="text-gray-300 hover:text-white transition-colors">Início</a></li>
              <li><a href="/browse" className="text-gray-300 hover:text-white transition-colors">Explorar</a></li>
              <li><a href="/favorites" className="text-gray-300 hover:text-white transition-colors">Favoritos</a></li>
              <li><a href="/about" className="text-gray-300 hover:text-white transition-colors">Sobre</a></li>
            </ul>
          </div>

          {/* Categories */}
          <div>
            <h3 className="text-lg font-semibold mb-4">Categorias</h3>
            <ul className="space-y-2">
              <li><span className="text-gray-300">Música</span></li>
              <li><span className="text-gray-300">Notícias</span></li>
              <li><span className="text-gray-300">Esportes</span></li>
              <li><span className="text-gray-300">Talk Shows</span></li>
            </ul>
          </div>
        </div>

        <div className="border-t border-gray-800 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
          <p className="text-gray-400 text-sm">
            © 2024 RadioWave. Feito com <Heart className="h-4 w-4 inline text-red-500" /> para os amantes de rádio.
          </p>
          <p className="text-gray-400 text-sm mt-2 md:mt-0">
            Dados fornecidos por Radio-Browser.info
          </p>
        </div>
      </div>
    </footer>
  )
}

export default Footer