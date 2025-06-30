import React from 'react'
import { motion } from 'framer-motion'
import { Radio, Globe, Users, Headphones, Heart, Star, Zap, Shield } from 'lucide-react'

const About: React.FC = () => {
  const features = [
    {
      icon: Globe,
      title: 'Alcance Global',
      description: 'Acesso a milhares de estações de rádio de mais de 200 países ao redor do mundo.'
    },
    {
      icon: Headphones,
      title: 'Qualidade Premium',
      description: 'Streaming de áudio em alta qualidade com suporte a múltiplos formatos.'
    },
    {
      icon: Heart,
      title: 'Favoritos Personalizados',
      description: 'Salve suas estações preferidas e acesse-as rapidamente a qualquer momento.'
    },
    {
      icon: Zap,
      title: 'Interface Rápida',
      description: 'Navegação intuitiva e carregamento instantâneo para a melhor experiência.'
    },
    {
      icon: Shield,
      title: 'Totalmente Gratuito',
      description: 'Sem taxas ocultas, sem assinaturas. Desfrute de rádio online completamente grátis.'
    },
    {
      icon: Star,
      title: 'Sempre Atualizado',
      description: 'Base de dados constantemente atualizada com novas estações e melhorias.'
    }
  ]

  const stats = [
    { number: '50,000+', label: 'Estações de Rádio' },
    { number: '200+', label: 'Países' },
    { number: '100+', label: 'Idiomas' },
    { number: '24/7', label: 'Disponibilidade' }
  ]

  return (
    <div className="min-h-screen">
      {/* Hero Section */}
      <section className="py-20 bg-gradient-to-br from-blue-600 via-purple-600 to-blue-800 text-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center">
            <motion.div
              initial={{ opacity: 0, y: 30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8 }}
            >
              <Radio className="h-20 w-20 mx-auto mb-6 text-yellow-400" />
              <h1 className="text-5xl md:text-6xl font-bold mb-6">
                Sobre o RadioWave
              </h1>
              <p className="text-xl md:text-2xl text-blue-100 max-w-4xl mx-auto">
                Conectando você às melhores estações de rádio do mundo inteiro, 
                com uma experiência única e totalmente gratuita.
              </p>
            </motion.div>
          </div>
        </div>
      </section>

      {/* Mission Section */}
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <motion.div
              initial={{ opacity: 0, x: -30 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.8 }}
            >
              <h2 className="text-4xl font-bold text-gray-900 mb-6">
                Nossa Missão
              </h2>
              <p className="text-lg text-gray-600 mb-6">
                O RadioWave nasceu da paixão pela música e pela diversidade cultural que o rádio proporciona. 
                Nossa missão é democratizar o acesso às estações de rádio de todo o mundo, oferecendo uma 
                plataforma moderna, intuitiva e completamente gratuita.
              </p>
              <p className="text-lg text-gray-600 mb-6">
                Acreditamos que a música e a informação não têm fronteiras, e queremos conectar pessoas 
                aos sons e vozes que mais amam, independentemente de onde estejam no mundo.
              </p>
              <div className="flex items-center space-x-4">
                <Users className="h-8 w-8 text-blue-600" />
                <span className="text-lg font-semibold text-gray-900">
                  Feito com ❤️ para amantes de rádio
                </span>
              </div>
            </motion.div>

            <motion.div
              initial={{ opacity: 0, x: 30 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.8, delay: 0.2 }}
              className="relative"
            >
              <div className="bg-gradient-to-r from-blue-500 to-purple-500 rounded-2xl p-8 text-white">
                <h3 className="text-2xl font-bold mb-4">Por que RadioWave?</h3>
                <ul className="space-y-3">
                  <li className="flex items-center space-x-3">
                    <div className="w-2 h-2 bg-yellow-400 rounded-full"></div>
                    <span>Interface moderna e intuitiva</span>
                  </li>
                  <li className="flex items-center space-x-3">
                    <div className="w-2 h-2 bg-yellow-400 rounded-full"></div>
                    <span>Sem anúncios intrusivos</span>
                  </li>
                  <li className="flex items-center space-x-3">
                    <div className="w-2 h-2 bg-yellow-400 rounded-full"></div>
                    <span>Busca avançada e filtros</span>
                  </li>
                  <li className="flex items-center space-x-3">
                    <div className="w-2 h-2 bg-yellow-400 rounded-full"></div>
                    <span>Favoritos sincronizados</span>
                  </li>
                </ul>
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-20 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-4xl font-bold text-gray-900 mb-4">
              RadioWave em Números
            </h2>
            <p className="text-xl text-gray-600">
              Conectando milhões de ouvintes às suas estações favoritas
            </p>
          </div>

          <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
            {stats.map((stat, index) => (
              <motion.div
                key={index}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, delay: index * 0.1 }}
                className="text-center"
              >
                <div className="text-4xl md:text-5xl font-bold text-blue-600 mb-2">
                  {stat.number}
                </div>
                <div className="text-gray-600 font-medium">
                  {stat.label}
                </div>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-4xl font-bold text-gray-900 mb-4">
              Recursos Incríveis
            </h2>
            <p className="text-xl text-gray-600">
              Tudo que você precisa para a melhor experiência de rádio online
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {features.map((feature, index) => {
              const Icon = feature.icon
              return (
                <motion.div
                  key={index}
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ duration: 0.5, delay: index * 0.1 }}
                  className="card p-8 text-center hover:shadow-xl transition-shadow"
                >
                  <div className="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-500 rounded-2xl mb-6">
                    <Icon className="h-8 w-8 text-white" />
                  </div>
                  <h3 className="text-xl font-semibold text-gray-900 mb-4">
                    {feature.title}
                  </h3>
                  <p className="text-gray-600">
                    {feature.description}
                  </p>
                </motion.div>
              )
            })}
          </div>
        </div>
      </section>

      {/* Technology Section */}
      <section className="py-20 bg-gradient-to-r from-blue-600 to-purple-600 text-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center">
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8 }}
            >
              <h2 className="text-4xl font-bold mb-6">
                Tecnologia de Ponta
              </h2>
              <p className="text-xl text-blue-100 mb-8 max-w-3xl mx-auto">
                Utilizamos as mais modernas tecnologias web para garantir uma experiência 
                rápida, confiável e responsiva em todos os dispositivos.
              </p>
              <div className="grid grid-cols-2 md:grid-cols-4 gap-8 mt-12">
                <div className="text-center">
                  <div className="text-3xl font-bold mb-2">React</div>
                  <div className="text-blue-200">Interface Moderna</div>
                </div>
                <div className="text-center">
                  <div className="text-3xl font-bold mb-2">API</div>
                  <div className="text-blue-200">Dados em Tempo Real</div>
                </div>
                <div className="text-center">
                  <div className="text-3xl font-bold mb-2">PWA</div>
                  <div className="text-blue-200">App-like Experience</div>
                </div>
                <div className="text-center">
                  <div className="text-3xl font-bold mb-2">Cloud</div>
                  <div className="text-blue-200">Alta Disponibilidade</div>
                </div>
              </div>
            </motion.div>
          </div>
        </div>
      </section>
    </div>
  )
}

export default About