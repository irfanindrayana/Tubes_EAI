-- User Management Database Initialization
CREATE DATABASE IF NOT EXISTS transbandung_users;
USE transbandung_users;

-- Grant permissions to microservice user
GRANT ALL PRIVILEGES ON transbandung_users.* TO 'microservice'@'%';
FLUSH PRIVILEGES;

-- Create default admin user (password: admin123)
-- This will be inserted after Laravel migrations run
