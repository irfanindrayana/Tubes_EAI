-- Inbox Database Initialization
CREATE DATABASE IF NOT EXISTS transbandung_inbox;
USE transbandung_inbox;

-- Grant permissions to microservice user
GRANT ALL PRIVILEGES ON transbandung_inbox.* TO 'microservice'@'%';
FLUSH PRIVILEGES;
