-- Script to initialize all microservice databases
CREATE DATABASE IF NOT EXISTS transbandung_users CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS transbandung_ticketing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS transbandung_payments CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS transbandung_reviews CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS transbandung_inbox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create additional user if needed
CREATE USER IF NOT EXISTS 'laravel'@'%' IDENTIFIED BY 'laravel123';
GRANT ALL PRIVILEGES ON transbandung_users.* TO 'laravel'@'%';
GRANT ALL PRIVILEGES ON transbandung_ticketing.* TO 'laravel'@'%';
GRANT ALL PRIVILEGES ON transbandung_payments.* TO 'laravel'@'%';
GRANT ALL PRIVILEGES ON transbandung_reviews.* TO 'laravel'@'%';
GRANT ALL PRIVILEGES ON transbandung_inbox.* TO 'laravel'@'%';
FLUSH PRIVILEGES;
