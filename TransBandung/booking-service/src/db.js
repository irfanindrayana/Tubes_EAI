const mysql = require('mysql2/promise');

// Database configuration
const dbConfig = {
  host: process.env.DB_HOST || 'localhost',
  port: parseInt(process.env.DB_PORT || '3308'),
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'transbandung_booking',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
};

// Create a connection pool
let pool;

const connectDB = async () => {
  try {
    pool = mysql.createPool(dbConfig);
    
    // Test the connection
    const connection = await pool.getConnection();
    console.log('Database connection established successfully.');
    connection.release();
    
    return pool;
  } catch (error) {
    console.error('Error connecting to the database:', error);
    // Retry after 5 seconds
    console.log('Retrying in 5 seconds...');
    setTimeout(connectDB, 5000);
  }
};

const getDB = () => pool;

module.exports = {
  connectDB,
  getDB,
};
