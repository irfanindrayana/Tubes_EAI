-- Create databases for each service
CREATE DATABASE IF NOT EXISTS transbandung_user;
CREATE DATABASE IF NOT EXISTS transbandung_booking;
CREATE DATABASE IF NOT EXISTS transbandung_route;
CREATE DATABASE IF NOT EXISTS transbandung_review;
CREATE DATABASE IF NOT EXISTS transbandung_payment;

-- Select the user database
USE transbandung_user;

-- Create users table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  full_name VARCHAR(100) NOT NULL,
  phone_number VARCHAR(20),
  user_type ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO users (username, password, email, full_name, phone_number, user_type) VALUES
('admin', '$2b$10$Xz/XWS.JMZmQtWxcwMdJ6.xArgp3XM/NZhBIr3JwfBFzOsYDLB5SK', 'admin@transbandung.com', 'Admin User', '081234567890', 'admin'),
('johndoe', '$2b$10$Xz/XWS.JMZmQtWxcwMdJ6.xArgp3XM/NZhBIr3JwfBFzOsYDLB5SK', 'john@example.com', 'John Doe', '081234567891', 'customer'),
('janedoe', '$2b$10$Xz/XWS.JMZmQtWxcwMdJ6.xArgp3XM/NZhBIr3JwfBFzOsYDLB5SK', 'jane@example.com', 'Jane Doe', '081234567892', 'customer');

-- Select the route database
USE transbandung_route;

-- Create routes table
CREATE TABLE routes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  start_point VARCHAR(100) NOT NULL,
  end_point VARCHAR(100) NOT NULL,
  distance FLOAT NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create schedules table
CREATE TABLE schedules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  route_id INT NOT NULL,
  departure_time TIME NOT NULL,
  arrival_time TIME NOT NULL,
  bus_number VARCHAR(20) NOT NULL,
  capacity INT NOT NULL DEFAULT 40,
  price DECIMAL(10, 2) NOT NULL,
  day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
  FOREIGN KEY (route_id) REFERENCES routes(id),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data for routes
INSERT INTO routes (name, start_point, end_point, distance, description) VALUES
('Line 1', 'Alun-alun Bandung', 'Terminal Leuwi Panjang', 10.5, 'Main city route through downtown'),
('Line 2', 'Terminal Cicaheum', 'Terminal Leuwi Panjang', 12.3, 'Cross-city route from east to west'),
('Line 3', 'Dago', 'Buah Batu', 8.7, 'North-south connection');

-- Insert sample data for schedules
INSERT INTO schedules (route_id, departure_time, arrival_time, bus_number, capacity, price, day_of_week) VALUES
(1, '07:00:00', '08:15:00', 'B-001', 40, 10000.00, 'Monday'),
(1, '09:00:00', '10:15:00', 'B-002', 40, 10000.00, 'Monday'),
(2, '08:00:00', '09:30:00', 'B-003', 35, 12000.00, 'Monday'),
(3, '07:30:00', '08:30:00', 'B-004', 40, 8000.00, 'Monday');

-- Select the booking database
USE transbandung_booking;

-- Create bookings table
CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  schedule_id INT NOT NULL,
  booking_date DATE NOT NULL,
  seat_number INT NOT NULL,
  status ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample bookings
INSERT INTO bookings (user_id, schedule_id, booking_date, seat_number, status) VALUES
(2, 1, '2025-06-01', 15, 'confirmed'),
(3, 2, '2025-06-01', 7, 'confirmed'),
(2, 3, '2025-06-02', 10, 'pending');

-- Select the payment database
USE transbandung_payment;

-- Create payments table
CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  amount DECIMAL(10, 2) NOT NULL,
  payment_method ENUM('credit_card', 'bank_transfer', 'e-wallet', 'cash') NOT NULL,
  transaction_id VARCHAR(100),
  status ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
  payment_date TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample payments
INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status, payment_date) VALUES
(1, 10000.00, 'bank_transfer', 'TRX-00001', 'completed', '2025-05-30 14:30:00'),
(2, 10000.00, 'e-wallet', 'TRX-00002', 'completed', '2025-05-30 15:45:00'),
(3, 12000.00, 'credit_card', 'TRX-00003', 'pending', NULL);

-- Select the review database
USE transbandung_review;

-- Create reviews table
CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  booking_id INT NOT NULL,
  rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample reviews
INSERT INTO reviews (user_id, booking_id, rating, comment) VALUES
(2, 1, 4, 'Good service, bus was clean but a bit late.'),
(3, 2, 5, 'Excellent service! On time and very comfortable ride.');
