-- Reviews Database Initialization
CREATE DATABASE IF NOT EXISTS transbandung_reviews;
USE transbandung_reviews;

-- Grant permissions to microservice user
GRANT ALL PRIVILEGES ON transbandung_reviews.* TO 'microservice'@'%';
FLUSH PRIVILEGES;
