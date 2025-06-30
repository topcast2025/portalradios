import React from 'react'
import { Waves, Heart, Github, Twitter, Mail, Radio } from 'lucide-react'

const Footer: React.FC = () => {
  return (
    <footer className="bg-slate-900 border-t border-slate-800">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-12">
          {/* Logo and Description */}
          <div className="col-span-1 md:col-span-2">
            <div className="flex items-center space-x-3 mb-6">
              <div className="p-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl">
                <Waves className="h-8 w-8 text-white" />
              </div>
              <div>
                <span className="text-2xl font-bold text-gradient">Radion</span>
                <div className="text-sm text-purple-400">Online Radio</div>
              </div>
            </div>
            <p className="text-gray-400 mb-8 max-w-md leading-relaxed">
              Descubra e ouça milhares de rádios online de todo o mundo. 
              Música, notícias, esportes e muito mais, tudo em um só lugar.
            </p>
            <div className="flex space-x-4">
              <a href="#" className="p-3 bg-slate-800 rounded-xl text-gray-400 hover:text-white hover:bg-purple-600 transition-all duration-300">
                <Github className="h-5 w-5" />
              </a>
              <a href="#" className="p-3 bg-slate-800 rounded-xl text-gray-400 hover:text-white hover:bg-purple-600 transition-all duration-300">
                <Twitter className="h-5 w-5" />
              </a>
              <a href="#" className="p-3 bg-slate-800 rounded-xl text-gray-400 hover:text-white hover:bg-purple-600 transition-all duration-300">
                <Mail className="h-5 w-5" />
              </a>
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="text-lg font-semibold text-white mb-6">Links Rápidos</h3>
            <ul className="space-y-3">
              <li><a href="/" className="text-gray-400 hover:text-purple-400 transition-colors">Início</a></li>
              <li><a href="/browse" className="text-gray-400 hover:text-purple-400 transition-colors">Explorar</a></li>
              <li><a href="/favorites" className="text-gray-400 hover:text-purple-400 transition-colors">Favoritos</a></li>
              <li><a href="/about" className="text-gray-400 hover:text-purple-400 transition-colors">Sobre</a></li>
            </ul>
          </div>

          {/* Categories */}
          <div>
            <h3 className="text-lg font-semibold text-white mb-6">Categorias</h3>
            <ul className="space-y-3">
              <li><span className="text-gray-400">Música</span></li>
              <li><span className="text-gray-400">Notícias</span></li>
              <li><span className="text-gray-400">Esportes</span></li>
              <li><span className="text-gray-400">Talk Shows</span></li>
            </ul>
          </div>
        </div>

        <div className="border-t border-slate-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
          <p className="text-gray-400 text-sm flex items-center">
            © 2024 Radion. Feito com <Heart className="h-4 w-4 inline text-pink-500 mx-1" /> para os amantes de rádio.
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