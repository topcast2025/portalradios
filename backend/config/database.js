const mysql = require('mysql2/promise');
require('dotenv').config();

const dbConfig = {
  host: process.env.DB_HOST || 'localhost',
  user: process.env.DB_USER || 'soradios_radion',
  password: process.env.DB_PASSWORD || 'Ant130915!',
  database: process.env.DB_NAME || 'soradios_radion',
  port: process.env.DB_PORT || 3306,
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
  acquireTimeout: 60000,
  timeout: 60000,
  reconnect: true,
  charset: 'utf8mb4'
};

// Create connection pool
const pool = mysql.createPool(dbConfig);

// Test connection
async function testConnection() {
  try {
    const connection = await pool.getConnection();
    console.log('✅ Conectado ao banco de dados MySQL');
    console.log(`📊 Banco: ${dbConfig.database}`);
    console.log(`👤 Usuário: ${dbConfig.user}`);
    console.log(`🏠 Host: ${dbConfig.host}:${dbConfig.port}`);
    connection.release();
  } catch (error) {
    console.error('❌ Erro ao conectar com o banco de dados:', error.message);
    console.error('🔧 Verifique as configurações de conexão:');
    console.error(`   - Host: ${dbConfig.host}:${dbConfig.port}`);
    console.error(`   - Database: ${dbConfig.database}`);
    console.error(`   - User: ${dbConfig.user}`);
    console.error('   - Certifique-se de que o MySQL está rodando');
    console.error('   - Verifique se o banco de dados existe');
    console.error('   - Verifique as permissões do usuário');
    process.exit(1);
  }
}

testConnection();

module.exports = pool;