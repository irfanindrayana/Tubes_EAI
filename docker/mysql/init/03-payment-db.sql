-- Payment Database Initialization
CREATE DATABASE IF NOT EXISTS transbandung_payments;
USE transbandung_payments;

-- Grant permissions to microservice user
GRANT ALL PRIVILEGES ON transbandung_payments.* TO 'microservice'@'%';
FLUSH PRIVILEGES;
