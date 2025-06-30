import React from 'react'
import { Routes, Route } from 'react-router-dom'
import { motion } from 'framer-motion'
import Header from './components/Header'
import Footer from './components/Footer'
import Home from './pages/Home'
import Browse from './pages/Browse'
import Favorites from './pages/Favorites'
import About from './pages/About'
import { RadioProvider } from './context/RadioContext'
import AudioPlayer from './components/AudioPlayer'

function App() {
  return (
    <RadioProvider>
      <div className="min-h-screen flex flex-col">
        <Header />
        
        <motion.main 
          className="flex-1"
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ duration: 0.5 }}
        >
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/browse" element={<Browse />} />
            <Route path="/favorites" element={<Favorites />} />
            <Route path="/about" element={<About />} />
          </Routes>
        </motion.main>

        <AudioPlayer />
        <Footer />
      </div>
    </RadioProvider>
  )
}

export default App