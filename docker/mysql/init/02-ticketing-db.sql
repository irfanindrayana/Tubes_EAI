-- Ticketing Database Initialization
CREATE DATABASE IF NOT EXISTS transbandung_ticketing;
USE transbandung_ticketing;

-- Grant permissions to microservice user
GRANT ALL PRIVILEGES ON transbandung_ticketing.* TO 'microservice'@'%';
FLUSH PRIVILEGES;
